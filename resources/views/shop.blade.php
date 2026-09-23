@extends('layouts.app')
@section('content')
<style>
  .brand-list li,
  .category-list li {
    line-height: 40px;
  }

  .brand-list li .chk-brand,
  .category-list li .chk-category {
    width: 1rem;
    height: 1rem;
    color: #e4e4e4;
    border: 0.125rem solid currentColor;
    border-radius: 0;
    margin-right: 0.75rem;
  }

  .filled-heart {
    color: orange;
  }

  /* ضبط الهوامش الاحترافي */
  .shop-main {
    gap: 2rem;
    padding-top: 2rem;
    padding-bottom: 2rem;
  }

  .shop-sidebar {
    padding-left: 1rem;
    margin-left: 0;
  }

  .shop-list {
    padding-right: 1rem;
    margin-right: 0;
  }

  @media (max-width: 991.98px) {
    .shop-main {
      flex-direction: column;
      gap: 1.5rem;
    }

    .shop-sidebar {
      position: fixed;
      top: 0;
      right: -320px;
      width: 320px;
      max-width: 100%;
      height: 100vh;
      background: #fff;
      z-index: 1050;
      overflow-y: auto;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      padding: 1.5rem !important;
      margin: 0 !important;
      box-shadow: -5px 0 25px rgba(0,0,0,0.1);
      visibility: hidden;
      opacity: 0;
    }
    
    .shop-sidebar.is-open {
      right: 0;
      visibility: visible;
      opacity: 1;
    }

    .shop-list {
      padding-right: 0;
    }
  }

  .filter-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  
  .filter-overlay.is-active {
    opacity: 1;
  }

  @media (min-width: 992px) {
    .shop-main {
      padding-top: 2.5rem;
      padding-bottom: 2.5rem;
    }
  }

  @media (min-width: 1200px) {
    .shop-main {
      gap: 2.5rem;
    }
  }

  /* تحسين هوامش العناصر الداخلية */
  .accordion-item {
    margin-bottom: 1.5rem !important;
    padding-bottom: 1rem !important;
  }

  .accordion-body {
    padding-top: 1rem !important;
    padding-bottom: 0.5rem !important;
  }

  .product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  .wgp-pagination {
    margin-top: 2rem;
    margin-bottom: 1rem;
  }

  .divider {
    margin: 2rem 0;
    height: 1px;
    background-color: #e4e4e4;
    border: none;
  }

  /* تحسين المسافات للشاشات الصغيرة */
  @media (max-width: 575.98px) {
    .products-grid {
      margin: 0 -0.5rem;
    }

    .product-card-wrapper {
      padding: 0 0.5rem;
    }
  }

  /* ضبط الهوامش للمناطق المحددة */
  .shop-sidebar {
    padding-right: 1.5rem !important;
    margin-right: 1rem;
  }

  @media (min-width: 992px) {
    .shop-sidebar {
      padding-right: 2rem !important;
      margin-right: 1.5rem;
    }
  }

  /* تحريك زر المفضلة إلى اليسار */
  .pc__btn-wl {
    top: 0.75rem !important;
    left: 0.75rem !important;
    right: auto !important;
    z-index: 10;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(255, 255, 255, 0.9) !important;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
  }

  .pc__btn-wl:hover {
    background-color: rgba(255, 255, 255, 1) !important;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  /* ضبط موضع النص والعناصر */
  .pc__info {
    padding-left: 3rem;
    padding-right: 0.5rem;
  }

  .pc__title {
    margin-right: 0;
    padding-right: 0;
  }

  /* تحسين المسافات في الشريط الجانبي */
  .shop-sidebar .accordion-item {
    margin-left: 0.5rem;
    padding-left: 0.5rem;
  }

  .category-list,
  .brand-list {
    padding-right: 1rem;
  }

  /* ضبط عرض النص في المنتجات */
  .pc__category,
  .pc__title a {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: calc(100% - 1rem);
  }

  /* ضبط الهوامش في منطقة عرض النتائج */
  .shop-main .container {
    padding-left: 2rem;
    padding-right: 2rem;
  }

  @media (max-width: 991.98px) {
    .shop-main .container {
      padding-left: 1rem;
      padding-right: 1rem;
    }
  }

  /* تحسين المسافات في الشريط الجانبي */
  .shop-sidebar {
    margin-left: 1rem;
  }

  /* ضبط المسافات في منطقة المنتجات */
  .shop-list {
    margin-right: 1rem;
  }

  @media (max-width: 991.98px) {
    .shop-list {
      margin-right: 0;
    }
  }

  /* تحسين عرض النتائج */
  .breadcrumb {
    margin-bottom: 1rem;
    padding-left: 0.5rem;
  }

  .shop-acs {
    margin-bottom: 1.5rem;
    padding-right: 0.5rem;
  }

  /* تحسين المسافات النهائية */
  @media (min-width: 992px) {
    .shop-main {
      max-width: 1400px;
      margin: 0 auto;
    }

    .shop-sidebar {
      flex: 0 0 280px;
      max-width: 280px;
    }

    .shop-list {
      flex: 1;
      min-width: 0;
    }
  }

  /* ضبط المسافات للشاشات الكبيرة */
  @media (min-width: 1200px) {
    .shop-sidebar {
      flex: 0 0 320px;
      max-width: 320px;
    }
  }

  /* تحسين عرض المنتجات */
  .product-card-wrapper {
    margin-bottom: 1.5rem;
  }

  .pc__info {
    position: relative;
    z-index: 1;
  }

  /* تحسين أزرار العرض */
  .view-options {
    background: rgba(248, 249, 250, 0.8);
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
  }

  .view-options span {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6c757d;
    margin-left: 8px;
  }

  .view-buttons {
    gap: 6px;
  }

  .view-buttons .btn {
    min-width: 40px;
    height: 32px;
    font-weight: 600;
    font-size: 0.875rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .view-buttons .btn:hover {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
  }

  .view-buttons .btn.active,
  .view-buttons .btn:active {
    background-color: #0056b3;
    border-color: #0056b3;
    color: white;
    box-shadow: 0 2px 4px rgba(0, 86, 179, 0.4);
  }

  /* تحسين المسافات */
  .col-size {
    margin-right: 1rem;
  }

  @media (max-width: 991.98px) {
    .view-options {
      padding: 6px 10px;
    }

    .view-buttons .btn {
      min-width: 35px;
      height: 28px;
      font-size: 0.8rem;
    }
  }

  /* تحسين مظهر الفلاتر */
  .filter-item {
    transition: all 0.3s ease;
  }

  .filter-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
    border-radius: 6px;
    padding: 4px 8px;
    margin: -4px -8px;
  }

  .form-check-input {
    width: 18px;
    height: 18px;
    border: 2px solid #dee2e6;
    border-radius: 4px;
    transition: all 0.3s ease;
  }

  .form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
  }

  .form-check-label {
    font-weight: 500;
    color: #495057;
    cursor: pointer;
    transition: color 0.3s ease;
  }

  .form-check-input:checked+.form-check-label {
    color: #007bff;
    font-weight: 600;
  }

  .list-item {
    transition: all 0.3s ease;
    border-radius: 8px;
    margin-bottom: 4px;
    padding: 8px 12px;
  }

  .list-item:hover {
    background-color: rgba(248, 249, 250, 0.8);
    transform: translateX(4px);
  }

  .badge {
    font-size: 0.75rem;
    font-weight: 600;
    min-width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .border-bottom {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1) !important;
  }

  .category-list ul,
  .brand-list {
    padding: 0;
    margin: 0;
  }

  .accordion-body {
    padding-top: 1rem !important;
  }

  /* تحسين الألوان */
  .bg-secondary {
    background-color: #6c757d !important;
  }

  .list-item:last-child {
    border-bottom: none !important;
  }

  /* حالة الفلتر المحدد */
  .list-item.selected {
    background-color: rgba(0, 123, 255, 0.1);
    border-left: 3px solid #007bff;
    transform: translateX(2px);
  }

  .list-item.selected .form-check-label {
    color: #007bff;
    font-weight: 600;
  }

  .bg-primary {
    background-color: #007bff !important;
  }

  /* تحسين المسافات */
  .accordion-item {
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  .accordion-button {
    background-color: rgba(248, 249, 250, 0.8);
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    font-weight: 600;
    color: #495057;
  }

  .accordion-button:not(.collapsed) {
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
    border-bottom-color: #007bff;
  }

  /* تحسين الأيقونات */
  .accordion-button__icon {
    width: 12px;
    height: 12px;
    fill: currentColor;
    transition: transform 0.3s ease;
  }

  .accordion-button:not(.collapsed) .accordion-button__icon {
    transform: rotate(180deg);
  }

  /* تحسين فلتر السعر */
  .price-filter-container {
    background: rgba(248, 249, 250, 0.5);
    border-radius: 12px;
    margin: 0.5rem 0;
  }

  .price-slider-wrapper {
    position: relative;
    padding: 1rem 0;
  }



  .selected-range {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: 2px solid #004085;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    position: relative;
    overflow: hidden;
  }

  .selected-range::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    animation: shimmer 2s infinite;
  }

  @keyframes shimmer {
    0% {
      transform: translateX(-100%);
    }

    100% {
      transform: translateX(100%);
    }
  }

  .range-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    padding-bottom: 8px;
  }

  .range-header small {
    color: #ffffff;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .range-values {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 8px;
  }

  .price-badge {
    background: rgba(255, 255, 255, 0.9);
    color: #007bff;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.5);
  }

  .range-separator {
    color: #ffffff;
    font-weight: 600;
    font-size: 0.85rem;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
  }

  /* تحسين شريط السعر */
  .price-range-slider {
    width: 100%;
    margin: 1rem 0;
  }

  /* تحسين الألوان */
  .bg-light {
    background-color: rgba(248, 249, 250, 0.8) !important;
  }

  .text-primary {
    color: #007bff !important;
  }

  .bg-primary {
    background-color: #007bff !important;
  }

  /* تأثيرات متحركة */
  @keyframes priceUpdate {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.05);
    }

    100% {
      transform: scale(1);
    }
  }

  .price-value.updating {
    animation: priceUpdate 0.3s ease;
  }

  /* تحسين المسافات */
  .price-range__info .row {
    margin: 0;
  }

  .price-range__info .col-6 {
    padding: 0 0.5rem;
  }

  /* تأثيرات إضافية للنطاق المحدد */
  .selected-range.updating {
    animation: priceUpdate 0.3s ease;
    transform: scale(1.02);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
  }

  .selected-range:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
  }

  .price-badge:hover {
    transform: scale(1.05);
    background: #ffffff;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
  }

  .price-badge.updating {
    animation: badgeUpdate 0.3s ease;
  }

  .price-badge.highlight {
    background: #ffc107;
    color: #000;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
  }

  @keyframes badgeUpdate {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.1);
      background: #ffc107;
    }

    100% {
      transform: scale(1);
    }
  }

  /* تحسين شريط التمرير */
  .slider-track {
    background: linear-gradient(90deg, #e9ecef, #007bff, #e9ecef);
    height: 6px;
    border-radius: 3px;
  }

  .slider-handle {
    background: #007bff;
    border: 3px solid white;
    box-shadow: 0 2px 6px rgba(0, 123, 255, 0.3);
    width: 20px;
    height: 20px;
    border-radius: 50%;
    transition: all 0.3s ease;
  }

  .slider-handle:hover {
    transform: scale(1.2);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
  }

  /* تحسين النصوص */
  .price-filter-container small {
    font-weight: 600;
    color: #6c757d;
  }

  .price-filter-container .fw-bold {
    font-size: 1rem;
    letter-spacing: 0.5px;
  }

  /* تأثيرات الحدود */
  .price-info-card {
    position: relative;
    overflow: hidden;
  }

  .price-info-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: #007bff;
    transition: all 0.3s ease;
    transform: translateX(-50%);
  }

  .price-info-card:hover::after {
    width: 80%;
  }
