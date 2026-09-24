<?php

namespace App\Services;

use App\Repositories\ResellerPortalRepository;
use App\Support\DecimalMoney;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Session intent only. No inventory is held until ReservationService commits. */
class ResellerReservationCartService
{
    public const MAX_LINES = 100;

    public function __construct(private readonly Session $session, private readonly ResellerPortalRepository $repository) {}

    private function key(int $profileId): string
    {
        return 'b2b_reservation_cart.'.$profileId;
    }

    public function items(int $profileId): array
    {
        return $this->session->get($this->key($profileId).'.items', []);
    }

    public function put(int $profileId, int $variantId, int $quantity, bool $add = false): void
    {
        $items = $this->items($profileId);
        $quantity += $add ? ($items[$variantId] ?? 0) : 0;
        $variant = $this->repository->variants([$variantId])->get($variantId);
        if (! $variant || ! $variant->is_active || ! $variant->product || ! $variant->inventoryItem) {
            throw ValidationException::withMessages(['product_variant_id' => __('A requested variant is unavailable.')]);
        }
        if ($quantity < 1 || $quantity > InventoryService::MAX_QUANTITY || $quantity > $variant->inventoryItem->available_quantity) {
            throw ValidationException::withMessages(['quantity' => __('Insufficient available stock.')]);
        }
        if (! isset($items[$variantId]) && count($items) >= self::MAX_LINES) {
            throw ValidationException::withMessages(['items' => __('The reservation cart is full.')]);
        }
        $items[$variantId] = $quantity;
        $this->save($profileId, $items);
    }

    public function remove(int $profileId, int $variantId): void
    {
        $items = $this->items($profileId);
        unset($items[$variantId]);
        $this->save($profileId, $items);
    }

    private function save(int $profileId, array $items): void
    {
        $this->session->put($this->key($profileId).'.items', $items);
        // An explicit cart edit starts a new form intent; failed POSTs never rotate it.
        $this->session->forget($this->key($profileId).'.idempotency_key');
    }

    public function clear(int $profileId): void
    {
        $this->session->forget($this->key($profileId));
    }

    public function preview(int $profileId): array
    {
        $items = $this->items($profileId);
        $variants = $this->repository->variants(array_keys($items));
        $lines = [];
        $total = 0;
        foreach ($items as $id => $quantity) {
            $variant = $variants->get($id);
            $available = $variant?->is_active ? ($variant->inventoryItem?->available_quantity ?? 0) : 0;
            $price = $variant?->product ? $variant->effectivePrice() : null;
            $cents = $price === null ? 0 : DecimalMoney::toCents($price);
            $safeTotal = $cents >= 0 && ($cents === 0 || $quantity <= intdiv(PHP_INT_MAX - $total, $cents));
            $lineTotal = $safeTotal ? $cents * $quantity : 0;
            $total += $lineTotal;
            $lines[] = ['product_variant_id' => $id, 'quantity' => $quantity, 'variant' => $variant,
                'available' => $available, 'price' => $price, 'line_total' => DecimalMoney::formatCents($lineTotal),
                'unavailable' => ! $variant?->product || $available < $quantity || ! $safeTotal];
        }
        $keyPath = $this->key($profileId).'.idempotency_key';
        if (! $this->session->has($keyPath)) {
            $this->session->put($keyPath, (string) Str::uuid());
        }

        // Current prices are a preview only; the domain creates immutable snapshots.
        return ['lines' => $lines, 'total' => DecimalMoney::formatCents($total), 'idempotencyKey' => $this->session->get($keyPath)];
    }
}
