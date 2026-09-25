<?php

namespace App\Events\B2B;

final class ReservationCancelled extends ReservationLifecycleEvent
{
    public function name(): string
    {
        return 'cancelled';
    }
}
