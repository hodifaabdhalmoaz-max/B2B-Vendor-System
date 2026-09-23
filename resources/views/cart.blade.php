@extends('layouts.app')

@php
    $cartCount = $items->count();
    $shippingName = $defaultAddress
        ? collect([$defaultAddress->city, $defaultAddress->state, $defaultAddress->country])->filter()->join('، ')
        : 'حدد عنوانك';
    $shippingUrl = Auth::check() ? route('user.addresses') : route('login');
@endphp

@push('styles')
<style>
    body:has(.cart-app-shell) {
        background: #f5f6f8 !important;
    }

    body:has(.cart-app-shell) #header,
    body:has(.cart-app-shell) .footer,
    body:has(.cart-app-shell) .footer-mobile,
    body:has(.cart-app-shell) > hr.mt-5 {
        display: none !important;
    }

    .cart-app-shell,
    .cart-app-shell * {
        box-sizing: border-box;
        letter-spacing: 0;
    }

    .cart-app-shell {
        width: 100%;
        max-width: 520px;
        min-height: 100dvh;
        margin: 0 auto;
        padding: 0 0 112px;
        overflow-x: hidden;
        background: #f5f6f8;
        color: #151515;
        direction: rtl;
    }

    .cart-app-header {
        position: sticky;
        top: 0;
        z-index: 30;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 94px;
        padding: 22px 18px 20px;
        border-bottom: 1px solid #ececec;
        background: #fff;
    }

    .cart-app-header__shipping {
        display: inline-flex;
        align-items: center;
        min-width: 0;
        color: #151515;
        text-decoration: none;
    }

    .cart-app-header__title {
        flex: 0 0 auto;
        color: #050505;
        font-size: clamp(32px, 9vw, 48px);
        font-weight: 900;
        line-height: 1;
    }

    .cart-app-header__destination {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-width: 0;
        margin-inline-start: 8px;
        color: #747474;
        font-size: clamp(17px, 5vw, 30px);
        font-weight: 600;
        line-height: 1.2;
    }

    .cart-app-header__destination span {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cart-app-header__destination i {
        flex: 0 0 auto;
        font-size: 0.9em;
    }

    .cart-app-header__close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        border: 0;
        background: transparent;
        color: #090909;
        text-decoration: none;
        font-size: 34px;
        line-height: 1;
    }

    .cart-app-header__close:active {
        transform: scale(0.94);
    }

    .cart-app-content {
        display: grid;
        gap: 14px;
        padding: 14px 12px 26px;
    }

    .cart-alert {
        margin: 0;
        padding: 12px 14px;
        border-radius: 10px;
        background: #fff;
        font-size: 14px;
        font-weight: 800;
    }

    .cart-alert.is-success {
        color: #278c04;
    }

    .cart-alert.is-error {
        color: #d61808;
    }

    .cart-item-list {
        display: grid;
        gap: 10px;
    }

    .cart-item-card {
        display: grid;
        grid-template-columns: 92px minmax(0, 1fr);
        gap: 12px;
        min-width: 0;
        padding: 10px;
        border: 1px solid #eeeeee;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(16, 24, 40, 0.04);
    }

    .cart-item-card__media {
        display: block;
        overflow: hidden;
        width: 92px;
        height: 116px;
        border-radius: 10px;
        background: #f0f0f0;
    }

    .cart-item-card__media img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-card__body {
        display: grid;
        min-width: 0;
        gap: 8px;
    }

    .cart-item-card__title {
        display: -webkit-box;
        margin: 0;
        overflow: hidden;
        color: #171717;
        font-size: 14px;
        font-weight: 900;
        line-height: 1.45;
        text-decoration: none;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .cart-item-card .order-item-options,
    .cart-item-card .shopping-cart__product-item__options {
        display: none !important;
    }

    .cart-item-card__options {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .cart-option-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        max-width: 100%;
        padding: 5px 8px;
        border-radius: 999px;
        background: #f8f4eb;
        color: #5e4b22;
        font-size: 11px;
        font-weight: 800;
    }

    .cart-option-pill__swatch {
        width: 12px;
        height: 12px;
        border: 1px solid rgba(0, 0, 0, 0.12);
        border-radius: 999px;
        background: var(--cart-color, #f0c14b);
    }

    .cart-item-card__price-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .cart-item-card__price {
        color: #ef4d1f;
        font-size: 18px;
        font-weight: 900;
        line-height: 1;
    }

    .cart-item-card__subtotal {
        color: #767676;
        font-size: 12px;
        font-weight: 800;
    }

    .cart-item-card__actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .cart-qty {
        display: inline-flex;
        align-items: center;
        min-width: 118px;
        overflow: hidden;
        border: 1px solid #eeeeee;
        border-radius: 999px;
        background: #fafafa;
    }

    .cart-qty form {
        margin: 0;
    }

    .cart-qty button,
    .cart-remove-button {
        border: 0;
        background: transparent;
        color: #111;
    }

    .cart-qty button {
        width: 36px;
        height: 34px;
        font-size: 19px;
        font-weight: 900;
        line-height: 1;
    }

    .cart-qty__number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        color: #111;
        font-size: 14px;
        font-weight: 900;
    }

    .cart-remove-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 999px;
        background: #fff3f0;
        color: #d73b18;
        font-size: 18px;
    }

    .cart-panel {
        padding: 12px;
        border: 1px solid #eeeeee;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(16, 24, 40, 0.04);
    }

    .cart-coupon-form {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
        margin: 0 0 10px;
    }

    .cart-coupon-form input[type="text"] {
        min-width: 0;
        height: 44px;
        border: 1px solid #eeeeee;
        border-radius: 999px;
        background: #fafafa;
        color: #151515;
        font-size: 13px;
        font-weight: 700;
        text-align: right;
    }

    .cart-coupon-form button,
    .cart-coupon-form input[type="submit"],
    .cart-empty-button {
        height: 44px;
        padding: 0 16px;
        border: 0;
        border-radius: 999px;
        background: #111;
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }

    .cart-empty-button {
        width: 100%;
        background: #fff3f0;
        color: #d73b18;
    }

    .cart-summary {
        display: grid;
        gap: 10px;
    }

    .cart-summary h2 {
        margin: 0 0 2px;
        color: #151515;
        font-size: 18px;
        font-weight: 900;
    }

    .cart-summary__row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #4f4f4f;
        font-size: 14px;
        font-weight: 800;
    }

    .cart-summary__row.is-total {
        padding-top: 10px;
        border-top: 1px solid #eeeeee;
        color: #111;
        font-size: 17px;
        font-weight: 900;
    }

    .cart-empty-state {
        display: grid;
        place-items: center;
        min-height: 260px;
        padding: 32px 18px;
        text-align: center;
    }

    .cart-empty-state i {
        color: #d4a853;
        font-size: 64px;
    }

    .cart-empty-state p {
        margin: 10px 0 16px;
        color: #4a4a4a;
        font-size: 17px;
        font-weight: 800;
    }

    .cart-shop-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 0 24px;
        border-radius: 999px;
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
        color: #fff;
        font-size: 14px;
        font-weight: 900;
        text-decoration: none;
    }

    .cart-recommendations {
        display: grid;
        gap: 14px;
        padding-top: 10px;
    }

    .cart-recommendations__title {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin: 4px 0;
    }

    .cart-recommendations__title span {
        width: 10px;
        height: 10px;
        background: #d7d7d7;
        transform: rotate(45deg);
    }

    .cart-recommendations__title h2 {
        margin: 0;
        color: #151515;
        font-size: 22px;
        font-weight: 900;
        line-height: 1.2;
    }

    .cart-recommendations__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .cart-recommendations__grid .market-product-card {
        position: relative;
        min-width: 0;
        overflow: hidden;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(16, 24, 40, 0.06);
    }

    .cart-recommendations__grid .market-product-card__media {
        position: relative;
        display: block;
        overflow: hidden;
        background: #f0f0f0;
    }

    .cart-recommendations__grid .market-product-card__media img {
        display: block;
        width: 100%;
        aspect-ratio: 0.78;
        object-fit: cover;
    }

    .cart-recommendations__grid .market-product-card__wishlist {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.92);
        color: #111;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }

    .cart-recommendations__grid .market-product-card__wishlist.is-active {
        color: #f04d4d;
    }

    .cart-recommendations__grid .market-product-card__body {
        padding: 8px 8px 10px;
    }

    .cart-recommendations__grid .market-product-card__title {
        display: -webkit-box;
        min-height: 39px;
        margin: 0 0 5px;
        overflow: hidden;
        color: #151515;
        font-size: 13px;
        font-weight: 900;
        line-height: 1.45;
        text-decoration: none;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .cart-recommendations__grid .market-product-card__badge {
        display: inline-flex;
        max-width: 100%;
        margin-bottom: 5px;
        overflow: hidden;
        color: #b07817;
        font-size: 11px;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cart-recommendations__grid .market-product-card__meta {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 8px;
        overflow: hidden;
        color: #4b4b4b;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .cart-recommendations__grid .market-product-card__stars {
        color: #111;
    }

    .cart-recommendations__grid .market-product-card__footer {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 6px;
    }

    .cart-recommendations__grid .market-product-card__price {
        color: #111;
        font-size: 20px;
        font-weight: 900;
        line-height: 1;
    }

    .cart-recommendations__grid .market-product-card__price.is-sale {
        color: #ef4d1f;
    }

    .cart-recommendations__grid .market-product-card__price small {
        font-size: 12px;
        font-weight: 900;
    }

    .cart-recommendations__grid .market-product-card__discount {
        display: inline-flex;
        padding: 2px 5px;
        border-radius: 4px;
        background: #fff0ed;
        color: #d73b18;
        font-size: 11px;
        font-weight: 900;
    }

    .cart-recommendations__grid .market-product-card__cart {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 33px;
        height: 33px;
        border: 0;
        border-radius: 999px;
        background: #111;
        color: #fff;
    }

    .cart-sticky-checkout {
        position: fixed;
        right: 50%;
        bottom: 0;
        z-index: 40;
        width: min(100dvw, 520px);
        padding: 10px 12px calc(10px + env(safe-area-inset-bottom));
        border-top: 1px solid #eeeeee;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 -12px 26px rgba(16, 24, 40, 0.08);
        transform: translateX(50%);
    }

    .cart-checkout-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 52px;
        border-radius: 10px;
        background: #111;
        color: #fff;
        font-size: 16px;
        font-weight: 900;
        text-decoration: none;
    }

    @media (max-width: 420px) {
        .cart-app-header {
            min-height: 82px;
            padding: 18px 14px 17px;
        }

        .cart-app-header__close {
            flex-basis: 38px;
            width: 38px;
            height: 38px;
            font-size: 30px;
        }

        .cart-item-card {
            grid-template-columns: 84px minmax(0, 1fr);
            gap: 10px;
        }

        .cart-item-card__media {
            width: 84px;
            height: 108px;
        }

        .cart-coupon-form {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<main class="cart-app-shell">
    <header class="cart-app-header" aria-label="السلة">
        <a class="cart-app-header__shipping" href="{{ $shippingUrl }}">
            <strong class="cart-app-header__title">السلة</strong>
            <span class="cart-app-header__destination">
                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                <span>الشحن إلى {{ $shippingName }}</span>
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </span>
        </a>

        <a class="cart-app-header__close" href="{{ route('home.index') }}" aria-label="إغلاق السلة">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </a>
    </header>

    <section class="cart-app-content">
        @if(Session::has('success'))
            <p class="cart-alert is-success">{{ Session::get('success') }}</p>
        @elseif(Session::has('error'))
            <p class="cart-alert is-error">{{ Session::get('error') }}</p>
        @endif

        @if($cartCount > 0)
            <div class="cart-item-list" aria-label="منتجات السلة">
                @foreach($items as $item)
                    @php
                        $product = $item->model;
                        $rawImage = $product?->image;
                        $productUrl = $product?->slug
                            ? route('shop.product.details', ['product_slug' => $product->slug])
                            : route('shop.index');
                        $imageSrc = $rawImage
                            ? (\Illuminate\Support\Str::startsWith($rawImage, 'products/')
                                ? asset('uploads/'.$rawImage)
                                : asset('uploads/products/thumbnails/'.$rawImage))
                            : asset('assets/images/home/demo3/category_1.png');
                        $options = $item->options ?? collect();
                    @endphp

                    <article class="cart-item-card">
                        <a class="cart-item-card__media" href="{{ $productUrl }}">
                            <img
                                src="{{ $imageSrc }}"
                                alt="{{ $item->name }}"
                                loading="lazy"
                                onerror="this.onerror=null;this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                            >
                        </a>

                        <div class="cart-item-card__body">
                            <a class="cart-item-card__title" href="{{ $productUrl }}">{{ $item->name }}</a>

                            <div class="cart-item-card__options">
                                @if(! empty($options['color_name']))
                                    <span class="cart-option-pill">
                                        <span class="cart-option-pill__swatch" style="--cart-color: {{ $options['color_hex'] ?? $options['color_code'] ?? '#f0c14b' }}"></span>
                                        {{ $options['color_name'] }}
                                    </span>
                                @endif

                                @if(! empty($options['size_name']))
                                    <span class="cart-option-pill">المقاس: {{ $options['size_name'] }}</span>
                                @endif
                            </div>

                            <div class="cart-item-card__price-row">
                                <span class="cart-item-card__price">{{ format_price($item->price) }}</span>
                                <span class="cart-item-card__subtotal">المجموع {{ format_price($item->subTotal()) }}</span>
                            </div>

                            <div class="cart-item-card__actions">
                                <div class="cart-qty" aria-label="تعديل الكمية">
                                    <form method="POST" action="{{ route('cart.qty.decrease', ['rowId' => $item->rowId]) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" aria-label="تقليل الكمية">-</button>
                                    </form>

                                    <span class="cart-qty__number">{{ $item->qty }}</span>

                                    <form method="POST" action="{{ route('cart.qty.increase', ['rowId' => $item->rowId]) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" aria-label="زيادة الكمية">+</button>
                                    </form>
                                </div>

                                <form method="POST" action="{{ route('cart.item.remove', ['rowId' => $item->rowId]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="cart-remove-button" type="submit" aria-label="حذف المنتج">
                                        <i class="bi bi-trash3" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <section class="cart-panel" aria-label="الكوبون">
                @if(! Session::has('coupon'))
                    <form action="{{ route('cart.coupon.apply') }}" method="POST" class="cart-coupon-form">
                        @csrf
                        <input type="text" name="coupon_code" placeholder="رمز الكوبون" value="">
                        <button type="submit">تطبيق</button>
                    </form>
                @else
                    <form action="{{ route('cart.coupon.remove') }}" method="POST" class="cart-coupon-form">
                        @csrf
                        @method('DELETE')
                        <input type="text" name="coupon_code" value="{{ Session::get('coupon')['code'] }} مطبق" readonly>
                        <button type="submit">إزالة</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('cart.empty') }}">
                    @csrf
                    @method('DELETE')
                    <button class="cart-empty-button" type="submit">إفراغ السلة</button>
                </form>
            </section>

            <section class="cart-panel cart-summary" aria-label="إجمالي السلة">
                <h2>إجمالي السلة</h2>

                @if(Session::has('discounts'))
                    <div class="cart-summary__row">
                        <span>المجموع الفرعي</span>
                        <strong>{{ format_price(Cart::instance('cart')->subtotal()) }}</strong>
                    </div>
                    <div class="cart-summary__row">
                        <span>خصم {{ Session::get('coupon')['code'] }}</span>
                        <strong>{{ format_price(Session::get('discounts')['discount']) }}</strong>
                    </div>
                    <div class="cart-summary__row">
                        <span>بعد الخصم</span>
                        <strong>{{ format_price(Session::get('discounts')['subtotal']) }}</strong>
                    </div>
                    <div class="cart-summary__row">
                        <span>الشحن</span>
                        <strong>مجاني</strong>
                    </div>
                    <div class="cart-summary__row is-total">
                        <span>الإجمالي</span>
                        <strong>{{ format_price(Session::get('discounts')['total']) }}</strong>
                    </div>
                @else
                    <div class="cart-summary__row">
                        <span>المجموع الفرعي</span>
                        <strong>{{ format_price(Cart::instance('cart')->subtotal()) }}</strong>
                    </div>
                    <div class="cart-summary__row">
                        <span>الشحن</span>
                        <strong>مجاني</strong>
                    </div>
                    <div class="cart-summary__row is-total">
                        <span>الإجمالي</span>
                        <strong>{{ format_price(Cart::instance('cart')->total()) }}</strong>
                    </div>
                @endif
            </section>
        @else
            <section class="cart-panel cart-empty-state" aria-label="السلة فارغة">
                <div>
                    <i class="bi bi-bag" aria-hidden="true"></i>
                    <p>لا توجد عناصر في سلتك</p>
                    <a href="{{ route('shop.index') }}" class="cart-shop-button">تسوق الآن</a>
                </div>
            </section>
        @endif

        <section class="cart-recommendations" aria-label="أشياء قد تعجبك">
            <div class="cart-recommendations__title">
                <span aria-hidden="true"></span>
                <h2>أشياء قد تعجبك</h2>
                <span aria-hidden="true"></span>
            </div>

            <div class="cart-recommendations__grid">
                @forelse($recommendedProducts as $product)
                    <x-shop.market-product-card :product="$product" />
                @empty
                    <div class="cart-panel text-center text-muted">لا توجد منتجات مقترحة حاليًا.</div>
                @endforelse
            </div>
        </section>
    </section>

    @if($cartCount > 0)
        <div class="cart-sticky-checkout">
            <a href="{{ route('cart.checkout') }}" class="cart-checkout-button">متابعة إلى الدفع</a>
        </div>
    @endif
</main>
@endsection
