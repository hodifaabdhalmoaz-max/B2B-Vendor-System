<?php

namespace App\Events\B2B;

final class ReservationPreparing extends ReservationLifecycleEvent
{
    public function name(): string
    {
        return 'preparing';
    }
}
