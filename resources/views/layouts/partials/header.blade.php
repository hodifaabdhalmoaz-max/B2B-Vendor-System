<header class="site-header">
    <!-- Top Bar -->
    <div class="top-bar bg-primary text-white py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="top-bar-contact d-flex align-items-center flex-wrap">
                        <div class="me-4 mb-1 mb-md-0">
                            <i data-lucide="phone" class="me-1" style="width: 14px; height: 14px;"></i>
                            <a href="tel:+967777548421" class="text-white text-decoration-none">777548421 967+</a>
                        </div>
                        <div class="me-4 mb-1 mb-md-0">
                            <i data-lucide="mail" class="me-1" style="width: 14px; height: 14px;"></i>
                            <a href="mailto:hodifaabdhalmoaz@gmail.com" class="text-white text-decoration-none">hodifaabdhalmoaz@gmail.com</a>
                        </div>
                        <div class="mb-1 mb-md-0">
                            <i data-lucide="globe" class="me-1" style="width: 14px; height: 14px;"></i>
                            <a href="https://hodifatech.com/" target="_blank" class="text-white text-decoration-none">hodifatech.com</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-end align-items-center">
                        <!-- Language Switcher -->
                        <div class="dropdown me-3">
                            <a class="text-white dropdown-toggle" href="#" role="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ app()->getLocale() == 'ar' ? 'العربية' : 'English' }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                <li><a class="dropdown-item" href="{{ route('language.switch', 'ar') }}">العربية</a></li>
                                <li><a class="dropdown-item" href="{{ route('language.switch', 'en') }}">English</a></li>
                            </ul>
                        </div>

                        <!-- Social Media Links -->
                        <div class="social-links d-flex">
                            <!-- Developer Links -->
                            <div class="me-3">
                                <small class="text-white-50 d-block mb-1">المطور:</small>
                                <a href="https://www.facebook.com/share/1E3T83a8KD/" target="_blank" class="text-white me-1" title="Facebook"><i data-lucide="facebook" style="width: 14px; height: 14px;"></i></a>
                                <a href="https://www.linkedin.com/in/hodifa-al-hodify-30644b289" target="_blank" class="text-white me-1" title="LinkedIn"><i data-lucide="linkedin" style="width: 14px; height: 14px;"></i></a>
                                <a href="https://x.com/moaz_abdh" target="_blank" class="text-white me-1" title="Twitter"><i data-lucide="twitter" style="width: 14px; height: 14px;"></i></a>
                                <a href="https://github.com/HA1234098765" target="_blank" class="text-white me-1" title="GitHub"><i data-lucide="github" style="width: 14px; height: 14px;"></i></a>
                                <a href="https://wa.me/qr/D74HW7MGE5RIK1" target="_blank" class="text-white" title="WhatsApp"><i data-lucide="message-circle" style="width: 14px; height: 14px;"></i></a>
                            </div>
                            <!-- Store Links -->
                            <div>
                                <small class="text-white-50 d-block mb-1">المتجر:</small>
                                <a href="https://www.facebook.com/profile.php?id=61558122398516&mibextid=ZbWKwL" target="_blank" class="text-white me-1" title="Facebook"><i data-lucide="facebook" style="width: 14px; height: 14px;"></i></a>
                                <a href="https://www.instagram.com/dunya_alatfaal/profilecard/?igsh=MTd1Y2ZrazBsanAyMA==" target="_blank" class="text-white me-1" title="Instagram"><i data-lucide="instagram" style="width: 14px; height: 14px;"></i></a>
                                <a href="https://wa.me/message/R74CYLSGZQD7C1" target="_blank" class="text-white" title="WhatsApp Business"><i data-lucide="message-circle" style="width: 14px; height: 14px;"></i></a>
                            </div>
                            <a href="https://github.com/HA1234098765" target="_blank" class="text-white"><i data-lucide="github" style="width: 14px; height: 14px;"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div class="main-header py-3">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-lg-3 col-md-4 col-6">
                    <div class="logo">
                        <a href="{{ route('home.index') }}">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}" class="img-fluid">
                        </a>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="col-lg-5 col-md-4 d-none d-md-block">
                    <form action="{{ route('shop.search') }}" method="GET" class="search-form">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search_placeholder') }}" value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="search" style="width: 16px; height: 16px;"></i> {{ __('messages.search') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Header Actions -->
                <div class="col-lg-4 col-md-4 col-6">
                    <div class="header-actions d-flex justify-content-end align-items-center">
                        <!-- Mobile Search Toggle -->
                        <div class="d-md-none me-3">
                            <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSearch" aria-expanded="false">
                                <i data-lucide="search" style="width: 20px; height: 20px;"></i>
                            </button>
                        </div>

                        <!-- Wishlist -->
                        <div class="me-3">
                            <a href="{{ route('wishlist.index') }}" class="{{ Route::is('wishlist.index') ? 'text-gold' : 'text-dark' }} position-relative">
                                <i data-lucide="heart" style="width: 20px; height: 20px;"></i>
                                @if(Cart::instance('wishlist')->content()->count() > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ Cart::instance('wishlist')->content()->count() }}
                                </span>
                                @endif
                            </a>
                        </div>

                        <!-- Cart -->
                        <div class="me-3">
                            <a href="{{ route('cart.index') }}" class="{{ Route::is('cart.index') ? 'text-gold' : 'text-dark' }} position-relative">
                                <i data-lucide="shopping-cart" style="width: 20px; height: 20px;"></i>
                                @if(Cart::instance('cart')->content()->count() > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ Cart::instance('cart')->content()->count() }}
                                </span>
                                @endif
                            </a>
                        </div>

                        <!-- User Account -->
                        <div class="dropdown">
                            <a class="text-dark dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i data-lucide="user" style="width: 20px; height: 20px;"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                @auth
                                <li><a class="dropdown-item" href="#">{{ __('messages.dashboard') }}</a></li>
                                <li><a class="dropdown-item" href="#">{{ __('messages.user_orders') }}</a></li>
                                <li><a class="dropdown-item" href="#">{{ __('messages.user_wishlist') }}</a></li>
                                <li><a class="dropdown-item" href="#">{{ __('messages.user_profile') }}</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                @if(auth()->check() && auth()->user()->utype === 'ADM')
                                <li><a class="dropdown-item" href="#">{{ __('messages.admin') }}</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                @endif
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">{{ __('messages.logout') }}</button>
                                    </form>
                                </li>
                                @else
                                <li><a class="dropdown-item" href="{{ route('login') }}">{{ __('messages.login') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('register') }}">{{ __('messages.register') }}</a></li>
                                @endauth
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Search Collapse -->
            <div class="collapse mt-3 d-md-none" id="mobileSearch">
                <form action="{{ route('shop.search') }}" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search_placeholder') }}" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.index') ? 'active' : '' }}" href="{{ route('home.index') }}">{{ __('messages.home') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('shop.index') ? 'active' : '' }}" href="{{ route('shop.index') }}">{{ __('messages.shop') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('categories.index') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                            {{ __('messages.product_category') ?? 'الفئات' }}
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="brandsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __('messages.product_brand') }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="brandsDropdown">
                            @if(isset($brands))
                            @foreach($brands as $brand)
                            <li><a class="dropdown-item" href="{{ route('shop.brand', $brand->slug) }}">{{ $brand->name }}</a></li>
                            @endforeach
                            @endif
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">{{ __('messages.about') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">{{ __('messages.contact') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>