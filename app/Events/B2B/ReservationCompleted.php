<?php

namespace App\Events\B2B;

final class ReservationCompleted extends ReservationLifecycleEvent
{
    public function name(): string
    {
        return 'completed';
    }
}
