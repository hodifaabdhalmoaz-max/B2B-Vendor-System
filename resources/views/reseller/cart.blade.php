@extends('reseller.layout')
@section('title', __('Reservation cart'))
@section('content')
<h1 class="h3">{{ __('Reservation cart') }}</h1><p>{{ __('Adding to the cart does not hold stock. Availability is checked when you submit.') }}</p>
@forelse($lines as $line)
    <article class="portal-card mb-3">
        @if($line['variant']?->product?->image)<img class="portal-cart-image" src="{{ asset('uploads/products/'.rawurlencode(basename($line['variant']->product->image))) }}" alt="{{ $line['variant']->product->name }}" width="80" height="80">@endif
        <h2 class="h5">{{ $line['variant']?->product?->name ?? __('Unavailable variant') }}</h2><p>{{ $line['variant']?->color?->name ?? __('Default color') }} · {{ $line['variant']?->size?->name ?? __('Default size') }} · {{ $line['variant']?->sku }}</p><p>{{ __('Available') }}: {{ $line['available'] }} · {{ __('Current price') }}: {{ $line['price'] ?? '—' }} {{ __('YER') }}</p>
        @if($line['unavailable'])<p class="text-danger" role="alert">{{ __('This line is unavailable at the requested quantity. Please update or remove it.') }}</p>@endif
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <form method="POST" action="{{ route('reseller.cart.update', $line['product_variant_id']) }}" class="d-flex gap-2 align-items-end">
                @csrf @method('PATCH')<div><label for="cart-quantity-{{ $loop->index }}">{{ __('Quantity') }}</label><input class="form-control" id="cart-quantity-{{ $loop->index }}" type="number" inputmode="numeric" min="1" name="quantity" value="{{ $line['quantity'] }}" required></div><button class="btn btn-outline-secondary">{{ __('Update') }}</button>
            </form><form method="POST" action="{{ route('reseller.cart.destroy', $line['product_variant_id']) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger">{{ __('Remove') }}</button></form>
        </div><p class="mt-3 mb-0">{{ __('Subtotal') }}: {{ $line['line_total'] }} {{ __('YER') }}</p>
    </article>
@empty
    <p>{{ __('Your reservation cart is empty.') }}</p><a class="btn btn-primary" href="{{ route('reseller.catalog') }}">{{ __('Browse catalog') }}</a>
@endforelse
@if(count($lines))
    <section class="portal-card">
        <h2 class="h4">{{ __('Preview total') }}: {{ $total }} {{ __('YER') }}</h2><p>{{ __('Final prices and availability are confirmed when the reservation is submitted.') }}</p>
        <form method="POST" action="{{ route('reseller.reservations.store') }}">
            @csrf<input type="hidden" name="idempotency_key" value="{{ is_string(old('idempotency_key')) && strlen(old('idempotency_key')) <= 255 && trim(old('idempotency_key')) !== '' ? old('idempotency_key') : $idempotencyKey }}">
            @foreach($lines as $line)<input type="hidden" name="items[{{ $loop->index }}][product_variant_id]" value="{{ $line['product_variant_id'] }}"><input type="hidden" name="items[{{ $loop->index }}][quantity]" value="{{ $line['quantity'] }}">@endforeach
            <label for="reservation-notes">{{ __('Notes') }}</label><textarea class="form-control mb-3" id="reservation-notes" name="notes" maxlength="65535">{{ is_string(old('notes')) ? old('notes') : '' }}</textarea><button class="btn btn-primary w-100" type="submit">{{ __('Submit reservation') }}</button>
        </form><form method="POST" action="{{ route('reseller.cart.clear') }}" class="mt-3">@csrf @method('DELETE')<button class="btn btn-outline-danger w-100">{{ __('Clear cart') }}</button></form>
    </section>
@endif
@endsection
