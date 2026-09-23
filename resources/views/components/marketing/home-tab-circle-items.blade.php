@props([
    'campaigns' => collect(),
])

@php
    $items = collect($campaigns)
        ->flatMap(fn ($campaign) => $campaign->assets
            ->where('is_active', true)
            ->where('role', 'circle_item')
            ->sortBy('sort_order')
            ->map(fn ($asset) => [
                'title' => $asset->title ?: $campaign->creative?->title,
                'subtitle' => $asset->subtitle ?: $asset->price_text,
                'image' => $asset->imageUrl(),
                'url' => $asset->resolvedUrl(),
            ]))
        ->take(10)
        ->values();
@endphp

@if($items->isNotEmpty())
    <section class="kids-market-campaign-circles" dir="rtl" aria-label="تصنيفات التبويب">
        @foreach($items as $item)
            <a class="kids-market-campaign-circle" href="{{ $item['url'] }}">
                <span><img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy"></span>
                <b>{{ $item['title'] }}</b>
                @if($item['subtitle'])
                    <small>{{ $item['subtitle'] }}</small>
                @endif
            </a>
        @endforeach
    </section>
@endif
