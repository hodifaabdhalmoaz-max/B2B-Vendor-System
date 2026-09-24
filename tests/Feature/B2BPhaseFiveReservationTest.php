<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Size;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class B2BPhaseFiveReservationTest extends TestCase
{
    use RefreshDatabase;

    private function profile(array $attributes = [], array $userAttributes = []): ResellerProfile
    {
        $user = User::factory()->create(array_merge(['utype' => User::TYPE_RESELLER, 'is_active' => true], $userAttributes));

        return ResellerProfile::create(array_merge([
            'user_id' => $user->id,
            'status' => ResellerProfile::STATUS_ACTIVE,
            'reservation_enabled' => true,
            'reservation_timeout_minutes' => 30,
        ], $attributes));
    }

    private function variant(int $stock, string $sku, string $price = '10.10'): ProductVariant
    {
        $product = Product::factory()->create(['regular_price' => $price, 'sale_price' => null, 'name' => "Product {$sku}"]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'is_active' => true,
            'price_adjustment' => '0.00',
        ]);
        InventoryItem::create(['product_variant_id' => $variant->id, 'stock_on_hand' => $stock, 'reserved_quantity' => 0]);

        return $variant;
    }

    private function request(ProductVariant $variant, int $quantity): array
    {
        return ['product_variant_id' => $variant->id, 'quantity' => $quantity];
    }

    private function service(): ReservationService
    {
        return app(ReservationService::class);
    }

    public function test_creation_snapshots_stock_and_idempotency(): void
    {
        $profile = $this->profile();
        $a = $this->variant(5, 'A');
        $b = $this->variant(3, 'B', '0.10');
        $items = [$this->request($b, 1), $this->request($a, 2)];
        $reservation = $this->service()->create($profile, $items, 'create-1', 'note');

        $this->assertSame(Reservation::STATUS_PENDING_REVIEW, $reservation->status);
        $this->assertSame('20.30', $reservation->total);
        $this->assertCount(2, $reservation->reservationItems);
        $this->assertSame([$a->id, $b->id], $reservation->reservationItems->pluck('product_variant_id')->all());
        $this->assertSame('Product A', $reservation->reservationItems[0]->product_name_snapshot);
        $this->assertSame('A', $reservation->reservationItems[0]->sku_snapshot);
        $this->assertSame($a->id, $reservation->reservationItems[0]->variant_snapshot['variant_id']);
        $this->assertSame('20.20', $reservation->reservationItems[0]->line_total);
        $this->assertSame(2, $a->inventoryItem->fresh()->reserved_quantity);
        $this->assertSame(1, $b->inventoryItem->fresh()->reserved_quantity);
        $this->assertSame(5, $a->inventoryItem->fresh()->stock_on_hand);
        $this->assertSame(2, InventoryMovement::where('type', InventoryMovement::TYPE_RESERVE)->count());
        $this->assertSame($reservation->id, $this->service()->create($profile, array_reverse($items), 'create-1', 'note')->id);
        $this->assertSame(1, Reservation::count());
        $this->assertSame(2, ReservationItem::count());
        $this->assertSame(2, InventoryMovement::count());
        $this->assertSame("reservation:{$reservation->id}:reserve:{$a->id}", InventoryMovement::where('product_variant_id', $a->id)->first()->idempotency_key);

        $this->assertValidation(fn () => $this->service()->create($profile, [$this->request($a, 3), $this->request($b, 1)], 'create-1', 'note'));
        $this->assertValidation(fn () => $this->service()->create($profile, $items, 'create-1', 'different'));
        $this->assertValidation(fn () => $this->service()->create($this->profile(), $items, 'create-1', 'note'));
    }

    public function test_creation_is_all_or_nothing_and_rejects_bad_items(): void
    {
        $profile = $this->profile();
        $a = $this->variant(5, 'A');
        $b = $this->variant(0, 'B');
        $this->assertValidation(fn () => $this->service()->create($profile, [$this->request($a, 2), $this->request($b, 1)], 'fail'));
        $this->assertSame(0, Reservation::count());
        $this->assertSame(0, ReservationItem::count());
        $this->assertSame(0, InventoryMovement::count());
        $this->assertSame(0, $a->inventoryItem->fresh()->reserved_quantity);
        foreach ([[], [$this->request($a, 1), $this->request($a, 1)], [$this->request($a, 0)], [['product_variant_id' => -1, 'quantity' => 1]], [$this->request($a, InventoryService::MAX_QUANTITY + 1)]] as $bad) {
            $this->assertValidation(fn () => $this->service()->create($profile, $bad, 'bad'));
        }
    }

    public function test_effective_sale_price_and_descriptive_variant_snapshot_are_server_generated(): void
    {
        $product = Product::factory()->create(['regular_price' => '10.10', 'sale_price' => '8.00']);
        $color = Color::create(['name' => 'Blue', 'code' => 'BLU', 'is_active' => true]);
        $size = Size::create(['name' => 'Medium', 'code' => 'M', 'is_active' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'sku' => 'SALE-BLU-M',
            'price_adjustment' => '2.30',
            'is_active' => true,
        ]);
        InventoryItem::create(['product_variant_id' => $variant->id, 'stock_on_hand' => 2, 'reserved_quantity' => 0]);
        $item = $this->service()->create($this->profile(), [$this->request($variant, 1)], 'sale-snapshot')->reservationItems->first();
        $this->assertSame('10.30', $item->unit_price);
        $this->assertSame($variant->variant_key, $item->variant_snapshot['variant_key']);
        $this->assertSame('Blue', $item->variant_snapshot['color_name']);
        $this->assertSame('BLU', $item->variant_snapshot['color_code']);
        $this->assertSame('Medium', $item->variant_snapshot['size_name']);
        $this->assertSame('M', $item->variant_snapshot['size_code']);
        $this->assertSame('2.30', $item->variant_snapshot['price_adjustment']);
    }

    public function test_timer_eligibility_and_legacy_invalid_price(): void
    {
        Date::setTestNow('2026-09-24 12:00:00');
        try {
            $variant = $this->variant(10, 'A');
            $profile = $this->profile();
            $reservation = $this->service()->create($profile, [$this->request($variant, 1)], 'timer');
            $this->assertSame('2026-09-24 12:30:00', $reservation->expires_at->format('Y-m-d H:i:s'));
            $noTimeout = $this->profile(['reservation_timeout_minutes' => null]);
            $this->assertNull($this->service()->create($noTimeout, [$this->request($variant, 1)], 'no-timer')->expires_at);

            foreach ([
                $this->profile(['status' => ResellerProfile::STATUS_INACTIVE]),
                $this->profile(['reservation_enabled' => false]),
                $this->profile([], ['is_active' => false]),
                $this->profile([], ['utype' => User::TYPE_USER]),
            ] as $blocked) {
                $this->assertValidation(fn () => $this->service()->create($blocked, [$this->request($variant, 1)], 'blocked-'.$blocked->id));
            }
            DB::table('product_variants')->where('id', $variant->id)->update(['price_adjustment' => '-100.00']);
            $this->assertValidation(fn () => $this->service()->create($profile, [$this->request($variant, 1)], 'negative'));
            $this->assertSame(2, $variant->inventoryItem->fresh()->reserved_quantity);
        } finally {
            Date::setTestNow();
        }
    }

    public function test_inactive_variant_is_rejected_without_inventory_mutation(): void
    {
        $variant = $this->variant(2, 'INACTIVE');
        $variant->update(['is_active' => false]);
        $this->assertValidation(fn () => $this->service()->create($this->profile(), [$this->request($variant, 1)], 'inactive-variant'));
        $this->assertSame(0, Reservation::count());
        $this->assertSame(0, InventoryMovement::count());
        $this->assertSame(0, $variant->inventoryItem->fresh()->reserved_quantity);
    }

    public function test_cancel_and_expire_release_once_even_after_deactivation(): void
    {
        Date::setTestNow('2026-09-24 12:00:00');
        try {
            $profile = $this->profile();
            $a = $this->variant(5, 'A');
            $b = $this->variant(3, 'B');
            $items = [$this->request($b, 1), $this->request($a, 2)];
            $reservation = $this->service()->create($profile, $items, 'cancel');
            $a->update(['is_active' => false]);
            $profile->update(['reservation_enabled' => false, 'status' => ResellerProfile::STATUS_INACTIVE]);
            $cancelled = $this->service()->cancel($reservation);
            $this->assertSame(Reservation::STATUS_CANCELLED, $cancelled->status);
            $this->assertNotNull($cancelled->cancelled_at);
            $this->assertNotNull($cancelled->released_at);
            $this->service()->cancel($reservation);
            $this->assertSame(0, $a->inventoryItem->fresh()->reserved_quantity);
            $this->assertSame(0, $b->inventoryItem->fresh()->reserved_quantity);
            $this->assertSame(2, InventoryMovement::where('type', InventoryMovement::TYPE_RELEASE)->count());

            $a->update(['is_active' => true]);
            $profile->update(['reservation_enabled' => true, 'status' => ResellerProfile::STATUS_ACTIVE]);
            $expiring = $this->service()->create($profile, $items, 'expire');
            $this->assertFalse($this->service()->expire($expiring));
            Date::setTestNow('2026-09-24 12:31:00');
            $this->assertTrue($this->service()->expire($expiring));
            $this->assertFalse($this->service()->expire($expiring));
            $this->assertSame(Reservation::STATUS_EXPIRED, $expiring->fresh()->status);
            $this->assertNull($expiring->fresh()->cancelled_at);
            $this->assertValidation(fn () => $this->service()->confirm($expiring));
            $this->assertValidation(fn () => $this->service()->markPreparing($reservation));
            $this->assertSame(0, $a->inventoryItem->fresh()->reserved_quantity);
            $this->assertSame(4, InventoryMovement::where('type', InventoryMovement::TYPE_RELEASE)->count());
        } finally {
            Date::setTestNow();
        }
    }

    public function test_state_machine_completion_and_stale_model_recheck(): void
    {
        $profile = $this->profile();
        $a = $this->variant(5, 'A');
        $b = $this->variant(3, 'B');
        $reservation = $this->service()->create($profile, [$this->request($b, 1), $this->request($a, 2)], 'complete');
        $this->assertValidation(fn () => $this->service()->markShipped($reservation));
        $this->assertValidation(fn () => $this->service()->complete($reservation));
        $this->service()->confirm($reservation);
        $this->assertNotNull($reservation->fresh()->confirmed_at);
        $this->assertValidation(fn () => $this->service()->complete($reservation));
        $this->service()->markPreparing($reservation);
        $this->service()->markShipped($reservation);
        $this->assertValidation(fn () => $this->service()->cancel($reservation));
        $b->update(['is_active' => false]);
        $this->service()->complete($reservation);
        $this->service()->complete($reservation);
        $this->assertSame(Reservation::STATUS_COMPLETED, $reservation->fresh()->status);
        $this->assertNull($reservation->fresh()->released_at);
        $this->assertSame(3, $a->inventoryItem->fresh()->stock_on_hand);
        $this->assertSame(2, $b->inventoryItem->fresh()->stock_on_hand);
        $this->assertSame(0, $a->inventoryItem->fresh()->reserved_quantity);
        $this->assertSame(2, InventoryMovement::where('type', InventoryMovement::TYPE_ORDER_COMPLETED)->count());
        $this->assertValidation(fn () => $this->service()->cancel($reservation));
        $this->assertValidation(fn () => $this->service()->confirm($reservation));
    }

    public function test_expiration_command_only_processes_due_pending_rows(): void
    {
        Date::setTestNow('2026-09-24 12:00:00');
        try {
            $profile = $this->profile();
            $variant = $this->variant(10, 'A');
            $due = $this->service()->create($profile, [$this->request($variant, 1)], 'due');
            $confirmed = $this->service()->create($profile, [$this->request($variant, 1)], 'confirmed');
            $this->service()->confirm($confirmed);
            Date::setTestNow('2026-09-24 12:20:00');
            $future = $this->service()->create($profile, [$this->request($variant, 1)], 'future');
            Date::setTestNow('2026-09-24 12:31:00');
            $this->assertSame(0, Artisan::call('reservations:expire'));
            $this->assertSame(Reservation::STATUS_EXPIRED, $due->fresh()->status);
            $this->assertSame(Reservation::STATUS_CONFIRMED, $confirmed->fresh()->status);
            $this->assertSame(Reservation::STATUS_PENDING_REVIEW, $future->fresh()->status);
            $this->assertSame(0, Artisan::call('reservations:expire'));
            $this->assertSame(1, InventoryMovement::where('type', InventoryMovement::TYPE_RELEASE)->count());
        } finally {
            Date::setTestNow();
        }
    }

    public function test_line_totals_use_integer_cents(): void
    {
        $this->assertSame('30.30', (new ReservationItem(['unit_price' => '10.10', 'quantity' => 3]))->line_total);
        $this->assertSame('0.30', (new ReservationItem(['unit_price' => '0.10', 'quantity' => 3]))->line_total);
    }

    public function test_unrepresentable_money_total_is_rejected_before_stock_is_reserved(): void
    {
        $variant = $this->variant(InventoryService::MAX_QUANTITY, 'LARGE', '9999999999.99');
        $this->assertValidation(fn () => $this->service()->create($this->profile(), [$this->request($variant, InventoryService::MAX_QUANTITY)], 'large-money'));
        $this->assertSame(0, Reservation::count());
        $this->assertSame(0, InventoryMovement::count());
        $this->assertSame(0, $variant->inventoryItem->fresh()->reserved_quantity);
    }

    public function test_inventory_calls_follow_ascending_variant_id_and_release_rolls_back(): void
    {
        $profile = $this->profile();
        $a = $this->variant(5, 'A');
        $b = $this->variant(5, 'B');
        $spy = new FaultingReservationInventory;
        app()->instance(InventoryService::class, $spy);
        try {
            $reservation = $this->service()->create($profile, [$this->request($b, 1), $this->request($a, 1)], 'lock-order');
            $this->assertSame(["reserve:{$a->id}", "reserve:{$b->id}"], $spy->calls);
            $spy->calls = [];
            $spy->failOn = 'release:'.$b->id;
            $this->expectRuntimeFailure(fn () => $this->service()->cancel($reservation));
            $this->assertSame(Reservation::STATUS_PENDING_REVIEW, $reservation->fresh()->status);
            $this->assertNull($reservation->fresh()->released_at);
            $this->assertSame(1, $a->inventoryItem->fresh()->reserved_quantity);
            $this->assertSame(1, $b->inventoryItem->fresh()->reserved_quantity);
            $this->assertSame(0, InventoryMovement::where('type', InventoryMovement::TYPE_RELEASE)->count());
            $this->assertSame(["release:{$a->id}", "release:{$b->id}"], $spy->calls);
        } finally {
            app()->forgetInstance(InventoryService::class);
        }
    }

    public function test_completion_failure_rolls_back_all_consumption(): void
    {
        $profile = $this->profile();
        $a = $this->variant(5, 'A');
        $b = $this->variant(5, 'B');
        $reservation = $this->service()->create($profile, [$this->request($b, 1), $this->request($a, 1)], 'rollback-complete');
        $this->service()->confirm($reservation);
        $this->service()->markPreparing($reservation);
        $this->service()->markShipped($reservation);
        $spy = new FaultingReservationInventory;
        $spy->failOn = 'complete:'.$b->id;
        app()->instance(InventoryService::class, $spy);
        try {
            $this->expectRuntimeFailure(fn () => $this->service()->complete($reservation));
            $this->assertSame(Reservation::STATUS_SHIPPED, $reservation->fresh()->status);
            $this->assertSame(5, $a->inventoryItem->fresh()->stock_on_hand);
            $this->assertSame(1, $a->inventoryItem->fresh()->reserved_quantity);
            $this->assertSame(0, InventoryMovement::where('type', InventoryMovement::TYPE_ORDER_COMPLETED)->count());
            $this->assertSame(["complete:{$a->id}", "complete:{$b->id}"], $spy->calls);
        } finally {
            app()->forgetInstance(InventoryService::class);
        }
    }

    public function test_reservation_item_snapshots_are_immutable(): void
    {
        $variant = $this->variant(2, 'A');
        $reservation = $this->service()->create($this->profile(), [$this->request($variant, 1)], 'immutable');
        $item = $reservation->reservationItems->first();
        $item->quantity = 2;
        $this->expectException(\InvalidArgumentException::class);
        $item->save();
    }

    private function expectRuntimeFailure(callable $action): void
    {
        try {
            $action();
            $this->fail('Expected injected inventory failure.');
        } catch (RuntimeException) {
            $this->assertTrue(true);
        }
    }

    private function assertValidation(callable $action): void
    {
        try {
            $action();
            $this->fail('Expected validation failure.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }
}

class FaultingReservationInventory extends InventoryService
{
    public array $calls = [];

    public ?string $failOn = null;

    public function reserve(ProductVariant $variant, int $quantity, string $idempotencyKey, array $context = []): InventoryMovement
    {
        $this->record('reserve', $variant);

        return parent::reserve($variant, $quantity, $idempotencyKey, $context);
    }

    public function release(ProductVariant $variant, int $quantity, string $idempotencyKey, array $context = []): InventoryMovement
    {
        $this->record('release', $variant);

        return parent::release($variant, $quantity, $idempotencyKey, $context);
    }

    public function consumeReserved(ProductVariant $variant, int $quantity, string $idempotencyKey, array $context = []): InventoryMovement
    {
        $this->record('complete', $variant);

        return parent::consumeReserved($variant, $quantity, $idempotencyKey, $context);
    }

    private function record(string $operation, ProductVariant $variant): void
    {
        $call = "{$operation}:{$variant->id}";
        $this->calls[] = $call;
        if ($this->failOn === $call) {
            throw new RuntimeException('Injected inventory failure.');
        }
    }
}
