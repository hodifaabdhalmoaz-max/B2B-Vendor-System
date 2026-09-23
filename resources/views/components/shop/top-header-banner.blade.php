@props([
    'banner' => null,
    'activeCategory' => 'all',
    'tabsTarget' => 'home',
    'showHero' => true,
    'showBenefits' => true,
    'promotions' => [],
])

@php
    $isKids = $activeCategory === 'kids';
    $isGifts = $activeCategory === 'gifts';
    $isToys = $activeCategory === 'toys';
    $isMother = $activeCategory === 'mother';
    $isCare = $activeCategory === 'care';
    $isAccessories = $activeCategory === 'accessories';
    $isShoes = $activeCategory === 'shoes';
    $usesStaticHero = in_array($activeCategory, ['kids', 'gifts', 'toys', 'mother', 'care', 'accessories', 'shoes'], true);
    $heroThemeClass = match ($activeCategory) {
        'kids' => 'kids-market-hero--kids',
        'gifts' => 'kids-market-hero--gifts',
        'toys' => 'kids-market-hero--toys',
        'mother' => 'kids-market-hero--mother',
        'care' => 'kids-market-hero--care',
        'accessories' => 'kids-market-hero--accessories',
        'shoes' => 'kids-market-hero--shoes',
        default => 'kids-market-hero--all',
    };
    $topThemeClass = match ($activeCategory) {
        'kids' => 'kids-market-top--kids',
        'gifts' => 'kids-market-top--gifts',
        'toys' => 'kids-market-top--toys',
        'mother' => 'kids-market-top--mother',
        'care' => 'kids-market-top--care',
        'accessories' => 'kids-market-top--accessories',
        'shoes' => 'kids-market-top--shoes',
        default => 'kids-market-top--all',
    };
    $heroCopy = match ($activeCategory) {
        'kids' => ['kicker' => 'ستايل الإجازة', 'title' => 'خصم حتى', 'subtitle' => '80% OFF'],
        'gifts' => ['kicker' => 'هدايا بأسعار لطيفة', 'title' => 'اختيارات مميزة حتى', 'subtitle' => '80% OFF'],
        'toys' => ['kicker' => 'ألعاب تعليمية وممتعة', 'title' => 'أفضل المنتجات حتى', 'subtitle' => '80% OFF'],
        'mother' => ['kicker' => 'راحة الأم والطفل', 'title' => 'أساسيات موثوقة حتى', 'subtitle' => '70% OFF'],
        'care' => ['kicker' => 'عناية يومية آمنة', 'title' => 'منتجات لطيفة حتى', 'subtitle' => '60% OFF'],
        'accessories' => ['kicker' => 'تفاصيل تكمل الإطلالة', 'title' => 'اكسسوارات مختارة حتى', 'subtitle' => '75% OFF'],
        'shoes' => ['kicker' => 'صيفي ومريح', 'title' => 'أحذية أطفال حتى', 'subtitle' => '75% OFF'],
        default => ['kicker' => '#عالم_دنيا_الأطفال', 'title' => 'وفر أكثر على', 'subtitle' => 'أفضل منتجات الأطفال'],
    };
    $tabUrl = fn (string $key) => $tabsTarget === 'categories'
        ? route('categories.index', $key === 'all' ? [] : ['market_tab' => $key])
        : route('home.index', $key === 'all' ? [] : ['market_tab' => $key]);

    $fallbackTabs = [
        ['key' => 'all', 'label' => 'الكل', 'url' => $tabUrl('all')],
        ['key' => 'kids', 'label' => 'الأطفال', 'url' => $tabUrl('kids')],
        ['key' => 'gifts', 'label' => 'هدايا', 'url' => $tabUrl('gifts')],
        ['key' => 'toys', 'label' => 'العاب وتعليم', 'url' => $tabUrl('toys')],
        ['key' => 'mother', 'label' => 'مستلزمات الأمومة', 'url' => $tabUrl('mother')],
        ['key' => 'care', 'label' => 'الصحة والعناية بالبشرة', 'url' => $tabUrl('care')],
        ['key' => 'accessories', 'label' => 'اكسسوارات', 'url' => $tabUrl('accessories')],
        ['key' => 'shoes', 'label' => 'أحذية', 'url' => $tabUrl('shoes')],
    ];

    $tabs = $fallbackTabs;

    $bannerProducts = $usesStaticHero ? collect() : collect(data_get($banner, 'products', []))->take(2);
    $heroCampaigns = collect($promotions['home_tab_hero'] ?? []);
    $offerStripCampaigns = collect($promotions['home_tab_offer_strip'] ?? []);
