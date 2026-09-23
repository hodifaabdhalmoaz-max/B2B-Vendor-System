@extends('layouts.app')

@section('content')
    @push('style')
        <link rel="stylesheet" href="{{ asset('assets/css/mobile-market.css') }}?v={{ filemtime(public_path('assets/css/mobile-market.css')) }}" type="text/css">
        <link rel="stylesheet" href="{{ asset('assets/css/message-center.css') }}?v={{ filemtime(public_path('assets/css/message-center.css')) }}" type="text/css">
    @endpush

    <main class="message-center-shell" dir="rtl">
        <header class="message-center-header">
            <a class="message-center-header__icon" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home.index') }}" aria-label="رجوع">
                <i class="bi bi-arrow-right"></i>
            </a>

            <h1>الرسائل</h1>

            <form method="POST" action="{{ route('messages.clear') }}">
                @csrf
                <button class="message-center-header__icon" type="submit" aria-label="حذف الرسائل">
                    <i class="bi bi-trash3"></i>
                </button>
            </form>
        </header>

        <section class="message-center-shortcuts" aria-label="أقسام الرسائل">
            @foreach($tabs as $tab)
                <a class="message-center-shortcut" href="{{ route('messages.section', $tab['key']) }}">
                    <span class="message-center-shortcut__icon">
                        <i class="bi {{ $tab['icon'] }}"></i>
                    </span>
                    <span>{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </section>

        @include('messages.partials.recommendations', ['recommendations' => $recommendations])
    </main>
@endsection

@push('scripts')
    <script>
        document.body.classList.add('message-center-body');
    </script>
@endpush
