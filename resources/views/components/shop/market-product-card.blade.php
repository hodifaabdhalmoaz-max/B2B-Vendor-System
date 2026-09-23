@props([
    'product',
])

@php
    $imageSrc = $product->image;

    if (! empty($imageSrc)) {
        $imageSrc = \Illuminate\Support\Str::startsWith($imageSrc, 'products/')
            ? asset('uploads/'.$imageSrc)
            : asset('uploads/products/'.$imageSrc);
    } else {
        $imageSrc = asset('assets/images/home/demo3/category_1.png');
    }

    $regularPrice = (float) ($product->regular_price ?? 0);
    $salePrice = (float) ($product->sale_price ?? 0);
    $hasSale = $salePrice > 0 && $salePrice < $regularPrice;
    $displayPrice = $hasSale ? $salePrice : $regularPrice;
    $discount = $hasSale && $regularPrice > 0 ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100) : null;
    $reviewsCount = (int) ($product->active_reviews_count ?? 0);
    $rating = (float) ($product->active_reviews_avg ?? 0);
    $wishlistItem = Cart::instance('wishlist')->content()->firstWhere('id', $product->id);
@endphp

<article class="market-product-card">
    <a class="market-product-card__media" href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">
        <img
            src="{{ $imageSrc }}"
            alt="{{ $product->name }}"
            loading="lazy"
            onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
        >
    </a>

    @if($wishlistItem)
        <form method="POST" action="{{ route('wishlist.item.remove', ['rowId' => $wishlistItem->rowId]) }}">
            @csrf
            @method('DELETE')
            <button class="market-product-card__wishlist is-active" type="submit" aria-label="إزالة من المفضلة">
                <i class="bi bi-heart-fill"></i>
            </button>
        </form>
    @else
        <form method="POST" action="{{ route('wishlist.add') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">
            <button class="market-product-card__wishlist" type="submit" aria-label="إضافة إلى المفضلة">
                <i class="bi bi-heart"></i>
            </button>
        </form>
    @endif

    <div class="market-product-card__body">
        <a class="market-product-card__title" href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">
            {{ $product->card_title ?? $product->name }}
        </a>

        @if($product->featured)
            <span class="market-product-card__badge"># اختيار شائع في دنيا الأطفال</span>
        @elseif($hasSale)
            <span class="market-product-card__badge">عرض محدود</span>
        @endif

        <div class="market-product-card__meta">
            <span>{{ $reviewsCount > 0 ? number_format($reviewsCount) . ' تقييم' : 'منتج رائج' }}</span>
            <span>|</span>
            <span class="market-product-card__stars" aria-label="التقييم {{ number_format($rating, 1) }}">
                {{ $rating > 0 ? str_repeat('★', max(1, min(5, (int) round($rating)))) : '★ ★ ★ ★ ★' }}
            </span>
        </div>

        <div class="market-product-card__footer">
            <div>
                <span class="market-product-card__price {{ $hasSale ? 'is-sale' : '' }}">
                    <small>ر.ي</small>{{ number_format($displayPrice, 0) }}
                </span>
                @if($discount)
                    <span class="market-product-card__discount">-{{ $discount }}%</span>
                @endif
            </div>

            @if(Cart::instance('cart')->content()->where('id', $product->id)->count() > 0)
                <a class="market-product-card__cart" href="{{ route('cart.index') }}" aria-label="المنتج في السلة">
                    <i class="bi bi-cart-check"></i>
                </a>
            @else
                <form method="POST" action="{{ route('cart.add') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button class="market-product-card__cart" type="submit" aria-label="إضافة إلى السلة">
                        <i class="bi bi-cart-plus"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>
</article>