@endphp

<header class="kids-market-top {{ $topThemeClass }}" dir="rtl">
    <div class="kids-market-searchbar">
        <div class="kids-market-icon-cluster">
            <a class="kids-market-icon" href="{{ route('messages.index') }}" aria-label="الرسائل">
                <i class="bi bi-envelope"></i>
                <span class="kids-market-dot" aria-hidden="true"></span>
            </a>
            <a class="kids-market-icon" href="{{ route('shop.offers') }}" aria-label="العروض اليومية">
                <i class="bi bi-calendar2-week"></i>
            </a>
        </div>

        <form class="kids-market-search" action="{{ route('shop.search') }}" method="GET">
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="فساتين أطفال، ألعاب، هدايا..."
                autocomplete="off"
                aria-label="البحث في المتجر"
            >
            <button class="kids-market-camera" type="button" aria-label="البحث بالصورة">
                <i class="bi bi-camera"></i>
            </button>
            <button class="kids-market-submit" type="submit" aria-label="بحث">
                <i class="bi bi-search"></i>
            </button>
        </form>

        <a class="kids-market-icon" href="{{ route('wishlist.index') }}" aria-label="المفضلة">
            <i class="bi bi-heart"></i>
        </a>
    </div>

    <nav class="kids-market-tabs-wrap" aria-label="أقسام المتجر الرئيسية" dir="rtl">
        <div class="kids-market-tabs">
            @foreach($tabs as $tab)
                <a
                    href="{{ $tab['url'] }}"
                    class="kids-market-tab {{ $activeCategory === $tab['key'] ? 'is-active' : '' }}"
                >
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        <a class="kids-market-menu" href="{{ route('categories.index') }}" aria-label="كل الفئات">
            <i class="bi bi-list"></i>
        </a>
    </nav>
</header>

@if($showHero)
@if($heroCampaigns->isNotEmpty())
    <x-marketing.home-tab-hero :campaigns="$heroCampaigns" :hero-theme-class="$heroThemeClass" />
