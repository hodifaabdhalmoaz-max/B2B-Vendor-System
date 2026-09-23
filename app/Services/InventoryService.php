<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The sole production boundary for real B2B stock changes.
 *
 * Call in ascending variant ID order when an outer transaction changes multiple
 * variants. Nested Laravel transactions use savepoints, so the caller can roll
 * back the whole future reservation when any one operation fails.
 */
class InventoryService
{
    public const MAX_QUANTITY = 4294967295; // MySQL UNSIGNED INT

    public function stockIn(ProductVariant $variant, int $quantity, string $idempotencyKey, array $context = []): InventoryMovement
    {
        return $this->mutate($variant, InventoryMovement::TYPE_STOCK_IN, $quantity, $idempotencyKey, $context);
    }

    public function stockOut(ProductVariant $variant, int $quantity, string $idempotencyKey, array $context = []): InventoryMovement
    {
        return $this->mutate($variant, InventoryMovement::TYPE_STOCK_OUT, $quantity, $idempotencyKey, $context);
    }

    public function reserve(ProductVariant $variant, int $quantity, string $idempotencyKey, array $context = []): InventoryMovement
    {
        return $this->mutate($variant, InventoryMovement::TYPE_RESERVE, $quantity, $idempotencyKey, $context);
    }

    public function release(ProductVariant $variant, int $quantity, string $idempotencyKey, array $context = []): InventoryMovement
    {
        return $this->mutate($variant, InventoryMovement::TYPE_RELEASE, $quantity, $idempotencyKey, $context);
    }

    public function consumeReserved(ProductVariant $variant, int $quantity, string $idempotencyKey, array $context = []): InventoryMovement
    {
        return $this->mutate($variant, InventoryMovement::TYPE_ORDER_COMPLETED, $quantity, $idempotencyKey, $context);
    }

    public function adjustStockTo(ProductVariant $variant, int $targetOnHand, string $idempotencyKey, array $context = []): InventoryMovement
    {
        return $this->mutate($variant, InventoryMovement::TYPE_ADJUSTMENT, $targetOnHand, $idempotencyKey, $context);
    }