</style>
<main class="pt-90">
  <section class="shop-main container-fluid px-3 px-md-4 px-lg-5 d-flex pt-4 pt-xl-5">
    <div class="shop-sidebar side-sticky bg-body pe-3 pe-lg-4" id="shopFilter">
      <div class="aside-header d-flex d-lg-none align-items-center mb-4 p-3 border-bottom">
        <h3 class="text-uppercase fs-6 mb-0 fw-bold">تصفية المنتجات</h3>
        <button class="btn-close-lg btn-close-aside ms-auto" id="closeFilterBtn"></button>
      </div>

      <div class="pt-3 pt-lg-0"></div>

      <div class="accordion" id="categories-list">
        <div class="accordion-item mb-4 pb-3">
          <h5 class="accordion-header" id="accordion-heading-1">
            <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button" data-bs-toggle="collapse"
              data-bs-target="#accordion-filter-1" aria-expanded="true" aria-controls="accordion-filter-1">
              فئات المنتجات
              <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                  <path
                    d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z" />
                </g>
              </svg>
            </button>
          </h5>
          <div id="accordion-filter-1" class="accordion-collapse collapse show border-0"
            aria-labelledby="accordion-heading-1" data-bs-parent="#categories-list">
            <div class="accordion-body px-0 pb-0 pt-3 category-list">
              <ul class="list list-inline mb-0">
                @foreach($categories as $category)
                <li class="list-item d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                  <div class="filter-item d-flex align-items-center flex-grow-1">
                    <input type="checkbox" class="chk-category form-check-input me-3" name="categories" value="{{$category->id}}"
                      @if(in_array($category->id, explode(',', $f_categories))) checked="checked" @endif/>
                    <label class="form-check-label flex-grow-1 cursor-pointer">{{ $category->name }}</label>
                  </div>
                  <span class="badge bg-secondary rounded-pill ms-2">{{ $category->products_count }}</span>
                </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>


      @if(isset($colors) && $colors->count() > 0)
      <div class="accordion" id="color-filters">
        <div class="accordion-item mb-4 pb-3">
          <h5 class="accordion-header" id="accordion-heading-color">
            <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button" data-bs-toggle="collapse"
              data-bs-target="#accordion-filter-color" aria-expanded="true" aria-controls="accordion-filter-color">
              الألوان
              <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                  <path
                    d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z" />
                </g>
              </svg>
            </button>
          </h5>
          <div id="accordion-filter-color" class="accordion-collapse collapse show border-0"
            aria-labelledby="accordion-heading-color" data-bs-parent="#color-filters">
            <div class="accordion-body px-0 pb-0">
              <div class="d-flex flex-wrap">
                @foreach($colors as $color)
                <label class="swatch-color-wrapper me-2 mb-2" title="{{ $color->name }}">
                  <input type="checkbox" name="colors" value="{{ $color->id }}" class="chk-color d-none"
                    @if(request('colors') && in_array($color->id, explode(',', request('colors')))) checked @endif>
                  <span class="swatch-color js-filter" style="background-color: {{ $color->hex_code ?? '#ccc' }}"></span>
                </label>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif


      @if(isset($sizes) && $sizes->count() > 0)
      <div class="accordion" id="size-filters">
        <div class="accordion-item mb-4 pb-3">
          <h5 class="accordion-header" id="accordion-heading-size">
            <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button" data-bs-toggle="collapse"
              data-bs-target="#accordion-filter-size" aria-expanded="true" aria-controls="accordion-filter-size">
              المقاسات
              <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                  <path
                    d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z" />
                </g>
              </svg>
            </button>
          </h5>
          <div id="accordion-filter-size" class="accordion-collapse collapse show border-0"
            aria-labelledby="accordion-heading-size" data-bs-parent="#size-filters">
            <div class="accordion-body px-0 pb-0">
              <div class="d-flex flex-wrap">
                @foreach($sizes as $size)
                <label class="swatch-size-wrapper">
                  <input type="checkbox" name="sizes" value="{{ $size->id }}" class="chk-size d-none"
                    @if(request('sizes') && in_array($size->id, explode(',', request('sizes')))) checked @endif>
                  <span class="swatch-size btn btn-sm btn-outline-light mb-3 me-3 js-filter">{{ $size->name }}</span>
                </label>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif


      <div class="accordion" id="brand-filters">
        <div class="accordion-item mb-4 pb-3">
          <h5 class="accordion-header" id="accordion-heading-brand">
            <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button" data-bs-toggle="collapse"
              data-bs-target="#accordion-filter-brand" aria-expanded="true" aria-controls="accordion-filter-brand">
              العلامات التجارية
              <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                  <path
                    d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z" />
                </g>
              </svg>
            </button>
          </h5>
          <div id="accordion-filter-brand" class="accordion-collapse collapse show border-0"
            aria-labelledby="accordion-heading-brand" data-bs-parent="#brand-filters">
            <div class="search-field multi-select accordion-body px-0 pb-0">
              <ul class="list list-inline mb-0 brand-list">
                @foreach($brands as $brand)
                <li class="list-item d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                  <div class="filter-item d-flex align-items-center flex-grow-1">
                    <input type="checkbox" name="brands" value="{{ $brand->id }}" class="chk-brand form-check-input me-3"
                      @if(in_array($brand->id, explode(',',$f_brands))) checked="checked" @endif>
                    <label class="form-check-label flex-grow-1 cursor-pointer">{{ $brand->name }}</label>
                  </div>
                  <span class="badge bg-secondary rounded-pill ms-2">{{ $brand->products_count }}</span>
                </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>


      <div class="accordion" id="price-filters">
        <div class="accordion-item mb-4">
          <h5 class="accordion-header mb-2" id="accordion-heading-price">
            <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button" data-bs-toggle="collapse"
              data-bs-target="#accordion-filter-price" aria-expanded="true" aria-controls="accordion-filter-price">
              السعر
              <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                  <path
                    d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z" />
                </g>
              </svg>
            </button>
          </h5>
          <div id="accordion-filter-price" class="accordion-collapse collapse show border-0"
            aria-labelledby="accordion-heading-price" data-bs-parent="#price-filters">
            <div class="price-filter-container p-3">
              <!-- شريط السعر -->
              <div class="price-slider-wrapper mb-4">
                <input class="price-range-slider" type="text" name="price_range" value="" data-slider-min="1"
                  data-slider-max="500000" data-slider-step="1000" data-slider-value="[{{ $min_price}},{{$max_price }}]" data-currency="ريال جديد" />
              </div>

              <!-- معلومات السعر -->
              <div class="price-range__info d-flex justify-content-between mt-2">
                <div>
                  <span class="text-secondary">أقل سعر: </span>
                  <span class="price-range__min">1 ريال جديد</span>
                </div>
                <div>
                  <span class="text-secondary">أعلى سعر: </span>
                  <span class="price-range__max">500,000 ريال جديد</span>
                </div>
              </div>


            </div>
          </div>
        </div>
      </div>
    </div>
    </div>

    <div class="shop-list flex-grow-1 ps-3 ps-lg-4">


      <!-- شريط البحث المتقدم -->
      @if(request('search'))
      <div class="search-info mb-4 p-3 bg-light rounded shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="mb-1">نتائج البحث عن: <strong>"{{ request('search') }}"</strong></h6>
            <small class="text-muted">{{ $products->total() }} منتج</small>
          </div>
          <a href="{{ route('shop.index') }}" class="btn btn-sm btn-outline-secondary">مسح البحث</a>
        </div>
      </div>
      @endif

      <div class="d-flex justify-content-between mb-4 pb-md-2">
        <div class="breadcrumb mb-0 d-none d-md-block flex-grow-1">
          <a href="{{route('home.index')}}" class="menu-link menu-link_us-s text-uppercase fw-medium">الرئيسية</a>
          <span class="breadcrumb-separator menu-link fw-medium ps-1 pe-1">/</span>
          <a href="#" class="menu-link menu-link_us-s text-uppercase fw-medium">المتجر</a>
        </div>

        <div class="shop-acs d-flex align-items-center justify-content-between justify-content-md-end flex-grow-1">


          <select class="shop-acs__select form-select w-auto border-0 py-0 order-1 order-md-0" aria-label="Sort Items"
            name="sort_by" id="sort_by">
            <option value="relevance" {{ request('sort_by', 'relevance') == 'relevance' ? 'selected':'' }}>الأكثر صلة</option>
            <option value="newest" {{ request('sort_by') == 'newest' ? 'selected':'' }}>الأحدث</option>
            <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected':'' }}>الأقدم</option>
            <option value="price_low_high" {{ request('sort_by') == 'price_low_high' ? 'selected':'' }}>السعر: من الأقل للأعلى</option>
            <option value="price_high_low" {{ request('sort_by') == 'price_high_low' ? 'selected':'' }}>السعر: من الأعلى للأقل</option>
            <option value="name_a_z" {{ request('sort_by') == 'name_a_z' ? 'selected':'' }}>الاسم: أ - ي</option>
            <option value="name_z_a" {{ request('sort_by') == 'name_z_a' ? 'selected':'' }}>الاسم: ي - أ</option>
            <option value="stock" {{ request('sort_by') == 'stock' ? 'selected':'' }}>الأكثر توفراً</option>
          </select>

          <div class="shop-asc__seprator mx-3 bg-light d-none d-md-block order-md-0"></div>

          <div class="col-size align-items-center order-1 d-none d-lg-flex">
            <div class="view-options d-flex align-items-center gap-2">
              <span class="text-uppercase fw-medium text-muted">عرض</span>
              <div class="view-buttons d-flex align-items-center">
                <button class="btn btn-outline-secondary btn-sm px-3 py-1 me-2 js-cols-size" data-target="products-grid" data-cols="2">2</button>
                <button class="btn btn-outline-secondary btn-sm px-3 py-1 me-2 js-cols-size" data-target="products-grid" data-cols="3">3</button>
                <button class="btn btn-outline-secondary btn-sm px-3 py-1 js-cols-size" data-target="products-grid" data-cols="4">4</button>
              </div>
            </div>
          </div>

          <div class="shop-filter d-flex align-items-center order-0 order-md-3 d-lg-none">
            <button class="btn-link btn-link_f d-flex align-items-center ps-0" id="desktopFilterBtn">
              <svg class="d-inline-block align-middle me-2" width="14" height="10" viewBox="0 0 14 10" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <use href="#icon_filter" />
              </svg>
              <span class="text-uppercase fw-medium d-inline-block align-middle">تصفية</span>
            </button>
          </div>
        </div>
      </div>

      <div class="products-grid row row-cols-2 row-cols-md-3 row-cols-lg-3 g-3 g-md-4" id="products-grid">
        @foreach( $products as $product )
        <div class="product-card-wrapper col">
          <div class="product-card mb-2 mb-md-3 mb-xxl-4 h-100">
            <div class="pc__img-wrapper">
              <div class="swiper-container background-img js-swiper-slider" data-settings='{"resizeObserver": true}'>
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <a href="{{route('shop.product.details',['product_slug'=>$product->slug])}}"><img loading="lazy" src="{{asset('uploads/products')}}/{{$product->image}}" width="330" height="400" alt="{{$product->name}}" class="pc__img"></a>
                  </div>
                  @if($product->images)
                    @foreach(explode(",",$product->images) as $gimg)
                    <div class="swiper-slide">
                      <a href="{{route('shop.product.details',['product_slug'=>$product->slug])}}"><img loading="lazy" src="{{asset('uploads/products')}}/{{trim($gimg)}}" width="330" height="400" alt="{{$product->name}}" class="pc__img"></a>
                    </div>
                    @endforeach
                  @endif
                </div>
                <span class="pc__img-prev"><svg width="7" height="11" viewBox="0 0 7 11"
                    xmlns="http://www.w3.org/2000/svg">
                    <use href="#icon_prev_sm" />
                  </svg></span>
                <span class="pc__img-next"><svg width="7" height="11" viewBox="0 0 7 11"
                    xmlns="http://www.w3.org/2000/svg">
                    <use href="#icon_next_sm" />
                  </svg></span>
              </div>
              @if(Cart::instance('cart')->content()->where('id', $product->id)->count()>0)
              <a href="{{route('cart.index')}}" class="cart-icon-btn" title="الذهاب للسلة"><i class="bi bi-cart-check fs-5"></i></a>
              @else
              <form name="addtocart-form" method="post" action="{{route('cart.add')}}">
                @csrf
                <input type="hidden" name="id" value="{{$product->id}}" />
                <input type="hidden" name="quantity" value="1" />
                <input type="hidden" name="name" value="{{$product->name}}" />
                <input type="hidden" name="price" value="{{$product->sale_price == '' ? $product->regular_price : $product->sale_price}}" />
                <button type="submit" class="cart-icon-btn border-0" data-aside="cartDrawer" title="إضافة للسلة"><i class="bi bi-cart-plus fs-5"></i></button>
              </form>
              @endif
            </div>

            <div class="pc__info position-relative">
              <h6 class="pc__title"><a href="{{route('shop.product.details',['product_slug'=>$product->slug])}}">{{$product->card_title}}</a></h6>
              <div class="product-card__price d-flex">
                <span class="money price">
                  @if($product->sale_price)
                  <s>{{ format_price($product->regular_price) }}</s> {{ format_price($product->sale_price) }}
                  @else
                  {{ format_price($product->regular_price) }}
                  @endif
                </span>
              </div>
              <x-star-rating :rating="$product->active_reviews_avg" :count="$product->active_reviews_count" />
              @if($product->colors && $product->colors->count() > 0)
              <div class="product-card__colors d-flex gap-1 my-2 flex-wrap" style="gap: 5px; margin-top: 8px; margin-bottom: 8px;">
                @foreach($product->colors as $color)
                <span class="color-dot" style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: {{ $color->hex_code ?? '#ccc' }}; border: 1px solid #ddd;" title="{{ $color->name }}"></span>
                @endforeach
              </div>
              @endif
              <div class="product-info-badges">
                <div class="delivery-badge">
                  <i class="bi bi-clock"></i>
                  <span>يصلك في <b>1-7</b> أيام</span>
                </div>
                <div class="shipping-badge">
                  <i class="bi bi-truck"></i>
                  <span>شحن مجاني</span>
                </div>
              </div>
              @if(Cart::instance('wishlist')->content()->where('id',$product->id)->count()>0)
              <form method="POST" action="{{ route('wishlist.item.remove',['rowId'=>Cart::instance('wishlist')->content()->where('id',$product->id)->first()->rowId])}}">
                @csrf
                @method('DELETE')
                <button type="submit" class="pc__btn-wl position-absolute top-0 start-0 bg-transparent border-0 js-add-wishlist filled-heart" title="إزالة من المفضلة">

                  <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <use href="#icon_heart" />
                  </svg>
                </button>
              </form>
              @else
              <form method="POST" action="{{route('wishlist.add')}}">
                @csrf
                <input type="hidden" name="id" value="{{ $product->id }}" />
                <input type="hidden" name="name" value="{{ $product->name }}" />
                <input type="hidden" name="price" value="{{ $product->sale_price=='' ? $product->regular_price : $product->sale_price }}" />
                <input type="hidden" name="quantity" value="1" />
                <button type="submit" class="pc__btn-wl position-absolute top-0 start-0 bg-transparent border-0 js-add-wishlist" title="إضافة للمفضلة">

                  <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <use href="#icon_heart" />
                  </svg>
                </button>
              </form>
              @endif
            </div>
          </div>
        </div>
        @endforeach

      </div>

      <div class="divider my-4"></div>
      <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 wgp-pagination py-3">
        {{$products->withQueryString()->links('pagination::bootstrap-5')}}
      </div>
    </div>
  </section>
