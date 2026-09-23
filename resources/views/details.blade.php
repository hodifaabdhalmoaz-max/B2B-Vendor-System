@extends('layouts.app')

@section('content')
@php
    $assetForPath = function (?string $path, string $fallbackFolder = 'uploads/products') {
        $path = trim((string) $path);

        if ($path === '') {
            return asset('assets/images/home/demo3/category_1.png');
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', 'uploads/', 'assets/', 'storage/'])) {
            return asset($path);
        }

        if (\Illuminate\Support\Str::startsWith($path, 'products/colors/')) {
            return asset('storage/'.$path);
        }

        return asset(trim($fallbackFolder, '/').'/'.$path);
    };

    $rawGallery = $product->images;
    $galleryNames = collect(is_array($rawGallery) ? $rawGallery : explode(',', (string) $rawGallery))
        ->map(fn ($image) => trim((string) $image))
        ->filter();

    $baseGallery = collect();
    if (! empty($product->image)) {
        $baseGallery->push([
            'src' => $assetForPath($product->image),
            'label' => $product->name,
        ]);
    }

    foreach ($galleryNames as $image) {
        $baseGallery->push([
            'src' => $assetForPath($image),
            'label' => $product->name,
        ]);
    }

    if ($baseGallery->isEmpty()) {
        $baseGallery->push([
            'src' => asset('assets/images/home/demo3/category_1.png'),
            'label' => $product->name,
        ]);
    }

    $colorImagesByColor = $product->colorImages->groupBy('color_id');
    $regularPrice = (float) ($product->regular_price ?? 0);
    $salePrice = (float) ($product->sale_price ?? 0);
    $hasSale = $salePrice > 0 && $salePrice < $regularPrice;
    $displayPrice = $hasSale ? $salePrice : $regularPrice;
    $discount = $hasSale && $regularPrice > 0 ? (int) round((($regularPrice - $salePrice) / $regularPrice) * 100) : null;
    $averageRating = (float) $product->average_rating;
    $reviewCount = (int) $product->review_count;
    $productRequiresOptions = $product->colors->isNotEmpty() || $product->sizes->isNotEmpty();
    $selectedColorId = (int) old('color_id', optional($product->colors->first())->id);
    $selectedSizeId = (int) old('size_id', optional($product->sizes->first())->id);
    $galleryForColor = function ($color) use ($assetForPath, $colorImagesByColor) {
        $colorGallery = collect();
        $legacyImage = $color?->pivot?->image;

        if ($legacyImage) {
            $colorGallery->push([
                'src' => $assetForPath($legacyImage, 'storage/products/colors'),
                'label' => $color->name,
            ]);
        }

        foreach ($colorImagesByColor->get($color?->id, collect()) as $image) {
            $colorGallery->push([
                'src' => $assetForPath($image->image_path),
                'label' => $color->name,
            ]);
        }

        return $colorGallery;
    };
    $selectedColor = $product->colors->firstWhere('id', $selectedColorId);
    $selectedColorGallery = $selectedColor ? $galleryForColor($selectedColor) : collect();
    $usesSelectedColorGallery = $selectedColorGallery->isNotEmpty();
    $initialGallery = $usesSelectedColorGallery ? $selectedColorGallery : $baseGallery;
    $firstImage = $initialGallery->first()['src'];
    $fallbackColorPreview = $baseGallery->first()['src'] ?? asset('assets/images/home/demo3/category_1.png');
    $wishlistItem = Cart::instance('wishlist')->content()->firstWhere('id', $product->id);
    $cartCount = Cart::instance('cart')->count();
@endphp

