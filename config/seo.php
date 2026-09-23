<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains SEO-related configuration options for the application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Meta Tags
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'title' => 'متجر الأطفال - أفضل ملابس وألعاب الأطفال',
        'description' => 'متجر متخصص في ملابس وألعاب الأطفال عالية الجودة. تسوق الآن واحصل على أفضل العروض والخصومات.',
        'keywords' => 'ملابس أطفال, ألعاب أطفال, متجر أطفال, ملابس أولاد, ملابس بنات, العاب تعليمية',
        'author' => 'Hudhaifa Al-Hudhaifi',
        'robots' => 'index, follow',
        'canonical' => null,
        'og_type' => 'website',
        'og_locale' => 'ar_SA',
        'twitter_card' => 'summary_large_image',
    ],

    /*
    |--------------------------------------------------------------------------
    | Site Information
    |--------------------------------------------------------------------------
    */
    'site' => [
        'name' => 'متجر الأطفال',
        'url' => env('APP_URL', 'https://kids-store.com'),
        'logo' => '/assets/images/logo.png',
        'favicon' => '/assets/images/favicon.ico',
        'theme_color' => '#007bff',
        'language' => 'ar',
        'direction' => 'rtl',
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Media
    |--------------------------------------------------------------------------
    */
    'social' => [
        'facebook' => 'https://facebook.com/kids-store',
        'twitter' => 'https://twitter.com/kids-store',
        'instagram' => 'https://instagram.com/kids-store',
        'linkedin' => 'https://linkedin.com/company/kids-store',
        'youtube' => 'https://youtube.com/c/kids-store',
        'whatsapp' => '+967777548421',
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'email' => 'hodifaabdhalmoaz@gmail.com',
        'phone' => '+967777548421',
        'address' => 'اليمن، صنعاء',
        'postal_code' => '12345',
        'country' => 'Yemen',
        'timezone' => 'Asia/Riyadh',
    ],

    /*
    |--------------------------------------------------------------------------
    | Structured Data (Schema.org)
    |--------------------------------------------------------------------------
    */
    'schema' => [
        'organization' => [
            '@type' => 'Organization',
            'name' => 'متجر الأطفال',
            'url' => env('APP_URL'),
            'logo' => env('APP_URL') . '/assets/images/logo.png',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+967777548421',
                'contactType' => 'customer service',
                'availableLanguage' => 'Arabic',
            ],
            'sameAs' => [
                'https://facebook.com/kids-store',
                'https://twitter.com/kids-store',
                'https://instagram.com/kids-store',
            ],
        ],
        'website' => [
            '@type' => 'WebSite',
            'name' => 'متجر الأطفال',
            'url' => env('APP_URL'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => env('APP_URL') . '/shop?search={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ],
        'store' => [
            '@type' => 'Store',
            'name' => 'متجر الأطفال',
            'image' => env('APP_URL') . '/assets/images/logo.png',
            'telephone' => '+967777548421',
            'email' => 'hodifaabdhalmoaz@gmail.com',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'اليمن، صنعاء',
                'addressCountry' => 'YE',
            ],
            'openingHours' => 'Mo-Su 09:00-22:00',
            'priceRange' => '$$',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap Configuration
    |--------------------------------------------------------------------------
    */
    'sitemap' => [
        'enabled' => env('SEO_ENABLED', true),
        'cache_duration' => 3600, // seconds
        'max_urls' => 50000,
        'priorities' => [
            'home' => 1.0,
            'shop' => 0.9,
            'categories' => 0.8,
            'brands' => 0.7,
            'products' => 0.6,
            'pages' => 0.5,
        ],
        'changefreq' => [
            'home' => 'daily',
            'shop' => 'daily',
            'categories' => 'weekly',
            'brands' => 'weekly',
            'products' => 'weekly',
            'pages' => 'monthly',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Tags Templates
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'product' => [
            'title' => '{product_name} - متجر الأطفال',
            'description' => 'اشتري {product_name} بأفضل سعر من متجر الأطفال. {product_description}',
            'keywords' => '{product_name}, {category_name}, ملابس أطفال, متجر أطفال',
        ],
        'category' => [
            'title' => '{category_name} - متجر الأطفال',
            'description' => 'تسوق أفضل {category_name} للأطفال من متجر الأطفال. جودة عالية وأسعار مناسبة.',
            'keywords' => '{category_name}, ملابس أطفال, متجر أطفال',
        ],
        'brand' => [
            'title' => 'منتجات {brand_name} - متجر الأطفال',
            'description' => 'اكتشف مجموعة {brand_name} المميزة للأطفال في متجر الأطفال.',
            'keywords' => '{brand_name}, ملابس أطفال, متجر أطفال',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics and Tracking
    |--------------------------------------------------------------------------
    */
    'analytics' => [
        'google_analytics' => env('GOOGLE_ANALYTICS_ID'),
        'google_tag_manager' => env('GOOGLE_TAG_MANAGER_ID'),
        'facebook_pixel' => env('FACEBOOK_PIXEL_ID'),
        'google_search_console' => env('GOOGLE_SEARCH_CONSOLE_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance and Optimization
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'lazy_loading' => true,
        'image_optimization' => true,
        'minify_html' => env('APP_ENV') === 'production',
        'compress_css' => env('APP_ENV') === 'production',
        'compress_js' => env('APP_ENV') === 'production',
        'cache_static_assets' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs Configuration
    |--------------------------------------------------------------------------
    */
    'breadcrumbs' => [
        'enabled' => true,
        'separator' => ' > ',
        'home_title' => 'الرئيسية',
        'show_current' => true,
        'schema_markup' => true,
    ],

];
