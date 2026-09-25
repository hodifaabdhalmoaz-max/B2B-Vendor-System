<?php

namespace App\Repositories;

use App\Events\B2B\ReservationLifecycleEvent;
use App\Models\ResellerNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ResellerNotificationRepository
{
    public const TYPES = ['reservation_created', 'reservation_confirmed', 'reservation_preparing', 'reservation_shipped', 'reservation_completed', 'reservation_cancelled', 'reservation_expired'];

    private function owned(User $user): Builder
    {
        return ResellerNotification::where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())->whereIn('type', self::TYPES);
    }

    public function persist(ReservationLifecycleEvent $event): void
    {
        $user = User::findOrFail($event->userId);
        // firstOrCreate recovers a competing unique-key insert. Never overwrite read state.
        ResellerNotification::firstOrCreate(['event_key' => $event->key()], [
            'id' => (string) Str::uuid(),
            'type' => 'reservation_'.$event->name(),
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'data' => [
                'reservation_id' => $event->reservationId,
                'reservation_number' => $event->reservationNumber,
                'status' => $event->status,
                'occurred_at' => $event->occurredAt,
                'actor_user_id' => $event->actorUserId,
            ],
            'created_at' => $event->occurredAt,
            'updated_at' => $event->occurredAt,
        ]);
    }

    public function paginate(User $user): LengthAwarePaginator
    {
        return $this->owned($user)->orderByDesc('created_at')->orderByDesc('id')->paginate(20)->withQueryString();
    }

    public function unreadCount(User $user): int
    {
        return $this->owned($user)->whereNull('read_at')->count();
    }

    public function markRead(User $user, string $id): void
    {
        $notification = $this->owned($user)->whereKey($id)->firstOrFail();
        $this->owned($user)->whereKey($notification->id)->whereNull('read_at')->update(['read_at' => now(), 'updated_at' => now()]);
    }

    public function markAllRead(User $user): void
    {
        $this->owned($user)->whereNull('read_at')->update(['read_at' => now(), 'updated_at' => now()]);
    }
}
