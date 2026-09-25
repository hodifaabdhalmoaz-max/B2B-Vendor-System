<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ResellerNotification;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ProductVariantService;
use App\Services\ReservationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class B2BPhaseNineStabilizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName(), 'Fresh schema requires SQLite :memory:.');
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->withSession(['locale' => 'en']);
        $this->assertTrue(Model::preventsLazyLoading());
        // Laravel normally exempts single-row hydration; exercise those paths too.
        ProductVariant::retrieved(function (ProductVariant $variant) {
            $variant->preventsLazyLoading = true;
        });
    }

    private function profile(): ResellerProfile
    {
        $user = User::factory()->create(['utype' => 'RES', 'is_active' => true, 'force_password_change' => false]);

        return ResellerProfile::create(['user_id' => $user->id, 'status' => 'active', 'reservation_enabled' => true, 'reservation_timeout_minutes' => 30]);
    }

    private function variant(?Product $product = null, ?Color $color = null): ProductVariant
    {
        $product ??= Product::factory()->create(['regular_price' => '10.10', 'sale_price' => null]);
        if ($color) {
            $product->colors()->syncWithoutDetaching([$color->id]);
        }
        $variant = app(ProductVariantService::class)->createVariant($product, [
            'color_id' => $color?->id, 'size_id' => null, 'sku' => 'P9-'.$product->id.'-'.($color?->id ?? 0),
            'price_adjustment' => '0.20', 'is_active' => true,
        ]);
        app(InventoryService::class)->stockIn($variant, 10, 'stock-'.$variant->id);

        return $variant;
    }

    private function reserve(ResellerProfile $profile, array $variants, string $key): Reservation
    {
        return app(ReservationService::class)->create($profile, array_map(fn ($v) => ['product_variant_id' => $v->id, 'quantity' => 2], $variants), $key);
    }

    public function test_fresh_schema_verifies_without_writes_and_migrations_are_current(): void
    {
        DB::enableQueryLog();
        $this->assertSame(0, Artisan::call('b2b:verify-schema'), Artisan::output());
        foreach (DB::getQueryLog() as $query) {
            $this->assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|alter|drop|create|replace|truncate)\b/i', $query['query']);
        }
        DB::disableQueryLog();
        $this->artisan('migrate:status')->assertSuccessful();
    }

    public function test_recorded_migration_with_missing_balance_column_is_drift_not_repaired(): void
    {
        Schema::table('inventory_movements', fn (Blueprint $table) => $table->dropColumn('stock_delta'));
        $this->artisan('b2b:verify-schema')->expectsOutput('[MISSING] inventory_movements.stock_delta')->assertFailed();
        $this->assertFalse(Schema::hasColumn('inventory_movements', 'stock_delta'));
        $this->assertDatabaseHas('migrations', ['migration' => '2026_09_24_000001_add_inventory_movement_balances']);
    }

    public function test_missing_unique_constraint_and_pending_migration_fail_verification(): void
    {
        Schema::table('notifications', fn (Blueprint $table) => $table->dropUnique(['event_key']));
        DB::table('migrations')->where('migration', '2026_09_26_000001_add_reservation_event_key_to_notifications')->delete();
        $this->artisan('b2b:verify-schema')
            ->expectsOutput('[MISSING INDEX] notifications.event_key unique')
            ->expectsOutput('[PENDING MIGRATION] migration 2026_09_26_000001_add_reservation_event_key_to_notifications')->assertFailed();
    }

    public function test_partial_unique_index_does_not_satisfy_durable_event_uniqueness(): void
    {
        Schema::table('notifications', fn (Blueprint $table) => $table->dropUnique(['event_key']));
        DB::statement('CREATE UNIQUE INDEX partial_events ON notifications(event_key) WHERE read_at IS NULL');
        $this->artisan('b2b:verify-schema')->expectsOutput('[MISSING INDEX] notifications.event_key unique')->assertFailed();
    }

    public function test_missing_table_and_incompatible_delta_type_are_reported(): void
    {
        Schema::drop('notifications');
        Schema::table('inventory_movements', fn (Blueprint $table) => $table->string('stock_delta')->nullable()->change());
        $this->withoutMockingConsoleOutput();
        $output = new \Symfony\Component\Console\Output\BufferedOutput;
        $this->assertSame(1, Artisan::call('b2b:verify-schema', [], $output));
        $text = $output->fetch();
        $this->assertStringContainsString('[MISSING] notifications', $text);
        $this->assertStringContainsString('[INCOMPATIBLE TYPE] inventory_movements.stock_delta signed delta', $text);
    }

    public function test_complete_http_workflow_preserves_snapshots_ledger_and_notifications(): void
    {
        $profile = $this->profile();
        $color = Color::create(['name' => 'Original blue', 'code' => 'BLUE']);
        $variant = $this->variant(color: $color);
        $product = Product::findOrFail($variant->product_id);
        $originalName = $product->name;
        $originalSku = $variant->sku;
        $this->actingAs($profile->user)->get('/reseller/catalog')->assertOk()->assertSee($originalName);
        $line = ['product_variant_id' => $variant->id, 'quantity' => 2];
        $this->post('/reseller/cart', $line)->assertSessionHasNoErrors();
        $key = $this->get('/reseller/cart')->assertOk()->viewData('idempotencyKey');
        $payload = ['idempotency_key' => $key, 'items' => [$line]];
        $this->post('/reseller/reservations', $payload)->assertSessionHasNoErrors();
        $reservation = Reservation::sole();
        $this->post('/reseller/reservations', $payload)->assertSessionHasNoErrors();
        $this->assertSame('pending_review', $reservation->status);
        $this->assertSame('10.30', $reservation->reservationItems()->sole()->unit_price);
        $product->update(['name' => 'Changed product', 'regular_price' => '99.00']);
        $color->update(['name' => 'Changed color']);
        app(ProductVariantService::class)->updateVariant($product, $variant, ['sku' => 'CHANGED-SKU', 'color_id' => $color->id, 'size_id' => null, 'price_adjustment' => '0.20', 'is_active' => true]);
        $this->get(route('reseller.reservations.show', $reservation))->assertOk()->assertSee($originalName)->assertSee($originalSku)->assertSee('Original blue');
        $admin = User::factory()->create(['utype' => 'ADM', 'is_active' => true]);
        $this->actingAs($admin)->get(route('admin.b2b.reservations.show', $reservation))->assertOk()->assertSee($originalName)->assertSee($originalSku)->assertSee('Original blue');
        foreach (['confirm' => 'confirmed', 'preparing' => 'preparing', 'ship' => 'shipped', 'complete' => 'completed'] as $action => $status) {
            foreach ([1, 2] as $replay) {
                $this->post(route('admin.b2b.reservations.'.$action, $reservation))->assertSessionHasNoErrors();
                $this->assertSame($status, $reservation->fresh()->status);
            }
        }
        $this->assertDatabaseHas('inventory_items', ['product_variant_id' => $variant->id, 'stock_on_hand' => 8, 'reserved_quantity' => 0]);
        $ledger = InventoryMovement::orderBy('id')->get();
        $this->assertSame(['stock_in', 'reserve', 'order_completed'], $ledger->pluck('type')->all());
        $this->assertSame([10, 0, -2], $ledger->pluck('stock_delta')->all());
        $this->assertSame([0, 2, -2], $ledger->pluck('reserved_delta')->all());
        $this->assertSame($admin->id, $ledger->last()->actor_user_id);
        $this->assertSame(['reservation_created', 'reservation_confirmed', 'reservation_preparing', 'reservation_shipped', 'reservation_completed'], ResellerNotification::orderBy('created_at')->orderBy('rowid')->pluck('type')->all());
        $this->assertSame(5, ResellerNotification::where('notifiable_id', $profile->user_id)->count());
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_cancel_and_expiry_release_stock_once_with_real_commits(): void
    {
        $profile = $this->profile();
        foreach (['cancel', 'expire'] as $action) {
            $variant = $this->variant();
            $reservation = $this->reserve($profile, [$variant], $action);
            for ($replay = 0; $replay < 2; $replay++) {
                if ($action === 'cancel') {
                    $response = $this->actingAs($profile->user)->post(route('reseller.reservations.cancel', $reservation));
                    if ($replay === 0) {
                        $response->assertSessionHasNoErrors();
                    } else {
                        // Portal cancellation remains pending-only; the domain replay is harmless.
                        $response->assertSessionHasErrors();
                        app(ReservationService::class)->cancel($reservation, $profile->user_id);
                    }
                } else {
                    $this->travel(31)->minutes();
                    $this->artisan('reservations:expire')->assertSuccessful();
                }
            }
            $this->assertSame($action === 'cancel' ? 'cancelled' : 'expired', $reservation->fresh()->status);
            $this->assertDatabaseHas('inventory_items', ['product_variant_id' => $variant->id, 'stock_on_hand' => 10, 'reserved_quantity' => 0]);
            $release = InventoryMovement::where('product_variant_id', $variant->id)->where('type', 'release')->sole();
            $this->assertSame(0, $release->stock_delta);
            $this->assertSame(-2, $release->reserved_delta);
            $this->assertSame(1, ResellerNotification::where('event_key', 'reservation:'.$reservation->id.':'.$reservation->fresh()->status)->count());
        }
    }

    public function test_real_inventory_endpoint_writes_complete_balances_and_admin_actor(): void
    {
        $variant = $this->variant();
        $admin = User::factory()->create(['utype' => 'ADM', 'is_active' => true]);
        $this->actingAs($admin);
        foreach ([['stock_in', 3, 3, 13], ['stock_out', 2, -2, 11], ['adjustment', 8, -3, 8]] as [$operation, $quantity, $delta, $balance]) {
            $this->post(route('admin.products.variants.inventory.store', [$variant->product_id, $variant]), ['operation' => $operation, 'quantity' => $quantity, 'idempotency_key' => $operation])->assertSessionHasNoErrors();
            $movement = InventoryMovement::where('idempotency_key', $operation)->sole();
            $this->assertSame([$delta, 0, $balance, 0], [$movement->stock_delta, $movement->reserved_delta, $movement->stock_on_hand_after, $movement->reserved_quantity_after]);
            $this->assertSame($admin->id, $movement->actor_user_id);
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $movement->request_fingerprint);
        }
    }

    public function test_variant_view_detached_dimensions_and_identity_update_are_strict_loading_safe(): void
    {
        $color = Color::create(['name' => 'Detached blue', 'code' => 'BLUE']);
        $variant = $this->variant(color: $color);
        $product = Product::findOrFail($variant->product_id);
        $product->colors()->detach();
        $admin = User::factory()->create(['utype' => 'ADM', 'is_active' => true]);
        $this->actingAs($admin)->get(route('admin.products.variants.index', $product))->assertOk()->assertSee('10.30')->assertSee('Detached blue');
        $this->put(route('admin.products.variants.update', [$product, $variant]), ['color_id' => null, 'size_id' => null, 'sku' => $variant->sku, 'price_adjustment' => '0.20', 'is_active' => true])->assertSessionHasErrors('variant');
        $this->assertSame($color->id, $variant->fresh()->color_id);
    }

    public function test_all_critical_pages_have_bounded_query_growth(): void
    {
        $profile = $this->profile();
        $variant = $this->variant();
        $small = $this->reserve($profile, [$variant], 'small');
        $admin = User::factory()->create(['utype' => 'ADM', 'is_active' => true]);
        $measure = function (User $user, Reservation $reservation) use ($variant): array {
            $this->actingAs($user);
            $urls = $user->utype === 'ADM' ? [
                'admin list' => route('admin.b2b.reservations.index'), 'admin detail' => route('admin.b2b.reservations.show', $reservation), 'variants' => route('admin.products.variants.index', $variant->product_id),
            ] : ['catalog' => '/reseller/catalog', 'reseller list' => '/reseller/reservations', 'reseller detail' => route('reseller.reservations.show', $reservation), 'notifications' => '/reseller/notifications'];
            $counts = [];
            foreach ($urls as $label => $url) {
                $this->get($url)->assertOk();
                DB::enableQueryLog();
                DB::flushQueryLog();
                $this->get($url)->assertOk();
                $counts[$label] = count(DB::getQueryLog());
                DB::disableQueryLog();
            }

            return $counts;
        };
        $before = $measure($profile->user, $small) + $measure($admin, $small);
        $variants = [];
        $product = Product::findOrFail($variant->product_id);
        for ($i = 0; $i < 6; $i++) {
            $variants[] = $this->variant();
            $this->reserve($profile, [$variants[$i]], 'growth-'.$i);
            $this->variant($product, Color::create(['name' => 'Color '.$i, 'code' => 'C'.$i]));
        }
        $large = $this->reserve($profile, $variants, 'large');
        $after = $measure($profile->user, $large) + $measure($admin, $large);
        foreach ($before as $page => $count) {
            $this->assertLessThanOrEqual($count + 1, $after[$page], $page.' query growth');
        }
        fwrite(STDOUT, PHP_EOL.'Phase 9 query counts: '.json_encode(['small' => $before, 'large' => $after]).PHP_EOL);
    }

    public function test_notification_read_posts_enforce_csrf_and_get_never_marks_read(): void
    {
        $profile = $this->profile();
        $this->reserve($profile, [$this->variant()], 'csrf');
        $notification = ResellerNotification::sole();
        $this->actingAs($profile->user)->get('/reseller/notifications')->assertOk();
        $this->assertNull($notification->fresh()->read_at);
        $this->app->instance('env', 'local');
        try {
            $this->post(route('reseller.notifications.read', $notification))->assertStatus(419);
            $this->post(route('reseller.notifications.read-all'))->assertStatus(419);
        } finally {
            $this->app->instance('env', 'testing');
        }
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_explicit_routes_and_controller_domain_boundaries_remain_separate(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());
        $this->assertFalse($routes->contains(fn ($route) => str_contains($route->uri(), 'reseller') && preg_match('/signup|register/', $route->uri())));
        $this->assertFalse($routes->contains(fn ($route) => str_contains($route->uri(), 'reservations') && preg_match('/status|update/', $route->uri())));
        foreach (['Admin/B2BReservationController', 'Reseller/ReservationController', 'Admin/InventoryController'] as $controller) {
            // Narrow source guard complements real HTTP ledger and Order-isolation tests.
            $source = file_get_contents(app_path('Http/Controllers/'.$controller.'.php'));
            $this->assertStringNotContainsString('App\\Models\\Order', $source);
            $this->assertStringNotContainsString('App\\Services\\OrderService', $source);
            $this->assertStringNotContainsString('App\\Models\\InventoryItem', $source);
            $service = match ($controller) {
                'Admin/InventoryController' => 'InventoryService',
                'Reseller/ReservationController' => 'ResellerPortalService',
                default => 'ReservationService',
            };
            $this->assertStringContainsString($service, $source);
        }
        $this->assertStringContainsString('ReservationService', file_get_contents(app_path('Services/ResellerPortalService.php')));
    }
}
