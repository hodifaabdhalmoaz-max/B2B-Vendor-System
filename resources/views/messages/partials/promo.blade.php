@if($campaigns->isEmpty())
    @include('messages.partials.empty-state', ['label' => 'لا توجد عروض حالية'])
@else
    <section class="message-center-promo-list" aria-label="العروض">
        @foreach($campaigns as $campaign)
            @php
                $creative = $campaign->creative;
                $asset = $campaign->assets->first();
                $url = $asset?->resolvedUrl() ?? $creative?->resolvedUrl() ?? route('shop.index');
                $expiresAt = $campaign->ends_at?->format('Y/m/d');
            @endphp

            <article class="message-center-promo">
                <time>{{ $campaign->created_at?->format('Y/m/d') }}</time>
                <div class="message-center-promo__body">
                    <h2>{{ $creative?->title ?? $campaign->name }}</h2>
                    @if($creative?->description || $creative?->subtitle)
                        <p>{{ $creative->description ?: $creative->subtitle }}</p>
                    @endif

                    <a class="message-center-coupon" href="{{ $url }}">
                        <span class="message-center-coupon__discount">
                            {{ $asset?->price_text ?: ($creative?->cta_text ?: 'عرض') }}
                        </span>
                        <span class="message-center-coupon__content">
                            <strong>{{ $asset?->title ?: ($creative?->subtitle ?: 'قسيمة متجر') }}</strong>
                            @if($asset?->subtitle)
                                <small>{{ $asset->subtitle }}</small>
                            @elseif($expiresAt)
                                <small>ينتهي في {{ $expiresAt }}</small>
                            @endif
                        </span>
                        <span class="message-center-coupon__button">تسوق</span>
                    </a>
                </div>
            </article>
        @endforeach

        <p class="message-center-end">لا توجد عروض أخرى</p>
    </section>
@endif
