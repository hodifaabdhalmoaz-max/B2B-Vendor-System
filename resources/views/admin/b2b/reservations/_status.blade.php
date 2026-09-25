<span class="badge bg-secondary">{{ $statuses[$reservation->status] ?? $reservation->status }}</span>
@if(\App\Services\AdminReservationService::isOverdue($reservation))
    <span class="badge bg-warning text-dark">{{ __('Overdue') }}</span>
    <small class="d-block">{{ __('Awaiting expiration processing') }}</small>
@endif