    private function mutate(ProductVariant $variant, string $type, int $requested, string $key, array $context): InventoryMovement
    {
        if ($requested < ($type === InventoryMovement::TYPE_ADJUSTMENT ? 0 : 1) || $requested > self::MAX_QUANTITY) {
            $this->fail('quantity', __('The inventory quantity is outside the supported range.'));
        }
        if (trim($key) === '' || strlen($key) > 255) {
            $this->fail('idempotency_key', __('A valid idempotency key is required.'));
        }

        $referenceType = $context['reference_type'] ?? null;
        $referenceId = $context['reference_id'] ?? null;
        $actorId = $context['actor_user_id'] ?? null;
        $metadata = $context['metadata'] ?? null;
        if (($referenceType !== null && (! is_string($referenceType) || trim($referenceType) === '' || strlen($referenceType) > 255))
            || ($referenceId !== null && (! is_int($referenceId) || $referenceId <= 0))
            || ($actorId !== null && (! is_int($actorId) || $actorId <= 0))
            || ($metadata !== null && ! is_array($metadata))) {
            $this->fail('context', __('Invalid inventory operation context.'));
        }

        $payload = [
            'variant_id' => $variant->getKey(),
            'type' => $type,
            'requested' => $requested,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'actor_user_id' => $actorId,
            'metadata' => $this->canonicalize($metadata),
        ];
        $fingerprint = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));

        try {
            return DB::transaction(function () use ($variant, $type, $requested, $key, $context, $fingerprint): InventoryMovement {
                // Avoid a gap lock on an absent unique key. A racing insert is
                // resolved by the unique index after this transaction rolls back.
                $existing = InventoryMovement::where('idempotency_key', $key)->first();
                if ($existing) {
                    return $this->replayOrConflict($existing, $fingerprint);
                }

                // Consistent lock order: variant first, then its inventory item.
                $currentVariant = ProductVariant::whereKey($variant->getKey())->lockForUpdate()->firstOrFail();
                $item = InventoryItem::where('product_variant_id', $variant->getKey())->lockForUpdate()->first();
                if (! $item) {
                    $this->fail('variant', __('Inventory item is missing for this variant.'));
                }
                $this->assertInvariant($item);

                $stock = (int) $item->stock_on_hand;
                $reserved = (int) $item->reserved_quantity;
                $available = $stock - $reserved;
                $stockDelta = 0;
                $reservedDelta = 0;

                switch ($type) {
                    case InventoryMovement::TYPE_STOCK_IN:
                        if ($requested > self::MAX_QUANTITY - $stock) {
                            $this->fail('quantity', __('Stock on hand would exceed the supported range.'));
                        }
                        $stockDelta = $requested;
                        break;
                    case InventoryMovement::TYPE_STOCK_OUT:
                        if ($requested > $available) {
                            $this->fail('quantity', __('Insufficient available stock.'));
                        }
                        $stockDelta = -$requested;
                        break;
                    case InventoryMovement::TYPE_RESERVE:
                        if (! $currentVariant->is_active) {
                            $this->fail('variant', __('Inactive variants cannot receive new reservations.'));
                        }
                        if ($requested > $available) {
                            $this->fail('quantity', __('Insufficient available stock.'));
                        }
                        $reservedDelta = $requested;
                        break;
                    case InventoryMovement::TYPE_RELEASE:
                        if ($requested > $reserved) {
                            $this->fail('quantity', __('Insufficient reserved quantity.'));
                        }
                        $reservedDelta = -$requested;
                        break;
                    case InventoryMovement::TYPE_ORDER_COMPLETED:
                        if ($requested > $reserved || $requested > $stock) {
                            $this->fail('quantity', __('Insufficient reserved quantity.'));
                        }
                        $stockDelta = -$requested;
                        $reservedDelta = -$requested;
                        break;
                    case InventoryMovement::TYPE_ADJUSTMENT:
                        if ($requested < $reserved) {
                            $this->fail('quantity', __('Physical stock cannot be adjusted below reserved quantity.'));
                        }
                        $stockDelta = $requested - $stock;
                        if ($stockDelta === 0) {
                            $this->fail('quantity', __('Stock is already at the requested quantity.'));
                        }
                        break;
                }

                $item->stock_on_hand = $stock + $stockDelta;
                $item->reserved_quantity = $reserved + $reservedDelta;
                $item->save();

                return InventoryMovement::create([
                    'product_variant_id' => $variant->getKey(),
                    'type' => $type,
                    'quantity' => $type === InventoryMovement::TYPE_ADJUSTMENT ? abs($stockDelta) : $requested,
                    'stock_delta' => $stockDelta,
                    'reserved_delta' => $reservedDelta,
                    'stock_on_hand_after' => $item->stock_on_hand,
                    'reserved_quantity_after' => $item->reserved_quantity,
                    'idempotency_key' => $key,
                    'request_fingerprint' => $fingerprint,
                    'reference_type' => $context['reference_type'] ?? null,
                    'reference_id' => $context['reference_id'] ?? null,
                    'actor_user_id' => $context['actor_user_id'] ?? null,
                    'metadata' => $context['metadata'] ?? null,
                ]);
            });
        } catch (QueryException $exception) {
            if (! $this->isIdempotencyUniqueViolation($exception)) {
                throw $exception;
            }

            // The failed transaction (including its item update) has rolled back.
            $existing = InventoryMovement::where('idempotency_key', $key)->lockForUpdate()->first();
            if (! $existing) {
                throw $exception;
            }

            return $this->replayOrConflict($existing, $fingerprint);
        }
    }

    private function replayOrConflict(InventoryMovement $movement, string $fingerprint): InventoryMovement
    {
        if ($movement->request_fingerprint !== $fingerprint) {
            $this->fail('idempotency_key', __('Idempotency key conflicts with a different inventory operation.'));
        }

        return $movement;
    }

    private function assertInvariant(InventoryItem $item): void
    {
        if ($item->stock_on_hand < 0 || $item->reserved_quantity < 0 || $item->reserved_quantity > $item->stock_on_hand
            || $item->stock_on_hand > self::MAX_QUANTITY || $item->reserved_quantity > self::MAX_QUANTITY) {
            $this->fail('variant', __('Persisted inventory balances are invalid.'));
        }
    }

    private function isIdempotencyUniqueViolation(QueryException $exception): bool
    {
        $state = (string) ($exception->errorInfo[0] ?? '');
        $code = (int) ($exception->errorInfo[1] ?? 0);
        $message = strtolower($exception->getMessage());

        return (($state === '23000' && in_array($code, [19, 1062], true)) || $state === '23505')
            && (str_contains($message, 'inventory_movements.idempotency_key')
                || str_contains($message, 'inventory_movements_idempotency_key_unique'));
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        ksort($value);

        return array_map(fn ($entry) => $this->canonicalize($entry), $value);
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
