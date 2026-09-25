@extends('layouts.admin')
@include('admin.b2b.reservations._styles')
@section('content')
<div class="main-content-inner"><div class="main-content-wrap b2b-reservations">
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>{{ __('Reservation details') }}: {{ $reservation->reservation_number }}</h3>
        <a href="{{ route('admin.b2b.reservations.index') }}">{{ __('B2B Reservations') }}</a>
    </div>
    @include('admin.b2b.reservations._feedback')
    <div class="wg-box mb-27">
        <div>@include('admin.b2b.reservations._status')</div>
        <p>{{ __('Dates use timezone') }}: {{ config('app.timezone') }}</p>
        <dl class="row">
            @foreach(['created_at' => 'Created at', 'expires_at' => 'Expires at', 'confirmed_at' => 'Confirmed at', 'cancelled_at' => 'Cancelled at', 'released_at' => 'Released at'] as $field => $label)
                <dt class="col-sm-4">{{ __($label) }}</dt><dd class="col-sm-8">{{ $reservation->{$field}?->format('Y-m-d H:i:s') ?? '—' }}</dd>
            @endforeach
            <dt class="col-sm-4">{{ __('Total') }}</dt><dd class="col-sm-8">{{ $reservation->total }} {{ __('YER') }}</dd>
            <dt class="col-sm-4">{{ __('Reseller note') }}</dt><dd class="col-sm-8" style="white-space: pre-wrap; overflow-wrap: anywhere">{{ $reservation->notes ?: '—' }}</dd>
        </dl>
        <div class="flex flex-wrap gap10">
            @foreach($actions as $action => $label)
                <form method="POST" action="{{ route('admin.b2b.reservations.'.$action, $reservation) }}">
                    @csrf
                    <button type="submit" class="tf-button {{ in_array($action, ['cancel', 'expire']) ? 'style-1' : '' }}">{{ $label }}</button>
                </form>
            @endforeach
        </div>
    </div>
    @php($profile = $reservation->resellerProfile)
    <div class="wg-box mb-27">
        <h5>{{ __('Reseller information') }}</h5>
        <dl class="row">
            @foreach(['name' => 'Name', 'username' => 'Username', 'mobile' => 'Mobile'] as $field => $label)
                <dt class="col-sm-4">{{ __($label) }}</dt><dd class="col-sm-8">{{ $profile?->user?->{$field} ?: '—' }}</dd>
            @endforeach
            @foreach(['business_name' => 'Business name', 'whatsapp' => 'WhatsApp', 'governorate' => 'Governorate', 'group_name' => 'Group'] as $field => $label)
                <dt class="col-sm-4">{{ __($label) }}</dt><dd class="col-sm-8">{{ $profile?->{$field} ?: '—' }}</dd>
            @endforeach
            <dt class="col-sm-4">{{ __('Account status') }}</dt><dd class="col-sm-8">{{ $profile?->user?->is_active ? __('Active') : __('Inactive') }}</dd>
            <dt class="col-sm-4">{{ __('Reseller profile status') }}</dt><dd class="col-sm-8">{{ $profile?->isActive() ? __('Active') : __('Inactive') }}</dd>
            <dt class="col-sm-4">{{ __('Reservations enabled') }}</dt><dd class="col-sm-8">{{ $profile?->reservation_enabled ? __('Enabled') : __('Disabled') }}</dd>
            <dt class="col-sm-4">{{ __('Reservation timeout (minutes)') }}</dt><dd class="col-sm-8">{{ $profile?->reservation_timeout_minutes ?? __('No timeout') }}</dd>
        </dl>
        @if($profile)
            <a href="{{ route('admin.resellers.edit', $profile->user_id) }}">{{ __('Edit reseller') }}</a>
            <a href="{{ route('admin.b2b.reservations.index', ['reseller_profile_id' => $profile->id]) }}">{{ __('All reservations for this reseller') }}</a>
        @endif
    </div>
    <div class="wg-box mb-27">
        <h5>{{ __('Historical item snapshots') }}</h5>
        <div class="table-responsive" tabindex="0" aria-label="{{ __('Historical item snapshots') }}">
            <table class="table table-striped table-bordered">
                <thead><tr>@foreach(['Product', 'SKU', 'Color', 'Size', 'Quantity', 'Unit price', 'Subtotal'] as $label)<th scope="col">{{ __($label) }}</th>@endforeach</tr></thead>
                <tbody>@foreach($reservation->reservationItems as $item)
                    <tr>
                        <td>{{ $item->product_name_snapshot }}</td><td>{{ $item->sku_snapshot }}</td>
                        <td>{{ $item->variant_snapshot['color_name'] ?? __('Default color') }}</td><td>{{ $item->variant_snapshot['size_name'] ?? __('Default size') }}</td>
                        <td>{{ $item->quantity }}</td><td>{{ $item->unit_price }} {{ __('YER') }}</td><td>{{ $item->line_total }} {{ __('YER') }}</td>
                    </tr>
                @endforeach</tbody>
            </table>
        </div>
    </div>
    <div class="wg-box">
        <h5>{{ __('Inventory movements') }}</h5>
        <p>{{ __('Movement SKU is current; historical SKU appears in item snapshots above.') }}</p>
        <div class="table-responsive" tabindex="0" aria-label="{{ __('Inventory movements') }}">
            <table class="table table-striped table-bordered">
                <thead><tr>@foreach(['SKU', 'Movement type', 'Quantity', 'Stock delta', 'Reserved delta', 'Stock on hand after', 'Reserved quantity after', 'Actor', 'Created at'] as $label)<th scope="col">{{ __($label) }}</th>@endforeach</tr></thead>
                <tbody>@forelse($movements as $movement)
                    <tr data-movement-id="{{ $movement->id }}">
                        <td>{{ $movement->productVariant?->sku ?? '—' }}</td>
                        <td>{{ __('Inventory movement '.$movement->type) }}</td>
                        <td>{{ $movement->quantity }}</td><td>{{ $movement->stock_delta }}</td><td>{{ $movement->reserved_delta }}</td>
                        <td>{{ $movement->stock_on_hand_after }}</td><td>{{ $movement->reserved_quantity_after }}</td>
                        <td>{{ $movement->actor?->name ?? __('System') }}</td><td>{{ $movement->created_at?->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @empty<tr><td colspan="9">{{ __('No inventory movements found.') }}</td></tr>@endforelse</tbody>
            </table>
        </div>
    </div>
</div></div>
@endsection
