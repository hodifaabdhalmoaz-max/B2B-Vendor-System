@extends('reseller.layout')
@section('title', __('Reservations'))
@section('content')
<h1 class="h3 mb-4">{{ __('Reservations') }}</h1>
@forelse($reservations as $reservation)@include('reseller.partials.reservation-card')@empty<p>{{ __('No reservations yet.') }}</p>@endforelse
{{ $reservations->links() }}
@endsection
