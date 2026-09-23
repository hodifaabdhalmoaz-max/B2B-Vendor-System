@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/mobile-market.css') }}?v={{ filemtime(public_path('assets/css/mobile-market.css')) }}" type="text/css" />

@php
    $resolveCategoryImage = function ($path) {
        if (empty($path)) {
            return asset('assets/images/home/demo3/category_1.png');
        }

        return \Illuminate\Support\Str::startsWith($path, 'categories/')
            ? asset('uploads/'.$path)
            : asset('uploads/categories/'.$path);
    };

    $categoryFilterUrl = function (?int $categoryId = null) use ($activeMarketCategory) {
        $query = [];

        if ($activeMarketCategory !== 'all') {
            $query['market_tab'] = $activeMarketCategory;
        }

        if ($categoryId) {
            $query['category_id'] = $categoryId;
        }

        return route('categories.index', $query);
    };

    $sideFilters = collect([
        [
            'id' => 'all',
            'label' => 'كل الفئات',
            'type' => 'all',
            'href' => $categoryFilterUrl(),
            'active' => ! $selectedCategory,
        ],
    ])->merge($visibleCategories->map(fn ($category) => [
        'id' => 'category-'.$category->id,
        'label' => $category->name,
        'type' => 'category',
        'category_id' => $category->id,
        'href' => $categoryFilterUrl($category->id),
        'active' => $selectedCategory?->id === $category->id,
    ]));
@endphp

<main>
    <div class="kids-market-shell kids-category-shell">
        <x-shop.top-header-banner
            :active-category="$activeMarketCategory"
            tabs-target="categories"
            :show-hero="false"
            :show-benefits="false"
        />

        <section class="kids-category-browser" dir="rtl" data-category-browser>
            <aside class="kids-category-sidebar" aria-label="فلترة الفئات">
                @foreach($sideFilters as $filter)
                    <a
                        href="{{ $filter['href'] }}"
                        class="kids-category-side-filter {{ $filter['active'] ? 'is-active' : '' }}"
                        data-category-filter="{{ $filter['id'] }}"
                        data-category-id="{{ $filter['category_id'] ?? '' }}"
                        data-filter-type="{{ $filter['type'] }}"
                        data-filter-label="{{ $filter['label'] }}"
                        aria-current="{{ $filter['active'] ? 'true' : 'false' }}"
                        aria-selected="{{ $filter['active'] ? 'true' : 'false' }}"
                    >
                        {{ $filter['label'] }}
                    </a>
                @endforeach
            </aside>

            <div class="kids-category-content">
                <section class="kids-category-picks" aria-label="فئات مختارة">
                    <h1 class="kids-category-heading" data-category-heading>{{ $selectedCategory ? $selectedCategory->name : 'مختارات من أجلك' }}</h1>
                    <div class="kids-category-grid">
                        @forelse($contentCategories as $category)
                            <a
                                class="kids-category-bubble"
                                href="{{ route('shop.category', $category->slug) }}"
                                data-category-card
                                data-category-id="{{ $category->id }}"
                            >
                                <span class="kids-category-bubble__image">
                                    <img
                                        src="{{ $resolveCategoryImage($category->image) }}"
                                        alt="{{ $category->name }}"
                                        loading="lazy"
                                        onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                                    >
                                </span>
                                <span class="kids-category-bubble__name">{{ $category->name }}</span>
                            </a>
                        @empty
                            <div class="kids-market-empty-state">لا توجد فئات مخصصة لهذا التبويب حاليا.</div>
                        @endforelse
                    </div>
                </section>

                <section class="kids-category-recommended" aria-label="منتجات مقترحة">
                    <h2 class="kids-category-heading kids-category-heading--small" data-products-heading>
                        {{ $selectedCategory ? 'منتجات مقترحة - '.$selectedCategory->name : 'منتجات مقترحة' }}
                    </h2>
                    <div class="kids-category-products">
                        @forelse($recommendedProducts as $product)
                            <div data-category-product data-category-id="{{ $product->category_id }}">
                                <x-shop.market-product-card :product="$product" />
                            </div>
                        @empty
                            <div class="kids-market-empty-state">لا توجد منتجات مقترحة حاليا.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        </section>
    </div>

    <section class="category-page container d-none d-lg-block py-5">
        <h1 class="fw-bold mb-4">جميع الفئات</h1>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4">
            @foreach($contentCategories as $category)
                <div class="col">
                    <a href="{{ route('shop.category', $category->slug) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm text-center py-4 px-2" style="border-radius: 12px;">
                            <div class="mb-3 mx-auto d-flex align-items-center justify-content-center" style="height: 80px;">
                                <img
                                    src="{{ $resolveCategoryImage($category->image) }}"
                                    alt="{{ $category->name }}"
                                    class="img-fluid"
                                    style="max-height: 100%; max-width: 100%; object-fit: contain;"
                                    onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                                >
                            </div>
                            <h2 class="h6 mb-0 fw-bold text-dark">{{ $category->name }}</h2>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
</main>

@endsection
