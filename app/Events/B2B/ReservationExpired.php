<?php

namespace App\Events\B2B;

final class ReservationExpired extends ReservationLifecycleEvent
{
    public function name(): string
    {
        return 'expired';
    }
}
