<article class="portal-card h-100 d-flex flex-column">
    <a href="{{ route('reseller.products.show', $product->id) }}">
        @if($product->image)<img class="portal-product-image" src="{{ asset('uploads/products/'.rawurlencode(basename($product->image))) }}" alt="{{ $product->name }}" loading="lazy" width="360" height="270">@endif
        <h3 class="h5 mt-3">{{ $product->name }}</h3>
    </a>
    <p class="text-muted mb-2">{{ $product->category?->name }}</p>
    <p class="portal-price" dir="ltr">{{ $product->portal_price_min }}@if($product->portal_price_min !== $product->portal_price_max) – {{ $product->portal_price_max }}@endif <span>{{ __('YER') }}</span></p>
    <p class="{{ $product->portal_available ? 'text-success' : 'text-danger' }}">{{ $product->portal_available ? __('Available') : __('All variants out of stock') }}</p>
    <div class="mt-auto">@include('reseller.partials.wishlist-button')</div>
</article>
