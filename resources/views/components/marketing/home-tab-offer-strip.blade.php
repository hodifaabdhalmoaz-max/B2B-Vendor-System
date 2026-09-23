@props([
    'campaigns' => collect(),
])

@php
    $items = collect($campaigns)->flatMap(function ($campaign) {
        $assets = $campaign->assets
            ->where('is_active', true)
            ->whereIn('role', ['promo_card', 'circle_item'])
            ->sortBy('sort_order')
            ->map(fn ($asset) => [
                'title' => $asset->title,
                'subtitle' => $asset->subtitle ?: $asset->price_text,
                'url' => $asset->resolvedUrl(),
                'icon' => 'bi-lightning-charge-fill',
            ]);

        if ($assets->isNotEmpty()) {
            return $assets;
        }

        return [[
            'title' => $campaign->creative?->title ?: $campaign->name,
            'subtitle' => $campaign->creative?->subtitle,
            'url' => $campaign->creative?->resolvedUrl() ?? route('shop.index'),
            'icon' => 'bi-truck',
        ]];
    })->take(2)->values();
@endphp

@if($items->isNotEmpty())
    <section class="kids-market-panel kids-market-benefits" dir="rtl" aria-label="مزايا التسوق">
        @foreach($items as $index => $item)
            <a class="kids-market-benefit" href="{{ $item['url'] }}">
                <i class="bi {{ $item['icon'] }}"></i>
                <span>
                    <b>{{ $item['title'] }}</b>
                    @if($item['subtitle'])
                        <span>{{ $item['subtitle'] }}</span>
                    @endif
                </span>
            </a>
        @endforeach
    </section>
@endif
