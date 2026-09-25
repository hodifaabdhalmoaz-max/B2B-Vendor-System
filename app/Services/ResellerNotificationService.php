<?php

namespace App\Services;

use App\Events\B2B\ReservationLifecycleEvent;
use App\Models\User;
use App\Repositories\ResellerNotificationRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ResellerNotificationService
{
    private const TITLES = [
        'reservation_created' => 'Reservation submitted',
        'reservation_confirmed' => 'Reservation confirmed',
        'reservation_preparing' => 'Reservation is being prepared',
        'reservation_shipped' => 'Reservation shipped',
        'reservation_completed' => 'Reservation completed',
        'reservation_cancelled' => 'Reservation cancelled',
        'reservation_expired' => 'Reservation expired',
    ];

    public function __construct(private readonly ResellerNotificationRepository $notifications) {}

    public function deliver(ReservationLifecycleEvent $event): void
    {
        $this->notifications->persist($event);
    }

    public function listing(User $user): LengthAwarePaginator
    {
        return $this->notifications->paginate($user)->through(function ($notification) {
            $data = is_array($notification->data) ? $notification->data : [];
            $number = is_string($data['reservation_number'] ?? null) ? $data['reservation_number'] : null;
            $id = filter_var($data['reservation_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            return [
                'id' => $notification->id,
                'title' => __(self::TITLES[$notification->type] ?? 'New notification'),
                'description' => $number ? __('Reservation :number status was updated.', ['number' => $number]) : __('New notification'),
                'url' => $id ? route('reseller.reservations.show', $id) : null,
                'created_at' => $notification->created_at,
                'unread' => $notification->read_at === null,
            ];
        });
    }

    public function unreadCount(User $user): int
    {
        return $this->notifications->unreadCount($user);
    }

    public function markRead(User $user, string $id): void
    {
        $this->notifications->markRead($user, $id);
    }

    public function markAllRead(User $user): void
    {
        $this->notifications->markAllRead($user);
    }
}
