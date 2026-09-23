<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>@yield('title', $seoMetadata['title'] ?? config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', $seoMetadata['metaDescription'] ?? '')">
    <meta name="keywords" content="@yield('meta_keywords', $seoMetadata['metaKeywords'] ?? '')">
    <meta name="author" content="{{ config('app.name') }}">
    <link rel="canonical" href="{{ $seoMetadata['canonical'] ?? url()->current() }}">
    <meta name="robots" content="{{ $seoMetadata['robots'] ?? 'index, follow' }}">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('title', $seoMetadata['ogTitle'] ?? config('app.name'))">
    <meta property="og:description" content="@yield('meta_description', $seoMetadata['ogDescription'] ?? '')">
    <meta property="og:image" content="{{ $seoMetadata['ogImage'] ?? asset('assets/images/logo.png') }}">
    <meta property="og:url" content="{{ $seoMetadata['ogUrl'] ?? url()->current() }}">
    <meta property="og:type" content="{{ $seoMetadata['ogType'] ?? 'website' }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="{{ $seoMetadata['twitterCard'] ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="@yield('title', $seoMetadata['twitterTitle'] ?? config('app.name'))">
    <meta name="twitter:description" content="@yield('meta_description', $seoMetadata['twitterDescription'] ?? '')">
    <meta name="twitter:image" content="{{ $seoMetadata['twitterImage'] ?? asset('assets/images/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @if(app()->getLocale() == 'ar')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.rtl.css') }}">
    @endif

    @stack('styles')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Arabic Support CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/arabic-support.css') }}">

    <!-- Arabic Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Tajawal:wght@300;400;500;700&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
</head>

<body class="{{ app()->getLocale() == 'ar' ? 'rtl arabic-font' : 'ltr' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Header -->
    @include('layouts.partials.header')

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.partials.footer')

    <!-- Floating WhatsApp Button -->
    <div class="whatsapp-float">
        <div class="dropdown dropup">
            <button class="btn btn-success rounded-circle whatsapp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i data-lucide="message-circle" style="width: 20px; height: 20px;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                <li>
                    <h6 class="dropdown-header">
                        <i data-lucide="message-circle" class="text-success me-2" style="width: 16px; height: 16px;"></i>تواصل معنا عبر واتساب
                    </h6>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item" href="https://wa.me/message/R74CYLSGZQD7C1" target="_blank">
                        <i data-lucide="store" class="text-primary me-2" style="width: 16px; height: 16px;"></i>
                        <div>
                            <strong>واتساب المتجر</strong>
                            <small class="d-block text-muted">للاستفسار عن المنتجات</small>
                        </div>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="https://wa.me/qr/D74HW7MGE5RIK1" target="_blank">
                        <i data-lucide="user" class="text-info me-2" style="width: 16px; height: 16px;"></i>
                        <div>
                            <strong>واتساب المطور</strong>
                            <small class="d-block text-muted">للدعم التقني</small>
                        </div>
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item" href="tel:+967777548421">
                        <i data-lucide="phone" class="text-success me-2" style="width: 16px; height: 16px;"></i>
                        <div>
                            <strong>اتصال مباشر</strong>
                            <small class="d-block text-muted">777548421 967+</small>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <style>
        .whatsapp-float {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
        }

        .whatsapp-btn {
            width: 60px;
            height: 60px;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
            border: none;
            animation: pulse 2s infinite;
        }

        .whatsapp-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(37, 211, 102, 0.6);
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
            }

            50% {
                box-shadow: 0 4px 12px rgba(37, 211, 102, 0.8);
            }

            100% {
                box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
            }
        }

        .dropdown-menu {
            min-width: 280px;
            border: none;
            border-radius: 15px;
        }

        .dropdown-item {
            padding: 12px 16px;
            border-radius: 8px;
            margin: 4px 8px;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            transform: translateX(-5px);
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .whatsapp-float {
                bottom: 15px;
                left: 15px;
            }

            .whatsapp-btn {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .dropdown-menu {
                min-width: 250px;
            }
        }
    </style>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/arabic-support.js') }}"></script>

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    @stack('scripts')

    @yield('scripts')
</body>

</html>