<article class="portal-card mb-3">
    <div class="d-flex flex-wrap justify-content-between gap-2"><a href="{{ route('reseller.reservations.show', $reservation->id) }}"><strong>{{ $reservation->reservation_number }}</strong></a><span class="portal-badge">@include('reseller.partials.status', ['status' => $reservation->status])</span></div>
    <p class="mt-2 mb-1">{{ $reservation->created_at->format('Y-m-d H:i') }} · {{ __('Items') }}: {{ $reservation->reservationItems->count() }}</p><strong>{{ $reservation->total }} {{ __('YER') }}</strong>
    @if($reservation->status === 'pending_review' && $reservation->expires_at)<p class="mb-0 mt-2">{{ __('Expires at') }}: <time datetime="{{ $reservation->expires_at->toIso8601String() }}">{{ $reservation->expires_at->format('Y-m-d H:i') }} ({{ config('app.timezone') }})</time></p>@endif
</article>
