@props([
    'campaign' => null,
    'campaigns' => null,
    'heroThemeClass' => 'kids-market-hero--all',
])

@php
    $campaigns = collect($campaigns ?? ($campaign ? [$campaign] : []))->values();

    $slides = $campaigns->flatMap(function ($campaign) {
        $creative = $campaign->creative;
        $bannerAssets = $campaign->assets
            ->where('is_active', true)
            ->whereIn('role', ['mobile_banner', 'desktop_banner', 'background'])
            ->sortBy('sort_order')
            ->values();

        $heroAssets = $campaign->assets
            ->where('is_active', true)
            ->where('role', 'hero_product')
            ->sortBy('sort_order')
            ->values();

        if ($bannerAssets->isNotEmpty()) {
            return $bannerAssets->map(fn ($bannerAsset) => [
                'campaign' => $campaign,
                'creative' => $creative,
                'background' => $bannerAsset,
                'hero_assets' => $heroAssets->take(2)->values(),
            ]);
        }

        $chunks = $heroAssets->isNotEmpty()
            ? $heroAssets->chunk(2)
            : collect([collect()]);

        return $chunks->map(fn ($assetChunk) => [
            'campaign' => $campaign,
            'creative' => $creative,
            'background' => null,
            'hero_assets' => $assetChunk->values(),
        ]);
    })->values();
@endphp

@if($slides->isNotEmpty())
    <section
        class="kids-market-hero-slider"
        dir="rtl"
        aria-label="إعلانات التبويب"
        data-hero-slider
        data-autoplay="4500"
    >
        <div class="kids-market-hero-track">
            @foreach($slides as $slide)
                @php
                    $campaign = $slide['campaign'];
                    $creative = $slide['creative'];
                    $backgroundAsset = $slide['background'];
                    $heroAssets = collect($slide['hero_assets']);
                    $link = $backgroundAsset?->resolvedUrl() ?? $creative?->resolvedUrl() ?? route('shop.index');
                    $style = collect([
                        $creative?->background_color ? 'background-color: '.$creative->background_color : null,
                        $creative?->text_color ? 'color: '.$creative->text_color : null,
                        $backgroundAsset ? "background-image: url('".$backgroundAsset->imageUrl()."')" : null,
                        $backgroundAsset ? 'background-size: cover' : null,
                        $backgroundAsset ? 'background-position: center' : null,
                    ])->filter()->implode('; ');
                @endphp

                <section
                    class="kids-market-hero {{ $heroThemeClass }} kids-market-hero--campaign kids-market-hero-slide {{ $loop->first ? 'is-active' : '' }}"
                    aria-label="{{ $creative?->alt_text ?? $campaign->name }}"
                    data-hero-slide
                    data-slide-index="{{ $loop->index }}"
                    @if(! $loop->first) aria-hidden="true" @endif
                    @if($style) style="{{ $style }}" @endif
                >
                    <div class="kids-market-hero-copy">
                        @if($creative?->subtitle)
                            <span class="kids-market-hero-kicker">{{ $creative->subtitle }}</span>
                        @endif

                        @if($creative?->title)
                            <h1 class="kids-market-hero-title">
                                {{ $creative->title }}
                                @if($creative?->description)
                                    <span class="kids-market-hero-subtitle">{{ $creative->description }}</span>
                                @endif
                            </h1>
                        @endif

                        <a class="kids-market-hero-btn" href="{{ $link }}">{{ $creative?->cta_text ?: 'تسوق الآن' }}</a>
                    </div>

                    @foreach($heroAssets as $asset)
                        <a class="kids-market-hero-product" href="{{ $asset->resolvedUrl() }}">
                            <img
                                src="{{ $asset->imageUrl() }}"
                                alt="{{ $asset->title ?? $creative?->alt_text ?? $campaign->name }}"
                                loading="lazy"
                                onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                            >
                            @if($asset->price_text)
                                <span class="kids-market-hero-price">{{ $asset->price_text }}</span>
                            @endif
                        </a>
                    @endforeach
                </section>
            @endforeach
        </div>

        @if($slides->count() > 1)
            <button class="kids-market-hero-nav kids-market-hero-nav--prev" type="button" data-hero-prev aria-label="الإعلان السابق">
                <i class="bi bi-chevron-right"></i>
            </button>
            <button class="kids-market-hero-nav kids-market-hero-nav--next" type="button" data-hero-next aria-label="الإعلان التالي">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="kids-market-hero-dots" aria-label="شرائح الإعلانات">
                @foreach($slides as $slide)
                    <button
                        type="button"
                        class="{{ $loop->first ? 'is-active' : '' }}"
                        data-hero-dot="{{ $loop->index }}"
                        aria-label="عرض الإعلان {{ $loop->iteration }}"
                        aria-current="{{ $loop->first ? 'true' : 'false' }}"
                    ></button>
                @endforeach
            </div>
        @endif
    </section>
@endif
