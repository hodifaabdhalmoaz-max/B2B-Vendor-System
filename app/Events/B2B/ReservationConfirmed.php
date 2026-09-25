<?php

namespace App\Events\B2B;

final class ReservationConfirmed extends ReservationLifecycleEvent
{
    public function name(): string
    {
        return 'confirmed';
    }
}
