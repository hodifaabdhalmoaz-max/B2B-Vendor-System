<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Models\Size;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class B2BPhaseSixPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['locale' => 'en']);
    }

    private function profile(array $user = [], array $profile = []): ResellerProfile
    {
        $user = User::factory()->create(array_merge(['utype' => 'RES', 'is_active' => true, 'force_password_change' => false], $user));

        return ResellerProfile::create(array_merge(['user_id' => $user->id, 'status' => 'active', 'reservation_enabled' => true, 'reservation_timeout_minutes' => 30], $profile));
    }

    private function variant(int $stock = 5, array $product = [], array $variant = []): ProductVariant
    {
        $product = Product::factory()->create(array_merge(['regular_price' => '10.10', 'sale_price' => null, 'quantity' => 100], $product));
        $variant = ProductVariant::create(array_merge(['product_id' => $product->id, 'sku' => 'EXACT-'.$product->id, 'is_active' => true, 'price_adjustment' => '0.20'], $variant));
        InventoryItem::create(['product_variant_id' => $variant->id, 'stock_on_hand' => $stock, 'reserved_quantity' => 0]);

        return $variant;
    }

    private function line(ProductVariant $variant, int $quantity = 1): array
    {
        return ['product_variant_id' => (string) $variant->id, 'quantity' => (string) $quantity];
    }

    private function reserve(ResellerProfile $profile, ProductVariant $variant, string $key, int $quantity = 1): Reservation
    {
        return app(ReservationService::class)->create($profile, [['product_variant_id' => $variant->id, 'quantity' => $quantity]], $key);
    }

    public function test_portal_access_and_forced_password_change(): void
    {
        $this->get('/reseller/catalog')->assertRedirect('/login');
        foreach ([['utype' => 'USR'], ['utype' => 'ADM'], ['is_active' => false]] as $attributes) {
            $this->actingAs($this->profile($attributes)->user)->get('/reseller/catalog')->assertForbidden();
        }
        $this->actingAs($this->profile([], ['status' => 'inactive'])->user)->get('/reseller/catalog')->assertForbidden();
        $this->actingAs($this->profile(['force_password_change' => true])->user)->get('/reseller/catalog')->assertRedirect(route('reseller.password.edit'));
        $this->actingAs($this->profile()->user)->get('/reseller')->assertOk();
    }

    public function test_every_portal_route_keeps_security_middleware(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            if (str_starts_with($route->getName() ?? '', 'reseller.') && ! str_starts_with($route->getName(), 'reseller.password.')) {
                foreach (['auth', 'reseller', 'force.password.change', 'smart.throttle:user_dashboard'] as $middleware) {
                    $this->assertContains($middleware, $route->gatherMiddleware());
                }
            }
        }
    }

    public function test_detail_uses_exact_available_stock_and_hides_internals(): void
    {
        $variant = $this->variant();
        $color = Color::create(['name' => 'Coral', 'code' => '#ff0000']);
        $size = Size::create(['name' => 'Small', 'code' => 'S']);
        $variant->product->colors()->attach($color, ['quantity' => 80]);
        $variant->product->sizes()->attach($size, ['quantity' => 60]);
        $variant->update(['color_id' => $color->id, 'size_id' => $size->id]);
        $variant->inventoryItem->update(['reserved_quantity' => 4]);
        $this->actingAs($this->profile()->user)->get('/reseller/products/'.$variant->product_id)
            ->assertOk()->assertSee('data-available="1"', false)->assertSee('Coral')->assertSee('Small')
            ->assertSee('10.30')->assertDontSee('reserved_quantity')->assertDontSee('InventoryMovement');
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_sold_out_visibility_and_add_rejection(): void
    {
        $v = $this->variant();
        $v->inventoryItem->update(['reserved_quantity' => 5]);
        $this->actingAs($this->profile()->user)->get('/reseller/products/'.$v->product_id)->assertOk()->assertSee('data-available="0"', false)->assertSee(__('Out of stock'))->assertSee('disabled');
        $this->get('/reseller/catalog')->assertSee(__('All variants out of stock'));
        $this->post('/reseller/cart', $this->line($v))->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_cart_crud_is_intent_only_and_scoped_to_profile(): void
    {
        $a = $this->profile();
        $b = $this->profile();
        $v = $this->variant();
        $key = 'b2b_reservation_cart.'.$a->id.'.items';
        $this->actingAs($a->user)->post('/reseller/cart', $this->line($v, 3))->assertRedirect('/reseller/cart')->assertSessionHas($key, [$v->id => 3]);
        $this->get('/reseller/cart')->assertOk()->assertSee('30.90');
        $this->actingAs($b->user)->get('/reseller/cart')->assertViewHas('lines', []);
        $this->actingAs($a->user)->patch('/reseller/cart/'.$v->id, ['quantity' => '2'])->assertSessionHas($key, [$v->id => 2]);
        $this->delete('/reseller/cart/'.$v->id)->assertSessionHas($key, []);
        $this->post('/reseller/cart', $this->line($v))->assertRedirect();
        $this->delete('/reseller/cart')->assertSessionMissing('b2b_reservation_cart.'.$a->id);
        $this->assertSame(0, $v->inventoryItem->fresh()->reserved_quantity);
        $this->assertSame(5, $v->inventoryItem->fresh()->stock_on_hand);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_cart_rejects_invalid_quantities_variants_and_missing_inventory(): void
    {
        $v = $this->variant();
        $this->actingAs($this->profile()->user);
        foreach ([0, -1, 6, '1.5'] as $quantity) {
            $this->post('/reseller/cart', ['product_variant_id' => $v->id, 'quantity' => $quantity])->assertSessionHasErrors('quantity');
            $this->patch('/reseller/cart/'.$v->id, ['quantity' => $quantity])->assertSessionHasErrors('quantity');
        }
        $this->post('/reseller/cart', ['product_variant_id' => 99999, 'quantity' => 1])->assertSessionHasErrors('product_variant_id');
        $v->update(['is_active' => false]);
        $this->post('/reseller/cart', $this->line($v))->assertSessionHasErrors('product_variant_id');
        $v->update(['is_active' => true]);
        $v->inventoryItem->delete();
        $this->post('/reseller/cart', $this->line($v))->assertSessionHasErrors('product_variant_id');
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_atomic_submit_and_identical_post_replay_after_cart_clear(): void
    {
        $p = $this->profile();
        $a = $this->variant();
        $b = $this->variant();
        $this->actingAs($p->user)->post('/reseller/cart', $this->line($a, 2));
        $this->post('/reseller/cart', $this->line($b));
        $key = $this->get('/reseller/cart')->assertOk()->viewData('idempotencyKey');
        $this->assertSame($key, $this->get('/reseller/cart')->viewData('idempotencyKey'));
        $payload = ['idempotency_key' => $key, 'items' => [$this->line($a, 2), $this->line($b)], 'notes' => 'Marketing stock'];
        $response = $this->post('/reseller/reservations', $payload)->assertSessionHasNoErrors();
        $reservation = Reservation::sole();
        $response->assertRedirect(route('reseller.reservations.show', $reservation))->assertSessionMissing('b2b_reservation_cart.'.$p->id);
        $this->post('/reseller/reservations', $payload)->assertRedirect(route('reseller.reservations.show', $reservation));
        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseCount('reservation_items', 2);
        $this->assertSame(2, $a->inventoryItem->fresh()->reserved_quantity);
        $this->assertSame(1, $b->inventoryItem->fresh()->reserved_quantity);
        $this->assertSame('30.90', $reservation->total);
        $this->assertSame(2, InventoryMovement::where('type', 'reserve')->count());
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $response->assertSessionMissing('coupon')->assertSessionMissing('checkout');
    }

    public function test_failed_submission_preserves_cart_key_notes_and_rolls_back_all_stock(): void
    {
        $p = $this->profile();
        $a = $this->variant();
        $b = $this->variant();
        $this->actingAs($p->user)->post('/reseller/cart', $this->line($a, 2));
        $this->post('/reseller/cart', $this->line($b));
        $key = $this->get('/reseller/cart')->viewData('idempotencyKey');
        $b->inventoryItem->update(['stock_on_hand' => 0]);
        $this->from('/reseller/cart')->post('/reseller/reservations', ['idempotency_key' => $key, 'items' => [$this->line($a, 2), $this->line($b)], 'notes' => 'Keep this'])
            ->assertRedirect('/reseller/cart')->assertSessionHasErrors()->assertSessionHas('b2b_reservation_cart.'.$p->id.'.items', [$a->id => 2, $b->id => 1]);
        $this->get('/reseller/cart')->assertSee($key)->assertSee('Keep this')->assertSee(__('This line is unavailable at the requested quantity. Please update or remove it.'));
        $this->assertDatabaseCount('reservations', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertSame(0, $a->inventoryItem->fresh()->reserved_quantity);
    }

    public function test_tampered_fields_and_conflicting_keys_are_rejected(): void
    {
        $p = $this->profile();
        $v = $this->variant();
        $this->actingAs($p->user);
        $payload = ['idempotency_key' => 'safe-key', 'items' => [$this->line($v)]];
        foreach (['unit_price', 'total', 'status', 'reseller_profile_id', 'expires_at', 'snapshots', 'reservation_number'] as $field) {
            $this->post('/reseller/reservations', $payload + [$field => '1'])->assertSessionHasErrors($field);
        }
        $tampered = $payload;
        $tampered['items'][0]['unit_price'] = '1';
        $this->post('/reseller/reservations', $tampered)->assertSessionHasErrors('items.0');
        $this->assertDatabaseCount('reservations', 0);
        $this->post('/reseller/reservations', $payload)->assertRedirect();
        $this->assertSame('10.30', Reservation::sole()->reservationItems()->sole()->unit_price);
        $payload['items'][0]['quantity'] = 2;
        $this->post('/reseller/reservations', $payload)->assertSessionHasErrors('idempotency_key');
        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_history_is_owned_and_detail_uses_immutable_snapshots(): void
    {
        $a = $this->profile();
        $b = $this->profile();
        $v = $this->variant(5, ['name' => 'Original product']);
        $color = Color::create(['name' => 'Original color', 'code' => '#abcdef']);
        $v->update(['color_id' => $color->id]);
        $own = $this->reserve($a, $v, 'own');
        $other = $this->reserve($b, $v, 'other');
        $sku = $v->sku;
        $v->product->update(['name' => 'Renamed live product', 'regular_price' => '999.00']);
        $v->update(['sku' => 'RENAMED-LIVE-SKU']);
        $color->update(['name' => 'Renamed live color']);
        $this->actingAs($a->user)->get('/reseller/reservations')->assertOk()->assertSee($own->reservation_number)->assertDontSee($other->reservation_number);
        $this->get('/reseller/reservations/'.$own->id)->assertOk()->assertSee('Original product')->assertSee($sku)->assertSee('Original color')->assertSee('10.30')->assertDontSee('Renamed live');
        $this->get('/reseller/reservations/'.$other->id)->assertNotFound();
        $this->post('/reseller/reservations/'.$other->id.'/cancel')->assertNotFound();
        $this->get('/reseller')->assertOk()->assertViewHas('recentReservations', fn ($rows) => $rows->pluck('id')->all() === [$own->id]);
    }

    public function test_self_cancel_releases_once_and_rejects_every_nonpending_status(): void
    {
        $p = $this->profile();
        $v = $this->variant(30);
        $r = $this->reserve($p, $v, 'cancel');
        $this->actingAs($p->user)->post('/reseller/reservations/'.$r->id.'/cancel')->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('cancelled', $r->fresh()->status);
        $this->post('/reseller/reservations/'.$r->id.'/cancel')->assertSessionHasErrors('status');
        $this->assertSame(0, $v->inventoryItem->fresh()->reserved_quantity);
        $this->assertSame(1, InventoryMovement::where('type', 'release')->count());
        foreach (['confirmed', 'preparing', 'shipped', 'completed', 'expired'] as $status) {
            $other = $this->reserve($p, $v, $status);
            DB::table('reservations')->where('id', $other->id)->update(['status' => $status]);
            $this->post('/reseller/reservations/'.$other->id.'/cancel')->assertSessionHasErrors('status');
            $this->get('/reseller/reservations/'.$other->id)->assertDontSee(__('Cancel reservation'));
        }
        $this->assertSame(1, InventoryMovement::where('type', 'release')->count());
    }

    public function test_wishlist_is_product_level_unique_and_owned(): void
    {
        $a = $this->profile();
        $b = $this->profile();
        $v = $this->variant();
        $url = '/reseller/wishlist/'.$v->product_id;
        $this->actingAs($a->user)->post($url)->assertRedirect();
        $this->post($url)->assertRedirect();
        $this->assertDatabaseCount('wishlists', 1);
        $this->get('/reseller/wishlist')->assertOk()->assertSee($v->product->name);
        $this->get('/reseller/catalog')->assertSee(__('Remove from wishlist'));
        $this->actingAs($b->user)->get('/reseller/wishlist')->assertDontSee($v->product->name);
        $this->delete($url)->assertRedirect();
        $this->assertDatabaseCount('wishlists', 1);
        $this->actingAs($a->user)->delete($url)->assertRedirect();
        $this->assertDatabaseCount('wishlists', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertFalse(session()->has('cart'));
    }

    public function test_search_filters_pagination_and_active_variant_price_range(): void
    {
        $v = $this->variant(5, ['name' => 'Searchable Rocket', 'SKU' => 'BASE-ROCKET', 'sale_price' => '8.00', 'is_offer' => true]);
        $v->product->category->update(['name' => 'Rocket category']);
        $other = $this->variant(0, ['name' => 'Unrelated item', 'is_offer' => false]);
        $hidden = $this->variant(5, ['name' => 'Inactive product'], ['is_active' => false, 'sku' => 'HIDDEN-SKU']);
        Product::factory()->create(['name' => 'Legacy only']);
        $this->actingAs($this->profile()->user);
        foreach (['Searchable Rocket', 'BASE-ROCKET', $v->sku, 'Rocket category'] as $q) {
            $this->get('/reseller/search?'.http_build_query(['q' => $q]))->assertOk()->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [$v->product_id]);
        }
        $this->get('/reseller/search?q=HIDDEN-SKU')->assertViewHas('products', fn ($p) => $p->isEmpty());
        $this->get('/reseller/catalog?in_stock=1&offers=1&category='.$v->product->category_id)->assertViewHas('products', fn ($p) => $p->pluck('id')->all() === [$v->product_id]);
        $color = Color::create(['name' => 'Blue', 'code' => '#0000ff']);
        ProductVariant::create(['product_id' => $v->product_id, 'sku' => 'PRICIER', 'color_id' => $color->id, 'price_adjustment' => '2.15', 'is_active' => true]);
        $this->get('/reseller/catalog')->assertSee('8.20')->assertSee('10.15')->assertDontSee($hidden->product->name)->assertDontSee('Legacy only');
        for ($i = 0; $i < 12; $i++) {
            $this->variant();
        }
        $this->get('/reseller/catalog?page=2')->assertOk()->assertViewHas('products', fn ($p) => $p->currentPage() === 2 && $p->total() === 14 && $p->count() === 2);
    }

    public function test_most_requested_uses_meaningful_b2b_demand_only(): void
    {
        $p = $this->profile();
        $a = $this->variant(50);
        $b = $this->variant(50);
        $c = $this->variant(50);
        $this->reserve($p, $a, 'first', 3);
        $this->reserve($p, $b, 'second', 2);
        $cancelled = $this->reserve($p, $b, 'cancelled', 20);
        app(ReservationService::class)->cancel($cancelled);
        $expired = $this->reserve($p, $c, 'expired', 30);
        DB::table('reservations')->where('id', $expired->id)->update(['expires_at' => now()->subMinute()]);
        app(ReservationService::class)->expire($expired);
        $this->actingAs($p->user)->get('/reseller')->assertOk()->assertViewHas('popular', fn ($products) => $products->pluck('id')->all() === [$a->product_id, $b->product_id]);
    }

    public function test_two_carts_may_compete_but_only_one_submission_holds_the_final_unit(): void
    {
        $a = $this->profile();
        $b = $this->profile();
        $v = $this->variant(1);
        $this->actingAs($a->user)->post('/reseller/cart', $this->line($v))->assertSessionHasNoErrors();
        $this->actingAs($b->user)->post('/reseller/cart', $this->line($v))->assertSessionHasNoErrors();
        $this->assertSame(0, $v->inventoryItem->fresh()->reserved_quantity);
        $this->actingAs($a->user)->post('/reseller/reservations', ['idempotency_key' => 'winner', 'items' => [$this->line($v)]])->assertSessionHasNoErrors();
        $this->actingAs($b->user)->post('/reseller/reservations', ['idempotency_key' => 'loser', 'items' => [$this->line($v)]])->assertSessionHasErrors();
        $this->assertDatabaseCount('reservations', 1);
        $this->assertSame(1, $v->inventoryItem->fresh()->reserved_quantity);
    }

    public function test_image_download_allowlist_and_copy_description_escape_untrusted_content(): void
    {
        $filename = 'phase-six-'.bin2hex(random_bytes(8)).'.png';
        $path = public_path('uploads/products/'.$filename);
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6S9sAAAAASUVORK5CYII='));
        try {
            $v = $this->variant(5, ['image' => $filename, 'description' => '<b>Reusable copy</b><script>alert(1)</script>']);
            $this->actingAs($this->profile()->user)->get('/reseller/products/'.$v->product_id)->assertOk()->assertSee('Reusable copy')->assertSee('data-copy-description', false)->assertDontSee('<script>alert(1)</script>', false);
            $this->get('/reseller/products/'.$v->product_id.'/images/0')->assertDownload($filename);
            $this->get('/reseller/products/'.$v->product_id.'/images/999')->assertNotFound();
            $v->product->update(['image' => '../../.env']);
            $this->get('/reseller/products/'.$v->product_id.'/images/0')->assertNotFound();
        } finally {
            unlink($path);
        }
    }

    public function test_domain_eligibility_is_final_and_foreign_idempotency_key_cannot_replay(): void
    {
        $a = $this->profile();
        $b = $this->profile([], ['reservation_enabled' => false]);
        $v = $this->variant();
        $this->actingAs($b->user)->post('/reseller/cart', $this->line($v))->assertSessionHasNoErrors();
        $payload = ['idempotency_key' => 'owned-key', 'items' => [$this->line($v)]];
        $this->post('/reseller/reservations', $payload)->assertSessionHasErrors('reseller_profile')->assertSessionHas('b2b_reservation_cart.'.$b->id.'.items');
        $this->assertDatabaseCount('reservations', 0);
        $this->actingAs($a->user)->post('/reseller/reservations', $payload)->assertRedirect();
        $b->update(['reservation_enabled' => true]);
        $this->actingAs($b->user)->post('/reseller/reservations', $payload)->assertSessionHasErrors('idempotency_key');
        $this->assertDatabaseCount('reservations', 1);
        $this->assertSame(1, $v->inventoryItem->fresh()->reserved_quantity);
    }

    public function test_cart_reload_uses_current_data_without_changing_intent_or_key(): void
    {
        $p = $this->profile();
        $v = $this->variant();
        $this->actingAs($p->user)->post('/reseller/cart', $this->line($v, 3));
        $key = $this->get('/reseller/cart')->viewData('idempotencyKey');
        $v->product->update(['regular_price' => '20.10', 'name' => 'Updated cart name']);
        $v->update(['sku' => 'UPDATED-CART-SKU', 'is_active' => false]);
        $response = $this->get('/reseller/cart')->assertOk()->assertSee('Updated cart name')->assertSee('UPDATED-CART-SKU')->assertSee('60.90')->assertSee($key);
        $this->assertSame(3, $response->viewData('lines')[0]['quantity']);
        $this->assertTrue($response->viewData('lines')[0]['unavailable']);
        $v->inventoryItem->delete();
        $v->delete();
        $this->get('/reseller/cart')->assertOk()->assertSee(__('Unavailable variant'));
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_catalog_queries_are_bounded_and_history_paginates(): void
    {
        $p = $this->profile();
        $v = $this->variant(20);
        for ($i = 0; $i < 13; $i++) {
            $this->reserve($p, $v, 'page-'.$i);
            $this->variant();
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        $catalog = app(\App\Services\ResellerCatalogService::class)->catalog($p->user_id, []);
        $this->assertCount(12, $catalog['products']);
        $this->assertLessThanOrEqual(9, count(DB::getQueryLog()));
        DB::disableQueryLog();
        $this->actingAs($p->user)->get('/reseller/reservations?page=2')->assertOk()->assertViewHas('reservations', fn ($rows) => $rows->total() === 13 && $rows->count() === 1);
    }

    public function test_form_validation_preserves_submission_key_and_locale_copy(): void
    {
        $p = $this->profile();
        $v = $this->variant();
        $this->actingAs($p->user)->post('/reseller/cart', $this->line($v));
        $key = $this->get('/reseller/cart')->viewData('idempotencyKey');
        $this->from('/reseller/cart')->post('/reseller/reservations', ['idempotency_key' => $key, 'items' => [['product_variant_id' => $v->id, 'quantity' => 0]]])->assertSessionHasErrors('items.0.quantity');
        $this->get('/reseller/cart')->assertSee($key);
        $this->from('/reseller/cart')->post('/reseller/reservations', ['idempotency_key' => ['invalid'], 'items' => [$this->line($v)], 'notes' => ['invalid']])->assertSessionHasErrors(['idempotency_key', 'notes']);
        $this->get('/reseller/cart')->assertOk()->assertSee($key);
        $this->withSession(['locale' => 'ar'])->get('/reseller/cart')->assertOk()->assertSee('سلة الحجز')->assertSee('إرسال الحجز');
        $this->withSession(['locale' => 'en'])->get('/reseller/cart')->assertOk()->assertSee('Reservation cart')->assertSee('Submit reservation');
        $this->assertDatabaseCount('reservations', 0);
    }
}
