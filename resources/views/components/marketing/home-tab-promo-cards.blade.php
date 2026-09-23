@props([
    'campaigns' => collect(),
])

@php
    $cards = collect($campaigns)
        ->flatMap(fn ($campaign) => $campaign->assets
            ->where('is_active', true)
            ->where('role', 'promo_card')
            ->sortBy('sort_order')
            ->map(fn ($asset) => [
                'title' => $asset->title ?: $campaign->creative?->title,
                'subtitle' => $asset->subtitle ?: $campaign->creative?->subtitle,
                'image' => $asset->imageUrl(),
                'url' => $asset->resolvedUrl(),
            ]))
        ->take(6)
        ->values();
@endphp

@if($cards->isNotEmpty())
    <section class="kids-market-campaign-cards" dir="rtl" aria-label="عروض مختارة">
        @foreach($cards as $card)
            <a class="kids-market-campaign-card" href="{{ $card['url'] }}">
                <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" loading="lazy">
                <span>
                    <b>{{ $card['title'] }}</b>
                    @if($card['subtitle'])
                        <small>{{ $card['subtitle'] }}</small>
                    @endif
                </span>
            </a>
        @endforeach
    </section>
@endif
