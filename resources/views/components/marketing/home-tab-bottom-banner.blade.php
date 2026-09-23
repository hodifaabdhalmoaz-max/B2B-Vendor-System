@props([
    'campaigns' => collect(),
])

@php
    $campaign = collect($campaigns)->first();
    $asset = $campaign?->assets
        ->where('is_active', true)
        ->whereIn('role', ['mobile_banner', 'desktop_banner', 'background'])
        ->sortBy('sort_order')
        ->first();
@endphp

@if($campaign)
    <section class="kids-market-bottom-campaign" dir="rtl" aria-label="{{ $campaign->creative?->alt_text ?? $campaign->name }}">
        <a href="{{ $asset?->resolvedUrl() ?? $campaign->creative?->resolvedUrl() ?? route('shop.index') }}">
            @if($asset)
                <img src="{{ $asset->imageUrl() }}" alt="{{ $asset->title ?? $campaign->name }}" loading="lazy">
            @endif
            <span>
                @if($campaign->creative?->subtitle)
                    <small>{{ $campaign->creative->subtitle }}</small>
                @endif
                <b>{{ $campaign->creative?->title ?? $campaign->name }}</b>
                @if($campaign->creative?->cta_text)
                    <em>{{ $campaign->creative->cta_text }}</em>
                @endif
            </span>
        </a>
    </section>
@endif