@else
<section class="kids-market-hero {{ $heroThemeClass }}" dir="rtl" aria-label="عروض دنيا الأطفال">
    <div class="kids-market-hero-copy">
        <span class="kids-market-hero-kicker">{{ $heroCopy['kicker'] }}</span>
        <h1 class="kids-market-hero-title">
            {{ $heroCopy['title'] }}
            <span class="kids-market-hero-subtitle">{{ $heroCopy['subtitle'] }}</span>
        </h1>
        <a class="kids-market-hero-btn" href="{{ route('shop.index') }}">تسوق الآن</a>
    </div>

    @forelse($bannerProducts as $product)
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
            $displayPrice = $salePrice > 0 && $salePrice < $regularPrice ? $salePrice : $regularPrice;
        @endphp

        <a class="kids-market-hero-product" href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">
            <img
                src="{{ $imageSrc }}"
                alt="{{ $product->name }}"
                loading="lazy"
                onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
            >
            <span class="kids-market-hero-price">{{ number_format($displayPrice, 0) }} ر.ي</span>
        </a>
    @empty
        @if($isKids)
            <a class="kids-market-hero-product" href="{{ route('shop.index') }}">
                <img src="{{ asset('assets/images/home/demo3/Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg') }}" alt="طقم بناتي للأطفال" loading="lazy">
                <span class="kids-market-hero-price">ر.ي30</span>
            </a>
            <a class="kids-market-hero-product" href="{{ route('shop.index') }}">
                <img src="{{ asset('assets/images/home/demo3/Baby Girl Clothes.jpg') }}" alt="ملابس أطفال بنات" loading="lazy">
                <span class="kids-market-hero-price">ر.ي27</span>
            </a>
        @elseif($isToys)
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'ألعاب تعليمية']) }}">
                <img src="{{ asset('assets/images/home/demo3/kkkk.png') }}" alt="ألعاب تعليمية للأطفال" loading="lazy">
                <span class="kids-market-hero-price">ر.ي32</span>
            </a>
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'ألعاب أطفال']) }}">
                <img src="{{ asset('assets/images/home/demo3/category_3.png') }}" alt="ألعاب أطفال ممتعة" loading="lazy">
                <span class="kids-market-hero-price">ر.ي46</span>
            </a>
        @elseif($isGifts)
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'هدايا أطفال']) }}">
                <img src="{{ asset('assets/images/home/demo3/category_2.png') }}" alt="هدايا أطفال" loading="lazy">
                <span class="kids-market-hero-price">ر.ي25</span>
            </a>
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'هدايا مواليد']) }}">
                <img src="{{ asset('assets/images/home/demo3/Cute and Adorable Babies with Cute Smile.jpg') }}" alt="هدايا مواليد" loading="lazy">
                <span class="kids-market-hero-price">ر.ي39</span>
            </a>
        @elseif($isMother)
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'مستلزمات الأمومة']) }}">
                <img src="{{ asset('assets/images/home/demo3/Strollers, Pushchairs, Prams & Travel systems Archives _ Itty Bitty.jpg') }}" alt="مستلزمات الأمومة" loading="lazy">
                <span class="kids-market-hero-price">ر.ي58</span>
            </a>
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'شنط ومستلزمات الرضع']) }}">
                <img src="{{ asset('assets/images/home/demo3/category_5.png') }}" alt="شنط ومستلزمات الرضع" loading="lazy">
                <span class="kids-market-hero-price">ر.ي34</span>
            </a>
        @elseif($isCare)
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'العناية بالبشرة للأطفال']) }}">
                <img src="{{ asset('assets/images/home/demo3/category_6.png') }}" alt="العناية بالبشرة للأطفال" loading="lazy">
                <span class="kids-market-hero-price">ر.ي22</span>
            </a>
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'صحة وعناية الأطفال']) }}">
                <img src="{{ asset('assets/images/home/demo3/Cute and Adorable Babies with Cute Smile.jpg') }}" alt="صحة وعناية الأطفال" loading="lazy">
                <span class="kids-market-hero-price">ر.ي31</span>
            </a>
        @elseif($isAccessories)
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'اكسسوارات أطفال']) }}">
                <img src="{{ asset('assets/images/home/demo3/category_7.png') }}" alt="اكسسوارات أطفال" loading="lazy">
                <span class="kids-market-hero-price">ر.ي18</span>
            </a>
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'شنط واكسسوارات أطفال']) }}">
                <img src="{{ asset('assets/images/home/demo3/category_9.jpg') }}" alt="شنط واكسسوارات أطفال" loading="lazy">
                <span class="kids-market-hero-price">ر.ي26</span>
            </a>
        @elseif($isShoes)
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'أحذية بنات']) }}">
                <img src="{{ asset('assets/images/products/Flower print sandals for baby girls.jpg') }}" alt="أحذية بنات" loading="lazy">
                <span class="kids-market-hero-price">ر.ي42</span>
            </a>
            <a class="kids-market-hero-product" href="{{ route('shop.search', ['search' => 'أحذية أولاد']) }}">
                <img src="{{ asset('assets/images/products/Mamas & Papas Unisex Baby Bear Booties - Sand.jpg') }}" alt="أحذية أولاد" loading="lazy">
                <span class="kids-market-hero-price">ر.ي31</span>
            </a>
        @else
            <a class="kids-market-hero-product" href="{{ route('shop.index') }}">
                <img src="{{ asset('assets/images/home/demo3/Autumn_Winter Children Boots Boys Girls Leather Martin Boots Plush Fashion Waterproof Non-slip Warm Kids Boots Shoes 21-36 210308.jpg') }}" alt="أحذية أطفال" loading="lazy">
                <span class="kids-market-hero-price">ر.ي29</span>
            </a>
            <a class="kids-market-hero-product" href="{{ route('shop.index') }}">
                <img src="{{ asset('assets/images/home/demo3/5pairs Toddler Girls Cartoon Graphic Socks.jpg') }}" alt="جوارب أطفال" loading="lazy">
                <span class="kids-market-hero-price">ر.ي12</span>
            </a>
        @endif
    @endforelse
