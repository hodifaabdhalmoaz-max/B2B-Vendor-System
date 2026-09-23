<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | application. These settings help protect against common attacks.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Two Factor Authentication
    |--------------------------------------------------------------------------
    */
    'two_factor' => [
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        'issuer' => env('TWO_FACTOR_ISSUER', config('app.name')),
        'window' => 1, // Time window for TOTP validation
        'recovery_codes_count' => 8,
        'required_for_admin' => true,
        'backup_codes_count' => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | Login Attempt Protection
    |--------------------------------------------------------------------------
    */
    'login_attempts' => [
        'max_attempts_per_ip' => env('RATE_LIMIT_LOGIN', 10),
        'max_attempts_per_email' => 5,
        'lockout_duration' => 15, // minutes
        'global_limit' => env('RATE_LIMIT_GENERAL', 100),
        'global_window' => 5, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'enabled' => env('SECURITY_HEADERS_ENABLED', true),
        'force_https' => env('FORCE_HTTPS', false),
        'hsts_max_age' => env('HSTS_MAX_AGE', 31536000),
        'csp_enabled' => env('CSP_ENABLED', true),
        'xss_protection' => env('XSS_PROTECTION', true),
        'csp_report_uri' => '/security/csp-report',
        'expect_ct_max_age' => 86400, // 1 day
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Security
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
        'max_age_days' => 90, // Force password change after 90 days
        'history_count' => 5, // Remember last 5 passwords
        'max_attempts' => 5,
        'lockout_duration' => 900, // seconds (15 minutes)
        'hash_rounds' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'encrypt' => env('SESSION_ENCRYPT', true),
        'secure_cookie' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => env('SESSION_HTTP_ONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'strict'),
        'lifetime' => env('SESSION_LIFETIME', 120), // minutes
        'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
        'regenerate_on_login' => true,
        'regenerate_frequency' => 15, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    */
    'csrf' => [
        'enabled' => true,
        'token_lifetime' => 3600, // seconds
        'regenerate_on_error' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'enabled' => true,
        'store' => 'redis',
        'limits' => [
            'api' => [
                'requests' => 60,
                'per_minutes' => 1,
            ],
            'auth' => [
                'requests' => 5,
                'per_minutes' => 1,
            ],
            'checkout' => [
                'requests' => 3,
                'per_minutes' => 1,
            ],
            'admin' => [
                'requests' => 10,
                'per_minutes' => 1,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_size' => 10240, // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'],
        'scan_for_malware' => true,
        'quarantine_suspicious' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist/Blacklist
    |--------------------------------------------------------------------------
    */
    'ip_filtering' => [
        'enabled' => false,
        'whitelist' => [],
        'blacklist' => [],
        'admin_whitelist' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => true,
        'log_failed_logins' => true,
        'log_admin_actions' => true,
        'log_sensitive_operations' => true,
        'retention_days' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'algorithm' => 'AES-256-CBC',
        'key_rotation_days' => 90,
        'encrypt_database_fields' => ['credit_card', 'ssn', 'phone'],
    ],
];
