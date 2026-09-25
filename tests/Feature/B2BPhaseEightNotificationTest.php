<?php

namespace Tests\Feature;

use App\Events\B2B\ReservationCreated;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ResellerNotification;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Models\User;
use App\Repositories\ResellerNotificationRepository;
use App\Services\ResellerNotificationService;
use App\Services\ReservationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class B2BPhaseEightNotificationTest extends TestCase
{
    // Real commits are essential: RefreshDatabase wraps tests in a synthetic transaction.
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName(), 'Fresh schema requires SQLite :memory:.');
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
    }

    private function profile(array $attributes = []): ResellerProfile
    {
        $user = User::factory()->create(array_merge(['utype' => 'RES', 'is_active' => true, 'force_password_change' => false], $attributes));

        return ResellerProfile::create(['user_id' => $user->id, 'status' => 'active', 'reservation_enabled' => true, 'reservation_timeout_minutes' => 30]);
    }

    private function reserve(?ResellerProfile $profile = null): Reservation
    {
        $product = Product::factory()->create(['regular_price' => '10.00', 'sale_price' => null]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'N-'.$product->id, 'is_active' => true, 'price_adjustment' => '0.00']);
        InventoryItem::create(['product_variant_id' => $variant->id, 'stock_on_hand' => 5, 'reserved_quantity' => 0]);

        return app(ReservationService::class)->create($profile ?? $this->profile(), [['product_variant_id' => $variant->id, 'quantity' => 2]], 'create-'.$variant->id);
    }

    private function seedNotification(User $user, array $attributes = []): ResellerNotification
    {
        return ResellerNotification::create(array_merge([
            'id' => (string) Str::uuid(), 'type' => 'reservation_created',
            'notifiable_type' => $user->getMorphClass(), 'notifiable_id' => $user->id,
            'data' => ['reservation_number' => 'RSV-TEST'],
        ], $attributes));
    }

    public function test_real_commits_produce_each_event_once_and_replays_do_not_emit(): void
    {
        $seen = [];
        Event::listen('App\\Events\\B2B\\*', function ($name, $payload) use (&$seen) {
            $this->assertSame(0, DB::transactionLevel());
            $seen[] = $payload[0]->name();
        });
        $profile = $this->profile();
        $r = $this->reserve($profile);
        $n = ResellerNotification::sole();
        $this->assertSame($profile->user_id, $n->notifiable_id);
        $this->assertSame('reservation_created', $n->type);
        $this->assertSame($r->reservation_number, $n->data['reservation_number']);
        $this->assertNull($n->read_at);
        $this->assertSame(1, $profile->user->notifications()->count());
        $service = app(ReservationService::class);
        $service->create($profile, [['product_variant_id' => $r->reservationItems[0]->product_variant_id, 'quantity' => 2]], $r->idempotency_key);
        $admin = User::factory()->create(['utype' => 'ADM']);
        foreach (['confirm', 'markPreparing', 'markShipped', 'complete'] as $method) {
            $service->$method($r, $admin->id);
            $service->$method($r, $admin->id);
        }
        $this->assertSame(['created', 'confirmed', 'preparing', 'shipped', 'completed'], $seen);
        $this->assertDatabaseCount('notifications', 5);
        $this->assertSame(5, ResellerNotification::where('notifiable_id', $profile->user_id)->count());
        $this->assertDatabaseCount('inventory_movements', 2);
        $this->assertDatabaseHas('inventory_items', ['stock_on_hand' => 3, 'reserved_quantity' => 0]);
    }

    public function test_listener_redelivery_is_database_idempotent_and_preserves_read_state(): void
    {
        $profile = $this->profile();
        $r = $this->reserve($profile);
        $n = ResellerNotification::sole();
        $n->markAsRead();
        $event = new ReservationCreated($r->id, $profile->id, $profile->user_id, $r->reservation_number, $r->status, null, $r->created_at->toDateTimeString());
        event($event);
        event($event);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertEquals($n->read_at, $n->fresh()->read_at);
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $this->seedNotification($profile->user, ['event_key' => $event->key()]);
    }

    public function test_outer_rollback_discards_created_and_transition_callbacks(): void
    {
        $seen = [];
        Event::listen('App\\Events\\B2B\\*', function ($name) use (&$seen) {
            $seen[] = $name;
        });
        DB::beginTransaction();
        $this->reserve();
        $this->assertDatabaseCount('notifications', 0);
        DB::rollBack();
        $this->assertDatabaseCount('reservations', 0);
        $this->assertSame([], $seen);
        $r = $this->reserve();
        DB::beginTransaction();
        app(ReservationService::class)->confirm($r);
        $this->assertDatabaseCount('notifications', 1);
        DB::rollBack();
        $this->assertSame('pending_review', $r->fresh()->status);
        $this->assertCount(1, $seen);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_failed_inventory_transaction_never_emits_or_persists_created_event(): void
    {
        $profile = $this->profile();
        $r = $this->reserve($profile);
        $variantId = $r->reservationItems[0]->product_variant_id;
        $count = ResellerNotification::count();
        Event::fake([ReservationCreated::class]);
        try {
            app(ReservationService::class)->create($profile, [['product_variant_id' => $variantId, 'quantity' => 99]], 'insufficient');
            $this->fail('Expected insufficient stock.');
        } catch (\Illuminate\Validation\ValidationException) {
            Event::assertNotDispatched(ReservationCreated::class);
            $this->assertDatabaseCount('notifications', $count);
            $this->assertDatabaseCount('reservations', 1);
            $this->assertDatabaseCount('inventory_movements', 1);
        }
    }

    public function test_additive_migration_preserves_legacy_rows_and_can_roll_back(): void
    {
        $user = $this->profile()->user;
        $legacy = $this->seedNotification($user, ['type' => 'legacy']);
        $migration = require database_path('migrations/2026_09_26_000001_add_reservation_event_key_to_notifications.php');
        $migration->down();
        $this->assertDatabaseHas('notifications', ['id' => $legacy->id]);
        $migration->up();
        $this->assertDatabaseHas('notifications', ['id' => $legacy->id, 'event_key' => null]);
    }

    public function test_outer_commit_delays_notification_until_business_state_is_committed(): void
    {
        DB::beginTransaction();
        $r = $this->reserve();
        $this->assertDatabaseCount('notifications', 0);
        DB::commit();
        $this->assertDatabaseHas('notifications', ['event_key' => "reservation:{$r->id}:created"]);
    }

    public function test_notification_write_failure_is_logged_without_failing_committed_reservation(): void
    {
        $this->mock(ResellerNotificationRepository::class)->shouldReceive('persist')->once()->andThrow(new RuntimeException('private SQL failure'));
        Log::shouldReceive('error')->once()->with('B2B reservation notification delivery failed.', \Mockery::on(fn ($c) => $c['event_type'] === 'created' && str_ends_with($c['event_key'], ':created') && isset($c['reservation_id']) && ! isset($c['exception'])));
        $r = $this->reserve();
        $this->assertSame('pending_review', $r->fresh()->status);
        $this->assertDatabaseCount('inventory_movements', 1);
        $this->assertDatabaseHas('inventory_items', ['reserved_quantity' => 2]);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_cancellation_from_all_allowed_states_releases_once(): void
    {
        $s = app(ReservationService::class);
        foreach (['pending_review', 'confirmed', 'preparing'] as $status) {
            $r = $this->reserve();
            if ($status !== 'pending_review') {
                $s->confirm($r);
            }
            if ($status === 'preparing') {
                $s->markPreparing($r);
            }
            $s->cancel($r);
            $s->cancel($r);
            $this->assertSame(1, ResellerNotification::where('event_key', "reservation:{$r->id}:cancelled")->count());
        }
        $this->assertSame(0, (int) InventoryItem::sum('reserved_quantity'));
        $this->assertSame(15, (int) InventoryItem::sum('stock_on_hand'));
        $this->assertDatabaseCount('inventory_movements', 6);
    }

    public function test_bulk_scheduler_expiry_replays_and_failed_rows(): void
    {
        $a = $this->reserve();
        $b = $this->reserve();
        $bad = $this->reserve();
        $future = $this->reserve();
        Reservation::whereIn('id', [$a->id, $b->id, $bad->id])->update(['expires_at' => now()->subMinute()]);
        InventoryItem::where('product_variant_id', $bad->reservationItems[0]->product_variant_id)->update(['reserved_quantity' => 0]);
        $this->artisan('reservations:expire')->assertExitCode(1);
        $this->artisan('reservations:expire')->assertExitCode(1);
        $this->assertSame(2, ResellerNotification::where('type', 'reservation_expired')->count());
        $this->assertSame('pending_review', $future->fresh()->status);
        $this->assertSame('pending_review', $bad->fresh()->status);
        InventoryItem::where('product_variant_id', $bad->reservationItems[0]->product_variant_id)->update(['reserved_quantity' => 2]);
        $this->artisan('reservations:expire')->assertSuccessful();
        $this->assertSame(3, ResellerNotification::where('type', 'reservation_expired')->count());
    }

    public function test_ownership_read_counts_bulk_update_and_links(): void
    {
        $a = $this->profile();
        $b = $this->profile();
        $r = $this->reserve($a);
        $n = ResellerNotification::sole();
        $this->actingAs($b->user)->get('/reseller/notifications')->assertOk()->assertDontSee($r->reservation_number);
        $this->post(route('reseller.notifications.read', $n->id))->assertNotFound();
        $this->get(route('reseller.reservations.show', $r->id))->assertNotFound();
        $this->actingAs($a->user)->withSession(['locale' => 'en'])->get('/reseller/notifications')->assertOk()->assertSee(route('reseller.reservations.show', $r->id), false);
        $this->get(route('reseller.reservations.show', $r->id))->assertOk();
        for ($i = 0; $i < 2; $i++) {
            $this->seedNotification($a->user);
            $this->seedNotification($a->user, ['read_at' => now()]);
        }
        $foreign = $this->seedNotification($b->user);
        $legacy = $this->seedNotification($a->user, ['type' => 'legacy']);
        $service = app(ResellerNotificationService::class);
        $this->assertSame(3, $service->unreadCount($a->user));
        $this->post(route('reseller.notifications.read', $n->id))->assertRedirect();
        $readAt = $n->fresh()->read_at;
        $this->post(route('reseller.notifications.read', $n->id))->assertRedirect();
        $this->assertEquals($readAt, $n->fresh()->read_at);
        $this->assertSame(2, $service->unreadCount($a->user));
        DB::enableQueryLog();
        DB::flushQueryLog();
        $service->markAllRead($a->user);
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
        $this->post(route('reseller.notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $service->unreadCount($a->user));
        $this->assertNull($foreign->fresh()->read_at);
        $this->assertNull($legacy->fresh()->read_at);
    }

    public function test_pagination_localization_malformed_data_and_bounded_queries(): void
    {
        $user = $this->profile()->user;
        $this->actingAs($user)->withSession(['locale' => 'en']);
        $this->seedNotification($user, ['data' => ['reservation_number' => 'OLDEST'], 'created_at' => now()->subDay()]);
        // Warm request before comparing growth.
        $this->get('/reseller/notifications')->assertOk();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->get('/reseller/notifications')->assertOk();
        $small = count(DB::getQueryLog());
        for ($i = 0; $i < 23; $i++) {
            $this->seedNotification($user);
        }
        $this->seedNotification($user, ['data' => ['reservation_number' => ['bad'], 'reservation_id' => ['bad']]]);
        $broken = $this->seedNotification($user);
        DB::table('notifications')->where('id', $broken->id)->update(['data' => '{invalid json']);
        DB::flushQueryLog();
        $this->get('/reseller/notifications?filter=all')->assertOk()->assertSee('Reservation submitted')->assertDontSee('OLDEST')->assertSee('filter=all', false)
            ->assertViewHas('notifications', fn ($p) => $p->count() === 20 && $p->total() === 26);
        $this->assertLessThanOrEqual($small + 1, count(DB::getQueryLog()));
        DB::disableQueryLog();
        $this->get('/reseller/notifications?page=2')->assertOk()->assertSee('OLDEST');
        $this->withSession(['locale' => 'ar'])->get('/reseller/notifications')->assertOk()->assertSee('الإشعارات')->assertSee('تم إرسال الحجز');
    }

    public function test_notification_routes_are_protected_and_read_is_not_get(): void
    {
        $this->get('/reseller/notifications')->assertRedirect('/login');
        foreach ([['utype' => 'USR'], ['utype' => 'ADM'], ['is_active' => false]] as $attributes) {
            $this->actingAs($this->profile($attributes)->user)->get('/reseller/notifications')->assertForbidden();
        }
        $this->actingAs($this->profile(['force_password_change' => true])->user)->get('/reseller/notifications')->assertRedirect(route('reseller.password.edit'));
        foreach (app('router')->getRoutes() as $route) {
            if (str_starts_with($route->getName() ?? '', 'reseller.notifications.')) {
                foreach (['web', 'auth', 'reseller', 'force.password.change', 'smart.throttle:user_dashboard'] as $m) {
                    $this->assertContains($m, $route->gatherMiddleware());
                }
                if ($route->getName() !== 'reseller.notifications.index') {
                    $this->assertSame(['POST'], $route->methods());
                }
            }
        }
    }

    public function test_reseller_cancel_and_admin_confirm_keep_recipient_and_permissions(): void
    {
        $profile = $this->profile();
        $r = $this->reserve($profile);
        $this->actingAs($profile->user)->post(route('reseller.reservations.cancel', $r->id))->assertRedirect();
        $this->assertDatabaseHas('notifications', ['event_key' => "reservation:{$r->id}:cancelled", 'notifiable_id' => $profile->user_id]);
        $r = $this->reserve($profile);
        $admin = User::factory()->create(['utype' => 'ADM']);
        $this->actingAs($admin)->post(route('admin.b2b.reservations.confirm', $r->id))->assertRedirect();
        $this->assertDatabaseHas('notifications', ['event_key' => "reservation:{$r->id}:confirmed", 'notifiable_id' => $profile->user_id]);
        $this->actingAs($profile->user)->post(route('reseller.reservations.cancel', $r->id))->assertSessionHasErrors();
        $this->assertSame('confirmed', $r->fresh()->status);
    }

    public function test_legacy_order_changes_do_not_produce_b2b_notifications(): void
    {
        $order = Order::factory()->create();
        $order->update(['status' => 'delivered']);
        $this->assertDatabaseCount('notifications', 0);
    }
}
