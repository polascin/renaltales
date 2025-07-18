<?php

/**
 * Renal Tales - Main Application Configuration
 *
 * This file contains the main configuration settings for the application.
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

return [
    // Application Settings
    'app' => [
        'name' => env('APP_NAME', 'Renal Tales'),
        'version' => env('APP_VERSION', '2025.v1.0'),
        'environment' => env('APP_ENV', 'development'), // development, testing, production
        'debug' => env('APP_DEBUG', true),
        'url' => env('APP_URL', 'http://localhost/renaltales'),
        'timezone' => env('APP_TIMEZONE', 'Europe/Bratislava'),
        'charset' => 'UTF-8',
        'locale' => env('APP_LOCALE', 'sk_SK'),
    ],

    // Language Settings
    'language' => [
        'default' => 'sk',
        'fallback' => 'en',
        'supported' => [
            'sk',
            'cs',
            'de',
            'en',
            'es',
            'fr',
            'it',
            'ru',
            'pl',
            'hu',
            // Add other supported languages as needed
        ],
        'path' => dirname(__DIR__) . '/resources/lang/',
    ],

    // Security Settings
    'security' => [
        'csrf_protection' => true,
        'session_lifetime' => 7200, // 2 hours in seconds
        'password_hash_algo' => PASSWORD_DEFAULT,
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes in seconds
    ],


    // Logging Settings
    'logging' => [
        'default' => 'file',
        'channels' => [
            'file' => [
                'driver' => 'file',
                'path' => dirname(__DIR__) . '/storage/logs/application.log',
                'level' => 'info',
                'max_files' => 5,
                'max_size' => '10MB',
            ],
            'error' => [
                'driver' => 'file',
                'path' => dirname(__DIR__) . '/storage/logs/error.log',
                'level' => 'error',
            ],
        ],
    ],

    // Cache Settings
    'cache' => [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => dirname(__DIR__) . '/storage/cache',
            ],
        ],
        'prefix' => 'renaltales_cache',
    ],

    // Session Settings
    'session' => [
        'driver' => 'file',
        'lifetime' => 120, // minutes
        'expire_on_close' => false,
        'encrypt' => false,
        'files' => dirname(__DIR__) . '/storage/sessions',
        'connection' => null,
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100],
        'cookie' => 'renal_tales_session',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
    ],

    // Mail Settings
    'mail' => [
        'default' => 'smtp',
        'mailers' => [
            'smtp' => [
                'transport' => 'smtp',
                'host' => env('MAIL_HOST', 'localhost'),
                'port' => env('MAIL_PORT', 587),
                'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'username' => env('MAIL_USERNAME'),
                'password' => env('MAIL_PASSWORD'),
                'timeout' => null,
            ],
        ],
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'noreply@renaltales.local'),
            'name' => 'Renal Tales',
        ],
    ],

    // File Upload Settings
    'upload' => [
        'max_file_size' => '2MB',
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'upload_path' => dirname(__DIR__) . '/storage/uploads',
    ],

    // View Settings
    'view' => [
        'paths' => [
            dirname(__DIR__) . '/views',
        ],
        'compiled' => dirname(__DIR__) . '/storage/cache/views',
    ],
];
