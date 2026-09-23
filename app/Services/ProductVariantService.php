<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\DecimalMoney;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductVariantService
{
    public function __construct(private readonly AuditService $auditService) {}

    public static function normalizeSku(?string $sku): ?string
    {
        if (! filled($sku)) {
            return null;
        }

        $normalized = Str::upper(trim((string) $sku));
        $normalized = preg_replace('/[^\p{Arabic}\p{L}\p{N}]+/u', '-', $normalized) ?? '';
        $normalized = trim($normalized, '-');
        $normalized = mb_substr($normalized, 0, 255, 'UTF-8');

        return $normalized !== '' ? $normalized : null;
    }

    public function createVariant(Product $product, array $data): ProductVariant
    {
        return DB::transaction(function () use ($product, $data): ProductVariant {
            $data = $this->normalizeVariantData($data);
            $this->validateDimensionsBelongToProduct($product, $data['color_id'], $data['size_id']);
            $this->validateUniqueIdentity($product, $data['color_id'], $data['size_id']);
            $this->validateEffectivePrice($product, $data['price_adjustment']);

            $manualSku = filled($data['sku']);
            $sku = $manualSku
                ? $this->normalizeManualSku($data['sku'])
                : $this->generateSku($product, $data['color_id'], $data['size_id']);

            if ($manualSku && ProductVariant::where('sku', $sku)->exists()) {
                throw ValidationException::withMessages([
                    'sku' => __('The variant SKU has already been taken.'),
                ]);
            }

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'color_id' => $data['color_id'],
                'size_id' => $data['size_id'],
                'sku' => $sku,
                'price_adjustment' => $data['price_adjustment'],
                'is_active' => $data['is_active'],
            ]);

            $this->ensureInventoryItem($variant);
            $this->log('product_variant_created', $variant);

            return $variant->load(['color', 'size', 'inventoryItem']);
        });
    }

    public function updateVariant(Product $product, ProductVariant $variant, array $data): ProductVariant
    {
        $this->assertVariantBelongsToProduct($product, $variant);

        return DB::transaction(function () use ($product, $variant, $data): ProductVariant {
            $data = $this->normalizeVariantData($data, $variant);
            $dimensionsChanged = $data['color_id'] != $variant->color_id || $data['size_id'] != $variant->size_id;
            if ($dimensionsChanged || $data['is_active']) {
                $this->validateDimensionsBelongToProduct($product, $data['color_id'], $data['size_id']);
            }
            if ($dimensionsChanged) {
                $this->guardIdentityChangeWithHistory($variant);
            }
            $this->validateUniqueIdentity($product, $data['color_id'], $data['size_id'], $variant);
            $this->validateEffectivePrice($product, $data['price_adjustment']);

            $manualSku = filled($data['sku']);
            $sku = $manualSku
                ? $this->normalizeManualSku($data['sku'])
                : $this->generateSku($product, $data['color_id'], $data['size_id'], $variant);

            if ($manualSku && ProductVariant::where('sku', $sku)->whereKeyNot($variant->id)->exists()) {
                throw ValidationException::withMessages([
                    'sku' => __('The variant SKU has already been taken.'),
                ]);
            }

            $variant->update([
                'color_id' => $data['color_id'],
                'size_id' => $data['size_id'],
                'sku' => $sku,
                'price_adjustment' => $data['price_adjustment'],
                'is_active' => $data['is_active'],
            ]);

            $this->ensureInventoryItem($variant);
            $this->log('product_variant_updated', $variant);

            return $variant->refresh()->load(['color', 'size', 'inventoryItem']);
        });
    }

    public function createSelectedCombinations(Product $product, array $combinationKeys): Collection
    {
        return DB::transaction(function () use ($product, $combinationKeys): Collection {
            $created = collect();
            $seen = [];
            $candidateKeys = $this->candidateCombinations($product)->pluck('key')->all();

            foreach ($combinationKeys as $combinationKey) {
                [$colorId, $sizeId] = $this->parseCombinationKey($combinationKey);
                $variantKey = ProductVariant::buildVariantKey($colorId, $sizeId);

                if (isset($seen[$variantKey])) {
                    throw ValidationException::withMessages([
                        'combinations' => __('Duplicate variant combinations were selected.'),
                    ]);
                }

                $seen[$variantKey] = true;
                if (! in_array($variantKey, $candidateKeys, true)) {
                    throw ValidationException::withMessages([
                        'combinations' => __('A selected combination is not in the current product candidates.'),
                    ]);
                }
                $this->validateDimensionsBelongToProduct($product, $colorId, $sizeId);
                $this->validateUniqueIdentity($product, $colorId, $sizeId);

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'color_id' => $colorId,
                    'size_id' => $sizeId,
                    'sku' => $this->generateSku($product, $colorId, $sizeId),
                    'price_adjustment' => '0.00',
                    'is_active' => true,
                ]);

                $this->ensureInventoryItem($variant);
                $created->push($variant->load(['color', 'size', 'inventoryItem']));
            }

            if ($created->isNotEmpty()) {
                $this->auditService->log('product_variants_bulk_created', [
                    'product_id' => $product->id,
                    'variant_ids' => $created->pluck('id')->all(),
                ]);
            }

            return $created;
        });
    }

    public function activate(Product $product, ProductVariant $variant): ProductVariant
    {
        $this->assertVariantBelongsToProduct($product, $variant);
        $this->validateDimensionsBelongToProduct($product, $variant->color_id, $variant->size_id);
        $variant->update(['is_active' => true]);
        $this->log('product_variant_activated', $variant);

        return $variant->refresh();
    }

    public function deactivate(Product $product, ProductVariant $variant): ProductVariant
    {
        $this->assertVariantBelongsToProduct($product, $variant);
        $variant->update(['is_active' => false]);
        $this->log('product_variant_deactivated', $variant);

        return $variant->refresh();
    }

    public function deleteIfSafe(Product $product, ProductVariant $variant): void
    {
        $this->assertVariantBelongsToProduct($product, $variant);

        DB::transaction(function () use ($variant): void {
            $variant->loadMissing('inventoryItem');

            if ($variant->reservationItems()->exists()) {
                throw ValidationException::withMessages([
                    'variant' => __('This variant has reservation history. Deactivate it instead.'),
                ]);
            }

            if ($variant->inventoryMovements()->exists()) {
                throw ValidationException::withMessages([
                    'variant' => __('This variant has inventory movement history. Deactivate it instead.'),
                ]);
            }

            $inventoryItem = $variant->inventoryItem;

            if ($inventoryItem && ((int) $inventoryItem->stock_on_hand > 0 || (int) $inventoryItem->reserved_quantity > 0)) {
                throw ValidationException::withMessages([
                    'variant' => __('This variant has stock or reserved quantity. Deactivate it instead.'),
                ]);
            }

            $details = $this->auditDetails($variant);
            $inventoryItem?->delete();
            $variant->delete();

            $this->auditService->log('product_variant_deleted', $details);
        });
    }

    public function candidateCombinations(Product $product): Collection
    {
        $product->loadMissing(['colors', 'sizes', 'variants']);

        $colors = $product->colors->values();
        $sizes = $product->sizes->values();
        $existing = $product->variants->keyBy('variant_key');

        if ($colors->isEmpty() && $sizes->isEmpty()) {
            return collect([$this->candidate(null, null, $existing)]);
        }

        if ($colors->isNotEmpty() && $sizes->isNotEmpty()) {
            return $colors->flatMap(fn ($color) => $sizes->map(fn ($size) => $this->candidate($color, $size, $existing)))->values();
        }

        if ($colors->isNotEmpty()) {
            return $colors->map(fn ($color) => $this->candidate($color, null, $existing))->values();
        }

        return $sizes->map(fn ($size) => $this->candidate(null, $size, $existing))->values();
    }

    public function generateSku(Product $product, ?int $colorId, ?int $sizeId, ?ProductVariant $ignoreVariant = null): string
    {
        $product->loadMissing(['colors', 'sizes']);

        $parts = [self::normalizeSku($product->SKU) ?: 'PRODUCT-'.$product->id];

        $color = $colorId ? $product->colors->firstWhere('id', $colorId) : null;
        $size = $sizeId ? $product->sizes->firstWhere('id', $sizeId) : null;

        if ($color) {
            $parts[] = self::normalizeSku($color->code ?: $color->name) ?: 'COLOR-'.$color->id;
        }

        if ($size) {
            $parts[] = self::normalizeSku($size->code ?: $size->name) ?: 'SIZE-'.$size->id;
        }

        if (! $color && ! $size) {
            $parts[] = 'DEFAULT';
        }

        $baseSku = mb_substr(implode('-', $parts), 0, 255, 'UTF-8');
        $sku = $baseSku;
        $counter = 2;

        while (
            ProductVariant::where('sku', $sku)
                ->when($ignoreVariant, fn ($query) => $query->whereKeyNot($ignoreVariant->id))
                ->exists()
        ) {
            $suffix = '-'.$counter;
            $sku = mb_substr($baseSku, 0, 255 - mb_strlen($suffix, 'UTF-8'), 'UTF-8').$suffix;
            $counter++;
        }

        return $sku;
    }

    public function assertVariantBelongsToProduct(Product $product, ProductVariant $variant): void
    {
        abort_unless((int) $variant->product_id === (int) $product->id, 404);
    }

    public function parseCombinationKey(string $combinationKey): array
    {
        if (! preg_match('/^C:(\d+)\|S:(\d+)$/', $combinationKey, $matches)) {
            throw ValidationException::withMessages([
                'combinations' => __('Invalid variant combination selected.'),
            ]);
        }

        $colorId = (int) $matches[1];
        $sizeId = (int) $matches[2];

        return [$colorId > 0 ? $colorId : null, $sizeId > 0 ? $sizeId : null];
    }

    private function normalizeVariantData(array $data, ?ProductVariant $variant = null): array
    {
        $priceAdjustment = $data['price_adjustment'] ?? $variant?->price_adjustment ?? 0;
        if (! is_numeric($priceAdjustment)
            || ! preg_match('/^-?\d+(?:\.\d{1,2})?$/', (string) $priceAdjustment)
            || abs((float) $priceAdjustment) > 9999999999.99) {
            throw ValidationException::withMessages([
                'price_adjustment' => __('The price adjustment must fit a decimal amount with two places.'),
            ]);
        }

        return [
            'color_id' => filled($data['color_id'] ?? null) ? (int) $data['color_id'] : null,
            'size_id' => filled($data['size_id'] ?? null) ? (int) $data['size_id'] : null,
            'sku' => $data['sku'] ?? null,
            'price_adjustment' => number_format((float) $priceAdjustment, 2, '.', ''),
            'is_active' => (bool) ($data['is_active'] ?? $variant?->is_active ?? true),
        ];
    }

    private function normalizeManualSku(?string $sku): string
    {
        $normalized = self::normalizeSku($sku);

        if (! $normalized) {
            throw ValidationException::withMessages([
                'sku' => __('The variant SKU is invalid.'),
            ]);
        }

        return $normalized;
    }

    private function validateDimensionsBelongToProduct(Product $product, ?int $colorId, ?int $sizeId): void
    {
        if ($colorId && ! $product->colors()->whereKey($colorId)->exists()) {
            throw ValidationException::withMessages([
                'color_id' => __('The selected color is not attached to this product.'),
            ]);
        }

        if ($sizeId && ! $product->sizes()->whereKey($sizeId)->exists()) {
            throw ValidationException::withMessages([
                'size_id' => __('The selected size is not attached to this product.'),
            ]);
        }
    }

    private function validateUniqueIdentity(Product $product, ?int $colorId, ?int $sizeId, ?ProductVariant $ignoreVariant = null): void
    {
        $variantKey = ProductVariant::buildVariantKey($colorId, $sizeId);
        $exists = $product->variants()
            ->where('variant_key', $variantKey)
            ->when($ignoreVariant, fn ($query) => $query->whereKeyNot($ignoreVariant->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'variant' => __('This exact product variant already exists.'),
            ]);
        }
    }

    private function guardIdentityChangeWithHistory(ProductVariant $variant): void
    {
        $inventory = $variant->inventoryItem;

        if ($variant->reservationItems()->exists()
            || $variant->inventoryMovements()->exists()
            || ($inventory && ((int) $inventory->stock_on_hand !== 0 || (int) $inventory->reserved_quantity !== 0))) {
            throw ValidationException::withMessages([
                'variant' => __('This variant has history or stock. Deactivate it and create a new variant for the new combination.'),
            ]);
        }
    }

    private function validateEffectivePrice(Product $product, string $priceAdjustment): void
    {
        if (DecimalMoney::toCents($product->current_price) + DecimalMoney::toCents($priceAdjustment) < 0) {
            throw ValidationException::withMessages([
                'price_adjustment' => __('The variant price adjustment cannot make the effective price negative.'),
            ]);
        }
    }

    private function ensureInventoryItem(ProductVariant $variant): InventoryItem
    {
        return $variant->inventoryItem()->firstOrCreate([], [
            'stock_on_hand' => 0,
            'reserved_quantity' => 0,
        ]);
    }

    private function candidate($color, $size, Collection $existing): array
    {
        $key = ProductVariant::buildVariantKey($color?->id, $size?->id);
        $variant = $existing->get($key);

        return [
            'key' => $key,
            'color_id' => $color?->id,
            'size_id' => $size?->id,
            'color_name' => $color?->name,
            'size_name' => $size?->name,
            'label' => trim(($color?->name ?: __('No color')).' / '.($size?->name ?: __('No size'))),
            'exists' => (bool) $variant,
            'variant_id' => $variant?->id,
        ];
    }

    private function log(string $action, ProductVariant $variant): void
    {
        $this->auditService->log($action, $this->auditDetails($variant));
    }

    private function auditDetails(ProductVariant $variant): array
    {
        return [
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'sku' => $variant->sku,
            'color_id' => $variant->color_id,
            'size_id' => $variant->size_id,
        ];
    }
}
