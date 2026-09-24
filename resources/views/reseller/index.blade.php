@extends('reseller.layout')
@section('content')
<h1 class="h3">{{ __('Reseller Portal') }}</h1>
<form method="GET" action="{{ route('reseller.search') }}" class="d-flex gap-2 my-4">
    <label for="dashboard-search" class="visually-hidden">{{ __('Search catalog') }}</label><input id="dashboard-search" class="form-control" name="q" maxlength="200" placeholder="{{ __('Search catalog') }}"><button class="btn btn-primary">{{ __('Search') }}</button>
</form>
<div class="row g-3 mb-4">
    @foreach(['pending_review', 'confirmed', 'preparing', 'shipped', 'completed'] as $status)
        <div class="col-6 col-md"><div class="portal-card"><span>@include('reseller.partials.status')</span><strong class="d-block h3 mt-2">{{ $statusCounts[$status] ?? 0 }}</strong></div></div>
    @endforeach
</div>
@foreach(['recentProducts' => 'Recent products', 'offers' => 'Offers', 'popular' => 'Most requested'] as $collection => $heading)
    <section class="mb-5"><h2 class="h4 mb-3">{{ __($heading) }}</h2><div class="row g-3">
        @forelse($$collection as $product)<div class="col-12 col-sm-6 col-lg-4">@include('reseller.partials.product-card')</div>@empty<p>{{ __('No products found.') }}</p>@endforelse
    </div></section>
@endforeach
<section><h2 class="h4">{{ __('Recent reservations') }}</h2>
    @forelse($recentReservations as $reservation)@include('reseller.partials.reservation-card')@empty<p>{{ __('No reservations yet.') }}</p>@endforelse
    <a class="btn btn-outline-secondary" href="{{ route('reseller.reservations.index') }}">{{ __('All reservations') }}</a>
</section>
@endsection
