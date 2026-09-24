@extends('reseller.layout')
@section('title', $reservation->reservation_number)
@section('content')
<h1 class="h3">{{ __('Reservation number') }}: {{ $reservation->reservation_number }}</h1>
@include('reseller.partials.reservation-card')
@foreach($reservation->reservationItems as $item)
    <article class="portal-card mb-3"><h2 class="h5">{{ $item->product_name_snapshot }}</h2><p>{{ __('SKU') }}: {{ $item->sku_snapshot }}</p><p>{{ $item->variant_snapshot['color_name'] ?? __('Default color') }} · {{ $item->variant_snapshot['size_name'] ?? __('Default size') }}</p><p>{{ __('Quantity') }}: {{ $item->quantity }} · {{ __('Unit price') }}: {{ $item->unit_price }} {{ __('YER') }}</p><strong>{{ __('Subtotal') }}: {{ $item->line_total }} {{ __('YER') }}</strong></article>
@endforeach
@if($reservation->notes)<p class="portal-description">{{ $reservation->notes }}</p>@endif
@if($reservation->status === 'pending_review')<form method="POST" action="{{ route('reseller.reservations.cancel', $reservation->id) }}">@csrf<button class="btn btn-outline-danger">{{ __('Cancel reservation') }}</button></form>@endif
@endsection
