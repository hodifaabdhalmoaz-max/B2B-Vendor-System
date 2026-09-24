<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\User;
use App\Support\DecimalMoney;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** The sole domain boundary for reservation inventory and state changes. */
class ReservationService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly AuditService $audit,
    ) {}

    /**
     * @param  array<int, array{product_variant_id: int, quantity: int}>  $items
     */
    public function create(ResellerProfile $profile, array $items, string $idempotencyKey, ?string $notes = null): Reservation
    {
        $items = $this->normalizeItems($items);
        if (trim($idempotencyKey) === '' || strlen($idempotencyKey) > 255) {
            $this->fail('idempotency_key', __('A valid idempotency key is required.'));
        }
        if ($notes !== null && mb_strlen($notes) > 65535) {
            $this->fail('notes', __('Reservation notes are too long.'));
        }

        $fingerprint = hash('sha256', json_encode([
            'reseller_profile_id' => $profile->getKey(),
            'items' => $items,
            'notes' => $notes,
        ], JSON_THROW_ON_ERROR));

        $existing = Reservation::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $this->replayOrConflict($existing, $fingerprint)->load('reservationItems');
        }

        // Only a generated number collision is retried. A duplicate creation key
        // is resolved against the committed winner after the losing transaction rolls back.
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                [$reservation, $created] = DB::transaction(function () use ($profile, $items, $idempotencyKey, $fingerprint, $notes): array {
                    $existing = Reservation::where('idempotency_key', $idempotencyKey)->first();
                    if ($existing) {
                        return [$this->replayOrConflict($existing, $fingerprint), false];
                    }

                    $currentProfile = ResellerProfile::whereKey($profile->getKey())->lockForUpdate()->first();
                    $user = $currentProfile ? User::whereKey($currentProfile->user_id)->lockForUpdate()->first() : null;
                    if (! $currentProfile || $currentProfile->status !== ResellerProfile::STATUS_ACTIVE
                        || ! $currentProfile->reservation_enabled || ! $user || ! $user->is_active
                        || $user->utype !== User::TYPE_RESELLER) {
                        $this->fail('reseller_profile', __('This reseller cannot create reservations.'));
                    }

                    $timeout = $currentProfile->reservation_timeout_minutes;
                    if ($timeout !== null && $timeout <= 0) {
                        $this->fail('reseller_profile', __('The reservation timeout is invalid.'));
                    }

                    $reservation = Reservation::create([
                        'reservation_number' => 'RSV-'.now()->format('Ymd').'-'.Str::ulid(),
                        'reseller_profile_id' => $currentProfile->id,
                        'idempotency_key' => $idempotencyKey,
                        'request_fingerprint' => $fingerprint,
                        'status' => Reservation::STATUS_PENDING_REVIEW,
                        'expires_at' => $timeout === null ? null : now()->addMinutes($timeout),
                        'notes' => $notes,
                    ]);

                    $totalCents = 0;
                    foreach ($items as $requested) {
                        // Phase 4 locks variant then inventory item. This outer lock keeps
                        // price and descriptive snapshots aligned with that same variant.
                        $variant = ProductVariant::whereKey($requested['product_variant_id'])->lockForUpdate()->first();
                        if (! $variant || ! $variant->is_active) {
                            $this->fail('items', __('A requested variant is unavailable.'));
                        }
                        $variant->load(['product', 'color', 'size']);
                        if (! $variant->product) {
                            $this->fail('items', __('A requested product is unavailable.'));
                        }
                        $price = $variant->effectivePrice();
                        $priceCents = DecimalMoney::toCents($price);
                        if ($priceCents < 0) {
                            $this->fail('items', __('A requested variant has an invalid price.'));
                        }
                        if ($priceCents !== 0 && $requested['quantity'] > intdiv(PHP_INT_MAX - $totalCents, $priceCents)) {
                            $this->fail('items', __('The reservation total exceeds the supported range.'));
                        }
                        $totalCents += $priceCents * $requested['quantity'];

                        $this->inventory->reserve($variant, $requested['quantity'], $this->movementKey($reservation, 'reserve', $variant->id), $this->context($reservation, $user->id));
                        ReservationItem::create([
                            'reservation_id' => $reservation->id,
                            'product_variant_id' => $variant->id,
                            'quantity' => $requested['quantity'],
                            'unit_price' => $price,
                            'product_name_snapshot' => $variant->product->name,
                            'sku_snapshot' => $variant->sku,
                            'variant_snapshot' => [
                                'variant_id' => $variant->id,
                                'variant_key' => $variant->variant_key,
                                'color_id' => $variant->color_id,
                                'color_name' => $variant->color?->name,
                                'color_code' => $variant->color?->code,
                                'size_id' => $variant->size_id,
                                'size_name' => $variant->size?->name,
                                'size_code' => $variant->size?->code,
                                'price_adjustment' => $variant->price_adjustment,
                            ],
                        ]);
                    }

                    return [$reservation, true];
                });

                if ($created) {
                    $this->log('reservation_created', $reservation, $profile->user_id);
                }

                return $reservation->load('reservationItems');
            } catch (QueryException $exception) {
                if ($this->isUniqueViolation($exception, 'idempotency_key')) {
                    $winner = Reservation::where('idempotency_key', $idempotencyKey)->first();
                    if ($winner) {
                        return $this->replayOrConflict($winner, $fingerprint)->load('reservationItems');
                    }
                }
                if ($this->isUniqueViolation($exception, 'reservation_number') && $attempt < 2) {
                    continue;
                }
                throw $exception;
            }
        }

        throw new \LogicException('Reservation number generation exhausted.');
    }

    public function confirm(Reservation|int $reservation, ?int $actorUserId = null): Reservation
    {
        return $this->transition($reservation, Reservation::STATUS_PENDING_REVIEW, Reservation::STATUS_CONFIRMED, 'reservation_confirmed', $actorUserId, 'confirmed_at');
    }

    public function markPreparing(Reservation|int $reservation, ?int $actorUserId = null): Reservation
    {
        return $this->transition($reservation, Reservation::STATUS_CONFIRMED, Reservation::STATUS_PREPARING, 'reservation_preparing', $actorUserId);
    }

    public function markShipped(Reservation|int $reservation, ?int $actorUserId = null): Reservation
    {
        return $this->transition($reservation, Reservation::STATUS_PREPARING, Reservation::STATUS_SHIPPED, 'reservation_shipped', $actorUserId);
    }

    public function complete(Reservation|int $reservation, ?int $actorUserId = null): Reservation
    {
        return $this->releaseOrConsume($reservation, [Reservation::STATUS_SHIPPED], Reservation::STATUS_COMPLETED, 'complete', 'reservation_completed', $actorUserId);
    }

    public function cancel(Reservation|int $reservation, ?int $actorUserId = null): Reservation
    {
        return $this->releaseOrConsume($reservation, [Reservation::STATUS_PENDING_REVIEW, Reservation::STATUS_CONFIRMED, Reservation::STATUS_PREPARING], Reservation::STATUS_CANCELLED, 'release', 'reservation_cancelled', $actorUserId);
    }

    /** Returns false when another worker won or the reservation is not due. */
    public function expire(Reservation|int $reservation, ?int $actorUserId = null): bool
    {
        $changed = false;
        $current = DB::transaction(function () use ($reservation, $actorUserId, &$changed): Reservation {
            $current = $this->locked($reservation);
            if ($current->status !== Reservation::STATUS_PENDING_REVIEW || $current->released_at !== null
                || $current->expires_at === null || $current->expires_at->isFuture()) {
                return $current;
            }
            $this->mutateItems($current, 'release', $actorUserId);
            $current->status = Reservation::STATUS_EXPIRED;
            $current->released_at = now();
            $current->save();
            $changed = true;

            return $current;
        });
        if ($changed) {
            $this->log('reservation_expired', $current, $actorUserId);
        }

        return $changed;
    }

    private function transition(Reservation|int $reservation, string $from, string $to, string $action, ?int $actorUserId, ?string $timestamp = null): Reservation
    {
        $changed = false;
        $current = DB::transaction(function () use ($reservation, $from, $to, $timestamp, &$changed): Reservation {
            $current = $this->locked($reservation);
            if ($current->status === $to) {
                return $current;
            }
            if ($current->status !== $from || $current->released_at !== null) {
                $this->fail('status', __('Invalid reservation state transition.'));
            }
            $current->status = $to;
            if ($timestamp) {
                $current->{$timestamp} = now();
            }
            $current->save();
            $changed = true;

            return $current;
        });
        if ($changed) {
            $this->log($action, $current, $actorUserId);
        }

        return $current;
    }

    private function releaseOrConsume(Reservation|int $reservation, array $from, string $to, string $operation, string $action, ?int $actorUserId): Reservation
    {
        $changed = false;
        $current = DB::transaction(function () use ($reservation, $from, $to, $operation, $actorUserId, &$changed): Reservation {
            $current = $this->locked($reservation);
            if ($current->status === $to) {
                return $current;
            }
            if (! in_array($current->status, $from, true) || $current->released_at !== null) {
                $this->fail('status', __('Invalid reservation state transition.'));
            }
            $this->mutateItems($current, $operation, $actorUserId);
            $current->status = $to;
            if ($to === Reservation::STATUS_CANCELLED) {
                $current->cancelled_at = now();
                $current->released_at = now();
            }
            $current->save();
            $changed = true;

            return $current;
        });
        if ($changed) {
            $this->log($action, $current, $actorUserId);
        }

        return $current;
    }

    private function mutateItems(Reservation $reservation, string $operation, ?int $actorUserId): void
    {
        $items = $reservation->reservationItems()->orderBy('product_variant_id')->get();
        foreach ($items as $item) {
            $variant = ProductVariant::findOrFail($item->product_variant_id);
            $key = $this->movementKey($reservation, $operation, $variant->id);
            $context = $this->context($reservation, $actorUserId);
            if ($operation === 'complete') {
                $this->inventory->consumeReserved($variant, $item->quantity, $key, $context);
            } else {
                $this->inventory->release($variant, $item->quantity, $key, $context);
            }
        }
    }

    private function locked(Reservation|int $reservation): Reservation
    {
        return Reservation::whereKey($reservation instanceof Reservation ? $reservation->getKey() : $reservation)->lockForUpdate()->firstOrFail();
    }

    private function movementKey(Reservation $reservation, string $operation, int $variantId): string
    {
        return "reservation:{$reservation->id}:{$operation}:{$variantId}";
    }

    private function context(Reservation $reservation, ?int $actorUserId): array
    {
        return ['reference_type' => 'reservation', 'reference_id' => $reservation->id, 'actor_user_id' => $actorUserId];
    }

    private function normalizeItems(array $items): array
    {
        if ($items === [] || ! array_is_list($items)) {
            $this->fail('items', __('At least one reservation item is required.'));
        }
        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item) || count($item) !== 2 || ! array_key_exists('product_variant_id', $item)
                || ! array_key_exists('quantity', $item)
                || ! is_int($item['product_variant_id']) || $item['product_variant_id'] <= 0
                || ! is_int($item['quantity']) || $item['quantity'] <= 0
                || $item['quantity'] > InventoryService::MAX_QUANTITY) {
                $this->fail('items', __('Reservation items require a valid variant and quantity.'));
            }
            if (isset($normalized[$item['product_variant_id']])) {
                $this->fail('items', __('Duplicate variants are not allowed in one reservation.'));
            }
            $normalized[$item['product_variant_id']] = $item;
        }
        ksort($normalized, SORT_NUMERIC);

        return array_values($normalized);
    }

    private function replayOrConflict(Reservation $reservation, string $fingerprint): Reservation
    {
        if ($reservation->request_fingerprint !== $fingerprint) {
            $this->fail('idempotency_key', __('Idempotency key conflicts with a different reservation request.'));
        }

        return $reservation;
    }

    private function isUniqueViolation(QueryException $exception, string $column): bool
    {
        $state = (string) ($exception->errorInfo[0] ?? '');
        $code = (int) ($exception->errorInfo[1] ?? 0);
        $message = strtolower($exception->getMessage());

        return (($state === '23000' && in_array($code, [19, 1062], true)) || $state === '23505')
            && (str_contains($message, "reservations.{$column}") || str_contains($message, "reservations_{$column}_unique"));
    }

    private function log(string $action, Reservation $reservation, ?int $actorUserId): void
    {
        $this->audit->log($action, [
            'reservation_id' => $reservation->id,
            'reservation_number' => $reservation->reservation_number,
            'reseller_profile_id' => $reservation->reseller_profile_id,
            'status' => $reservation->status,
            'item_count' => $reservation->reservationItems()->count(),
        ], $actorUserId);
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
