@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/mobile-market.css') }}?v={{ filemtime(public_path('assets/css/mobile-market.css')) }}" type="text/css" />

<style>
    @media (max-width: 991.98px) {
        body:has(.kids-category-detail-shell) .header-mobile,
        body:has(.kids-category-detail-shell) #header,
        body:has(.kids-category-detail-shell) .header {
            display: none !important;
        }

        .kids-category-detail-shell {
            --kids-market-top-height: 70px;
        }

        .category-detail-topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) 42px 42px;
            gap: 8px;
            align-items: center;
            width: 100%;
            max-width: 430px;
            min-height: var(--kids-market-top-height);
            margin: 0 auto;
            padding: 10px 12px;
            background: #fff;
            border-bottom: 1px solid #eceff3;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
            direction: rtl;
        }

        .category-detail-topbar__icon,
        .category-detail-topbar__submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 12px;
            background: #fff;
            color: #111;
            font-size: 24px;
            line-height: 1;
            text-decoration: none;
            transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .category-detail-topbar__icon:active,
        .category-detail-topbar__submit:active,
        .category-floating-cart:active {
            transform: scale(0.94);
        }

        .category-detail-search {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 38px 48px;
            align-items: center;
            min-width: 0;
            height: 48px;
            overflow: hidden;
            border: 2px solid #111;
            border-radius: 14px;
            background: #fff;
            direction: rtl;
        }

        .category-detail-search input {
            min-width: 0;
            width: 100%;
            height: 100%;
            border: 0;
            padding: 0 14px 0 6px;
            background: transparent;
            color: #333;
            font-size: 16px;
            font-weight: 800;
            outline: 0;
            text-align: right;
        }

        .category-detail-search input::placeholder {
            color: #777;
            opacity: 1;
        }

        .category-detail-search__camera {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            color: #9ca3af;
            font-size: 21px;
        }

        .category-detail-topbar__submit {
            width: 48px;
            height: 48px;
            border-radius: 0;
            background: #050505;
            color: #fff;
            font-size: 25px;
        }

        .category-floating-cart {
            position: fixed;
            inset-inline-end: 16px;
            bottom: calc(82px + env(safe-area-inset-bottom));
            z-index: 1040;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border: 2px solid #111;
            border-radius: 50%;
            background: #fff;
            color: #111;
            text-decoration: none;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .category-floating-cart i {
            font-size: 25px;
            line-height: 1;
        }

        .category-floating-cart__count {
            position: absolute;
            top: -5px;
            inset-inline-start: -5px;
            min-width: 20px;
            height: 20px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ff4d2d;
            color: #fff;
            font-size: 11px;
            font-weight: 950;
            line-height: 20px;
            text-align: center;
        }

        .category-detail-related {
            padding-top: 12px;
        }

        @media (max-width: 380px) {
            .category-detail-topbar {
                grid-template-columns: 38px minmax(0, 1fr) 38px 38px;
                gap: 6px;
                padding-inline: 8px;
            }

            .category-detail-topbar__icon {
                width: 38px;
                height: 38px;
                font-size: 22px;
            }

            .category-detail-search {
                height: 44px;
                grid-template-columns: minmax(0, 1fr) 34px 44px;
            }

            .category-detail-search input {
                font-size: 14px;
            }

            .category-detail-topbar__submit {
                width: 44px;
                height: 44px;
            }
        }
    }
</style>

