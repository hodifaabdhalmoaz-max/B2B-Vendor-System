@if($campaigns->isEmpty())
    @include('messages.partials.empty-state', ['label' => $emptyLabel ?? 'لا توجد رسائل حالية'])
@else
    <section class="message-center-list" aria-label="رسائل التسويق">
        @foreach($campaigns as $campaign)
            @php
                $creative = $campaign->creative;
                $asset = $campaign->assets->first();
                $url = $asset?->resolvedUrl() ?? $creative?->resolvedUrl() ?? route('shop.index');
            @endphp

            <a class="message-center-news-card" href="{{ $url }}">
                @if($asset)
                    <img src="{{ $asset->imageUrl() }}" alt="{{ $asset->title ?? $creative?->alt_text ?? $campaign->name }}" loading="lazy">
                @else
                    <span class="message-center-news-card__icon">
                        <i class="bi bi-megaphone"></i>
                    </span>
                @endif

                <span>
                    <small>{{ $campaign->created_at?->format('Y/m/d') }}</small>
                    <strong>{{ $creative?->title ?? $campaign->name }}</strong>
                    @if($creative?->description || $creative?->subtitle)
                        <em>{{ $creative->description ?: $creative->subtitle }}</em>
                    @endif
                </span>
            </a>
        @endforeach
    </section>
@endif