<style>
    body:has(.product-detail-page) header,
    body:has(.product-detail-page) #header,
    body:has(.product-detail-page) .header,
    body:has(.product-detail-page) .site-header,
    body:has(.product-detail-page) .mobile-header,
    body:has(.product-detail-page) footer,
    body:has(.product-detail-page) .footer,
    body:has(.product-detail-page) .footer-mobile,
    body:has(.product-detail-page) .mobile-bottom-nav {
        display: none !important;
    }

    body:has(.product-detail-page) {
        background: #f3f4f6;
        overflow-x: clip;
    }

    body:has(.product-detail-page) .pt-90 {
        padding-top: 0 !important;
    }

    html:has(.product-detail-page) {
        overflow-x: hidden;
    }

    .product-detail-page {
        --pd-coral: #ff7448;
        --pd-yellow: #ffe17d;
        --pd-teal: #64c9cf;
        --pd-ink: #111827;
        --pd-muted: #6b7280;
        --pd-line: #ececec;
        direction: rtl;
        width: 100%;
        max-width: 520px;
        min-height: 100vh;
        margin: 0 auto;
        padding-top: 106px;
        padding-bottom: 96px;
        background: #f5f5f5;
        color: var(--pd-ink);
        font-family: "Tajawal", "Cairo", sans-serif;
    }

    .product-detail-page,
    .product-detail-page *,
    .pd-sticky-cart,
    .pd-sticky-cart * {
        box-sizing: border-box;
    }

    .pd-topbar {
        position: fixed;
        top: 0;
        left: 50%;
        z-index: 80;
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr) 36px 36px 32px;
        align-items: center;
        gap: 7px;
        min-height: 56px;
        padding: 7px 10px;
        background: #fff;
        border-bottom: 1px solid var(--pd-line);
        width: min(100dvw, 520px);
        max-width: 100dvw;
        transform: translateX(-50%);
        direction: ltr;
    }

    .pd-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        min-width: 0;
        border: 1px solid transparent;
        border-radius: 10px;
        background: #fff;
        color: #111;
        font-size: 21px;
        line-height: 1;
        text-decoration: none;
    }

    .pd-search-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        height: 40px;
        padding: 0 10px;
        border: 1px solid #dedede;
        border-radius: 8px;
        background: #f8f8f8;
        color: #555;
        text-decoration: none;
        direction: rtl;
    }

    .pd-search-pill span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
        font-weight: 700;
    }

    .pd-cart-shortcut {
        position: relative;
    }

    .pd-cart-count {
        position: absolute;
        top: -2px;
        right: -4px;
        min-width: 17px;
        height: 17px;
        padding: 0 4px;
        border-radius: 999px;
        background: #f04423;
        color: #fff;
        font-size: 11px;
        font-weight: 900;
        line-height: 17px;
        text-align: center;
    }

    .pd-nav-tabs {
        position: fixed;
        top: 56px;
        left: 50%;
        z-index: 70;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        background: #fff;
        border-bottom: 1px solid var(--pd-line);
        width: min(100dvw, 520px);
        max-width: 100dvw;
        transform: translateX(-50%);
    }

    .pd-nav-tabs a {
        position: relative;
        padding: 12px 4px 13px;
        color: #6b7280;
        min-width: 0;
        font-size: 14px;
        font-weight: 900;
        text-align: center;
        text-decoration: none;
    }

    .pd-nav-tabs a.is-active,
    .pd-nav-tabs a:focus {
        color: #111;
    }

    .pd-nav-tabs a.is-active::after {
        position: absolute;
        right: 50%;
        bottom: 5px;
        width: 34px;
        height: 4px;
        border-radius: 999px;
        background: #111;
        transform: translateX(50%);
        content: "";
    }

    .pd-gallery {
        position: relative;
        background: #fff;
    }

    .pd-main-carousel {
        position: relative;
        overflow: hidden;
        min-height: 380px;
        background: #fff;
        direction: ltr;
        cursor: grab;
    }

    .pd-main-carousel.is-dragging {
        cursor: grabbing;
    }

    .pd-main-track {
        display: flex;
        width: 100%;
        overflow-x: auto;
        scroll-behavior: smooth;
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .pd-main-track::-webkit-scrollbar {
        display: none;
    }

    .pd-main-slide {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 100%;
        min-width: 100%;
        min-height: 380px;
        scroll-snap-align: start;
        background: #fff;
    }

    .pd-main-slide img {
        width: 100%;
        max-height: 480px;
        object-fit: contain;
        user-select: none;
        -webkit-user-drag: none;
    }

    .pd-image-counter {
        position: absolute;
        top: 310px;
        left: 16px;
        padding: 4px 9px;
        border-radius: 999px;
        background: rgba(0, 0, 0, .52);
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        direction: ltr;
    }

    .pd-thumb-strip,
    .pd-color-gallery-strip {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 10px 12px 14px;
        background: #fff;
        scroll-behavior: smooth;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
    }

    .pd-thumb-strip::-webkit-scrollbar,
    .pd-color-gallery-strip::-webkit-scrollbar {
        display: none;
    }

    .pd-thumb {
        width: 58px;
        height: 70px;
        padding: 0;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        background: #fff;
        overflow: hidden;
        flex: 0 0 auto;
        scroll-snap-align: start;
    }

    .pd-thumb.is-active {
        border: 2px solid #111;
    }

    .pd-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pd-card {
        margin-top: 8px;
        padding: 14px 14px 16px;
        background: #fff;
    }

    .pd-price-row {
        display: flex;
        align-items: baseline;
        gap: 8px;
        flex-wrap: wrap;
        direction: ltr;
        justify-content: flex-end;
    }

    .pd-price {
        color: #e9411c;
        font-size: 32px;
        font-weight: 950;
        letter-spacing: 0;
    }

    .pd-price small {
        margin-right: 2px;
        font-size: 15px;
        font-weight: 900;
    }

    .pd-discount {
        padding: 3px 6px;
        border-radius: 4px;
        background: #f04423;
        color: #fff;
        font-size: 13px;
        font-weight: 900;
    }

    .pd-old-price {
        color: #888;
        font-size: 14px;
        text-decoration: line-through;
    }

    .pd-coupon-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        overflow: visible;
        width: 100%;
        max-width: 100%;
        margin: 9px 0 0;
        padding: 0 0 3px;
    }

    .pd-coupon-row span {
        display: inline-flex;
        min-width: max-content;
        padding: 5px 8px;
        background: #fff5ef;
        color: #d95028;
        font-size: 12px;
        font-weight: 900;
    }

    .pd-title-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 12px;
        align-items: start;
        margin-top: 12px;
    }

    .pd-title {
        margin: 0;
        color: #111;
        font-size: 18px;
        font-weight: 950;
        line-height: 1.45;
    }

    .pd-rating-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 0;
        color: #111;
        font-size: 14px;
        font-weight: 900;
        white-space: nowrap;
        text-decoration: none;
    }

    .pd-rating-pill i,
    .pd-stars {
        color: #ffc400;
    }

    .pd-section {
        margin-top: 8px;
        margin-bottom: 0;
        padding: 16px 14px;
        background: #fff;
        overflow: hidden;
    }

    .pd-card,
    .pd-gallery {
        margin-bottom: 0;
    }

    .pd-section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        color: #111;
        font-size: 16px;
        font-weight: 950;
        min-width: 0;
        max-width: 100%;
    }

    .pd-selected-label {
        color: #111;
        font-weight: 950;
    }

    .pd-variant-form {
        display: grid;
        gap: 16px;
        min-width: 0;
        max-width: 100%;
    }

    .pd-variant-form > * {
        min-width: 0;
        max-width: 100%;
    }

    .pd-color-options,
    .pd-size-options,
    .pd-option-tags,
    .pd-recommend-tags {
        display: flex;
        gap: 9px;
        overflow-x: auto;
        min-width: 0;
        max-width: 100%;
        scrollbar-width: none;
        scroll-behavior: smooth;
        scroll-snap-type: x proximity;
    }

    .pd-color-options::-webkit-scrollbar,
    .pd-size-options::-webkit-scrollbar,
    .pd-option-tags::-webkit-scrollbar,
    .pd-recommend-tags::-webkit-scrollbar {
        display: none;
    }

    .pd-color-option,
    .pd-size-option {
        position: relative;
        flex: 0 0 auto;
        cursor: pointer;
        scroll-snap-align: start;
    }

    .pd-color-option input,
    .pd-size-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .pd-color-box {
        display: block;
        position: relative;
        width: 58px;
        height: 70px;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        background: #f6f6f6;
        overflow: hidden;
    }

    .pd-color-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pd-color-box--fallback img {
        opacity: 0.92;
    }

    .pd-color-chip {
        position: absolute;
        inset-inline-end: 4px;
        bottom: 4px;
        width: 16px;
        height: 16px;
        border: 2px solid #fff;
        border-radius: 50%;
        box-shadow: 0 2px 7px rgba(0, 0, 0, 0.2);
    }

    .pd-color-swatch {
        display: block;
        width: 100%;
        height: 100%;
        border: 6px solid #fff;
    }

    .pd-color-option input:checked + .pd-color-box,
    .pd-size-option input:checked + .pd-size-box {
        border: 2px solid #111;
    }

    .pd-size-box,
    .pd-option-tag,
    .pd-recommend-tag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 74px;
        min-height: 43px;
        padding: 0 15px;
        border: 1px solid #f1f1f1;
        border-radius: 3px;
        background: #f7f7f7;
        color: #111;
        font-size: 15px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .pd-size-detail {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px;
        border-radius: 4px;
        background: #f8f8f8;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.45;
        min-width: 0;
        max-width: 100%;
    }

    .pd-size-detail strong {
        color: #111;
    }

    .pd-link-row {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 12px;
        color: #111;
        font-size: 13px;
        font-weight: 900;
        min-width: 0;
        max-width: 100%;
    }

    .pd-option-tag {
        min-width: auto;
        min-height: 40px;
        max-width: 100%;
    }

    .pd-option-tags {
        flex-wrap: wrap;
        overflow: visible;
    }

    .pd-shipping-row {
        display: grid;
        gap: 8px;
        color: #111;
        font-size: 14px;
        font-weight: 800;
    }

    .pd-shipping-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .pd-shipping-line strong {
        color: #14855b;
    }

    .pd-muted {
        color: var(--pd-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .pd-review-summary {
        display: grid;
        gap: 12px;
    }

    .pd-review-score {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .pd-review-score strong {
        font-size: 28px;
        font-weight: 950;
    }

    .pd-fit-bars {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .pd-fit-bar span {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 900;
    }

    .pd-fit-bar i {
        display: block;
        height: 6px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .pd-fit-bar b {
        display: block;
        height: 100%;
        background: #111;
    }

    .pd-review-tags {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .pd-review-tags span {
        padding: 8px 10px;
        border-radius: 3px;
        background: #f7f7f7;
        color: #111;
        font-size: 13px;
        font-weight: 900;
    }

    .pd-review-item {
        padding: 16px 0;
        border-top: 1px solid var(--pd-line);
    }

    .pd-review-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #111;
        font-size: 14px;
        font-weight: 900;
    }

    .pd-review-text {
        margin: 8px 0 0;
        color: #111;
        font-size: 15px;
        line-height: 1.6;
    }

    .pd-review-form {
        display: grid;
        gap: 10px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--pd-line);
    }

    .pd-review-form input,
    .pd-review-form textarea,
    .pd-review-form select {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 14px;
    }

    .pd-review-form button {
        border: 0;
        border-radius: 6px;
        background: #111;
        color: #fff;
        padding: 11px 14px;
        font-size: 14px;
        font-weight: 900;
    }

    .pd-detail-feature {
        padding: 14px;
        border-radius: 4px;
        background: linear-gradient(90deg, #f0f4ff, #eef8ff);
        color: #111;
        font-size: 14px;
        line-height: 1.7;
    }

    .pd-detail-list {
        display: grid;
        gap: 1px;
        margin-top: 12px;
        background: #f3f4f6;
    }

    .pd-detail-list div {
        display: grid;
        grid-template-columns: 105px 1fr;
        gap: 12px;
        padding: 12px;
        background: #fff;
        font-size: 14px;
    }

    .pd-detail-list span {
        color: #6b7280;
    }

    .pd-recommend-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .pd-product-card {
        position: relative;
        overflow: hidden;
        border-radius: 4px;
        background: #fff;
    }

    .pd-product-card__image {
        display: block;
        aspect-ratio: 1 / 1.18;
        background: #f8f8f8;
    }

    .pd-product-card__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pd-product-card__body {
        padding: 8px 8px 10px;
    }

    .pd-product-card__title {
        display: -webkit-box;
        min-height: 38px;
        overflow: hidden;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        color: #111;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.45;
        text-decoration: none;
    }

    .pd-product-card__meta {
        margin-top: 5px;
        color: #9a6b00;
        font-size: 12px;
        font-weight: 900;
    }

    .pd-product-card__price {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 8px;
        color: #e9411c;
        font-size: 20px;
        font-weight: 950;
        direction: ltr;
    }

    .pd-product-card__price small {
        font-size: 12px;
    }

    .pd-product-card__cart {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 7px;
        background: #f5f5f5;
        color: #111;
        text-decoration: none;
    }

    .pd-sticky-cart {
        position: fixed;
        left: 50%;
        bottom: 0;
        z-index: 100;
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr);
        gap: 8px;
        width: min(100dvw, 520px);
        max-width: 100dvw;
        padding: 8px 10px calc(8px + env(safe-area-inset-bottom));
        background: #fff;
        border-top: 1px solid var(--pd-line);
        transform: translateX(-50%);
    }

    .pd-sticky-cart form {
        margin: 0;
    }

    .pd-wishlist-button,
    .pd-add-cart-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 50px;
        border: 0;
        border-radius: 10px;
        font-weight: 950;
        text-decoration: none;
        min-width: 0;
    }

    .pd-wishlist-button {
        background: #fff;
        color: #111;
        font-size: 30px;
        border: 1px solid #e5e7eb;
    }

    .pd-wishlist-button.is-active {
        color: var(--pd-coral);
    }

    .pd-add-cart-button {
        background: #050505;
        color: #fff;
        font-size: 15px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pd-empty-state {
        padding: 20px 0;
        color: #6b7280;
        text-align: center;
        font-size: 14px;
        font-weight: 800;
    }

    @media (min-width: 768px) {
        .product-detail-page {
            margin-top: 14px;
            margin-bottom: 14px;
            box-shadow: 0 18px 50px rgba(17, 24, 39, .14);
        }
    }

    @media (max-width: 767px) {
        .product-detail-page {
            max-width: none;
            margin: 0;
        }

        .pd-sticky-cart {
            right: 0;
            left: 0;
            width: 100%;
            max-width: 100%;
            transform: none;
        }

        .pd-topbar,
        .pd-nav-tabs {
            right: 0;
            left: 0;
            width: 100%;
            max-width: 100%;
            transform: none;
        }
    }

    @media (max-width: 380px) {
        .pd-topbar {
            grid-template-columns: 34px minmax(0, 1fr) 34px 34px 30px;
            gap: 5px;
            padding-inline: 8px;
        }

        .pd-icon-btn {
            width: 34px;
            height: 34px;
            font-size: 20px;
        }

        .pd-search-pill {
            height: 38px;
            padding-inline: 8px;
        }

        .pd-nav-tabs a {
            font-size: 13px;
        }

        .pd-sticky-cart {
            grid-template-columns: 50px minmax(0, 1fr);
            padding-inline: 8px;
        }

        .pd-add-cart-button {
            font-size: 14px;
        }
    }
</style>

<main class="product-detail-page">
    <div class="pd-topbar">
        <a class="pd-icon-btn" href="javascript:history.back()" aria-label="رجوع">
            <i class="bi bi-chevron-left"></i>
        </a>
        <a class="pd-search-pill" href="{{ route('shop.search', ['search' => $product->name]) }}" aria-label="بحث عن المنتج">
            <span>{{ $product->name }}</span>
            <i class="bi bi-search"></i>
        </a>
        <a class="pd-icon-btn pd-cart-shortcut" href="{{ route('cart.index') }}" aria-label="السلة">
            <i class="bi bi-cart3"></i>
            @if($cartCount > 0)
                <b class="pd-cart-count">{{ $cartCount }}</b>
            @endif
        </a>
        <button class="pd-icon-btn" type="button" data-share-product aria-label="مشاركة">
            <i class="bi bi-upload"></i>
        </button>
        <button class="pd-icon-btn" type="button" aria-label="المزيد">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
    </div>

    <nav class="pd-nav-tabs" aria-label="أقسام تفاصيل المنتج">
        <a href="#product-goods" class="is-active" data-product-tab>المنتج</a>
        <a href="#product-reviews" data-product-tab>التقييمات</a>
        <a href="#product-details" data-product-tab>التفاصيل</a>
        <a href="#product-recommend" data-product-tab>مقترحات</a>
    </nav>

    <section id="product-goods" class="pd-gallery">
        <div class="pd-main-carousel" data-main-carousel>
            <div class="pd-main-track" data-main-track aria-label="صور المنتج">
                @foreach($initialGallery as $index => $image)
                    <div class="pd-main-slide" data-main-slide data-image-src="{{ $image['src'] }}">
                        <img
                            src="{{ $image['src'] }}"
                            alt="{{ $image['label'] }}"
                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                            @if($loop->first) id="product-detail-main-image" @endif
                        >
                    </div>
                @endforeach
            </div>
        </div>
        <span class="pd-image-counter"><b data-current-image-index>1</b>/<b data-total-image-count>{{ $initialGallery->count() }}</b></span>
        <div class="pd-thumb-strip" data-base-gallery style="{{ $usesSelectedColorGallery ? 'display: none;' : '' }}">
            @foreach($baseGallery as $index => $image)
                <button
                    type="button"
                    class="pd-thumb {{ $loop->first ? 'is-active' : '' }}"
                    data-detail-thumb
                    data-image-src="{{ $image['src'] }}"
                    data-image-index="{{ $index + 1 }}"
                    aria-label="صورة {{ $index + 1 }}"
                >
                    <img src="{{ $image['src'] }}" alt="{{ $image['label'] }}" loading="lazy">
                </button>
            @endforeach
        </div>
    </section>

    <section class="pd-card">
        <div class="pd-price-row">
            <span class="pd-price"><small>ر.ي</small>{{ number_format($displayPrice, 0) }}</span>
            @if($discount)
                <span class="pd-discount">-{{ $discount }}%</span>
            @endif
            @if($hasSale)
                <span class="pd-old-price">ر.ي {{ number_format($regularPrice, 0) }}</span>
            @endif
        </div>

        <div class="pd-coupon-row" aria-label="عروض المنتج">
            <span>خصم خاص على منتجات الأطفال</span>
            <span>شحن مجاني للطلبات المؤهلة</span>
            <span>استبدال سهل خلال الفترة المحددة</span>
        </div>

        <div class="pd-title-row">
            <h1 class="pd-title">{{ $product->name }}</h1>
            <a class="pd-rating-pill" href="#product-reviews">
                <i class="bi bi-star-fill"></i>
                {{ number_format($averageRating ?: 4.8, 2) }}
                <span>({{ $reviewCount ?: '0' }})</span>
            </a>
        </div>
    </section>

    <section class="pd-section">
        <form class="pd-variant-form" id="product-add-to-cart-form" method="POST" action="{{ route('cart.add') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $product->id }}">
            <input type="hidden" name="name" value="{{ $product->name }}">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="price" value="{{ $displayPrice }}">

            @if($product->colors->isNotEmpty())
                <div>
                    <div class="pd-section-title">
                        <span>اللون: <b class="pd-selected-label" data-selected-color-name>{{ optional($product->colors->firstWhere('id', $selectedColorId))->name ?? $product->colors->first()->name }}</b></span>
                        <i class="bi bi-chevron-left"></i>
                    </div>
                    <div class="pd-color-options">
                        @foreach($product->colors as $color)
                            @php
                                $colorImages = $colorImagesByColor->get($color->id, collect());
                                $firstColorImage = optional($colorImages->first())->image_path;
                                $legacyImage = $color->pivot->image ?? null;
                                $hasColorPreview = (bool) ($firstColorImage || $legacyImage);
                                $colorPreview = $firstColorImage
                                    ? $assetForPath($firstColorImage)
                                    : ($legacyImage ? $assetForPath($legacyImage, 'storage/products/colors') : $fallbackColorPreview);
                                $isSelectedColor = $selectedColorId === (int) $color->id;
                            @endphp
                            <label class="pd-color-option">
                                <input
                                    type="radio"
                                    name="color_id"
                                    value="{{ $color->id }}"
                                    data-color-name="{{ $color->name }}"
                                    {!! $isSelectedColor ? 'checked' : '' !!}
                                    required
                                >
                                <span class="pd-color-box {{ $hasColorPreview ? '' : 'pd-color-box--fallback' }}" title="{{ $color->name }}">
                                    @if($colorPreview)
                                        <img src="{{ $colorPreview }}" alt="{{ $color->name }}" loading="lazy">
                                        @unless($hasColorPreview)
                                            <i class="pd-color-chip" style="background: {{ $color->hex_code ?: '#d1d5db' }}"></i>
                                        @endunless
                                    @else
                                        <i class="pd-color-swatch" style="background: {{ $color->hex_code ?: '#d1d5db' }}"></i>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @foreach($product->colors as $color)
                        @php
                            $colorGallery = $galleryForColor($color);
                            $hasColorGallery = $colorGallery->isNotEmpty();
                            $colorGalleryStyle = $selectedColorId === (int) $color->id ? '' : 'display: none;';
                        @endphp
                        @if($hasColorGallery)
                            <div class="pd-color-gallery-strip" data-color-gallery="{{ $color->id }}" style="{{ $colorGalleryStyle }}">
                                @foreach($colorGallery as $imageIndex => $image)
                                    <button
                                        type="button"
                                        class="pd-thumb {{ $selectedColorId === (int) $color->id && $loop->first ? 'is-active' : '' }}"
                                        data-detail-thumb
                                        data-image-src="{{ $image['src'] }}"
                                        data-image-index="{{ $imageIndex + 1 }}"
                                    >
                                        <img src="{{ $image['src'] }}" alt="{{ $color->name }}" loading="lazy">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if($product->sizes->isNotEmpty())
                <div>
                    <div class="pd-section-title">
                        <span>المقاس: <b class="pd-selected-label" data-selected-size-name>{{ optional($product->sizes->firstWhere('id', $selectedSizeId))->name ?? $product->sizes->first()->name }}</b></span>
                        <i class="bi bi-chevron-left"></i>
                    </div>
                    <div class="pd-size-options">
                        @foreach($product->sizes as $size)
                            @php
                                $sizeNote = trim((string) $size->description);
                                $adjustment = (float) ($size->pivot->price_adjustment ?? 0);
                                $quantity = (int) ($size->pivot->quantity ?? 0);
                                $baseSizeDetail = $sizeNote !== '' ? $sizeNote : 'تفاصيل المقاس '.$size->name.' مناسبة لهذا المنتج.';
                                $stockText = $quantity > 0 ? ' الكمية المتاحة: '.$quantity.'.' : '';
                                $adjustmentText = $adjustment > 0 ? ' زيادة السعر: ر.ي '.number_format($adjustment, 0).'.' : '';
                                $sizeDetail = trim($baseSizeDetail.$stockText.$adjustmentText);
                                $isSelectedSize = $selectedSizeId === (int) $size->id;
                            @endphp
                            <label class="pd-size-option">
                                <input
                                    type="radio"
                                    name="size_id"
                                    value="{{ $size->id }}"
                                    data-size-name="{{ $size->name }}"
                                    data-size-detail="{{ $sizeDetail }}"
                                    {!! $isSelectedSize ? 'checked' : '' !!}
                                    required
                                >
                                <span class="pd-size-box">{{ $size->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @php
                        $selectedSizeDescription = optional($product->sizes->firstWhere('id', $selectedSizeId))->description ?: 'اختاري المقاس المناسب لطفلك من الخيارات المتاحة.';
                    @endphp
                    <div class="pd-size-detail" data-size-detail-box>
                        <span>{{ $selectedSizeDescription }}</span>
                        <i class="bi bi-chevron-left"></i>
                    </div>
                    <div class="pd-link-row">
                        <span><i class="bi bi-rulers"></i> دليل المقاسات</span>
                        <span><i class="bi bi-pencil-square"></i> ساعدني في اختيار المقاس</span>
                        <span class="pd-muted">94% وجدوه مطابقًا للمقاس</span>
                    </div>
                </div>
            @endif

            <div>
                <div class="pd-section-title">
                    <span>خيارات إضافية</span>
                </div>
                <div class="pd-option-tags">
                    @if($product->brand)
                        <a class="pd-option-tag" href="{{ route('shop.brand', $product->brand->slug) }}">{{ $product->brand->name }}</a>
                    @endif
                    @if($product->category)
                        <a class="pd-option-tag" href="{{ route('shop.category', $product->category->slug) }}">{{ $product->category->name }}</a>
                    @endif
                    @if($product->is_offer)
                        <span class="pd-option-tag">عرض خاص</span>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <section class="pd-section">
        <div class="pd-section-title">
            <span>الشحن إلى</span>
            <i class="bi bi-geo-alt-fill"></i>
        </div>
        <div class="pd-shipping-row">
            <div class="pd-shipping-line">
                <span>
                    @if($defaultAddress)
                        {{ $defaultAddress->city ?: $defaultAddress->state ?: 'عنوانك المحدد' }}
                    @else
                        العنوان غير محدد
                    @endif
                </span>
                <i class="bi bi-chevron-left"></i>
            </div>
            <div class="pd-shipping-line">
                <strong><i class="bi bi-truck"></i> شحن مجاني للطلبات المؤهلة</strong>
                <span class="pd-muted">تقدير الوصول خلال 5 إلى 10 أيام</span>
            </div>
            <p class="pd-muted mb-0">
                @if($defaultAddress)
                    سيتم إرسال الطلب إلى: {{ $defaultAddress->full_address }}
                @else
                    سجّل الدخول أو أضف عنوانًا من لوحة حسابك ليتم استخدامه في الطلب.
                @endif
            </p>
        </div>
    </section>

    <section id="product-reviews" class="pd-section">
        <div class="pd-section-title">
            <span>التقييمات</span>
            <a href="#product-reviews" class="pd-muted">عرض المزيد <i class="bi bi-chevron-left"></i></a>
        </div>
        <div class="pd-review-summary">
            <div class="pd-review-score">
                <div>
                    <strong>{{ number_format($averageRating ?: 4.8, 2) }}</strong>
                    <span class="pd-stars">{{ str_repeat('★', max(1, min(5, (int) round($averageRating ?: 5)))) }}</span>
                    <span class="pd-muted">({{ $reviewCount }} تقييم)</span>
                </div>
            </div>
            <div class="pd-fit-bars">
                <div class="pd-fit-bar"><span>صغير 3%</span><i><b style="width: 3%"></b></i></div>
                <div class="pd-fit-bar"><span>مطابق 94%</span><i><b style="width: 94%"></b></i></div>
                <div class="pd-fit-bar"><span>كبير 3%</span><i><b style="width: 3%"></b></i></div>
            </div>
            <div class="pd-review-tags">
                <span>خامة جيدة</span>
                <span>سأشتريه مرة أخرى</span>
                <span>مناسب للأطفال</span>
            </div>
        </div>

        @forelse($product->reviews->take(4) as $review)
            <article class="pd-review-item">
                <div class="pd-review-head">
                    <span>{{ \Illuminate\Support\Str::mask($review->user?->name ?? 'عميل', '*', 1, 3) }}</span>
                    <span class="pd-stars">{{ str_repeat('★', (int) $review->rating) }}</span>
                </div>
                <p class="pd-review-text">{{ $review->comment }}</p>
                <div class="pd-muted">{{ $review->created_at?->format('Y/m/d') }}</div>
            </article>
        @empty
            <div class="pd-empty-state">لا توجد تقييمات لهذا المنتج بعد.</div>
        @endforelse

        @auth
            <form class="pd-review-form" method="POST" action="{{ route('review.store', $product->id) }}">
                @csrf
                <select name="rating" required>
                    <option value="">اختاري التقييم</option>
                    <option value="5">5 نجوم</option>
                    <option value="4">4 نجوم</option>
                    <option value="3">3 نجوم</option>
                    <option value="2">نجمتان</option>
                    <option value="1">نجمة واحدة</option>
                </select>
                <input type="text" name="title" placeholder="عنوان مختصر للتقييم">
                <textarea name="comment" rows="3" placeholder="اكتبي رأيك في المنتج" required></textarea>
                <button type="submit">إرسال التقييم</button>
            </form>
        @else
            <div class="pd-empty-state">
                <a href="{{ route('login') }}">سجلي الدخول لإضافة تقييمك.</a>
            </div>
        @endauth
    </section>

    <section id="product-details" class="pd-section">
        <div class="pd-section-title">
            <span>تفاصيل المنتج</span>
            <i class="bi bi-chevron-left"></i>
        </div>
        <div class="pd-detail-feature">
            <strong>مميزات المنتج:</strong>
            <div>{!! nl2br(e($product->short_description ?: $product->description)) !!}</div>
        </div>
        <div class="pd-detail-list">
            @if($product->SKU)
                <div><span>رمز المنتج</span><b>{{ $product->SKU }}</b></div>
            @endif
            @if($product->brand)
                <div><span>العلامة</span><b>{{ $product->brand->name }}</b></div>
            @endif
            @if($product->category)
                <div><span>الفئة</span><b>{{ $product->category->name }}</b></div>
            @endif
            @if($product->dimensions)
                <div><span>الأبعاد</span><b>{{ $product->dimensions }}</b></div>
            @endif
            @if($product->weight)
                <div><span>الوزن</span><b>{{ $product->weight }}</b></div>
            @endif
            <div><span>التوفر</span><b>{{ $product->stock_status === 'instock' ? 'متوفر' : 'غير متوفر' }}</b></div>
        </div>
    </section>

    <section id="product-recommend" class="pd-section">
        <div class="pd-section-title">
            <span>أشياء قد تعجبك</span>
        </div>
        <div class="pd-recommend-tags">
            <span class="pd-recommend-tag" style="background:#111;color:#fff;">مقترح</span>
            @if($product->category)
                <a class="pd-recommend-tag" href="{{ route('shop.category', $product->category->slug) }}">{{ $product->category->name }}</a>
            @endif
            @if($product->brand)
                <a class="pd-recommend-tag" href="{{ route('shop.brand', $product->brand->slug) }}">{{ $product->brand->name }}</a>
            @endif
        </div>
        <div class="pd-recommend-grid mt-3">
            @forelse($rproducts as $related)
                @php
                    $relatedPrice = (float) (($related->sale_price > 0 && $related->sale_price < $related->regular_price) ? $related->sale_price : $related->regular_price);
                    $relatedDiscount = ($related->sale_price > 0 && $related->sale_price < $related->regular_price && $related->regular_price > 0)
                        ? (int) round((($related->regular_price - $related->sale_price) / $related->regular_price) * 100)
                        : null;
                @endphp
                <article class="pd-product-card">
                    <a class="pd-product-card__image" href="{{ route('shop.product.details', $related->slug) }}">
                        <img src="{{ $assetForPath($related->image) }}" alt="{{ $related->name }}" loading="lazy">
                    </a>
                    <div class="pd-product-card__body">
                        <a class="pd-product-card__title" href="{{ route('shop.product.details', $related->slug) }}">
                            {{ $related->card_title ?? $related->name }}
                        </a>
                        <div class="pd-product-card__meta">
                            {{ $related->featured ? '# اختيار شائع' : 'منتج مناسب' }}
                        </div>
                        <div class="pd-product-card__price">
                            <span><small>ر.ي</small>{{ number_format($relatedPrice, 0) }}</span>
                            <a class="pd-product-card__cart" href="{{ route('shop.product.details', $related->slug) }}" aria-label="عرض المنتج">
                                <i class="bi bi-cart-plus"></i>
                            </a>
                        </div>
                        @if($relatedDiscount)
                            <div class="pd-muted">-{{ $relatedDiscount }}%</div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="pd-empty-state">لا توجد مقترحات حاليًا.</div>
            @endforelse
        </div>
    </section>
</main>

<div class="pd-sticky-cart">
    @if($wishlistItem)
        <form method="POST" action="{{ route('wishlist.item.remove', ['rowId' => $wishlistItem->rowId]) }}">
            @csrf
            @method('DELETE')
            <button class="pd-wishlist-button is-active" type="submit" aria-label="إزالة من المفضلة">
                <i class="bi bi-heart-fill"></i>
            </button>
        </form>
    @else
        <form method="POST" action="{{ route('wishlist.add') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">
            <button class="pd-wishlist-button" type="submit" aria-label="إضافة إلى المفضلة">
                <i class="bi bi-heart"></i>
            </button>
        </form>
    @endif

    <button class="pd-add-cart-button" form="product-add-to-cart-form" type="submit">
        {{ $discount ? 'خصم '.$discount.'% - ' : '' }}إضافة للسلة
    </button>
</div>
@endsection

@push('scripts')
<script>
    document.body.classList.add('product-detail-view');

    document.addEventListener('DOMContentLoaded', function () {
        const mainCarousel = document.querySelector('[data-main-carousel]');
        const mainTrack = document.querySelector('[data-main-track]');
        const imageCounter = document.querySelector('[data-current-image-index]');
        const totalCounter = document.querySelector('[data-total-image-count]');
        const selectedColorName = document.querySelector('[data-selected-color-name]');
        const selectedSizeName = document.querySelector('[data-selected-size-name]');
        const sizeDetailBox = document.querySelector('[data-size-detail-box] span');
        let activeGallery = document.querySelector('[data-color-gallery]:not([style*="display: none"])')
            || document.querySelector('[data-base-gallery]');
        let scrollSyncTimer = null;

        function visibleThumbs() {
            if (activeGallery) {
                return Array.from(activeGallery.querySelectorAll('[data-detail-thumb]'));
            }

            return [];
        }

        function setCounters(index, total) {
            if (imageCounter) {
                imageCounter.textContent = String(index + 1);
            }

            if (totalCounter) {
                totalCounter.textContent = String(total || 1);
            }
        }

        function markActiveThumb(index) {
            document.querySelectorAll('[data-detail-thumb]').forEach(function (item) {
                item.classList.remove('is-active');
            });

            const thumbs = visibleThumbs();
            if (thumbs[index]) {
                thumbs[index].classList.add('is-active');
                thumbs[index].scrollIntoView({
                    block: 'nearest',
                    inline: 'nearest',
                    behavior: 'smooth'
                });
            }
        }

        function setActiveSlide(index, shouldScroll = true) {
            if (!mainTrack) {
                return;
            }

            const slides = Array.from(mainTrack.querySelectorAll('[data-main-slide]'));
            if (slides.length === 0) {
                return;
            }

            const safeIndex = Math.max(0, Math.min(index, slides.length - 1));
            setCounters(safeIndex, slides.length);
            markActiveThumb(safeIndex);

            if (shouldScroll) {
                mainTrack.scrollTo({
                    left: safeIndex * mainTrack.clientWidth,
                    behavior: 'smooth'
                });
            }
        }

        function rebuildMainSlidesFrom(gallery) {
            if (!mainTrack || !gallery) {
                return;
            }

            const thumbs = Array.from(gallery.querySelectorAll('[data-detail-thumb]'));
            if (thumbs.length === 0) {
                return;
            }

            mainTrack.innerHTML = '';

            thumbs.forEach(function (thumb, index) {
                const slide = document.createElement('div');
                const image = document.createElement('img');

                slide.className = 'pd-main-slide';
                slide.dataset.mainSlide = '';
                slide.dataset.imageSrc = thumb.dataset.imageSrc || '';

                image.src = thumb.dataset.imageSrc || '';
                image.alt = thumb.querySelector('img')?.alt || @json($product->name);
                image.loading = index === 0 ? 'eager' : 'lazy';

                if (index === 0) {
                    image.id = 'product-detail-main-image';
                }

                slide.appendChild(image);
                mainTrack.appendChild(slide);
            });

            mainTrack.scrollLeft = 0;
            setActiveSlide(0, false);
        }

        function bindThumbs(scope = document) {
            scope.querySelectorAll('[data-detail-thumb]').forEach(function (thumb) {
                if (thumb.dataset.thumbBound === '1') {
                    return;
                }

                thumb.dataset.thumbBound = '1';
                thumb.addEventListener('click', function () {
                    const gallery = thumb.closest('[data-color-gallery], [data-base-gallery]');
                    activeGallery = gallery || activeGallery;

                    if (gallery) {
                        rebuildMainSlidesFrom(gallery);
                    }

                    const thumbs = visibleThumbs();
                    const index = Math.max(0, thumbs.indexOf(thumb));
                    setActiveSlide(index);
                });
            });
        }

        bindThumbs();
        if (mainTrack) {
            mainTrack.scrollLeft = 0;
            setActiveSlide(0, false);
        }

        if (mainTrack) {
            mainTrack.addEventListener('scroll', function () {
                window.clearTimeout(scrollSyncTimer);
                scrollSyncTimer = window.setTimeout(function () {
                    const index = Math.round(mainTrack.scrollLeft / Math.max(1, mainTrack.clientWidth));
                    setActiveSlide(index, false);
                }, 80);
            }, { passive: true });
        }

        if (mainCarousel && mainTrack) {
            let isDragging = false;
            let startX = 0;
            let startScrollLeft = 0;

            mainCarousel.addEventListener('pointerdown', function (event) {
                if (event.pointerType !== 'mouse' || mainTrack.querySelectorAll('[data-main-slide]').length < 2) {
                    return;
                }

                isDragging = true;
                startX = event.clientX;
                startScrollLeft = mainTrack.scrollLeft;
                mainCarousel.classList.add('is-dragging');
                mainCarousel.setPointerCapture(event.pointerId);
            });

            mainCarousel.addEventListener('pointermove', function (event) {
                if (!isDragging) {
                    return;
                }

                event.preventDefault();
                mainTrack.scrollLeft = startScrollLeft - (event.clientX - startX);
            });

            function stopDragging() {
                if (!isDragging) {
                    return;
                }

                isDragging = false;
                mainCarousel.classList.remove('is-dragging');
                const index = Math.round(mainTrack.scrollLeft / Math.max(1, mainTrack.clientWidth));
                setActiveSlide(index);
            }

            mainCarousel.addEventListener('pointerup', stopDragging);
            mainCarousel.addEventListener('pointercancel', stopDragging);
            mainCarousel.addEventListener('mouseleave', stopDragging);
        }

        document.querySelectorAll('input[name="color_id"]').forEach(function (input) {
            input.addEventListener('change', function () {
                if (selectedColorName) {
                    selectedColorName.textContent = input.dataset.colorName || '';
                }

                const baseGallery = document.querySelector('[data-base-gallery]');
                let nextGallery = null;

                document.querySelectorAll('[data-color-gallery]').forEach(function (gallery) {
                    const isActive = gallery.dataset.colorGallery === input.value;
                    gallery.style.display = isActive ? '' : 'none';

                    if (isActive) {
                        nextGallery = gallery;
                    }
                });

                if (baseGallery) {
                    baseGallery.style.display = nextGallery ? 'none' : '';
                }

                activeGallery = nextGallery || baseGallery;
                rebuildMainSlidesFrom(activeGallery);
            });
        });

        document.querySelectorAll('input[name="size_id"]').forEach(function (input) {
            input.addEventListener('change', function () {
                if (selectedSizeName) {
                    selectedSizeName.textContent = input.dataset.sizeName || '';
                }

                if (sizeDetailBox) {
                    sizeDetailBox.textContent = input.dataset.sizeDetail || '';
                }
            });

            if (input.checked && sizeDetailBox && input.dataset.sizeDetail) {
                sizeDetailBox.textContent = input.dataset.sizeDetail;
            }
        });

        document.querySelectorAll('[data-product-tab]').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('[data-product-tab]').forEach(function (item) {
                    item.classList.remove('is-active');
                });
                tab.classList.add('is-active');
            });
        });

        const shareButton = document.querySelector('[data-share-product]');
        if (shareButton) {
            shareButton.addEventListener('click', function () {
                const shareData = {
                    title: @json($product->name),
                    url: window.location.href
                };

                if (navigator.share) {
                    navigator.share(shareData);
                    return;
                }

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(window.location.href);
                }
            });
        }
    });
</script>
@endpush