@php
    $resolveCategoryImage = function (?string $path) {
        if (! $path) {
            return asset('assets/images/home/demo3/category_1.png');
        }

        return \Illuminate\Support\Str::startsWith($path, 'categories/')
            ? asset('uploads/'.$path)
            : asset('uploads/categories/'.$path);
    };

    $queryUrl = function (array $overrides = [], array $remove = ['page']) use ($category) {
        $query = request()->query();

        foreach ($remove as $key) {
            unset($query[$key]);
        }

        foreach ($overrides as $key => $value) {
            if ($value === null || $value === '') {
                unset($query[$key]);
            } else {
                $query[$key] = $value;
            }
        }

        $baseUrl = route('shop.category', $category->slug);

        return empty($query) ? $baseUrl : $baseUrl.'?'.http_build_query($query);
    };

    $currentOrder = request('order', 'relevance');
    $currentDirection = request('order_dir', 'desc');
    $priceDirection = $currentOrder === 'price' && $currentDirection === 'asc' ? 'desc' : 'asc';
    $filterCount = 0;
    $filterCount += request('brands') ? 1 : 0;
    $filterCount += request('min_price') ? 1 : 0;
    $filterCount += request('max_price') && (int) request('max_price') !== 500000 ? 1 : 0;
    $filterCount += count((array) request('colors', []));
    $filterCount += count((array) request('sizes', []));
@endphp

