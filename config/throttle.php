<?php

// config/throttle.php
// Centralized throttle configuration — all rate limits in one place

return [

    /*
    |--------------------------------------------------------------------------
    | Web Route Rate Limits
    |--------------------------------------------------------------------------
    | Limits are defined as [max_attempts, decay_minutes].
    | Authenticated users get a multiplier applied to their limits.
    */

    'web' => [
        // Public browsing pages (home, shop, product pages, static pages)
        'public' => [
            'max_attempts' => (int) env('THROTTLE_WEB_PUBLIC', 120),
            'decay_minutes' => 1,
        ],

        // Cart operations (add, remove, update quantity)
        'cart' => [
            'max_attempts' => (int) env('THROTTLE_WEB_CART', 60),
            'decay_minutes' => 1,
        ],

        // Checkout & payment (authenticated only)
        'checkout' => [
            'max_attempts' => (int) env('THROTTLE_WEB_CHECKOUT', 30),
            'decay_minutes' => 1,
        ],

        // Search (both full-page and AJAX quick search)
        'search' => [
            'max_attempts' => (int) env('THROTTLE_WEB_SEARCH', 40),
            'decay_minutes' => 1,
        ],

        // Wishlist operations
        'wishlist' => [
            'max_attempts' => (int) env('THROTTLE_WEB_WISHLIST', 40),
            'decay_minutes' => 1,
        ],

        // User dashboard (authenticated)
        'user_dashboard' => [
            'max_attempts' => (int) env('THROTTLE_WEB_USER', 60),
            'decay_minutes' => 1,
        ],

        // Admin panel (authenticated admins)
        'admin' => [
            'max_attempts' => (int) env('THROTTLE_WEB_ADMIN', 200),
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Rate Limits
    |--------------------------------------------------------------------------
    */

    'api' => [
        // General API endpoints
        'default' => [
            'max_attempts' => (int) env('THROTTLE_API_DEFAULT', 60),
            'decay_minutes' => 1,
        ],

        // Authentication endpoints (login, register)
        'auth' => [
            'max_attempts' => (int) env('THROTTLE_API_AUTH', 10),
            'decay_minutes' => 5,
        ],

        // Product listing / search API
        'products' => [
            'max_attempts' => (int) env('THROTTLE_API_PRODUCTS', 120),
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Multiplier
    |--------------------------------------------------------------------------
    | Authenticated users get this multiplier applied to their rate limits.
    | e.g., 2.0 means they get double the limit of guests.
    */

    'auth_multiplier' => (float) env('THROTTLE_AUTH_MULTIPLIER', 2.0),

    /*
    |--------------------------------------------------------------------------
    | Admin Multiplier
    |--------------------------------------------------------------------------
    | Admin users get this multiplier on top of auth_multiplier.
    */

    'admin_multiplier' => (float) env('THROTTLE_ADMIN_MULTIPLIER', 5.0),

    /*
    |--------------------------------------------------------------------------
    | Bot Detection Settings
    |--------------------------------------------------------------------------
    */

    'bot_protection' => [
        // Enable/disable bot protection middleware
        'enabled' => (bool) env('BOT_PROTECTION_ENABLED', true),

        // Maximum requests per IP within the window before flagging as suspicious
        'suspicious_threshold' => (int) env('BOT_SUSPICIOUS_THRESHOLD', 100),

        // Time window in minutes for counting requests
        'window_minutes' => (int) env('BOT_WINDOW_MINUTES', 1),

        // How long to block a flagged IP (in minutes)
        'block_duration' => (int) env('BOT_BLOCK_DURATION', 15),

        // IPs that are always allowed (whitelisted)
        'whitelisted_ips' => array_filter(
            explode(',', env('BOT_WHITELISTED_IPS', '127.0.0.1,::1'))
        ),

        // User agents that are always blocked
        'blocked_user_agents' => [
            'python-requests',
            'scrapy',
            'httpclient',
            'ahrefsbot',
            'semrushbot',
            'dotbot',
            'mj12bot',
            'blexbot',
        ],

        // User agents that are known good bots (allowed but tracked)
        'good_bots' => [
            'googlebot',
            'bingbot',
            'yandexbot',
            'duckduckbot',
            'facebookexternalhit',
            'twitterbot',
            'linkedinbot',
            'whatsapp',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exponential Backoff Settings
    |--------------------------------------------------------------------------
    | For repeat offenders, the block duration increases exponentially.
    */

    'backoff' => [
        // Enable exponential backoff for repeat offenders
        'enabled' => (bool) env('THROTTLE_BACKOFF_ENABLED', true),

        // Base block duration in minutes
        'base_minutes' => 1,

        // Maximum block duration in minutes (cap)
        'max_minutes' => 60,

        // Multiplier per offense (2 = double each time)
        'multiplier' => 2,

        // How many offenses before triggering backoff
        'offense_threshold' => 3,
    ],
];
