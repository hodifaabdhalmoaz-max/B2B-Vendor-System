@extends('layouts.admin')
@include('admin.b2b.reservations._styles')
@section('content')
<div class="main-content-inner"><div class="main-content-wrap b2b-reservations">
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>{{ __('B2B Reservations') }}</h3>
        <a href="{{ route('admin.resellers.index') }}">{{ __('Resellers') }}</a>
    </div>
    @include('admin.b2b.reservations._feedback')
    <div class="wg-box mb-27">
        <p>{{ __('Counts reflect search, reseller, date and overdue filters.') }}</p>
        <nav class="flex flex-wrap gap10" aria-label="{{ __('Reservation status') }}">
            @foreach(['all' => __('All')] + $statuses as $value => $label)
                <a class="tf-button {{ ($filters['status'] ?? 'all') === $value ? '' : 'style-1' }}"
                   @if(($filters['status'] ?? 'all') === $value) aria-current="page" @endif
                   href="{{ route('admin.b2b.reservations.index', array_merge($filters, ['status' => $value, 'page' => 1])) }}">
                    {{ $label }} ({{ $counts[$value] }})
                </a>
            @endforeach
        </nav>
        <form method="GET" action="{{ route('admin.b2b.reservations.index') }}">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? 'all' }}">
            <div class="row g-3">
                <div class="col-lg-4 col-sm-6"><label for="search">{{ __('Search reservations or reseller identity') }}</label><input id="search" name="search" type="search" maxlength="150" value="{{ $filters['search'] ?? '' }}"></div>
                <div class="col-lg-2 col-sm-6"><label for="reseller_profile_id">{{ __('Reseller profile ID') }}</label><input id="reseller_profile_id" name="reseller_profile_id" type="number" min="1" value="{{ $filters['reseller_profile_id'] ?? '' }}"></div>
                <div class="col-lg-3 col-sm-6"><label for="start_date">{{ __('Start date') }}</label><input id="start_date" name="start_date" type="date" value="{{ $filters['start_date'] ?? '' }}"></div>
                <div class="col-lg-3 col-sm-6"><label for="end_date">{{ __('End date') }}</label><input id="end_date" name="end_date" type="date" value="{{ $filters['end_date'] ?? '' }}"></div>
            </div>
            @if($selectedReseller)<p class="mt-3">{{ __('Reseller') }}: {{ $selectedReseller->user?->name }} — {{ $selectedReseller->business_name }}</p>@endif
            <div class="flex flex-wrap items-center gap20 mt-3">
                <label><input type="checkbox" name="overdue" value="1" @checked($filters['overdue'] ?? false)> {{ __('Overdue only') }}</label>
                <button class="tf-button" type="submit">{{ __('Filter') }}</button>
                <a href="{{ route('admin.b2b.reservations.index') }}">{{ __('Reset filters') }}</a>
            </div>
        </form>
    </div>
    <div class="wg-box">
        <p>{{ __('Dates use timezone') }}: {{ config('app.timezone') }}</p>
        <div class="table-responsive" tabindex="0" aria-label="{{ __('B2B Reservations') }}">
            <table class="table table-striped table-bordered">
                <thead><tr>
                    @foreach(['Reservation number', 'Reseller', 'Business name', 'Created at', 'Status', 'Item count', 'Total', 'Expires at', 'Actions'] as $label)<th scope="col">{{ __($label) }}</th>@endforeach
                </tr></thead>
                <tbody>
                    @forelse($reservations as $reservation)
                        <tr>
                            <td><a href="{{ route('admin.b2b.reservations.show', $reservation) }}">{{ $reservation->reservation_number }}</a></td>
                            <td>{{ $reservation->resellerProfile?->user?->name }}<br><small>{{ $reservation->resellerProfile?->user?->username }}</small></td>
                            <td>{{ $reservation->resellerProfile?->business_name ?: '—' }}</td>
                            <td>{{ $reservation->created_at?->format('Y-m-d H:i') }}</td>
                            <td>@include('admin.b2b.reservations._status')</td>
                            <td>{{ $reservation->reservation_items_count }}</td>
                            <td>{{ $reservation->total }} {{ __('YER') }}</td>
                            <td>{{ $reservation->expires_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td><a href="{{ route('admin.b2b.reservations.show', $reservation) }}">{{ __('Reservation details') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9">{{ __('No reservations found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $reservations->links('pagination::bootstrap-5') }}
    </div>
</div></div>
@endsection