<div class="kids-market-shell kids-category-detail-shell d-block d-lg-none">
    <header class="category-detail-topbar" aria-label="شريط تفاصيل الفئة" dir="rtl">
        <a class="category-detail-topbar__icon" href="{{ route('categories.index') }}" aria-label="رجوع">
            <i class="bi bi-chevron-right"></i>
        </a>

        <form class="category-detail-search" action="{{ route('shop.search') }}" method="GET" role="search">
            <input type="search" name="search" value="{{ request('search', $category->name) }}" placeholder="{{ $category->name }}" aria-label="البحث داخل المتجر">
            <span class="category-detail-search__camera" aria-hidden="true">
                <i class="bi bi-camera"></i>
            </span>
            <button class="category-detail-topbar__submit" type="submit" aria-label="بحث">
                <i class="bi bi-search"></i>
            </button>
        </form>

        <button class="category-detail-topbar__icon" type="button" data-bs-toggle="modal" data-bs-target="#filterModal" aria-label="الفلاتر">
            <i class="bi bi-list-ul"></i>
        </button>

        <a class="category-detail-topbar__icon" href="{{ route('wishlist.index') }}" aria-label="المفضلة">
            <i class="bi bi-heart"></i>
        </a>
    </header>

    <a class="category-floating-cart" href="{{ route('cart.index') }}" aria-label="السلة">
        <i class="bi bi-cart3"></i>
        @if(Cart::instance('cart')->content()->count() > 0)
            <span class="category-floating-cart__count">{{ Cart::instance('cart')->content()->count() }}</span>
        @endif
    </a>

    <main class="category-detail-mobile" dir="rtl">
        <section class="category-detail-related" aria-label="فئات مرتبطة">
            @foreach($relatedCategories as $relatedCategory)
                <a
                    class="category-detail-related__item {{ $relatedCategory->id === $category->id ? 'is-active' : '' }}"
                    href="{{ route('shop.category', $relatedCategory->slug) }}"
                >
                    <span class="category-detail-related__image">
                        <img
                            src="{{ $resolveCategoryImage($relatedCategory->image) }}"
                            alt="{{ $relatedCategory->name }}"
                            loading="lazy"
                            onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                        >
                    </span>
                    <span>{{ $relatedCategory->name }}</span>
                </a>
            @endforeach
        </section>

        <section class="category-detail-sortbar" aria-label="ترتيب وفرز المنتجات">
            <a class="{{ $currentOrder === 'relevance' ? 'is-active' : '' }}" href="{{ $queryUrl(['order' => 'relevance', 'order_dir' => 'desc']) }}">
                Recommend
                <i class="bi bi-chevron-down"></i>
            </a>
            <a class="{{ $currentOrder === 'bestselling' ? 'is-active' : '' }}" href="{{ $queryUrl(['order' => 'bestselling', 'order_dir' => 'desc']) }}">Most Popular</a>
            <a class="{{ $currentOrder === 'price' ? 'is-active' : '' }}" href="{{ $queryUrl(['order' => 'price', 'order_dir' => $priceDirection]) }}">
                Price
                <i class="bi bi-arrow-down-up"></i>
            </a>
            <button type="button" data-bs-toggle="modal" data-bs-target="#filterModal">
                Filter
                @if($filterCount > 0)
                    <span>{{ $filterCount }}</span>
                @endif
                <i class="bi bi-funnel"></i>
            </button>
        </section>

        <section class="category-detail-filterchips" aria-label="فلاتر سريعة">
            <a class="{{ request('featured') === '1' ? 'is-active' : '' }}" href="{{ $queryUrl(['featured' => request('featured') === '1' ? null : '1']) }}">
                <span class="category-detail-trends">Trends</span>
            </a>
            <a class="is-static" href="{{ route('categories.index', ['category_id' => $category->id]) }}">
                Category <i class="bi bi-chevron-down"></i>
            </a>
            @if($sizes->isNotEmpty())
                <button type="button" data-bs-toggle="modal" data-bs-target="#filterModal">
                    Age <i class="bi bi-chevron-down"></i>
                </button>
            @endif
            @if($colors->isNotEmpty())
                <button type="button" data-bs-toggle="modal" data-bs-target="#filterModal">
                    Color <i class="bi bi-chevron-down"></i>
                </button>
            @endif
            <button type="button" data-bs-toggle="modal" data-bs-target="#sortModal">
                Sort <i class="bi bi-chevron-down"></i>
            </button>
        </section>

        <section class="category-detail-products" aria-label="منتجات {{ $category->name }}">
            @forelse($products as $product)
                <x-shop.market-product-card :product="$product" />
            @empty
                <div class="kids-market-empty-state">لا توجد منتجات في هذه الفئة حاليا.</div>
            @endforelse
        </section>

        @if($products->hasPages())
            <div class="category-detail-pagination">
                {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </main>
</div>

<main class="category-detail-desktop d-none d-lg-block" dir="rtl">
    <div class="container py-5">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
            <div>
                <div class="text-muted small mb-1">
                    <a href="{{ route('home.index') }}" class="text-muted text-decoration-none">الرئيسية</a>
                    <span>/</span>
                    <a href="{{ route('categories.index') }}" class="text-muted text-decoration-none">الفئات</a>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ $category->name }}</h1>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-dark" type="button" data-bs-toggle="modal" data-bs-target="#sortModal">ترتيب</button>
                <button class="btn btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#filterModal">فلترة</button>
            </div>
        </div>

        <div class="d-flex gap-3 overflow-auto pb-3 mb-4">
            @foreach($relatedCategories as $relatedCategory)
                <a class="text-center text-decoration-none text-dark" style="width: 96px; flex: 0 0 96px;" href="{{ route('shop.category', $relatedCategory->slug) }}">
                    <img src="{{ $resolveCategoryImage($relatedCategory->image) }}" alt="{{ $relatedCategory->name }}" class="rounded-circle mb-2" style="width: 74px; height: 74px; object-fit: cover;">
                    <span class="d-block small fw-bold">{{ $relatedCategory->name }}</span>
                </a>
            @endforeach
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
            @forelse($products as $product)
                <div class="col">
                    <x-shop.market-product-card :product="$product" />
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border text-center">لا توجد منتجات في هذه الفئة حاليا.</div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</main>

<x-sort-modal
    :action="route('shop.category', $category->slug)"
    :extra-inputs="[
        'market_tab' => request('market_tab'),
        'featured' => request('featured'),
    ]"
/>

<x-filter-modal
    :action="route('shop.category', $category->slug)"
    :reset-url="route('shop.category', $category->slug)"
    :brands="$brands"
    :colors="$colors"
    :sizes="$sizes"
    :extra-inputs="[
        'market_tab' => request('market_tab'),
        'featured' => request('featured'),
    ]"
/>

@endsection
