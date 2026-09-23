<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Size;
use App\Models\User;
use App\Services\ProductVariantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class B2BPhaseThreeProductVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseller_username_and_mobile_login_identifier_collisions_are_blocked(): void
    {
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        User::factory()->create(['mobile' => '777548421']);

        $this->actingAs($admin)
            ->post(route('admin.resellers.store'), $this->validResellerPayload([
                'username' => '777548421',
                'mobile' => '700900100',
                'email' => null,
            ]))
            ->assertSessionHasErrors('username');

        User::factory()->create(['username' => '777111222', 'mobile' => '700900101']);

        $this->actingAs($admin)
            ->post(route('admin.resellers.store'), $this->validResellerPayload([
                'username' => 'safe-reseller',
                'mobile' => '777111222',
                'email' => null,
            ]))
            ->assertSessionHasErrors('mobile');
    }

    public function test_reseller_update_collision_checks_ignore_self_but_reject_other_accounts(): void
    {
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $reseller = $this->createReseller(['username' => '777222333', 'mobile' => '700900102']);
        User::factory()->create(['username' => '777999888', 'mobile' => '700900103']);

        $this->actingAs($admin)
            ->put(route('admin.resellers.update', $reseller), $this->validResellerPayload([
                'username' => '777222333', 'mobile' => '700900102', 'email' => $reseller->email,
            ]))
            ->assertSessionDoesntHaveErrors();

        $this->actingAs($admin)
            ->put(route('admin.resellers.update', $reseller), $this->validResellerPayload([
                'username' => '700900103', 'mobile' => '700900102', 'email' => $reseller->email,
            ]))
            ->assertSessionHasErrors('username');

        $this->actingAs($admin)
            ->put(route('admin.resellers.update', $reseller), $this->validResellerPayload([
                'username' => '777222333', 'mobile' => '777999888', 'email' => $reseller->email,
            ]))
            ->assertSessionHasErrors('mobile');
    }

    public function test_variant_identity_shapes_sku_rules_and_server_generated_key(): void
    {
        [$product, $black, $white, $medium, $large] = $this->productWithDimensions();
        $service = app(ProductVariantService::class);

        $blackMedium = $service->createVariant($product, [
            'color_id' => $black->id,
            'size_id' => $medium->id,
            'sku' => null,
            'price_adjustment' => '1.50',
            'is_active' => true,
        ]);

        $this->assertSame(ProductVariant::buildVariantKey($black->id, $medium->id), $blackMedium->variant_key);
        $this->assertSame('SET105-BLK-M', $blackMedium->sku);

        $service->createVariant($product, [
            'color_id' => $white->id,
            'size_id' => $medium->id,
            'sku' => null,
            'price_adjustment' => '0.00',
            'is_active' => true,
        ]);

        $service->createVariant($product, [
            'color_id' => $black->id,
            'size_id' => $large->id,
            'sku' => ' manual sku ',
            'price_adjustment' => '0.00',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('product_variants', ['sku' => 'MANUAL-SKU']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->createVariant($product, [
            'color_id' => $black->id,
            'size_id' => $medium->id,
            'sku' => null,
            'price_adjustment' => '0.00',
            'is_active' => true,
        ]);
    }

    public function test_color_only_size_only_default_duplicates_and_manual_sku_conflicts_are_rejected(): void
    {
        [$product, $black, , $medium] = $this->productWithDimensions();
        $service = app(ProductVariantService::class);

        $service->createVariant($product, ['color_id' => $black->id, 'size_id' => null, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]);
        $this->expectValidationExceptionFor(fn () => $service->createVariant($product, ['color_id' => $black->id, 'size_id' => null, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]));

        $service->createVariant($product, ['color_id' => null, 'size_id' => $medium->id, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]);
        $this->expectValidationExceptionFor(fn () => $service->createVariant($product, ['color_id' => null, 'size_id' => $medium->id, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]));

        $service->createVariant($product, ['color_id' => null, 'size_id' => null, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]);
        $this->expectValidationExceptionFor(fn () => $service->createVariant($product, ['color_id' => null, 'size_id' => null, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]));

        $this->expectValidationExceptionFor(fn () => $service->createVariant($product, [
            'color_id' => $black->id,
            'size_id' => $medium->id,
            'sku' => 'SET105-BLK',
            'price_adjustment' => 0,
            'is_active' => true,
        ]));
    }

    public function test_generated_sku_uses_deterministic_suffix_when_base_is_taken(): void
    {
        [$product, $black, , $medium] = $this->productWithDimensions();
        $service = app(ProductVariantService::class);
        $service->createVariant($product, [
            'color_id' => $black->id, 'size_id' => null, 'sku' => 'SET105-M',
            'price_adjustment' => 0, 'is_active' => true,
        ]);

        $generated = $service->createVariant($product, [
            'color_id' => null, 'size_id' => $medium->id, 'sku' => null,
            'price_adjustment' => 0, 'is_active' => true,
        ]);

        $this->assertSame('SET105-M-2', $generated->sku);
    }

    public function test_database_unique_key_rejects_duplicate_default_and_partial_dimensions(): void
    {
        [$product, $black] = $this->productWithDimensions();
        $service = app(ProductVariantService::class);
        $service->createVariant($product, ['color_id' => null, 'size_id' => null, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]);
        $service->createVariant($product, ['color_id' => $black->id, 'size_id' => null, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]);

        foreach ([ProductVariant::buildVariantKey(null, null), ProductVariant::buildVariantKey($black->id, null)] as $key) {
            try {
                \Illuminate\Support\Facades\DB::table('product_variants')->insert([
                    'product_id' => $product->id,
                    'color_id' => $key === ProductVariant::buildVariantKey(null, null) ? null : $black->id,
                    'size_id' => null,
                    'variant_key' => $key,
                    'sku' => 'DIRECT-'.str_replace([':', '|'], '-', $key),
                    'price_adjustment' => 0,
                    'is_active' => true,
                ]);
                $this->fail('The database accepted a duplicate variant key.');
            } catch (\Illuminate\Database\QueryException $exception) {
                $this->assertSame('23000', $exception->errorInfo[0]);
            }
        }
    }

    public function test_admin_variant_routes_are_authorized_and_nested_ownership_is_enforced(): void
    {
        [$product, $black, , $medium] = $this->productWithDimensions();
        [$otherProduct, $otherColor, , $otherSize] = $this->productWithDimensions(['SKU' => 'OTHER']);
        $variant = app(ProductVariantService::class)->createVariant($otherProduct, [
            'color_id' => $otherColor->id,
            'size_id' => $otherSize->id,
            'sku' => null,
            'price_adjustment' => 0,
            'is_active' => true,
        ]);

        $user = User::factory()->create(['utype' => User::TYPE_USER]);
        $reseller = $this->createReseller();
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        $this->actingAs($user)->get(route('admin.products.variants.index', $product))->assertForbidden();
        $this->actingAs($reseller)->get(route('admin.products.variants.index', $product))->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.products.variants.deactivate', [$product, $variant]))
            ->assertNotFound();

        $this->actingAs($admin)->get(route('admin.products.variants.index', $product))
            ->assertOk()
            ->assertSee('Candidate combinations');

        $this->actingAs($admin)
            ->put(route('admin.products.variants.update', [$product, $variant]), [
                'color_id' => $black->id, 'size_id' => $medium->id, 'sku' => 'FORGED',
                'price_adjustment' => 0, 'is_active' => 1,
            ])
            ->assertNotFound();

        $unattachedColor = Color::factory()->create();
        $this->actingAs($admin)
            ->post(route('admin.products.variants.store', $product), [
                'color_id' => $unattachedColor->id,
                'size_id' => $medium->id,
                'sku' => null,
                'price_adjustment' => 0,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('color_id');

        $this->actingAs($admin)
            ->post(route('admin.products.variants.store', $product), [
                'product_id' => $otherProduct->id,
                'variant_key' => ProductVariant::buildVariantKey($black->id, $medium->id),
                'color_id' => $black->id,
                'size_id' => $medium->id,
                'sku' => null,
                'price_adjustment' => 0,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors(['product_id', 'variant_key']);
    }

    public function test_invalid_dimensions_and_manual_sku_do_not_turn_into_default_values(): void
    {
        [$product, $black, , $medium] = $this->productWithDimensions();
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        $this->actingAs($admin)->post(route('admin.products.variants.store', $product), [
            'color_id' => 'not-an-id', 'size_id' => $medium->id, 'sku' => null,
            'price_adjustment' => 0, 'is_active' => 1,
        ])->assertSessionHasErrors('color_id');

        $this->actingAs($admin)->post(route('admin.products.variants.store', $product), [
            'color_id' => $black->id, 'size_id' => $medium->id, 'sku' => '!!!',
            'price_adjustment' => 0, 'is_active' => 1,
        ])->assertSessionHasErrors('sku');

        $this->assertSame(0, $product->variants()->count());
    }

    public function test_price_adjustment_rejects_excess_precision_and_negative_effective_price(): void
    {
        [$product, $black, , $medium] = $this->productWithDimensions();
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $payload = [
            'color_id' => $black->id, 'size_id' => $medium->id,
            'sku' => null, 'is_active' => 1,
        ];

        $this->actingAs($admin)->post(route('admin.products.variants.store', $product), $payload + [
            'price_adjustment' => '-1.001',
        ])->assertSessionHasErrors('price_adjustment');

        $this->actingAs($admin)->post(route('admin.products.variants.store', $product), $payload + [
            'price_adjustment' => '-101.00',
        ])->assertSessionHasErrors('price_adjustment');

        $this->assertSame(0, $product->variants()->count());
    }

    public function test_bulk_creation_rejects_non_candidates_without_partial_writes(): void
    {
        [$product, $black, , $medium] = $this->productWithDimensions();
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        $this->actingAs($admin)->post(route('admin.products.variants.bulk-store', $product), [
            'combinations' => [
                ProductVariant::buildVariantKey($black->id, $medium->id),
                ProductVariant::buildVariantKey($black->id, null),
            ],
        ])->assertSessionHasErrors('combinations');

        $this->assertSame(0, $product->variants()->count());
    }

    public function test_candidate_generation_and_bulk_creation_are_explicit_and_zero_inventory_only(): void
    {
        [$product, $black, $white, $medium, $large] = $this->productWithDimensions();
        $product->colors()->updateExistingPivot($black->id, ['quantity' => 99]);
        $product->sizes()->updateExistingPivot($medium->id, ['quantity' => 77]);

        $service = app(ProductVariantService::class);
        $candidates = $service->candidateCombinations($product->fresh());

        $this->assertCount(4, $candidates);
        $this->assertSame(0, $product->variants()->count());

        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $selected = [
            ProductVariant::buildVariantKey($black->id, $medium->id),
            ProductVariant::buildVariantKey($white->id, $large->id),
        ];

        $this->actingAs($admin)
            ->post(route('admin.products.variants.bulk-store', $product), ['combinations' => $selected])
            ->assertRedirect(route('admin.products.variants.index', $product));

        $this->assertSame(2, $product->variants()->count());
        $this->assertDatabaseHas('inventory_items', ['stock_on_hand' => 0, 'reserved_quantity' => 0]);
        $this->assertSame(99, (int) $product->colors()->whereKey($black->id)->first()->pivot->quantity);
        $this->assertSame(77, (int) $product->sizes()->whereKey($medium->id)->first()->pivot->quantity);
    }

    public function test_candidate_generation_for_partial_and_default_dimension_sets(): void
    {
        [$product, $black, $white, $medium] = $this->productWithDimensions();
        $service = app(ProductVariantService::class);

        $product->sizes()->detach();
        $this->assertEqualsCanonicalizing(
            [ProductVariant::buildVariantKey($black->id, null), ProductVariant::buildVariantKey($white->id, null)],
            $service->candidateCombinations($product->fresh())->pluck('key')->all()
        );

        $product->colors()->detach();
        $product->sizes()->attach($medium->id, ['quantity' => 0, 'price_adjustment' => 0]);
        $this->assertSame([ProductVariant::buildVariantKey(null, $medium->id)], $service->candidateCombinations($product->fresh())->pluck('key')->all());

        $product->sizes()->detach();
        $this->assertSame([ProductVariant::buildVariantKey(null, null)], $service->candidateCombinations($product->fresh())->pluck('key')->all());
    }

    public function test_safe_deletion_rules_and_deactivation_preserve_records(): void
    {
        [$product, $black, $white, $medium, $large] = $this->productWithDimensions();
        $service = app(ProductVariantService::class);

        $empty = $service->createVariant($product, ['color_id' => $black->id, 'size_id' => $medium->id, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]);
        $emptyId = $empty->id;
        $service->deleteIfSafe($product, $empty);
        $this->assertDatabaseMissing('product_variants', ['id' => $emptyId]);
        $this->assertDatabaseMissing('inventory_items', ['product_variant_id' => $emptyId]);

        $withReservation = $service->createVariant($product, ['color_id' => $white->id, 'size_id' => $medium->id, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]);
        $this->createReservationItem($withReservation);
        $this->expectValidationExceptionFor(fn () => $service->deleteIfSafe($product, $withReservation));

        $withMovement = $service->createVariant($product, ['color_id' => $black->id, 'size_id' => $large->id, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]);
        InventoryMovement::create([
            'product_variant_id' => $withMovement->id,
            'type' => InventoryMovement::TYPE_ADJUSTMENT,
            'quantity' => 1,
        ]);
        $this->expectValidationExceptionFor(fn () => $service->deleteIfSafe($product, $withMovement));

        $withStock = $service->createVariant($product, ['color_id' => null, 'size_id' => $large->id, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]);
        $withStock->inventoryItem->update(['stock_on_hand' => 5, 'reserved_quantity' => 0]);
        $this->expectValidationExceptionFor(fn () => $service->deleteIfSafe($product, $withStock));

        $withReserved = $service->createVariant($product, ['color_id' => $white->id, 'size_id' => $large->id, 'sku' => null, 'price_adjustment' => 0, 'is_active' => true]);
        $withReserved->inventoryItem->update(['stock_on_hand' => 5, 'reserved_quantity' => 2]);
        $this->expectValidationExceptionFor(fn () => $service->deleteIfSafe($product, $withReserved));

        $service->deactivate($product, $withReserved);
        $this->assertDatabaseHas('product_variants', ['id' => $withReserved->id, 'is_active' => false]);
        $this->assertDatabaseHas('inventory_items', ['product_variant_id' => $withReserved->id, 'stock_on_hand' => 5, 'reserved_quantity' => 2]);
    }

    public function test_historical_variant_identity_cannot_change_and_detached_inactive_variant_cannot_reactivate(): void
    {
        [$product, $black, $white, $medium] = $this->productWithDimensions();
        $service = app(ProductVariantService::class);
        $variant = $service->createVariant($product, [
            'color_id' => $black->id, 'size_id' => $medium->id, 'sku' => null,
            'price_adjustment' => 0, 'is_active' => true,
        ]);
        $this->createReservationItem($variant);

        $this->expectValidationExceptionFor(fn () => $service->updateVariant($product, $variant, [
            'color_id' => $white->id, 'size_id' => $medium->id, 'sku' => $variant->sku,
            'price_adjustment' => 0, 'is_active' => false,
        ]));

        $service->deactivate($product, $variant);
        $product->colors()->detach($black->id);
        $this->expectValidationExceptionFor(fn () => $service->activate($product, $variant));

        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $this->actingAs($admin)->get(route('admin.products.variants.index', $product))
            ->assertOk()
            ->assertSee('Detached from product');
        $this->actingAs($admin)->put(route('admin.products.variants.update', [$product, $variant]), [
            'color_id' => $black->id, 'size_id' => $medium->id,
            'sku' => 'HISTORICAL-SET105', 'price_adjustment' => '2.00', 'is_active' => 0,
        ])->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id, 'color_id' => $black->id, 'is_active' => false,
            'sku' => 'HISTORICAL-SET105',
        ]);
    }

    public function test_product_color_size_delete_and_product_edit_legacy_safety(): void
    {
        [$product, $black, , $medium] = $this->productWithDimensions();
        $variant = app(ProductVariantService::class)->createVariant($product, [
            'color_id' => $black->id,
            'size_id' => $medium->id,
            'sku' => null,
            'price_adjustment' => 0,
            'is_active' => true,
        ]);
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        $this->actingAs($admin)->delete(route('admin.product.delete', ['id' => $product->id]))->assertSessionHasErrors('product');
        $this->actingAs($admin)->delete(route('admin.color.delete', ['id' => $black->id]))->assertSessionHasErrors('color');
        $this->actingAs($admin)->delete(route('admin.size.delete', ['id' => $medium->id]))->assertSessionHasErrors('size');

        $this->actingAs($admin)
            ->put(route('admin.product.update'), $this->productUpdatePayload($product, ['colors' => [], 'sizes' => [$medium->id]]))
            ->assertSessionHasErrors('colors');

        $this->actingAs($admin)
            ->put(route('admin.product.update'), $this->productUpdatePayload($product, ['colors' => [$black->id], 'sizes' => []]))
            ->assertSessionHasErrors('sizes');

        $this->actingAs($admin)
            ->put(route('admin.product.update'), $this->productUpdatePayload($product, [
                'name' => 'Updated Baby Set',
                'colors' => [$black->id],
                'sizes' => [$medium->id],
            ]))
            ->assertRedirect(route('admin.products'));

        $this->assertSame('Updated Baby Set', $product->fresh()->name);
        $this->assertSame(42, (int) $product->fresh()->quantity);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'color_id' => $black->id, 'size_id' => $medium->id]);
    }

    public function test_product_price_update_rejects_negative_variant_price_without_persisting_changes(): void
    {
        [$product] = $this->productWithDimensions();
        $variant = $this->createPriceVariant($product, '-80.00');
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        $this->actingAs($admin)->put(route('admin.product.update'), $this->productUpdatePayload($product, [
            'regular_price' => '50.00', 'quantity' => 999,
        ]))->assertSessionHasErrors('regular_price');

        $this->assertSame('100.00', $product->fresh()->regular_price);
        $this->assertNull($product->fresh()->sale_price);
        $this->assertSame('42', (string) $product->fresh()->quantity);
        $this->assertSame('-80.00', $variant->fresh()->price_adjustment);
        $this->assertSame('20.00', $variant->fresh()->effectivePrice());
    }

    public function test_product_price_update_allows_zero_effective_variant_price(): void
    {
        [$product] = $this->productWithDimensions();
        $variant = $this->createPriceVariant($product, '-80.00');
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        $this->actingAs($admin)->put(route('admin.product.update'), $this->productUpdatePayload($product, [
            'regular_price' => '80.00',
        ]))->assertRedirect(route('admin.products'));

        $this->assertSame('80.00', $product->fresh()->current_price);
        $this->assertSame('0.00', $variant->fresh()->effectivePrice());
        $this->assertSame(42, $product->fresh()->quantity);
    }

    public function test_strictest_variant_adjustment_governs_product_price_changes(): void
    {
        [$product, $black, $white, $medium] = $this->productWithDimensions();
        $this->createPriceVariant($product, '10.00', $black->id, $medium->id);
        $this->createPriceVariant($product, '-30.00', $white->id, $medium->id);
        $strictest = $this->createPriceVariant($product, '-70.00', $black->id, null);
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        $this->actingAs($admin)->put(route('admin.product.update'), $this->productUpdatePayload($product, [
            'regular_price' => '69.99',
        ]))->assertSessionHasErrors('regular_price');
        $this->assertSame('100.00', $product->fresh()->regular_price);

        $this->actingAs($admin)->put(route('admin.product.update'), $this->productUpdatePayload($product, [
            'regular_price' => '70.00',
        ]))->assertRedirect(route('admin.products'));
        $this->assertSame('0.00', $strictest->fresh()->effectivePrice());
    }

    public function test_product_price_guard_uses_valid_sale_price(): void
    {
        [$product] = $this->productWithDimensions(['sale_price' => '70.00']);
        $variant = $this->createPriceVariant($product, '-60.00');
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        $this->assertSame('70.00', $product->current_price);
        $this->actingAs($admin)->put(route('admin.product.update'), $this->productUpdatePayload($product, [
            'sale_price' => '50.00',
        ]))->assertSessionHasErrors('sale_price');

        $this->assertSame('70.00', $product->fresh()->sale_price);
        $this->assertSame('10.00', $variant->fresh()->effectivePrice());
    }

    public function test_zero_null_and_non_discount_sale_prices_use_regular_price(): void
    {
        [$product] = $this->productWithDimensions();
        $variant = $this->createPriceVariant($product, '-80.00');
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        foreach (['0.00', null, '80.00', '90.00'] as $salePrice) {
            $this->actingAs($admin)->put(route('admin.product.update'), $this->productUpdatePayload($product->fresh(), [
                'regular_price' => '80.00', 'sale_price' => $salePrice,
            ]))->assertRedirect(route('admin.products'));

            $this->assertSame('80.00', $product->fresh()->current_price);
            $this->assertSame('0.00', $variant->fresh()->effectivePrice());
        }

        $this->actingAs($admin)->put(route('admin.product.update'), $this->productUpdatePayload($product->fresh(), [
            'regular_price' => '79.99', 'sale_price' => null,
        ]))->assertSessionHasErrors('regular_price');
    }

    public function test_variant_create_and_update_still_reject_negative_prices_and_allow_zero(): void
    {
        [$product, $black, , $medium] = $this->productWithDimensions();
        $service = app(ProductVariantService::class);
        $variant = $this->createPriceVariant($product, '-100.00', $black->id, $medium->id);
        $this->assertSame('0.00', $variant->effectivePrice());

        $this->expectValidationExceptionFor(fn () => $this->createPriceVariant($product, '-100.01', null, $medium->id));
        $this->expectValidationExceptionFor(fn () => $service->updateVariant($product, $variant, [
            'color_id' => $black->id, 'size_id' => $medium->id,
            'sku' => $variant->sku, 'price_adjustment' => '-100.01', 'is_active' => true,
        ]));

        $this->assertSame('-100.00', $variant->fresh()->price_adjustment);
    }

    public function test_existing_invalid_variant_price_is_visible_and_is_not_rewritten(): void
    {
        [$product] = $this->productWithDimensions();
        $variant = $this->createPriceVariant($product, '-80.00');
        \Illuminate\Support\Facades\DB::table('product_variants')->where('id', $variant->id)
            ->update(['price_adjustment' => '-120.00']);
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        $this->assertSame('-20.00', $variant->fresh()->effectivePrice());
        $this->actingAs($admin)->put(route('admin.product.update'), $this->productUpdatePayload($product, [
            'regular_price' => '110.00',
        ]))->assertSessionHasErrors('regular_price');
        $this->assertSame('100.00', $product->fresh()->regular_price);
        $this->assertSame('-120.00', $variant->fresh()->price_adjustment);

        $this->actingAs($admin)->put(route('admin.product.update'), $this->productUpdatePayload($product->fresh(), [
            'name' => 'Price Audit Name', 'sale_price' => null,
        ]))->assertRedirect(route('admin.products'));
        $this->assertSame('Price Audit Name', $product->fresh()->name);
        $this->assertSame('-120.00', $variant->fresh()->price_adjustment);
    }

    private function createPriceVariant(Product $product, string $adjustment, ?int $colorId = null, ?int $sizeId = null): ProductVariant
    {
        return app(ProductVariantService::class)->createVariant($product, [
            'color_id' => $colorId,
            'size_id' => $sizeId,
            'sku' => null,
            'price_adjustment' => $adjustment,
            'is_active' => true,
        ]);
    }

    private function productWithDimensions(array $productOverrides = []): array
    {
        $product = Product::factory()->create(array_merge([
            'name' => 'Baby Set 105',
            'SKU' => 'SET105',
            'regular_price' => '100.00',
            'sale_price' => null,
            'quantity' => 42,
            'is_offer' => false,
            'stock_status' => 'instock',
        ], $productOverrides));

        $black = Color::factory()->create(['name' => 'Black', 'code' => $this->uniqueDimensionCode('colors', 'BLK')]);
        $white = Color::factory()->create(['name' => 'White', 'code' => $this->uniqueDimensionCode('colors', 'WHT')]);
        $medium = Size::factory()->create(['name' => 'Medium', 'code' => $this->uniqueDimensionCode('sizes', 'M')]);
        $large = Size::factory()->create(['name' => 'Large', 'code' => $this->uniqueDimensionCode('sizes', 'L')]);

        $product->colors()->attach([
            $black->id => ['quantity' => 0, 'price_adjustment' => 0],
            $white->id => ['quantity' => 0, 'price_adjustment' => 0],
        ]);
        $product->sizes()->attach([
            $medium->id => ['quantity' => 0, 'price_adjustment' => 0],
            $large->id => ['quantity' => 0, 'price_adjustment' => 0],
        ]);

        return [$product->fresh(), $black, $white, $medium, $large];
    }

    private function uniqueDimensionCode(string $table, string $baseCode): string
    {
        if (! \Illuminate\Support\Facades\DB::table($table)->where('code', $baseCode)->exists()) {
            return $baseCode;
        }

        return $baseCode.uniqid();
    }

    private function expectValidationExceptionFor(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Expected validation exception was not thrown.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertNotEmpty($exception->errors());
        }
    }

    private function createReservationItem(ProductVariant $variant): void
    {
        $profile = ResellerProfile::create([
            'user_id' => User::factory()->create()->id,
            'status' => ResellerProfile::STATUS_ACTIVE,
        ]);

        $reservation = Reservation::create([
            'reservation_number' => 'RSV-'.uniqid(),
            'reseller_profile_id' => $profile->id,
            'status' => Reservation::STATUS_PENDING_REVIEW,
        ]);

        ReservationItem::create([
            'reservation_id' => $reservation->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => '10.00',
            'product_name_snapshot' => $variant->product->name,
            'sku_snapshot' => $variant->sku,
        ]);
    }

    private function productUpdatePayload(Product $product, array $overrides = []): array
    {
        return array_merge([
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'regular_price' => $product->regular_price,
            'sale_price' => '0.00',
            'SKU' => $product->SKU,
            'stock_status' => $product->stock_status,
            'featured' => $product->featured ? 1 : 0,
            'is_offer' => $product->is_offer ? 1 : 0,
            'quantity' => $product->quantity,
            'category_id' => $product->category_id,
            'category_ids' => [$product->category_id],
            'brand_id' => $product->brand_id,
            'colors' => $product->colors()->pluck('colors.id')->all(),
            'sizes' => $product->sizes()->pluck('sizes.id')->all(),
            'dimensions' => null,
            'weight' => null,
        ], $overrides);
    }

    private function validResellerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Sana Reseller',
            'username' => 'sana-reseller',
            'email' => 'sana-reseller@example.com',
            'mobile' => '700100200',
            'password' => 'temporary-password',
            'password_confirmation' => 'temporary-password',
            'business_name' => 'Sana Kids Wholesale',
            'whatsapp' => '700100200',
            'governorate' => 'Sana',
            'group_name' => 'A',
            'notes' => 'Phase 3 account',
            'reservation_enabled' => true,
            'reservation_timeout_minutes' => 45,
        ], $overrides);
    }

    private function createReseller(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'username' => 'reseller-'.uniqid(),
            'utype' => User::TYPE_RESELLER,
            'password' => Hash::make('password'),
            'force_password_change' => false,
        ], $overrides));

        $user->resellerProfile()->create([
            'business_name' => 'Wholesale Account',
            'whatsapp' => $user->mobile,
            'status' => ResellerProfile::STATUS_ACTIVE,
            'reservation_enabled' => true,
            'reservation_timeout_minutes' => 30,
        ]);

        return $user->load('resellerProfile');
    }
}