</main>

<!-- زر التصفية العائم للجوال -->
<button class="btn btn-primary btn-filter-mobile d-lg-none" id="mobileFilterBtn">
  <svg width="18" height="14" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
    <use href="#icon_filter" fill="white" />
  </svg>
  <span>تصفية</span>
</button>

<form id="frmfilter" method="GET" action="{{ route('shop.index') }}">
  <input type="hidden" name="search" value="{{ request('search') }}" />
  <input type="hidden" name="per_page" id="per_page" value="{{ $size }}" />
  <input type="hidden" name="sort_by" id="sort_by_hidden" value="{{ $order }}" />
  <input type="hidden" name="brands" id="hdnBrands" value="{{ $f_brands }}" />
  <input type="hidden" name="categories" id="hdnCategories" value="{{ $f_categories }}" />
  <input type="hidden" name="colors" id="hdnColors" value="{{ request('colors') }}" />
  <input type="hidden" name="sizes" id="hdnSizes" value="{{ request('sizes') }}" />
  <input type="hidden" name="min_price" id="hdnMinPrice" value="{{ $min_price }}" />
  <input type="hidden" name="max_price" id="hdnMaxPrice" value="{{ $max_price }}" />
</form>
@endsection

@push('scripts')
<script>
  $(function() {
    $("#pagesize").on("change", function() {
      $("#products-grid").addClass("filter-loading");
      $("#per_page").val($("#pagesize option:selected").val());
      $("#frmfilter").submit();
    });

    $("#sort_by").on("change", function() {
      $("#products-grid").addClass("filter-loading");
      $("#sort_by_hidden").val($("#sort_by option:selected").val());
      $("#frmfilter").submit();
    });

    $("input[name='brands']").on("change", function() {
      var brands = "";
      $("input[name='brands']:checked").each(function() {
        if (brands == "") {
          brands += $(this).val();
        } else {
          brands += "," + $(this).val();
        }
      });

      $("#hdnBrands").val(brands);
      $("#products-grid").addClass("filter-loading");
      $("#frmfilter").submit();
    });


    $("input[name='categories']").on("change", function() {
      var categories = "";
      $("input[name='categories']:checked").each(function() {
        if (categories == "") {
          categories += $(this).val();
        } else {
          categories += "," + $(this).val();
        }
      });

      $("#hdnCategories").val(categories);
      $("#products-grid").addClass("filter-loading");
      $("#frmfilter").submit();
    });
    $("[name='price_range']").on("change", function() {
      var min = $(this).val().split(',')[0];
      var max = $(this).val().split(',')[1];


      $("#hdnMinPrice").val(min);
      $("#hdnMaxPrice").val(max);

      // إظهار حالة التحميل
      $("#products-grid").addClass("filter-loading");

      setTimeout(() => {
        $("#frmfilter").submit();
      }, 1000);





    });

    // فلترة الألوان
    $("input[name='colors']").on("change", function() {
      var colors = "";
      $("input[name='colors']:checked").each(function() {
        if (colors == "") {
          colors += $(this).val();
        } else {
          colors += "," + $(this).val();
        }
      });
      $("#hdnColors").val(colors);
      $("#products-grid").addClass("filter-loading");
      $("#frmfilter").submit();
    });

    // فلترة الأحجام
    $("input[name='sizes']").on("change", function() {
      var sizes = "";
      $("input[name='sizes']:checked").each(function() {
        if (sizes == "") {
          sizes += $(this).val();
        } else {
          sizes += "," + $(this).val();
        }
      });
      $("#hdnSizes").val(sizes);
      $("#products-grid").addClass("filter-loading");
      $("#frmfilter").submit();
    });

    // تحسين تفاعل الألوان والأحجام
    $('.swatch-color').on('click', function(e) {
      e.preventDefault();
      $(this).siblings('input[type="checkbox"]').trigger('click');
    });

    $('.swatch-size').on('click', function(e) {
      e.preventDefault();
      $(this).siblings('input[type="checkbox"]').trigger('click');
    });

    // تحديث مظهر الفلاتر المحددة
    $('input[type="checkbox"]').on('change', function() {
      if ($(this).is(':checked')) {
        $(this).siblings('.swatch-color, .swatch-size').addClass('active');
      } else {
        $(this).siblings('.swatch-color, .swatch-size').removeClass('active');
      }
    });

    // تطبيق الحالة الأولية للفلاتر
    $('input[type="checkbox"]:checked').each(function() {
      $(this).siblings('.swatch-color, .swatch-size').addClass('active');
    });

    // تحسين أزرار العرض
    $('.js-cols-size').on('click', function(e) {
      e.preventDefault();

      // إزالة الحالة النشطة من جميع الأزرار
      $('.js-cols-size').removeClass('active');

      // إضافة الحالة النشطة للزر المضغوط
      $(this).addClass('active');

      // الحصول على عدد الأعمدة
      var cols = $(this).data('cols');
      var target = $(this).data('target');

      // تطبيق التغيير على الشبكة
      if (target && $('#' + target).length) {
        $('#' + target).removeClass('row-cols-2 row-cols-3 row-cols-4 row-cols-lg-2 row-cols-lg-3 row-cols-lg-4')
          .addClass('row-cols-lg-' + cols);
      }

      // حفظ الإعداد في localStorage
      localStorage.setItem('shop-grid-cols', cols);
    });

    // استعادة الإعداد المحفوظ عند تحميل الصفحة
    var savedCols = localStorage.getItem('shop-grid-cols') || '3';
    $('.js-cols-size[data-cols="' + savedCols + '"]').addClass('active');

    // تطبيق الإعداد المحفوظ
    $('#products-grid').removeClass('row-cols-2 row-cols-3 row-cols-4 row-cols-lg-2 row-cols-lg-3 row-cols-lg-4')
      .addClass('row-cols-lg-' + savedCols);

    // تحسين تفاعل الفلاتر
    $('.form-check-label').on('click', function(e) {
      e.preventDefault();
      var checkbox = $(this).siblings('.form-check-input');
      checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
    });

    // تحديث العدادات عند تغيير الفلاتر
    $('.chk-category, .chk-brand').on('change', function() {
      var $listItem = $(this).closest('.list-item');
      var $badge = $listItem.find('.badge');

      if ($(this).is(':checked')) {
        $listItem.addClass('selected');
        $badge.removeClass('bg-secondary').addClass('bg-primary');
      } else {
        $listItem.removeClass('selected');
        $badge.removeClass('bg-primary').addClass('bg-secondary');
      }
    });

    // تطبيق الحالة الأولية للفلاتر المحددة
    $('.chk-category:checked, .chk-brand:checked').each(function() {
      var $listItem = $(this).closest('.list-item');
      var $badge = $listItem.find('.badge');
      $listItem.addClass('selected');
      $badge.removeClass('bg-secondary').addClass('bg-primary');
    });

    // تحسين فلتر السعر
    $('.price-range-slider').on('slide', function(slideEvt) {
      var values = slideEvt.value;
      var minPrice = values[0];
      var maxPrice = values[1];

      // تحديث القيم
      $('.price-range__min').text('$' + minPrice);
      $('.price-range__max').text('$' + maxPrice);
    });

    // Mobile Filter Custom Drawer Logic
    const shopFilter = document.getElementById('shopFilter');
    const mobileFilterBtn = document.getElementById('mobileFilterBtn');
    const desktopFilterBtn = document.getElementById('desktopFilterBtn');
    const closeFilterBtn = document.getElementById('closeFilterBtn');

    function openFilter(e) {
      if(e) e.preventDefault();
      shopFilter.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      
      let filterOverlay = document.getElementById('filterOverlay');
      if (!filterOverlay) {
          filterOverlay = document.createElement('div');
          filterOverlay.className = 'filter-overlay';
          filterOverlay.id = 'filterOverlay';
          document.body.appendChild(filterOverlay);
          
          setTimeout(() => filterOverlay.classList.add('is-active'), 10);
          filterOverlay.addEventListener('click', closeFilter);
      } else {
          filterOverlay.classList.add('is-active');
      }
    }

    function closeFilter(e) {
      if(e) e.preventDefault();
      shopFilter.classList.remove('is-open');
      document.body.style.overflow = '';
      const overlay = document.getElementById('filterOverlay');
      if (overlay) {
          overlay.classList.remove('is-active');
          setTimeout(() => overlay.remove(), 300);
      }
    }

    if (mobileFilterBtn) mobileFilterBtn.addEventListener('click', openFilter);
    if (desktopFilterBtn) desktopFilterBtn.addEventListener('click', openFilter);
    if (closeFilterBtn) closeFilterBtn.addEventListener('click', closeFilter);

  });
