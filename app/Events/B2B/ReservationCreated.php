<?php

namespace App\Events\B2B;

final class ReservationCreated extends ReservationLifecycleEvent
{
    public function name(): string
    {
        return 'created';
    }
}
