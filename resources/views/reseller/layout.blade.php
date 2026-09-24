<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
    <title>@yield('title', __('Reseller Portal')) — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/reseller.css') }}">
    <script src="{{ asset('assets/js/reseller.js') }}" defer></script>
</head>
<body class="reseller-portal">
    <a class="visually-hidden-focusable" href="#portal-main">{{ __('Skip to content') }}</a>
    <header class="portal-header">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
            <a class="portal-brand" href="{{ route('reseller.index') }}">{{ __('Reseller Portal') }}</a>
            <div class="d-flex gap-2 align-items-center">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('language.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">{{ __('Logout') }}</button></form>
            </div>
        </div>
        <nav class="container portal-nav" aria-label="{{ __('Reseller navigation') }}">
            @foreach(['reseller.index' => 'Dashboard', 'reseller.catalog' => 'Catalog', 'reseller.reservations.index' => 'Reservations', 'reseller.wishlist.index' => 'Wishlist', 'reseller.cart.index' => 'Reservation cart'] as $routeName => $label)
                <a href="{{ route($routeName) }}" @if(request()->routeIs($routeName)) aria-current="page" @endif>{{ __($label) }}</a>
            @endforeach
        </nav>
    </header>
    <main id="portal-main" class="container py-4">
        @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
    <a class="portal-cart-link btn btn-primary" href="{{ route('reseller.cart.index') }}">{{ __('Reservation cart') }}</a>
</body>
</html>