</section>
@endif
@endif

@if($showBenefits)
@if($offerStripCampaigns->isNotEmpty())
    <x-marketing.home-tab-offer-strip :campaigns="$offerStripCampaigns" />
@else
<section class="kids-market-panel kids-market-benefits" dir="rtl" aria-label="مزايا التسوق">
    <div class="kids-market-benefit">
        <i class="bi bi-truck"></i>
        <span>
            <b>شحن مجاني</b>
            <span>على الطلبات المؤهلة</span>
        </span>
    </div>
    <a class="kids-market-benefit" href="{{ route('shop.offers') }}">
        <i class="bi bi-lightning-charge-fill"></i>
        <span>
            <b>عروض سريعة</b>
            <span>شاهد المزيد</span>
        </span>
    </a>
</section>
@endif
@endif

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.kids-market-tabs').forEach(function (tabs) {
                    const activeTab = tabs.querySelector('.kids-market-tab.is-active');

                    if (activeTab) {
                        activeTab.scrollIntoView({ block: 'nearest', inline: 'center' });
                    }
                });

                document.querySelectorAll('[data-hero-slider]').forEach(function (slider) {
                    const slides = Array.from(slider.querySelectorAll('[data-hero-slide]'));
                    const dots = Array.from(slider.querySelectorAll('[data-hero-dot]'));
                    const prev = slider.querySelector('[data-hero-prev]');
                    const next = slider.querySelector('[data-hero-next]');
                    const delay = parseInt(slider.dataset.autoplay || '4500', 10);
                    let index = slides.findIndex((slide) => slide.classList.contains('is-active'));
                    let timer = null;

                    if (slides.length <= 1) {
                        return;
                    }

                    index = index >= 0 ? index : 0;

                    const show = function (targetIndex) {
                        index = (targetIndex + slides.length) % slides.length;

                        slides.forEach(function (slide, slideIndex) {
                            const active = slideIndex === index;
                            slide.classList.toggle('is-active', active);
                            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
                        });

                        dots.forEach(function (dot, dotIndex) {
                            const active = dotIndex === index;
                            dot.classList.toggle('is-active', active);
                            dot.setAttribute('aria-current', active ? 'true' : 'false');
                        });
                    };

                    const stop = function () {
                        if (timer) {
                            window.clearInterval(timer);
                            timer = null;
                        }
                    };

                    const start = function () {
                        stop();
                        timer = window.setInterval(function () {
                            show(index + 1);
                        }, delay);
                    };

                    prev?.addEventListener('click', function () {
                        show(index - 1);
                        start();
                    });

                    next?.addEventListener('click', function () {
                        show(index + 1);
                        start();
                    });

                    dots.forEach(function (dot) {
                        dot.addEventListener('click', function () {
                            show(parseInt(dot.dataset.heroDot, 10));
                            start();
                        });
                    });

                    slider.addEventListener('pointerenter', stop);
                    slider.addEventListener('pointerleave', start);
                    slider.addEventListener('focusin', stop);
                    slider.addEventListener('focusout', start);

                    show(index);
                    start();
                });
            });
        </script>
    @endpush
@endonce
