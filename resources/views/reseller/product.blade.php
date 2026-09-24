@extends('reseller.layout')
@section('title', $product->name)
@section('content')
<a href="{{ route('reseller.catalog') }}">{{ __('Catalog') }}</a><h1 class="h3 mt-3">{{ $product->name }}</h1><p>{{ __('SKU') }}: {{ $product->SKU }}</p>
<div class="row g-3 mb-4">@foreach($images as $index => $image)
    <figure class="col-6 col-md-4"><img class="portal-product-image" src="{{ asset($image['path']) }}" alt="{{ $image['label'] }}" width="360" height="270" loading="lazy"><figcaption>{{ $image['label'] }}</figcaption><a class="btn btn-outline-secondary w-100 mt-2" href="{{ route('reseller.products.images.download', [$product->id, $index]) }}">{{ __('Download image') }}</a></figure>
@endforeach</div>
<section class="portal-card mb-4">
    <p>{{ strip_tags($product->short_description ?? '') }}</p><div id="marketing-description" class="portal-description">{{ $description }}</div>
    <button type="button" class="btn btn-outline-secondary my-3" data-copy-description data-success="{{ __('Description copied.') }}" data-fallback="{{ __('Select and copy the description below.') }}">{{ __('Copy description') }}</button>
    <p id="copy-feedback" role="status" aria-live="polite"></p><textarea id="copy-fallback" class="form-control mb-3" aria-label="{{ __('Description') }}" readonly hidden></textarea>
    @include('reseller.partials.wishlist-button')
</section>
<h2 class="h4">{{ __('Choose an exact variant') }}</h2><p>{{ __('Adding to the cart does not hold stock. Availability is checked when you submit.') }}</p>
<div class="row g-3">@foreach($product->variants as $variant)
    @php($available = $variant->inventoryItem?->available_quantity ?? 0)
    <div class="col-12 col-md-6"><article class="portal-card">
        <h3 class="h5">{{ $variant->color?->name ?? __('Default color') }} · {{ $variant->size?->name ?? __('Default size') }}</h3><p>{{ __('SKU') }}: {{ $variant->sku }}</p><strong class="portal-price">{{ $variant->effectivePrice() }} {{ __('YER') }}</strong>
        <p class="mt-2" data-variant="{{ $variant->id }}" data-available="{{ $available }}">{{ __('Available') }}: {{ $available }} @if($available <= 0)<span class="text-danger">— {{ __('Out of stock') }}</span>@endif</p>
        <form method="POST" action="{{ route('reseller.cart.store') }}" class="d-flex gap-2 align-items-end">
            @csrf<input type="hidden" name="product_variant_id" value="{{ $variant->id }}">
            <div><label for="quantity-{{ $variant->id }}">{{ __('Quantity') }}</label><input id="quantity-{{ $variant->id }}" class="form-control" type="number" inputmode="numeric" name="quantity" min="1" max="{{ $available }}" value="1" required @disabled($available <= 0)></div><button class="btn btn-primary" @disabled($available <= 0)>{{ __('Add to reservation cart') }}</button>
        </form>
    </article></div>
@endforeach</div>
@endsection
