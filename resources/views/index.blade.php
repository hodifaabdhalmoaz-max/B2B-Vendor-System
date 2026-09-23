@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mobile-market.css') }}?v={{ filemtime(public_path('assets/css/mobile-market.css')) }}" type="text/css" />
@endpush
@section('content')
<style>
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

  .pc__info {
    padding-left: 3rem;
    padding-right: 0.5rem;
  }

  .pc__title {
    margin-right: 0;
    padding-right: 0;
  }

  .pc__category,
  .pc__title a {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: calc(100% - 1rem);
  }

  .filled-heart {
    color: orange;
  }

  .product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  /* Fix empty space above New Arrivals on mobile */
  @media (max-width: 768px) {
    .slideshow-text {
      top: 25% !important;
      transform: translate(-50%, -25%) !important;
    }

    .slideshow {
      min-height: 500px !important;
    }
  }
</style>
<main>

  <!-- Mobile marketplace experience -->
  <div class="kids-market-shell d-block d-lg-none">
    <x-shop.top-header-banner
      :banner="$banner"
      :activeCategory="$activeMarketCategory"
      :promotions="$homeTabPromotions ?? []"
    />
    <x-shop.mobile-market-home
      :categories="$categories"
      :featured-products="$featured_products"
      :latest-products="$latest_products"
      :offer-products="$offer_products"
      :market-products="$market_products"
      :active-category="$activeMarketCategory"
      :promotions="$homeTabPromotions ?? []"
    />
  </div>

  <!-- Desktop-only Slideshow Section -->
  <div class="d-none d-lg-block">
    <section class="swiper-container js-swiper-slider swiper-number-pagination slideshow" data-settings='{
          "autoplay": {
            "delay": 5000
          },
          "slidesPerView": 1,
          "effect": "fade",
          "loop": true
        }'>
      <div class="swiper-wrapper">
        @forelse($slides as $slide)
        <div class="swiper-slide">
          <div class="overflow-hidden position-relative h-100">
            <div class="slideshow-character position-absolute bottom-0 pos_left-center">
              @if($slide->image)
              <img loading="lazy" src="{{ asset('uploads/slides/' . $slide->image) }}" width="400" height="733"
                alt="{{ $slide->title ?? 'شريحة عرض' }}"
                class="slideshow-character__img animate animate_fade animate_btt animate_delay-9 w-auto h-auto" />
              @endif
            </div>
            <div class="slideshow-text container position-absolute start-50 top-50 translate-middle">
              @if($slide->tagline)
              <h6 class="text_inline_dash text-uppercase fs-base fw-medium animate animate_fade animate_btt animate_delay-3">
                {{ $slide->tagline }}
              </h6>
              @endif
              @if($slide->title)
              <h2 class="h1 fw-normal mb-0 animate animate_fade animate_btt animate_delay-5">{{ $slide->title }}</h2>
              @endif
              @if($slide->subtitle)
              <h2 class="h1 fw-bold animate animate_fade animate_btt animate_delay-7">{{ $slide->subtitle }}</h2>
              @endif
              <a href="{{ $slide->link ?? '#' }}"
                class="btn-link btn-link_lg default-underline fw-medium animate animate_fade animate_btt animate_delay-7">تسوق
                الآن</a>
            </div>
          </div>
        </div>
        @empty
        <div class="swiper-slide">
          <div class="overflow-hidden position-relative h-100">
            <div class="slideshow-character position-absolute bottom-0 pos_left-center">
              <img loading="lazy" src="{{ asset('assets/images/home/demo3/slideshow-character1.png') }}" width="542" height="733"
                alt="ملابس أطفال عصرية"
                class="slideshow-character__img animate animate_fade animate_btt animate_delay-9 w-auto h-auto" />
              <div class="character_markup type2">
                <p
                  class="text-uppercase font-sofia mark-grey-color animate animate_fade animate_btt animate_delay-10 mb-0">
                  So Cuts</p>
              </div>
            </div>
            <div class="slideshow-text container position-absolute start-50 top-50 translate-middle">
              <h6 class="text_inline_dash text-uppercase fs-base fw-medium animate animate_fade animate_btt animate_delay-3">
                وصل حديثاً</h6>
              <h2 class="h1 fw-normal mb-0 animate animate_fade animate_btt animate_delay-5">طفل سعيد</h2>
              <h2 class="h1 fw-bold animate animate_fade animate_btt animate_delay-7">مع متجرنا</h2>
              <a href="#"
                class="btn-link btn-link_lg default-underline fw-medium animate animate_fade animate_btt animate_delay-7">تسوق
                الآن</a>
            </div>
          </div>
        </div>

        <div class="swiper-slide">
          <div class="overflow-hidden position-relative h-100">
            <div class="slideshow-character position-absolute bottom-0 pos_left-center">
              <img loading="lazy" src="{{ asset('assets/images/slideshow-character1.png') }}" width="400" height="733"
                alt="مجموعة أطفال شتوية"
                class="slideshow-character__img animate animate_fade animate_btt animate_delay-9 w-auto h-auto" />
              <div class="character_markup">
                <p class="text-uppercase font-sofia fw-bold animate animate_fade animate_rtl animate_delay-10">للشتاء
                </p>
              </div>
            </div>
            <div class="slideshow-text container position-absolute start-50 top-50 translate-middle">
              <h6 class="text_inline_dash text-uppercase fs-base fw-medium animate animate_fade animate_btt animate_delay-3">
                وصل حديثاً</h6>
              <h2 class="h1 fw-normal mb-0 animate animate_fade animate_btt animate_delay-5">عالم الأطفال</h2>
              <h2 class="h1 fw-bold animate animate_fade animate_btt animate_delay-7">المميز</h2>
              <a href="#"
                class="btn-link btn-link_lg default-underline fw-medium animate animate_fade animate_btt animate_delay-7">تسوق
                الآن</a>
            </div>
          </div>
        </div>

        <div class="swiper-slide">
          <div class="overflow-hidden position-relative h-100">
            <div class="slideshow-character position-absolute bottom-0 pos_left-center">
              <img loading="lazy" src="{{ asset('assets/images/slideshow-character2.png') }}" width="400" height="690"
                alt="منتجات أطفال ممتعة"
                class="slideshow-character__img animate animate_fade animate_rtl animate_delay-10 w-auto h-auto" />
            </div>
            <div class="slideshow-text container position-absolute start-50 top-50 translate-middle">
              <h6 class="text_inline_dash text-uppercase fs-base fw-medium animate animate_fade animate_btt animate_delay-3">
                وصل حديثاً</h6>
              <h2 class="h1 fw-normal mb-0 animate animate_fade animate_btt animate_delay-5">أشياء ممتعة</h2>
              <h2 class="h1 fw-bold animate animate_fade animate_btt animate_delay-7">لطفلك</h2>
              <a href="#"
                class="btn-link btn-link_lg default-underline fw-medium animate animate_fade animate_btt animate_delay-7">تسوق
                الآن</a>
            </div>
          </div>
        </div>
        @endforelse
      </div>

      <div class="container">
        <div
          class="slideshow-pagination slideshow-number-pagination d-flex align-items-center position-absolute bottom-0 mb-5">
        </div>
      </div>
    </section>
  </div>
  <div class="container mw-1620 bg-white border-radius-10 d-none d-lg-block">
    <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>
    <section class="category-carousel container">
      <h2 class="section-title text-center mb-5">تسوق حسب الفئة</h2>

      <div class="position-relative">
        <div class="swiper-container js-swiper-slider" data-settings='{
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": 8,
              "slidesPerGroup": 1,
              "effect": "none",
              "loop": true,
              "navigation": {
                "nextEl": ".products-carousel__next-1",
                "prevEl": ".products-carousel__prev-1"
              },
              "breakpoints": {
                "320": {
                  "slidesPerView": 2,
                  "slidesPerGroup": 2,
                  "spaceBetween": 15
                },
                "768": {
                  "slidesPerView": 4,
                  "slidesPerGroup": 4,
                  "spaceBetween": 30
                },
                "992": {
                  "slidesPerView": 6,
                  "slidesPerGroup": 1,
                  "spaceBetween": 45,
                  "pagination": false
                },
                "1200": {
                  "slidesPerView": 8,
                  "slidesPerGroup": 1,
                  "spaceBetween": 60,
                  "pagination": false
                }
              }
            }'>
          <div class="swiper-wrapper">
            @foreach($categories as $category)
            <div class="swiper-slide">
              @php
              $imageSrc = $category->image;
              if (!empty($imageSrc)) {
              if (\Illuminate\Support\Str::startsWith($imageSrc, 'categories/')) {
              $imageSrc = asset('uploads/' . $imageSrc);
              } else {
              $imageSrc = asset('uploads/categories/' . $imageSrc);
              }
              } else {
              $imageSrc = asset('assets/images/home/demo3/category_1.png');
              }
              @endphp
              <a href="{{ route('shop.category', $category->slug) }}" class="d-block">
                <img loading="lazy" class="w-100 h-auto mb-3"
                  src="{{ $imageSrc }}?v={{ time() }}"
                  width="124" height="124"
                  alt="{{ $category->name }}"
                  onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'" />
              </a>
              <div class="text-center">
                <a href="{{ route('shop.category', $category->slug) }}" class="menu-link fw-medium">{{ $category->name }}</a>
              </div>
            </div>
            @endforeach
          </div><!-- /.swiper-wrapper -->
        </div><!-- /.swiper-container js-swiper-slider -->

        <div
          class="products-carousel__prev products-carousel__prev-1 position-absolute top-50 d-flex align-items-center justify-content-center">
          <svg width="25" height="25" viewBox="0 0 25 25" xmlns="http://www.w3.org/2000/svg">
            <use href="#icon_prev_md" />
          </svg>
        </div><!-- /.products-carousel__prev -->
        <div
          class="products-carousel__next products-carousel__next-1 position-absolute top-50 d-flex align-items-center justify-content-center">
          <svg width="25" height="25" viewBox="0 0 25 25" xmlns="http://www.w3.org/2000/svg">
            <use href="#icon_next_md" />
          </svg>
        </div><!-- /.products-carousel__next -->
      </div><!-- /.position-relative -->
    </section>

    <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

    <section class="hot-deals container">
      <h2 class="section-title text-center mb-5">العروض الساخنة</h2>
      <div class="row">
        <div
          class="col-md-6 col-lg-4 col-xl-20per d-flex align-items-center flex-column justify-content-center py-4 align-items-md-start">
          <h2>تخفيضات دنيا الأطفال</h2>
          <h2 class="fw-bold">خصم قد يصل إلى 50%</h2>

          <div class="position-relative d-flex align-items-center text-center pt-xxl-4 js-countdown mb-3"
            data-date="18-3-2024" data-time="06:50">
            <div class="day countdown-unit">
              <span class="countdown-num d-block"></span>
              <span class="countdown-word text-uppercase text-secondary">أيام</span>
            </div>

            <div class="hour countdown-unit">
              <span class="countdown-num d-block"></span>
              <span class="countdown-word text-uppercase text-secondary">ساعات</span>
            </div>

            <div class="min countdown-unit">
              <span class="countdown-num d-block"></span>
              <span class="countdown-word text-uppercase text-secondary">دقائق</span>
            </div>

            <div class="sec countdown-unit">
              <span class="countdown-num d-block"></span>
              <span class="countdown-word text-uppercase text-secondary">ثواني</span>
            </div>
          </div>

          <a href="{{ route('shop.offers') }}" class="btn-link default-underline text-uppercase fw-medium mt-3">عرض الكل</a>
        </div>
        <div class="col-md-6 col-lg-8 col-xl-80per">
          <div class="position-relative">
            <div class="swiper-container js-swiper-slider" data-settings='{
                  "autoplay": {
                    "delay": 5000
                  },
                  "slidesPerView": 4,
                  "slidesPerGroup": 4,
                  "effect": "none",
                  "loop": false,
                  "breakpoints": {
                    "320": {
                      "slidesPerView": 2,
                      "slidesPerGroup": 2,
                      "spaceBetween": 14
                    },
                    "768": {
                      "slidesPerView": 2,
                      "slidesPerGroup": 3,
                      "spaceBetween": 24
                    },
                    "992": {
                      "slidesPerView": 3,
                      "slidesPerGroup": 1,
                      "spaceBetween": 30,
                      "pagination": false
                    },
                    "1200": {
                      "slidesPerView": 4,
                      "slidesPerGroup": 1,
                      "spaceBetween": 30,
                      "pagination": false
                    }
                  }
                }'>
              <div class="swiper-wrapper">
                @foreach($offer_products as $product)
                <div class="swiper-slide">
                  <div class="product-card mb-2 mb-md-3 h-100">
                    <div class="pc__img-wrapper">
                      <div class="swiper-container background-img js-swiper-slider" data-settings='{"resizeObserver": true}'>
                        <div class="swiper-wrapper">
                          <div class="swiper-slide">
                            <a href="{{route('shop.product.details',['product_slug'=>$product->slug])}}"><img loading="lazy" src="{{asset('uploads/products')}}/{{$product->image}}" width="330" height="400" alt="{{$product->name}}" class="pc__img"></a>
                          </div>
                          @if($product->images)
                          <div class="swiper-slide">
                            @foreach(explode(",",$product->images) as $gimg)
                            <a href="{{route('shop.product.details',['product_slug'=>$product->slug])}}"><img loading="lazy" src="{{asset('uploads/products')}}/{{trim($gimg)}}" width="330" height="400" alt="{{$product->name}}" class="pc__img"></a>
                            @endforeach
                          </div>
                          @endif
                        </div>
                        <span class="pc__img-prev"><svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg">
                            <use href="#icon_prev_sm" />
                          </svg></span>
                        <span class="pc__img-next"><svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg">
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
                        <span class="money price text-secondary">
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
              </div><!-- /.swiper-wrapper -->
            </div><!-- /.swiper-container js-swiper-slider -->
          </div><!-- /.position-relative -->
        </div>
      </div>
    </section>

    <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

    <section class="category-banner container">
      <div class="row">
        <div class="col-md-6">
          <div class="category-banner__item border-radius-10 mb-5">
            <img loading="lazy" class="h-auto" src="{{ asset('assets/images/home/demo3/category_9.jpg') }}" width="690" height="665"
              alt="" />
            <div class="category-banner__item-mark">
              يبدأ من 19$
            </div>
            <div class="category-banner__item-content">
              <h3 class="mb-0">جاكيتات</h3>
              <a href="#" class="btn-link default-underline text-uppercase fw-medium">تسوق الآن</a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="category-banner__item border-radius-10 mb-5">
            <img loading="lazy" class="h-auto" src="{{ asset('assets/images/home/demo3/category_10.jpg') }}" width="690" height="665"
              alt="" />
            <div class="category-banner__item-mark">
              يبدأ من 19$
            </div>
            <div class="category-banner__item-content">
              <h3 class="mb-0">ملابس رياضية</h3>
              <a href="#" class="btn-link default-underline text-uppercase fw-medium">تسوق الآن</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

    <section class="products-grid container">
      <h2 class="section-title text-center mb-5">المنتجات المميزة</h2>

      <div class="row">
        @foreach($featured_products as $product)
        <div class="col-6 col-md-4 col-lg-3">
          <div class="product-card mb-3 mb-md-4 mb-xxl-5 h-100">
            <div class="pc__img-wrapper">
              <div class="swiper-container background-img js-swiper-slider" data-settings='{"resizeObserver": true}'>
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <a href="{{route('shop.product.details',['product_slug'=>$product->slug])}}"><img loading="lazy" src="{{asset('uploads/products')}}/{{$product->image}}" width="330" height="400" alt="{{$product->name}}" class="pc__img"></a>
                  </div>
                  @if($product->images)
                  <div class="swiper-slide">
                    @foreach(explode(",",$product->images) as $gimg)
                    <a href="{{route('shop.product.details',['product_slug'=>$product->slug])}}"><img loading="lazy" src="{{asset('uploads/products')}}/{{trim($gimg)}}" width="330" height="400" alt="{{$product->name}}" class="pc__img"></a>
                    @endforeach
                  </div>
                  @endif
                </div>
                <span class="pc__img-prev"><svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg">
                    <use href="#icon_prev_sm" />
                  </svg></span>
                <span class="pc__img-next"><svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg">
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
                <span class="money price text-secondary">
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
      </div><!-- /.row -->

      <div class="text-center mt-2">
        <a class="btn-link btn-link_lg default-underline text-uppercase fw-medium" href="{{ route('shop.index') }}">تحميل المزيد</a>
      </div>
    </section>

    <!-- Brands Section -->
    @if($brands->count() > 0)
    <section class="brands-carousel container">
      <h2 class="section-title text-center mb-5">العلامات التجارية المميزة</h2>

      <div class="position-relative">
        <div class="swiper-container js-swiper-slider" data-settings='{
              "autoplay": {
                "delay": 3000
              },
              "slidesPerView": 2,
              "slidesPerGroup": 1,
              "effect": "none",
              "loop": true,
              "breakpoints": {
                "768": {
                  "slidesPerView": 4
                },
                "992": {
                  "slidesPerView": 6
                }
              }
            }'>
          <div class="swiper-wrapper">
            @foreach($brands as $brand)
            <div class="swiper-slide">
              <div class="text-center p-3">
                @php
                $brandImageSrc = $brand->image;
                if (!empty($brandImageSrc)) {
                if (\Illuminate\Support\Str::startsWith($brandImageSrc, 'brands/')) {
                $brandImageSrc = asset('uploads/' . $brandImageSrc);
                } else {
                $brandImageSrc = asset('uploads/brands/' . $brandImageSrc);
                }
                } else {
                $brandImageSrc = asset('assets/images/home/demo3/category_1.png');
                }
                @endphp
                <img loading="lazy" class="w-100 h-auto mb-3"
                  src="{{ $brandImageSrc }}?v={{ time() }}"
                  width="124" height="124"
                  alt="{{ $brand->name }}"
                  onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'" />
                <div class="text-center">
                  <a href="{{ route('shop.brand', $brand->slug) }}" class="menu-link fw-medium">{{ $brand->name }}</a>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </section>
    @endif
  </div>

  <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

</main>
@endsection
