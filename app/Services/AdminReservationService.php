<?php

namespace App\Services;

use App\Models\Reservation;
use App\Repositories\AdminReservationRepository;

/** Presentation/query coordination only; never mutates reservations or inventory. */
class AdminReservationService
{
    public function __construct(private readonly AdminReservationRepository $repository) {}

    public static function statuses(): array
    {
        return [
            Reservation::STATUS_PENDING_REVIEW => __('Pending review'),
            Reservation::STATUS_CONFIRMED => __('Confirmed'),
            Reservation::STATUS_PREPARING => __('Preparing'),
            Reservation::STATUS_SHIPPED => __('Shipped'),
            Reservation::STATUS_COMPLETED => __('Completed'),
            Reservation::STATUS_CANCELLED => __('Cancelled'),
            Reservation::STATUS_EXPIRED => __('Expired'),
        ];
    }

    public function index(array $filters): array
    {
        $statuses = self::statuses();
        $counts = array_replace(array_fill_keys(array_keys($statuses), 0), $this->repository->counts($filters));

        return [
            'reservations' => $this->repository->paginate($filters),
            'statuses' => $statuses,
            'counts' => ['all' => array_sum($counts)] + $counts,
            'filters' => $filters,
            'selectedReseller' => $this->repository->reseller(isset($filters['reseller_profile_id']) ? (int) $filters['reseller_profile_id'] : null),
        ];
    }

    public function show(Reservation $reservation): array
    {
        $reservation = $this->repository->detail($reservation);
        $actions = match ($reservation->status) {
            Reservation::STATUS_PENDING_REVIEW => ['confirm' => __('Confirm reservation'), 'cancel' => __('Cancel reservation')],
            Reservation::STATUS_CONFIRMED => ['preparing' => __('Mark preparing'), 'cancel' => __('Cancel reservation')],
            Reservation::STATUS_PREPARING => ['ship' => __('Mark shipped'), 'cancel' => __('Cancel reservation')],
            Reservation::STATUS_SHIPPED => ['complete' => __('Complete reservation')],
            default => [],
        };
        if (self::isOverdue($reservation)) {
            $actions['expire'] = __('Expire reservation');
        }

        return ['reservation' => $reservation, 'statuses' => self::statuses(), 'actions' => $actions,
            'movements' => $this->repository->movements($reservation)];
    }

    public static function isOverdue(Reservation $reservation): bool
    {
        return $reservation->status === Reservation::STATUS_PENDING_REVIEW
            && $reservation->expires_at !== null && $reservation->expires_at->lte(now());
    }
}
