<?php

namespace App\Listeners;

use App\Events\B2B\ReservationLifecycleEvent;
use App\Services\ResellerNotificationService;

class PersistResellerReservationNotification
{
    public function __construct(private readonly ResellerNotificationService $notifications) {}

    public function handle(ReservationLifecycleEvent $event): void
    {
        $this->notifications->deliver($event);
    }
}
