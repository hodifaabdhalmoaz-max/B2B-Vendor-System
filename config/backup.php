<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all the backup-related configuration options for
    | the application. These settings control how backups are created,
    | stored, and managed.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Backup Storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'local_path' => storage_path('app/backups'),
        'cloud_disk' => env('BACKUP_CLOUD_DISK', 's3'),
        'cloud_path' => env('BACKUP_CLOUD_PATH', 'backups'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention Policy
    |--------------------------------------------------------------------------
    */
    'retention' => [
        'local_days' => env('BACKUP_LOCAL_RETENTION_DAYS', 7),
        'cloud_days' => env('BACKUP_CLOUD_RETENTION_DAYS', 30),
        'max_local_files' => env('BACKUP_MAX_LOCAL_FILES', 10),
        'max_cloud_files' => env('BACKUP_MAX_CLOUD_FILES', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Backup Settings
    |--------------------------------------------------------------------------
    */
    'database' => [
        'enabled' => true,
        'compress' => env('BACKUP_DB_COMPRESS', true),
        'encrypt' => env('BACKUP_DB_ENCRYPT', false),
        'single_transaction' => true,
        'include_routines' => true,
        'include_triggers' => true,
        'add_drop_table' => true,
        'timeout' => 300, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Files Backup Settings
    |--------------------------------------------------------------------------
    */
    'files' => [
        'enabled' => true,
        'include' => [
            'app',
            'config',
            'database/migrations',
            'database/seeders',
            'public/uploads',
            'resources',
            'routes',
            'storage/app/public',
            'lang',
        ],
        'exclude' => [
            'node_modules',
            'vendor',
            '.git',
            '.env*',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'bootstrap/cache',
            '*.log',
            'temp',
            'tmp',
        ],
        'compress' => true,
        'verify_integrity' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Settings
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'enabled' => env('BACKUP_ENCRYPTION_ENABLED', false),
        'key' => env('BACKUP_ENCRYPTION_KEY'),
        'cipher' => 'AES-256-CBC',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATIONS_ENABLED', true),
        'mail' => [
            'enabled' => true,
            'to' => env('BACKUP_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS')),
            'on_success' => true,
            'on_failure' => true,
        ],
        'slack' => [
            'enabled' => env('BACKUP_SLACK_ENABLED', false),
            'webhook_url' => env('BACKUP_SLACK_WEBHOOK'),
            'channel' => env('BACKUP_SLACK_CHANNEL', '#backups'),
            'username' => env('BACKUP_SLACK_USERNAME', 'Backup Bot'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Schedule Settings
    |--------------------------------------------------------------------------
    */
    'schedule' => [
        'database' => [
            'enabled' => true,
            'frequency' => 'daily', // daily, hourly, weekly
            'time' => '02:00', // for daily backups
            'timezone' => 'Asia/Riyadh',
        ],
        'files' => [
            'enabled' => true,
            'frequency' => 'weekly', // daily, weekly, monthly
            'day' => 'sunday', // for weekly backups
            'time' => '03:00',
            'timezone' => 'Asia/Riyadh',
        ],
        'full' => [
            'enabled' => true,
            'frequency' => 'weekly',
            'day' => 'saturday',
            'time' => '01:00',
            'timezone' => 'Asia/Riyadh',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Check Settings
    |--------------------------------------------------------------------------
    */
    'health_check' => [
        'enabled' => true,
        'max_age_hours' => 25, // Alert if last backup is older than this
        'min_size_mb' => 1, // Alert if backup is smaller than this
        'check_integrity' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloud Storage Settings
    |--------------------------------------------------------------------------
    */
    'cloud' => [
        's3' => [
            'enabled' => env('BACKUP_S3_ENABLED', false),
            'bucket' => env('BACKUP_S3_BUCKET'),
            'region' => env('BACKUP_S3_REGION', 'us-east-1'),
            'storage_class' => 'STANDARD_IA', // STANDARD, STANDARD_IA, GLACIER
        ],
        'google' => [
            'enabled' => env('BACKUP_GOOGLE_ENABLED', false),
            'bucket' => env('BACKUP_GOOGLE_BUCKET'),
            'project_id' => env('BACKUP_GOOGLE_PROJECT_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Settings
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => true,
        'log_level' => 'info',
        'metrics' => [
            'backup_duration' => true,
            'backup_size' => true,
            'success_rate' => true,
        ],
        'alerts' => [
            'backup_failed' => true,
            'backup_too_old' => true,
            'backup_too_small' => true,
            'storage_full' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'verify_checksums' => true,
        'sign_backups' => false,
        'quarantine_failed' => true,
        'audit_access' => true,
    ],
];
