<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Tests\TestCase;

class B2BPhaseFourInventoryTest extends TestCase
{
    use RefreshDatabase;

    private function variant(string $sku = 'P4-DEFAULT'): ProductVariant
    {
        $product = Product::factory()->create(['quantity' => 100]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'is_active' => true,
            'price_adjustment' => '0.00',
        ]);
        InventoryItem::create(['product_variant_id' => $variant->id, 'stock_on_hand' => 0, 'reserved_quantity' => 0]);

        return $variant;
    }

    private function service(): InventoryService
    {
        return app(InventoryService::class);
    }

    private function balances(ProductVariant $variant, int $stock, int $reserved): void
    {
        $item = $variant->inventoryItem()->firstOrFail();
        $this->assertSame($stock, $item->stock_on_hand);
        $this->assertSame($reserved, $item->reserved_quantity);
        $this->assertSame($stock - $reserved, $item->available_quantity);
    }

    public function test_stock_in_writes_one_complete_movement_and_replays_exactly(): void
    {
        $variant = $this->variant();
        $actor = User::factory()->create();
        $context = ['reference_type' => 'admin_manual', 'reference_id' => 12, 'actor_user_id' => $actor->id, 'metadata' => ['source' => 'count']];
        $movement = $this->service()->stockIn($variant, 10, 'in-1', $context);

        $this->balances($variant, 10, 0);
        $this->assertSame(InventoryMovement::TYPE_STOCK_IN, $movement->type);
        $this->assertSame(10, $movement->quantity);
        $this->assertSame(10, $movement->stock_delta);
        $this->assertSame(0, $movement->reserved_delta);
        $this->assertSame(10, $movement->stock_on_hand_after);
        $this->assertSame(0, $movement->reserved_quantity_after);
        $this->assertSame('in-1', $movement->idempotency_key);
        $this->assertSame('admin_manual', $movement->reference_type);
        $this->assertSame(12, $movement->reference_id);
        $this->assertSame($actor->id, $movement->actor_user_id);
        $this->assertSame($movement->id, $this->service()->stockIn($variant, 10, 'in-1', $context)->id);
        $this->assertSame(1, InventoryMovement::count());

        $this->expectException(ValidationException::class);
        $this->service()->stockIn($variant, 20, 'in-1', $context);
    }

    public function test_reserve_stock_out_release_and_consume_respect_locked_balances(): void
    {
        $variant = $this->variant();
        $service = $this->service();
        $service->stockIn($variant, 10, 'seed');
        $service->reserve($variant, 7, 'reserve-7');
        $movement = $service->reserve($variant, 3, 'reserve-3');
        $this->balances($variant, 10, 10);
        $this->assertSame(0, $movement->stock_delta);
        $this->assertSame(3, $movement->reserved_delta);
        $this->assertSame(10, $movement->reserved_quantity_after);
        $this->failsWithoutMovement(fn () => $service->reserve($variant, 1, 'reserve-over'));

        $service->release($variant, 6, 'release-6');
        $this->balances($variant, 10, 4);
        $out = $service->stockOut($variant, 6, 'out-6');
        $this->balances($variant, 4, 4);
        $this->assertSame(-6, $out->stock_delta);
        $this->failsWithoutMovement(fn () => $service->stockOut($variant, 1, 'out-over'));
        $this->failsWithoutMovement(fn () => $service->release($variant, 5, 'release-over'));

        $fulfilled = $service->consumeReserved($variant, 2, 'fulfill-2');
        $this->balances($variant, 2, 2);
        $this->assertSame(-2, $fulfilled->stock_delta);
        $this->assertSame(-2, $fulfilled->reserved_delta);
        $this->assertSame(2, $fulfilled->stock_on_hand_after);
        $this->assertSame(2, $fulfilled->reserved_quantity_after);
        $this->assertSame($fulfilled->id, $service->consumeReserved($variant, 2, 'fulfill-2')->id);
        $this->failsWithoutMovement(fn () => $service->consumeReserved($variant, 3, 'fulfill-over'));
    }

    public function test_adjustment_is_absolute_and_never_releases_reserved_stock(): void
    {
        $variant = $this->variant();
        $service = $this->service();
        $service->stockIn($variant, 10, 'seed');
        $service->reserve($variant, 7, 'reserve');
        $this->failsWithoutMovement(fn () => $service->adjustStockTo($variant, 5, 'too-low'));
        $movement = $service->adjustStockTo($variant, 7, 'adjust-7');
        $this->balances($variant, 7, 7);
        $this->assertSame(3, $movement->quantity);
        $this->assertSame(-3, $movement->stock_delta);
        $this->assertSame(0, $movement->reserved_delta);
        $this->assertSame(7, $movement->stock_on_hand_after);
        $this->assertSame(7, $movement->reserved_quantity_after);
        $this->assertSame($movement->id, $service->adjustStockTo($variant, 7, 'adjust-7')->id);
        $this->failsWithoutMovement(fn () => $service->adjustStockTo($variant, 7, 'new-noop'));
    }

    public function test_all_operation_keys_conflict_on_changed_payload_or_context(): void
    {
        $variant = $this->variant();
        $other = $this->variant('P4-OTHER');
        $service = $this->service();
        $service->stockIn($variant, 10, 'in');
        $service->reserve($variant, 4, 'reserve');
        $service->release($variant, 1, 'release');
        $service->consumeReserved($variant, 1, 'consume');
        $service->adjustStockTo($variant, 12, 'adjust');
        foreach ([
            fn () => $service->stockIn($other, 10, 'in'),
            fn () => $service->stockOut($variant, 10, 'in'),
            fn () => $service->reserve($variant, 4, 'reserve', ['reference_type' => 'reservation', 'reference_id' => 1]),
            fn () => $service->release($variant, 2, 'release'),
            fn () => $service->consumeReserved($variant, 2, 'consume'),
            fn () => $service->adjustStockTo($variant, 13, 'adjust'),
        ] as $operation) {
            $this->failsWithoutMovement($operation);
        }
        $this->assertSame(5, InventoryMovement::count());
        $this->balances($variant, 12, 2);
    }

    public function test_replay_for_release_and_reserve_does_not_apply_again(): void
    {
        $variant = $this->variant();
        $service = $this->service();
        $service->stockIn($variant, 2, 'in');
        $reserve = $service->reserve($variant, 2, 'reserve');
        $this->assertSame($reserve->id, $service->reserve($variant, 2, 'reserve')->id);
        $release = $service->release($variant, 2, 'release');
        $this->assertSame($release->id, $service->release($variant, 2, 'release')->id);
        $this->balances($variant, 2, 0);
        $this->assertSame(3, InventoryMovement::count());
        $this->failsWithoutMovement(fn () => $service->release($variant, 2, 'release-again'));
    }

    public function test_stock_out_replay_is_single_movement(): void
    {
        $variant = $this->variant();
        $service = $this->service();
        $service->stockIn($variant, 5, 'in');
        $movement = $service->stockOut($variant, 3, 'out');
        $this->assertSame($movement->id, $service->stockOut($variant, 3, 'out')->id);
        $this->balances($variant, 2, 0);
        $this->assertSame(2, InventoryMovement::count());
    }

    public function test_outer_transaction_can_rollback_multiple_service_calls_together(): void
    {
        $variant = $this->variant();
        try {
            DB::transaction(function () use ($variant): void {
                $this->service()->stockIn($variant, 4, 'outer-in');
                $this->service()->reserve($variant, 2, 'outer-reserve');
                throw new \RuntimeException('Abort outer operation');
            });
            $this->fail('Expected the outer transaction to abort.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Abort outer operation', $exception->getMessage());
        }
        $this->balances($variant, 0, 0);
        $this->assertSame(0, InventoryMovement::count());
    }

    public function test_corrupted_persisted_balances_are_not_hidden_or_mutated(): void
    {
        $variant = $this->variant();
        DB::table('inventory_items')->where('product_variant_id', $variant->id)->update(['reserved_quantity' => 1]);
        $this->assertSame(-1, $variant->inventoryItem()->firstOrFail()->available_quantity);
        $this->failsWithoutMovement(fn () => $this->service()->stockIn($variant, 2, 'corrupt'));
        $this->balances($variant, 0, 1);
    }

    public function test_movement_insert_failure_rolls_back_inventory_change(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('SQLite trigger used to force the ledger insert failure.');
        }
        $variant = $this->variant();
        DB::unprepared("CREATE TRIGGER reject_phase_four_movement BEFORE INSERT ON inventory_movements BEGIN SELECT RAISE(ABORT, 'forced ledger failure'); END");
        try {
            $this->expectException(\Illuminate\Database\QueryException::class);
            $this->service()->stockIn($variant, 5, 'failed-insert');
        } finally {
            DB::unprepared('DROP TRIGGER reject_phase_four_movement');
            $this->balances($variant, 0, 0);
            $this->assertSame(0, InventoryMovement::count());
        }
    }

    public function test_movements_are_immutable_through_eloquent_instances(): void
    {
        $variant = $this->variant();
        $movement = $this->service()->stockIn($variant, 2, 'immutable');
        foreach ([
            fn () => $movement->update(['quantity' => 9]),
            fn () => $movement->updateQuietly(['quantity' => 9]),
            fn () => $movement->delete(),
            fn () => $movement->deleteQuietly(),
        ] as $operation) {
            try {
                $operation();
                $this->fail('Movement was modified.');
            } catch (InvalidArgumentException $exception) {
                $this->assertSame('Inventory movements are append-only.', $exception->getMessage());
            }
        }
        $this->assertSame(2, $movement->fresh()->quantity);
        $this->assertSame(1, InventoryMovement::count());
    }

    public function test_inactive_variant_can_be_released_consumed_and_physically_managed(): void
    {
        $variant = $this->variant();
        $service = $this->service();
        $service->stockIn($variant, 10, 'in');
        $service->reserve($variant, 6, 'reserve');
        $variant->update(['is_active' => false]);
        $this->failsWithoutMovement(fn () => $service->reserve($variant, 1, 'inactive-reserve'));
        $service->release($variant, 2, 'inactive-release');
        $service->consumeReserved($variant, 2, 'inactive-consume');
        $service->stockIn($variant, 1, 'inactive-in');
        $service->stockOut($variant, 1, 'inactive-out');
        $service->adjustStockTo($variant, 7, 'inactive-adjust');
        $this->balances($variant, 7, 2);
    }

    public function test_validation_limits_and_model_invariants(): void
    {
        $variant = $this->variant();
        $service = $this->service();
        foreach ([0, -1] as $quantity) {
            foreach (['stockIn', 'stockOut', 'reserve', 'release', 'consumeReserved'] as $method) {
                $this->failsWithoutMovement(fn () => $service->$method($variant, $quantity, 'invalid-'.$method.'-'.$quantity));
            }
        }
        $this->failsWithoutMovement(fn () => $service->stockIn($variant, 1, ' '));
        $service->stockIn($variant, InventoryService::MAX_QUANTITY, 'max');
        $this->failsWithoutMovement(fn () => $service->stockIn($variant, 1, 'overflow'));
        $item = $variant->inventoryItem()->firstOrFail();
        foreach ([['stock_on_hand' => -1], ['reserved_quantity' => -1], ['reserved_quantity' => InventoryService::MAX_QUANTITY + 1]] as $invalid) {
            try {
                $item->update($invalid);
                $this->fail('Invalid inventory balance was accepted.');
            } catch (InvalidArgumentException) {
                $item->refresh();
            }
        }
        $this->assertSame(InventoryService::MAX_QUANTITY, $item->available_quantity);
    }

    public function test_zero_backfill_ignores_all_legacy_quantity_columns_and_fabricates_no_ledger(): void
    {
        $product = Product::factory()->create(['quantity' => 100]);
        $color = Color::factory()->create();
        $size = Size::factory()->create();
        $product->colors()->attach($color->id, ['quantity' => 80]);
        $product->sizes()->attach($size->id, ['quantity' => 60]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'color_id' => $color->id, 'size_id' => $size->id, 'sku' => 'LEGACY-P4']);

        $migration = require database_path('migrations/2026_09_24_000002_backfill_inventory_items.php');
        $migration->up();
        $migration->up();
        $this->balances($variant, 0, 0);
        $this->assertSame(1, InventoryItem::where('product_variant_id', $variant->id)->count());
        $this->assertSame(0, InventoryMovement::count());
    }

    public function test_admin_inventory_route_checks_role_ownership_and_retry_key(): void
    {
        $variant = $this->variant();
        $other = $this->variant('P4-OTHER');
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $user = User::factory()->create(['utype' => User::TYPE_USER]);
        $url = route('admin.products.variants.inventory.store', [$variant->product, $variant]);
        $payload = ['operation' => 'stock_in', 'quantity' => 5, 'idempotency_key' => 'admin-key'];

        $this->actingAs($user)->post($url, $payload)->assertForbidden();
        $this->actingAs($admin)->post(route('admin.products.variants.inventory.store', [$other->product, $variant]), $payload)->assertNotFound();
        $this->actingAs($admin)->post($url, $payload)->assertRedirect();
        $this->actingAs($admin)->post($url, $payload)->assertRedirect();
        $this->balances($variant, 5, 0);
        $this->assertSame(1, InventoryMovement::count());
        $this->actingAs($admin)->post($url, ['operation' => 'stock_out', 'quantity' => 6, 'idempotency_key' => 'admin-fail'])->assertSessionHasErrors('quantity');
        $this->actingAs($admin)->post($url, ['operation' => 'stock_out', 'quantity' => 2, 'idempotency_key' => 'admin-out'])->assertRedirect();
        $this->actingAs($admin)->post($url, ['operation' => 'adjustment', 'quantity' => 7, 'idempotency_key' => 'admin-adjust'])->assertRedirect();
        $this->balances($variant, 7, 0);
        $this->assertSame(3, InventoryMovement::count());
        $this->actingAs($admin)->post($url, ['operation' => 'stock_in', 'quantity' => 1, 'idempotency_key' => 'admin-key'])->assertSessionHasErrors('idempotency_key');
    }

    public function test_mysql_concurrent_reservations_require_dedicated_integration_database(): void
    {
        $this->markTestSkipped('PHPUnit uses in-memory SQLite; real MySQL row-lock concurrency requires two independent MySQL connections in a dedicated integration environment.');
    }

    private function failsWithoutMovement(callable $operation): void
    {
        $before = InventoryMovement::count();
        try {
            $operation();
            $this->fail('Expected inventory validation failure.');
        } catch (ValidationException) {
            $this->assertSame($before, InventoryMovement::count());
        }
    }
}
