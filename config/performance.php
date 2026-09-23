<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains performance-related configuration options.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Caching Strategy
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('CACHE_ENABLED', true),
        'default_ttl' => env('CACHE_TTL', 3600), // 1 hour
        'tags_enabled' => true,
        
        'strategies' => [
            'products' => [
                'ttl' => 7200, // 2 hours
                'tags' => ['products'],
                'key_prefix' => 'product_',
            ],
            'categories' => [
                'ttl' => 14400, // 4 hours
                'tags' => ['categories'],
                'key_prefix' => 'category_',
            ],
            'brands' => [
                'ttl' => 14400, // 4 hours
                'tags' => ['brands'],
                'key_prefix' => 'brand_',
            ],
            'pages' => [
                'ttl' => 86400, // 24 hours
                'tags' => ['pages'],
                'key_prefix' => 'page_',
            ],
            'settings' => [
                'ttl' => 86400, // 24 hours
                'tags' => ['settings'],
                'key_prefix' => 'setting_',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Optimization
    |--------------------------------------------------------------------------
    */
    'database' => [
        'query_cache' => true,
        'eager_loading' => true,
        'connection_pooling' => false,
        'read_write_split' => false,
        
        'optimizations' => [
            'select_only_needed_columns' => true,
            'use_indexes' => true,
            'avoid_n_plus_one' => true,
            'chunk_large_datasets' => true,
            'use_raw_queries_when_needed' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Optimization
    |--------------------------------------------------------------------------
    */
    'images' => [
        'optimization_enabled' => env('IMAGE_OPTIMIZATION', true),
        'lazy_loading' => true,
        'webp_conversion' => true,
        'responsive_images' => true,
        
        'sizes' => [
            'thumbnail' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
            'large' => [1200, 1200],
        ],
        
        'quality' => [
            'jpeg' => 85,
            'webp' => 80,
            'png' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Asset Optimization
    |--------------------------------------------------------------------------
    */
    'assets' => [
        'minification' => env('ASSET_MINIFICATION', true),
        'compression' => env('ASSET_COMPRESSION', true),
        'versioning' => true,
        'cdn_enabled' => env('CDN_ENABLED', false),
        'cdn_url' => env('CDN_URL'),
        
        'css' => [
            'minify' => true,
            'combine' => true,
            'inline_critical' => true,
        ],
        
        'js' => [
            'minify' => true,
            'combine' => true,
            'defer_loading' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Optimization
    |--------------------------------------------------------------------------
    */
    'session' => [
        'driver_optimization' => true,
        'garbage_collection' => true,
        'compression' => false,
        
        'cleanup' => [
            'enabled' => true,
            'probability' => 2, // 2% chance
            'max_lifetime' => 7200, // 2 hours
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Memory Management
    |--------------------------------------------------------------------------
    */
    'memory' => [
        'monitoring_enabled' => env('MEMORY_MONITORING', true),
        'limit_mb' => env('MEMORY_LIMIT', 256),
        'warning_threshold' => 80, // percentage
        'cleanup_threshold' => 90, // percentage
        
        'optimizations' => [
            'unset_large_variables' => true,
            'garbage_collection' => true,
            'object_pooling' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Optimization
    |--------------------------------------------------------------------------
    */
    'response' => [
        'compression' => [
            'enabled' => true,
            'level' => 6, // 1-9
            'min_size' => 1024, // bytes
        ],
        
        'headers' => [
            'cache_control' => true,
            'etag' => true,
            'last_modified' => true,
            'expires' => true,
        ],
        
        'static_files' => [
            'max_age' => 31536000, // 1 year
            'immutable' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Background Jobs
    |--------------------------------------------------------------------------
    */
    'jobs' => [
        'queue_optimization' => true,
        'batch_processing' => true,
        'priority_queues' => true,
        
        'strategies' => [
            'image_processing' => 'background',
            'email_sending' => 'background',
            'cache_warming' => 'background',
            'analytics_processing' => 'background',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Alerts
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => env('PERFORMANCE_MONITORING', true),
        'slow_query_threshold' => 1000, // milliseconds
        'memory_usage_alerts' => true,
        'response_time_alerts' => true,
        
        'thresholds' => [
            'response_time_warning' => 1000, // ms
            'response_time_critical' => 3000, // ms
            'memory_usage_warning' => 80, // percentage
            'memory_usage_critical' => 95, // percentage
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development vs Production
    |--------------------------------------------------------------------------
    */
    'environment_specific' => [
        'development' => [
            'query_logging' => true,
            'debug_bar' => true,
            'cache_disabled' => false,
            'minification_disabled' => true,
        ],
        
        'production' => [
            'query_logging' => false,
            'debug_bar' => false,
            'cache_enabled' => true,
            'minification_enabled' => true,
            'opcache_enabled' => true,
        ],
    ],

];
