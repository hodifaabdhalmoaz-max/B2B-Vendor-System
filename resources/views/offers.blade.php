@extends('layouts.app')
@section('content')
<style>
  /* Custom Styles for Category Shop */
  .category-shop-wrapper {
    background-color: #f8f9fa;
    min-height: 100vh;
  }
  .category-header {
    background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
    padding: 100px 15px 45px 15px; /* Added extra top padding to clear sticky header */
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .category-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="contact-pattern" width="30" height="30" patternUnits="userSpaceOnUse"><circle cx="15" cy="15" r="2" fill="white" opacity="0.1"/><circle cx="5" cy="5" r="1" fill="white" opacity="0.05"/><circle cx="25" cy="25" r="1" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23contact-pattern)"/></svg>');
  }
  .category-header h1 {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
  }
  .search-bar-wrapper {
    background: white;
    border-radius: 12px;
    padding: 8px 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    margin: 0 auto;
    max-width: 600px;
    position: relative;
    z-index: 2;
    transform: translateY(20px); /* Creates the overlapping effect cleanly */
  }
  .search-bar-wrapper input {
    border: none;
    box-shadow: none;
    outline: none;
    background: transparent;
    width: 100%;
    padding: 5px;
  }
  
  .filter-tabs-container {
    display: flex;
    align-items: center;
    overflow-x: auto;
    white-space: nowrap;
    padding: 10px 15px;
    margin-top: 20px;
    gap: 10px;
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
  .filter-tabs-container::-webkit-scrollbar {
    display: none;
  }
  
  .filter-tab {
    background: white;
    border: 1px solid #e2e8f0;
    color: #64748b;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s;
  }
  .filter-tab.active, .filter-tab:hover {
    background: #f8f9fa;
    border-color: #d4a853;
    color: #d4a853;
    font-weight: 600;
  }
</style>

{{-- Shared modal styles --}}
<x-shop-modal-styles />

<div class="category-shop-wrapper">
  <!-- Header -->
  <div class="category-header">
    <div class="container position-relative">
      <div class="breadcrumb-dashboard mx-auto mb-3">
        <a href="{{ route('home.index') }}">
          <i class="bi bi-house"></i>
          الرئيسية
        </a>
        <i class="bi bi-chevron-left" style="color: #a0aec0; font-size: 12px;"></i>
        <span style="color: #1a202c; font-weight: 700;">العروض</span>
      </div>
      <h1>العروض والخصومات</h1>
    
    <div class="search-bar-wrapper d-flex align-items-center">
      <x-search-bar class="w-100" action="{{ route('shop.offers') }}" placeholder="ابحث الآن في العروض والخصومات" />
    </div>
    </div>
  </div>

  <div class="container">
    <!-- Filters & Tabs -->
    <div class="d-flex align-items-center mb-4">
      
      <!-- Scrollable Tabs -->
      <div class="filter-tabs-container flex-grow-1 ms-2" style="margin-top: 0; padding: 0;">
        <a href="{{ route('shop.offers', ['tab' => 'all']) }}" class="filter-tab {{ $current_tab == 'all' ? 'active' : '' }}">
          ({{ $products->total() }}) الكل
        </a>
        @foreach($tabs as $tab)
        <a href="{{ route('shop.offers', ['tab' => $tab->slug]) }}" class="filter-tab {{ $current_tab == $tab->slug ? 'active' : '' }}">
          {{ $tab->name }}
        </a>
        @endforeach
      </div>

      <!-- Action Buttons -->
      <div class="sort-filter-container flex-shrink-0">
        <button class="sort-filter-btn" data-bs-toggle="modal" data-bs-target="#sortModal">
          ترتيب
          <i class="bi bi-arrow-down-up"></i>
        </button>
        <span class="action-divider">|</span>
        <button class="sort-filter-btn" data-bs-toggle="modal" data-bs-target="#filterModal">
          <i class="bi bi-sliders"></i>
          فرز
          <i class="bi bi-chevron-down ms-1" style="font-size: 0.8rem;"></i>
        </button>
      </div>

    </div>

    <!-- Products Grid -->
    <div class="products-grid row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4" id="products-grid">
      @forelse($products as $product)
      <div class="product-card-wrapper col">
        <div class="product-card mb-2 mb-md-3 mb-xxl-4 h-100">
          <div class="pc__img-wrapper">
            <div class="swiper-container background-img js-swiper-slider" data-settings='{"resizeObserver": true}'>
              <div class="swiper-wrapper">
                <div class="swiper-slide">
                  <a href="{{route('shop.product.details',['product_slug'=>$product->slug])}}">
                    <img loading="lazy" src="{{ asset('uploads/products') }}/{{ $product->image }}" alt="{{ $product->name }}" class="pc__img" onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'">
                  </a>
                </div>
                @if($product->images)
                  @foreach(explode(",",$product->images) as $gimg)
                  <div class="swiper-slide">
                    <a href="{{route('shop.product.details',['product_slug'=>$product->slug])}}">
                      <img loading="lazy" src="{{ asset('uploads/products') }}/{{ trim($gimg) }}" alt="{{ $product->name }}" class="pc__img" onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'">
                    </a>
                  </div>
                  @endforeach
                @endif
              </div>
              <span class="pc__img-prev"><svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg"><use href="#icon_prev_sm" /></svg></span>
              <span class="pc__img-next"><svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg"><use href="#icon_next_sm" /></svg></span>
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
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><use href="#icon_heart" /></svg>
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
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><use href="#icon_heart" /></svg>
              </button>
            </form>
            @endif
          </div>
        </div>
      </div>
      @empty
      <div class="col-12 text-center py-5">
        <h5 class="text-muted">لا توجد منتجات مخفضة حالياً.</h5>
      </div>
      @endforelse
    </div>
    
    <div class="d-flex justify-content-center mt-4">
      {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

  </div>
</div>

{{-- Sort & Filter Modals (reusable components) --}}
<x-sort-modal
  :action="route('shop.offers')"
  :extra-inputs="['tab' => request('tab', 'all'), 'q' => request('q')]"
/>

<x-filter-modal
  :action="route('shop.offers')"
  :reset-url="route('shop.offers')"
  :brands="$brands"
  :colors="$colors"
  :sizes="$sizes"
  :extra-inputs="['tab' => request('tab', 'all'), 'q' => request('q')]"
/>

@endsection
