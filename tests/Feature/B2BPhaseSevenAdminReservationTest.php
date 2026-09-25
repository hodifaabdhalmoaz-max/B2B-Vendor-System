<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthAdmin;
use App\Models\Color;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Models\Size;
use App\Models\User;
use App\Services\AdminReservationService;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class B2BPhaseSevenAdminReservationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['locale' => 'en']);
        $this->admin = User::factory()->create(['utype' => 'ADM', 'is_active' => true]);
    }

    private function profile(array $attributes = [], array $identity = []): ResellerProfile
    {
        $user = User::factory()->create(array_merge(['utype' => 'RES', 'is_active' => true], $identity));

        return ResellerProfile::create(array_merge(['user_id' => $user->id, 'status' => 'active', 'reservation_enabled' => true, 'reservation_timeout_minutes' => 30], $attributes));
    }

    private function variant(): ProductVariant
    {
        $product = Product::factory()->create(['name' => 'Historical product', 'regular_price' => '10.10', 'sale_price' => null]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'ORIGINAL-'.$product->id, 'is_active' => true, 'price_adjustment' => '0.00']);
        InventoryItem::create(['product_variant_id' => $variant->id, 'stock_on_hand' => 100, 'reserved_quantity' => 0]);

        return $variant;
    }

    private function reserve(?ResellerProfile $profile = null, ?array $variants = null): Reservation
    {
        return app(ReservationService::class)->create($profile ?? $this->profile(), array_map(
            fn (ProductVariant $variant) => ['product_variant_id' => $variant->id, 'quantity' => 2],
            $variants ?? [$this->variant()]
        ), (string) Str::uuid(), 'Historical reseller note');
    }

    private function url(string $action = 'index', ?Reservation $reservation = null, array $query = []): string
    {
        return route('admin.b2b.reservations.'.$action, $reservation ? [$reservation] + $query : $query);
    }

    private function action(Reservation $reservation, string $action)
    {
        return $this->actingAs($this->admin)->from($this->url('show', $reservation))->post($this->url($action, $reservation));
    }

    private function moveTo(Reservation $reservation, string $state): void
    {
        foreach (['confirm' => Reservation::STATUS_CONFIRMED, 'preparing' => Reservation::STATUS_PREPARING, 'ship' => Reservation::STATUS_SHIPPED, 'complete' => Reservation::STATUS_COMPLETED] as $action => $target) {
            $this->action($reservation, $action)->assertSessionHasNoErrors();
            if ($target === $state) {
                break;
            }
        }
    }

    public function test_all_routes_require_active_admin_and_explicit_post_actions(): void
    {
        $reservation = $this->reserve();
        $routes = collect(app('router')->getRoutes()->getRoutes())->filter(fn ($route) => str_starts_with($route->getName() ?? '', 'admin.b2b.reservations.'));
        $this->assertCount(8, $routes);
        foreach ($routes as $route) {
            foreach (['auth', AuthAdmin::class, 'smart.throttle:admin'] as $middleware) {
                $this->assertContains($middleware, $route->gatherMiddleware());
            }
            $action = Str::afterLast($route->getName(), '.');
            $method = in_array($action, ['index', 'show']) ? 'get' : 'post';
            if ($method === 'post') {
                $this->assertSame(['POST'], $route->methods());
            }
            $url = $this->url($action, $action === 'index' ? null : $reservation);
            $this->{$method}($url)->assertRedirect(route('login'));
        }
        foreach ([['utype' => 'USR'], ['utype' => 'RES'], ['utype' => 'ADM', 'is_active' => false]] as $attributes) {
            $this->actingAs(User::factory()->create($attributes));
            foreach ($routes as $route) {
                $action = Str::afterLast($route->getName(), '.');
                $method = in_array($action, ['index', 'show']) ? 'get' : 'post';
                $this->{$method}($this->url($action, $action === 'index' ? null : $reservation))->assertForbidden();
            }
        }
        $this->actingAs($this->admin)->get($this->url())->assertOk();
        $this->get($this->url('show', $reservation))->assertOk();
        $this->get($this->url('cancel', $reservation))->assertStatus(405);
        $this->delete($this->url('show', $reservation))->assertStatus(405);
        $this->patch($this->url('show', $reservation), ['status' => 'completed'])->assertStatus(405);
        $this->assertDatabaseCount('inventory_movements', 1);
    }

    public function test_lifecycle_posts_require_csrf_tokens_outside_test_bypass(): void
    {
        $reservation = $this->reserve();
        $this->actingAs($this->admin);
        $this->app->instance('env', 'local');
        try {
            foreach (['confirm', 'preparing', 'ship', 'complete', 'cancel', 'expire'] as $action) {
                $this->post($this->url($action, $reservation))->assertStatus(419);
            }
        } finally {
            $this->app->instance('env', 'testing');
        }
        $this->assertSame('pending_review', $reservation->fresh()->status);
        $this->assertDatabaseCount('inventory_movements', 1);
    }

    public function test_grouped_search_reseller_status_and_inclusive_date_filters(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->profile(['business_name' => 'Needle business', 'whatsapp' => '777needle'], ['name' => 'Needle identity', 'username' => 'needle-login', 'mobile' => '733needle']);
        $first = $this->reserve($profile);
        $first->forceFill(['created_at' => '2026-09-10 00:00:00'])->save();
        $last = $this->reserve($profile);
        $last->forceFill(['created_at' => '2026-09-11 23:59:59'])->save();
        $confirmed = $this->reserve($profile);
        app(ReservationService::class)->confirm($confirmed);
        $confirmed->forceFill(['created_at' => '2026-09-10 12:00:00'])->save();
        $outside = $this->reserve($profile);
        $outside->forceFill(['created_at' => '2026-09-12 00:00:00'])->save();
        $other = $this->reserve($this->profile(['business_name' => 'Needle other']));
        $other->forceFill(['created_at' => '2026-09-10 12:00:00'])->save();
        $filters = ['status' => 'pending_review', 'reseller_profile_id' => $profile->id, 'start_date' => '2026-09-10', 'end_date' => '2026-09-11'];
        foreach (['Needle business', '777needle', 'Needle identity', 'needle-login', '733needle'] as $term) {
            $this->get($this->url(query: $filters + ['search' => $term]))->assertOk()
                ->assertViewHas('reservations', fn ($rows) => $rows->pluck('id')->all() === [$last->id, $first->id]);
        }
        $this->get($this->url(query: ['search' => $first->reservation_number]))->assertViewHas('reservations', fn ($rows) => $rows->pluck('id')->all() === [$first->id]);
        $this->get($this->url(query: ['end_date' => '2026-09-10']))->assertViewHas('reservations', fn ($rows) => $rows->total() === 3);
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $this->actingAs($this->admin)->from($this->url());
        foreach ([['status' => 'ordered'], ['search' => ['invalid']], ['reseller_profile_id' => 999], ['start_date' => '2026-02-30'], ['end_date' => 'bad'], ['page' => 0], ['overdue' => 5]] as $filter) {
            $this->get($this->url(query: $filter))->assertSessionHasErrors(array_key_first($filter));
        }
        $this->get($this->url(query: ['start_date' => '2026-09-11', 'end_date' => '2026-09-10']))->assertSessionHasErrors('end_date');
    }

    public function test_status_cards_use_one_grouped_query_and_respect_other_filters(): void
    {
        $profile = $this->profile();
        foreach (array_keys(AdminReservationService::statuses()) as $status) {
            $reservation = $this->reserve($profile);
            $reservation->update(['status' => $status]);
        }
        $this->reserve();
        $this->actingAs($this->admin);
        DB::enableQueryLog();
        $response = $this->get($this->url(query: ['status' => 'confirmed', 'reseller_profile_id' => $profile->id]));
        $queries = collect(DB::getQueryLog());
        DB::disableQueryLog();
        $response->assertOk()->assertViewHas('counts', fn ($counts) => $counts['all'] === 7 && count(array_filter($counts, fn ($n) => (int) $n === 1)) === 7)
            ->assertViewHas('reservations', fn ($rows) => $rows->total() === 1);
        $this->assertCount(1, $queries->filter(fn ($query) => str_contains(strtolower($query['query']), 'group by "status"')));
        $this->get($this->url(query: ['status' => 'all']))->assertViewHas('counts', fn ($counts) => $counts['all'] === 8 && (int) $counts['pending_review'] === 2);
    }

    public function test_pagination_retains_filters_and_uses_decimal_money(): void
    {
        $profile = $this->profile(['business_name' => 'Paginated']);
        $variant = $this->variant();
        for ($i = 0; $i < 22; $i++) {
            $this->reserve($profile, [$variant]);
        }
        $query = ['search' => 'Paginated', 'reseller_profile_id' => $profile->id, 'status' => 'pending_review'];
        $this->actingAs($this->admin)->get($this->url(query: $query))->assertOk()
            ->assertViewHas('reservations', fn ($rows) => $rows->count() === 20 && $rows->total() === 22 && str_contains($rows->nextPageUrl(), 'search=Paginated') && str_contains($rows->nextPageUrl(), 'reseller_profile_id='.$profile->id))
            ->assertSee('20.20');
        $this->get($this->url(query: $query + ['page' => 2]))->assertViewHas('reservations', fn ($rows) => $rows->count() === 2);
    }

    public function test_detail_preserves_snapshots_and_displays_reseller_context_without_secrets(): void
    {
        $variant = $this->variant();
        $color = Color::create(['name' => 'Original coral', 'code' => '#ff0000']);
        $size = Size::create(['name' => 'Original small', 'code' => 'S']);
        $variant->update(['color_id' => $color->id, 'size_id' => $size->id]);
        $profile = $this->profile(['business_name' => 'Historical business', 'whatsapp' => '771234567', 'governorate' => 'Aden', 'group_name' => 'Group A']);
        $reservation = $this->reserve($profile, [$variant]);
        $sku = $variant->sku;
        $variant->product->update(['name' => 'Renamed current product', 'regular_price' => '999.99']);
        $variant->update(['sku' => 'RENAMED-SKU']);
        $color->update(['name' => 'Renamed color']);
        $size->update(['name' => 'Renamed size']);
        $this->actingAs($this->admin)->get($this->url('show', $reservation))->assertOk()
            ->assertSee('Historical product')->assertSee($sku)->assertSee('Original coral')->assertSee('Original small')->assertSee('20.20')
            ->assertSee('Historical business')->assertSee('771234567')->assertSee('Aden')->assertSee('Group A')->assertSee('Historical reseller note')
            ->assertDontSee('Renamed current product')->assertDontSee('Renamed color')->assertDontSee('Renamed size')->assertDontSee('999.99')
            ->assertDontSee($profile->user->password)->assertDontSee($reservation->idempotency_key)->assertDontSee($reservation->request_fingerprint);
    }

    public function test_confirm_preparing_ship_and_replays_hold_inventory_without_movements(): void
    {
        $variant = $this->variant();
        $reservation = $this->reserve(variants: [$variant]);
        foreach (['confirm' => 'confirmed', 'preparing' => 'preparing', 'ship' => 'shipped'] as $action => $status) {
            for ($i = 0; $i < 2; $i++) {
                $this->action($reservation, $action)->assertRedirect($this->url('show', $reservation))->assertSessionHasNoErrors()->assertSessionHas('status');
                $this->assertSame($status, $reservation->fresh()->status);
                $this->assertDatabaseCount('inventory_movements', 1);
                $this->assertDatabaseHas('inventory_items', ['product_variant_id' => $variant->id, 'stock_on_hand' => 100, 'reserved_quantity' => 2]);
            }
        }
        $this->assertNotNull($reservation->fresh()->confirmed_at);
    }

    public function test_complete_consumes_every_item_exactly_once_with_admin_actor(): void
    {
        $variants = [$this->variant(), $this->variant()];
        $reservation = $this->reserve(variants: $variants);
        $this->moveTo($reservation, 'shipped');
        for ($i = 0; $i < 2; $i++) {
            $this->action($reservation, 'complete')->assertSessionHasNoErrors();
            $this->assertSame('completed', $reservation->fresh()->status);
            $this->assertDatabaseCount('inventory_movements', 4);
            foreach ($variants as $variant) {
                $this->assertDatabaseHas('inventory_items', ['product_variant_id' => $variant->id, 'stock_on_hand' => 98, 'reserved_quantity' => 0]);
                $this->assertSame(1, InventoryMovement::where('product_variant_id', $variant->id)->where('type', InventoryMovement::TYPE_ORDER_COMPLETED)->where('actor_user_id', $this->admin->id)->count());
            }
        }
    }

    public function test_admin_cancels_pending_confirmed_and_preparing_once(): void
    {
        foreach (['pending_review', 'confirmed', 'preparing'] as $status) {
            $variant = $this->variant();
            $reservation = $this->reserve(variants: [$variant]);
            if ($status !== 'pending_review') {
                $this->moveTo($reservation, $status);
            }
            $this->action($reservation, 'cancel')->assertSessionHasNoErrors();
            $this->action($reservation, 'cancel')->assertSessionHasNoErrors(); // Existing service idempotency is preserved.
            $current = $reservation->fresh();
            $this->assertSame('cancelled', $current->status);
            $this->assertNotNull($current->cancelled_at);
            $this->assertNotNull($current->released_at);
            $this->assertDatabaseHas('inventory_items', ['product_variant_id' => $variant->id, 'stock_on_hand' => 100, 'reserved_quantity' => 0]);
            $this->assertSame(1, InventoryMovement::where('product_variant_id', $variant->id)->where('type', 'release')->where('actor_user_id', $this->admin->id)->count());
        }
    }

    public function test_invalid_cancellation_and_stale_actions_leave_current_state_intact(): void
    {
        foreach (['shipped', 'completed', 'expired'] as $status) {
            $reservation = $this->reserve();
            if ($status === 'expired') {
                $reservation->update(['expires_at' => now()->subMinute()]);
                app(ReservationService::class)->expire($reservation);
            } else {
                $this->moveTo($reservation, $status);
            }
            $before = InventoryMovement::count();
            $this->action($reservation, 'cancel')->assertSessionHasErrors('status');
            $this->assertSame($status, $reservation->fresh()->status);
            $this->assertDatabaseCount('inventory_movements', $before);
        }
        $stale = $this->reserve();
        $this->actingAs($this->admin)->get($this->url('show', $stale))->assertSee('Confirm reservation');
        app(ReservationService::class)->cancel($stale->id);
        $before = InventoryMovement::count();
        $this->action($stale, 'confirm')->assertSessionHasErrors('status');
        $this->get($this->url('show', $stale))->assertSee('Invalid reservation state transition.');
        $this->assertSame('cancelled', $stale->fresh()->status);
        $this->assertDatabaseCount('inventory_movements', $before);
    }

    public function test_due_only_expiration_overdue_filter_and_read_only_get(): void
    {
        $this->freezeTime();
        $due = $this->reserve();
        $due->update(['expires_at' => now()]);
        $future = $this->reserve();
        $confirmed = $this->reserve();
        app(ReservationService::class)->confirm($confirmed);
        $confirmed->update(['expires_at' => now()->subDay()]);
        $noTimeout = $this->reserve();
        $noTimeout->update(['expires_at' => null]);
        $this->actingAs($this->admin)->get($this->url('show', $due))->assertOk()->assertSee('Awaiting expiration processing')->assertSee($this->url('expire', $due), false);
        $this->get($this->url(query: ['overdue' => 1]))->assertViewHas('reservations', fn ($rows) => $rows->pluck('id')->all() === [$due->id]);
        $this->assertSame('pending_review', $due->fresh()->status);
        $this->assertDatabaseCount('inventory_movements', 4);
        foreach ([$future, $confirmed, $noTimeout] as $notDue) {
            $this->get($this->url('show', $notDue))->assertDontSee($this->url('expire', $notDue), false);
            $this->action($notDue, 'expire')->assertSessionHasErrors('status');
        }
        $this->action($due, 'expire')->assertSessionHasNoErrors();
        $this->assertSame('expired', $due->fresh()->status);
        $this->assertNotNull($due->fresh()->released_at);
        $this->assertDatabaseHas('inventory_movements', ['reference_type' => 'reservation', 'reference_id' => $due->id, 'type' => 'release', 'actor_user_id' => $this->admin->id]);
        $this->action($due, 'expire')->assertSessionHasErrors('status');
        $this->assertDatabaseCount('inventory_movements', 5);
    }

    public function test_detail_actions_follow_exact_states_and_no_item_edit_forms_exist(): void
    {
        $reservation = $this->reserve();
        $expected = ['pending_review' => ['confirm', 'cancel'], 'confirmed' => ['preparing', 'cancel'], 'preparing' => ['ship', 'cancel'], 'shipped' => ['complete'], 'completed' => [], 'cancelled' => [], 'expired' => []];
        foreach ($expected as $state => $actions) {
            $reservation->update(['status' => $state]);
            $response = $this->actingAs($this->admin)->get($this->url('show', $reservation))->assertOk();
            foreach (['confirm', 'preparing', 'ship', 'complete', 'cancel', 'expire'] as $action) {
                if (in_array($action, $actions)) {
                    $response->assertSee('action="'.$this->url($action, $reservation).'"', false);
                } else {
                    $response->assertDontSee('action="'.$this->url($action, $reservation).'"', false);
                }
            }
            $response->assertDontSee('name="quantity"', false)->assertDontSee('name="unit_price"', false)->assertDontSee('name="status"', false);
        }
    }

    public function test_ledger_is_scoped_read_only_and_b2c_orders_are_isolated(): void
    {
        $reservation = $this->reserve();
        $other = $this->reserve();
        $own = InventoryMovement::where('reference_id', $reservation->id)->firstOrFail();
        $foreign = InventoryMovement::where('reference_id', $other->id)->firstOrFail();
        $manual = InventoryMovement::create(['product_variant_id' => $own->product_variant_id, 'type' => 'adjustment', 'quantity' => 1, 'reference_type' => 'manual', 'reference_id' => $reservation->id, 'idempotency_key' => 'manual-example']);
        $order = Order::factory()->create(['name' => 'B2C-ONLY-CUSTOMER']);
        $this->actingAs($this->admin)->get($this->url('show', $reservation))->assertOk()
            ->assertViewHas('movements', fn ($rows) => $rows->pluck('id')->all() === [$own->id])
            ->assertSee('data-movement-id="'.$own->id.'"', false)
            ->assertDontSee('data-movement-id="'.$foreign->id.'"', false)->assertDontSee('data-movement-id="'.$manual->id.'"', false)
            ->assertDontSee('B2C-ONLY-CUSTOMER');
        $this->get($this->url())->assertViewHas('reservations', fn ($rows) => $rows->total() === 2)->assertDontSee('B2C-ONLY-CUSTOMER');
        $this->get(route('admin.orders'))->assertOk()->assertViewHas('orders', fn ($rows) => $rows->pluck('id')->all() === [$order->id])
            ->assertDontSee($reservation->reservation_number)->assertDontSee($other->reservation_number);
        $this->assertTrue($order->fresh()->is($order));
        $this->assertSame(\App\Http\Controllers\Admin\OrderController::class.'@index', app('router')->getRoutes()->getByName('admin.orders')->getActionName());
        $this->assertFalse(collect(app('router')->getRoutes()->getRoutes())->contains(fn ($route) => str_starts_with($route->getName() ?? '', 'admin.b2b.') && array_intersect(['DELETE', 'PATCH', 'PUT'], $route->methods())));
        $this->assertDatabaseCount('inventory_movements', 3);
    }

    public function test_reseller_admin_links_filter_to_profile(): void
    {
        $profile = $this->profile();
        $own = $this->reserve($profile);
        $this->reserve();
        $url = $this->url(query: ['reseller_profile_id' => $profile->id]);
        $this->actingAs($this->admin)->get(route('admin.resellers.index'))->assertOk()->assertSee($url, false);
        $this->get(route('admin.resellers.edit', $profile->user_id))->assertOk()->assertSee($url, false);
        $this->get($url)->assertViewHas('reservations', fn ($rows) => $rows->pluck('id')->all() === [$own->id]);
    }

    public function test_list_and_detail_query_counts_are_bounded_as_data_grows(): void
    {
        $reservation = $this->reserve();
        $this->actingAs($this->admin);
        $countQueries = function (string $url): int {
            DB::enableQueryLog();
            DB::flushQueryLog();
            $this->get($url)->assertOk();
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $count;
        };
        // Warm the layout/translator before comparing the number of database queries.
        $this->get($this->url())->assertOk();
        $smallList = $countQueries($this->url());
        $smallDetail = $countQueries($this->url('show', $reservation));
        for ($i = 0; $i < 8; $i++) {
            $this->reserve();
        }
        $manyItems = $this->reserve(variants: [$this->variant(), $this->variant(), $this->variant(), $this->variant()]);
        $largeList = $countQueries($this->url());
        $largeDetail = $countQueries($this->url('show', $manyItems));
        $this->assertLessThanOrEqual($smallList + 1, $largeList);
        $this->assertLessThanOrEqual($smallDetail + 1, $largeDetail);
        $this->assertLessThanOrEqual(14, $largeList);
        $this->assertLessThanOrEqual(14, $largeDetail);
    }

    public function test_english_and_arabic_labels_render_and_dictionaries_align(): void
    {
        $reservation = $this->reserve();
        $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get($this->url('show', $reservation))->assertSee('B2B Reservations')->assertSee('Inventory movements');
        $this->withSession(['locale' => 'ar'])->get($this->url('show', $reservation))->assertSee('حجوزات التاجرات')->assertSee('حركات المخزون');
        $ar = json_decode(file_get_contents(base_path('lang/ar.json')), true, 512, JSON_THROW_ON_ERROR);
        $en = json_decode(file_get_contents(base_path('lang/en.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEqualsCanonicalizing(array_keys($ar), array_keys($en));
        $this->withSession(['locale' => 'ar'])->get(route('admin.resellers.index'))->assertSee('حجوزات التاجرات');
    }

    public function test_database_errors_are_reported_without_exposing_sql_to_admin(): void
    {
        $reservation = $this->reserve();
        $this->mock(ReservationService::class, function ($mock) use ($reservation): void {
            $mock->shouldReceive('confirm')->once()->withArgs(fn ($current, $actor) => $current->id === $reservation->id && $actor === $this->admin->id)
                ->andThrow(new \Illuminate\Database\QueryException('sqlite', 'secret-sql-statement', [], new \PDOException('private-database-details')));
        });
        $this->action($reservation, 'confirm')->assertRedirect($this->url('show', $reservation))->assertSessionHasErrors('reservation');
        $this->get($this->url('show', $reservation))->assertSee('Unable to process the reservation. Refresh its details and try again.')
            ->assertDontSee('secret-sql-statement')->assertDontSee('private-database-details');
        $this->assertSame('pending_review', $reservation->fresh()->status);
        $this->assertDatabaseCount('inventory_movements', 1);
    }
}
