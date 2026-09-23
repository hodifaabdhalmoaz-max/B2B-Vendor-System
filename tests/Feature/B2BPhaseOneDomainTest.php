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
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class B2BPhaseOneDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_one_reseller_profile_and_relationships_work(): void
    {
        $user = User::factory()->create();

        $profile = ResellerProfile::create([
            'user_id' => $user->id,
            'business_name' => 'Kids Wholesale Sana',
            'whatsapp' => '777000111',
            'governorate' => 'Sana',
            'group_name' => 'A',
            'status' => ResellerProfile::STATUS_ACTIVE,
        ]);

        $this->assertTrue($profile->isActive());
        $this->assertTrue($user->resellerProfile->is($profile));
        $this->assertTrue($profile->user->is($user));

        $this->expectException(QueryException::class);

        ResellerProfile::create([
            'user_id' => $user->id,
            'business_name' => 'Duplicate Profile',
            'status' => ResellerProfile::STATUS_ACTIVE,
        ]);
    }

    public function test_product_can_contain_multiple_variants_with_unique_skus(): void
    {
        $product = Product::factory()->create();
        $black = Color::factory()->create(['name' => 'Black', 'code' => 'BLACK']);
        $white = Color::factory()->create(['name' => 'White', 'code' => 'WHITE']);
        $medium = Size::factory()->create(['name' => 'Medium', 'code' => 'M']);

        $variantOne = ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $black->id,
            'size_id' => $medium->id,
            'sku' => 'WHOLESALE-BLACK-M',
            'price_adjustment' => '1.50',
        ]);

        $variantTwo = ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $white->id,
            'size_id' => $medium->id,
            'sku' => 'WHOLESALE-WHITE-M',
            'price_adjustment' => '2.00',
        ]);

        $this->assertCount(2, $product->variants);
        $this->assertTrue($variantOne->product->is($product));
        $this->assertTrue($variantOne->color->is($black));
        $this->assertTrue($variantOne->size->is($medium));
        $this->assertTrue($black->productVariants->contains($variantOne));
        $this->assertTrue($medium->productVariants->contains($variantTwo));

        $this->expectException(QueryException::class);

        ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $black->id,
            'size_id' => $medium->id,
            'sku' => 'WHOLESALE-BLACK-M',
        ]);
    }

    public function test_inventory_item_belongs_to_one_variant_and_available_quantity_is_calculated(): void
    {
        $variant = $this->createVariant('INV-BLACK-M');

        $inventoryItem = InventoryItem::create([
            'product_variant_id' => $variant->id,
            'stock_on_hand' => 25,
            'reserved_quantity' => 7,
            'low_stock_threshold' => 5,
        ]);

        $this->assertTrue($variant->inventoryItem->is($inventoryItem));
        $this->assertTrue($inventoryItem->productVariant->is($variant));
        $this->assertSame(18, $inventoryItem->available_quantity);
        $this->assertTrue($inventoryItem->canReserve(18));
        $this->assertFalse($inventoryItem->canReserve(19));

        $this->expectException(QueryException::class);

        InventoryItem::create([
            'product_variant_id' => $variant->id,
            'stock_on_hand' => 10,
            'reserved_quantity' => 0,
        ]);
    }

    public function test_inventory_item_rejects_reserved_quantity_above_stock_on_hand(): void
    {
        $variant = $this->createVariant('INV-OVER-RESERVED');

        $this->expectException(InvalidArgumentException::class);

        InventoryItem::create([
            'product_variant_id' => $variant->id,
            'stock_on_hand' => 5,
            'reserved_quantity' => 6,
        ]);
    }

    public function test_reservation_relationships_and_snapshots_work(): void
    {
        $profile = ResellerProfile::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'Aden Reseller',
            'status' => ResellerProfile::STATUS_ACTIVE,
        ]);
        $variant = $this->createVariant('SNAP-BLACK-L');

        $reservation = Reservation::create([
            'reservation_number' => 'RSV-20260923-0001',
            'reseller_profile_id' => $profile->id,
            'status' => Reservation::STATUS_PENDING_REVIEW,
            'expires_at' => now()->addMinutes(30),
        ]);

        $item = ReservationItem::create([
            'reservation_id' => $reservation->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'unit_price' => '12.75',
            'product_name_snapshot' => $variant->product->name,
            'sku_snapshot' => $variant->sku,
            'variant_snapshot' => [
                'color' => $variant->color?->name,
                'size' => $variant->size?->name,
            ],
        ]);

        $this->assertTrue($profile->reservations->contains($reservation));
        $this->assertTrue($reservation->resellerProfile->is($profile));
        $this->assertTrue($reservation->reservationItems->contains($item));
        $this->assertTrue($item->reservation->is($reservation));
        $this->assertTrue($item->productVariant->is($variant));
        $this->assertSame('SNAP-BLACK-L', $item->sku_snapshot);
        $this->assertSame('Black', $item->variant_snapshot['color']);
        $this->assertSame('38.25', $item->line_total);
    }

    public function test_inventory_movement_records_can_be_created(): void
    {
        $variant = $this->createVariant('MOVE-BLACK-M');
        $actor = User::factory()->create();

        $movement = InventoryMovement::create([
            'product_variant_id' => $variant->id,
            'type' => InventoryMovement::TYPE_STOCK_IN,
            'quantity' => 50,
            'reference_type' => 'manual_adjustment',
            'reference_id' => 1001,
            'actor_user_id' => $actor->id,
            'idempotency_key' => 'stock-in-1001',
            'metadata' => ['note' => 'opening stock'],
        ]);

        $this->assertTrue($variant->inventoryMovements->contains($movement));
        $this->assertTrue($movement->productVariant->is($variant));
        $this->assertTrue($movement->actor->is($actor));
        $this->assertSame('opening stock', $movement->metadata['note']);

        $this->expectException(QueryException::class);

        InventoryMovement::create([
            'product_variant_id' => $variant->id,
            'type' => InventoryMovement::TYPE_STOCK_IN,
            'quantity' => 50,
            'idempotency_key' => 'stock-in-1001',
        ]);
    }

    public function test_reservation_items_and_inventory_movements_reject_non_positive_quantities(): void
    {
        $profile = ResellerProfile::create([
            'user_id' => User::factory()->create()->id,
            'status' => ResellerProfile::STATUS_ACTIVE,
        ]);
        $variant = $this->createVariant('QTY-GUARD');
        $reservation = Reservation::create([
            'reservation_number' => 'RSV-20260923-0002',
            'reseller_profile_id' => $profile->id,
            'status' => Reservation::STATUS_PENDING_REVIEW,
        ]);

        try {
            ReservationItem::create([
                'reservation_id' => $reservation->id,
                'product_variant_id' => $variant->id,
                'quantity' => 0,
                'unit_price' => '10.00',
                'product_name_snapshot' => $variant->product->name,
                'sku_snapshot' => $variant->sku,
            ]);

            $this->fail('Reservation item accepted a non-positive quantity.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Reservation item quantity must be positive.', $exception->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);

        InventoryMovement::create([
            'product_variant_id' => $variant->id,
            'type' => InventoryMovement::TYPE_ADJUSTMENT,
            'quantity' => 0,
        ]);
    }

    public function test_existing_product_and_user_behavior_is_not_broken(): void
    {
        $admin = User::factory()->create(['utype' => 'ADM']);
        $user = User::factory()->create(['utype' => 'USR']);
        $activeProduct = Product::factory()->create(['stock_status' => 'instock']);
        $inactiveProduct = Product::factory()->create(['stock_status' => 'outofstock']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue(Product::active()->get()->contains($activeProduct));
        $this->assertFalse(Product::active()->get()->contains($inactiveProduct));
    }

    private function createVariant(string $sku): ProductVariant
    {
        $product = Product::factory()->create([
            'name' => 'Wholesale Baby Set',
            'regular_price' => '11.25',
            'sale_price' => null,
        ]);
        $color = Color::factory()->create(['name' => 'Black', 'code' => uniqid('BLACK')]);
        $size = Size::factory()->create(['name' => 'Medium', 'code' => uniqid('M')]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'sku' => $sku,
            'price_adjustment' => '0.00',
            'is_active' => true,
        ]);
    }
}
