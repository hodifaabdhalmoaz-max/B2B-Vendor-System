<?php

namespace App\Events\B2B;

final class ReservationShipped extends ReservationLifecycleEvent
{
    public function name(): string
    {
        return 'shipped';
    }
}