</script>

<style>
  .swatch-color::after {
    background-color: transparent !important;
    content: '' !important;
  }

  .swatch-color.active {
    border: 2px solid #000 !important;
    transform: scale(1.1);
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.15) !important;
  }

  .swatch-color.active::after {
    content: '✓' !important;
    color: #fff !important;
    font-size: 14px !important;
    font-weight: bold !important;
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.8) !important;
  }

  .swatch-size.active {
    background-color: #e6b13a !important;
    color: white !important;
    border-color: #e6b13a !important;
    box-shadow: 0 4px 6px rgba(230, 177, 58, 0.2) !important;
  }

  .swatch-color-wrapper,
  .swatch-size-wrapper {
    cursor: pointer;
  }

  .search-info {
    border-left: 4px solid #007bff;
  }

  .swatch-color {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-block;
    border: 2px solid #ddd;
    margin: 2px;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  /* تحسين زر التصفية للهواتف */
  .btn-filter-mobile {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 999;
    display: none;
    padding: 12px 24px;
    border-radius: 50px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease;
  }

  @keyframes slideUp {
    from {
      transform: translateY(100px);
    }

    to {
      transform: translateY(0);
    }
  }

  @media (max-width: 991.98px) {
    .btn-filter-mobile {
      display: flex;
      align-items: center;
      gap: 8px;
    }
  }

  /* تأثير التحميل */
  .filter-loading {
    position: relative;
    opacity: 0.6;
    pointer-events: none;
  }

  .filter-loading::after {
    content: "";
    position: absolute;
    top: 20%;
    left: 50%;
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 1000;
    transform: translate(-50%, -50%);
  }

  @keyframes spin {
    0% {
      transform: translate(-50%, -50%) rotate(0deg);
    }

    100% {
      transform: translate(-50%, -50%) rotate(360deg);
    }
  }
</style>
@endpush