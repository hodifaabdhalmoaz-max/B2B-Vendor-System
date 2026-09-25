<?php

namespace App\Events\B2B;

/** Immutable committed lifecycle snapshot; consumers must not mutate the aggregate. */
abstract class ReservationLifecycleEvent
{
    public function __construct(
        public readonly int $reservationId,
        public readonly int $resellerProfileId,
        public readonly int $userId,
        public readonly string $reservationNumber,
        public readonly string $status,
        public readonly ?int $actorUserId,
        public readonly string $occurredAt,
    ) {}

    abstract public function name(): string;

    public function key(): string
    {
        return "reservation:{$this->reservationId}:{$this->name()}";
    }
}
