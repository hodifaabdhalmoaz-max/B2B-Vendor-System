@extends('layouts.app')

@section('content')
    @push('style')
        <link rel="stylesheet" href="{{ asset('assets/css/mobile-market.css') }}?v={{ filemtime(public_path('assets/css/mobile-market.css')) }}" type="text/css">
        <link rel="stylesheet" href="{{ asset('assets/css/message-center.css') }}?v={{ filemtime(public_path('assets/css/message-center.css')) }}" type="text/css">
    @endpush

    <main class="message-center-shell" dir="rtl">
        <header class="message-center-header message-center-header--section">
            <a class="message-center-header__icon" href="{{ route('messages.index') }}" aria-label="رجوع">
                <i class="bi bi-arrow-right"></i>
            </a>

            <h1>{{ $sectionMeta['title'] ?? 'الرسائل' }}</h1>

            <a class="message-center-header__icon" href="{{ route('cart.index') }}" aria-label="السلة">
                <i class="bi bi-cart3"></i>
            </a>
        </header>

        @if($section === 'orders')
            @include('messages.partials.orders', ['orders' => $orders])
        @elseif($section === 'promo')
            @include('messages.partials.promo', ['campaigns' => $campaigns])
        @else
            @include('messages.partials.campaign-list', [
                'campaigns' => $campaigns,
                'emptyLabel' => $section === 'activity' ? 'لا توجد نشاطات حالية' : 'لا توجد أخبار حالية',
            ])
        @endif

        @include('messages.partials.recommendations', ['recommendations' => $recommendations])
    </main>
@endsection

@push('scripts')
    <script>
        document.body.classList.add('message-center-body');
    </script>
@endpush
