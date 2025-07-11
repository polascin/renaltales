<?php

/**
 * Development Environment Configuration
 * 
 * This configuration is optimized for development environment
 * with debug enabled, verbose logging, and development-friendly settings.
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

return [
    // Application Settings
    'app' => [
        'name' => 'Renal Tales - Development',
        'version' => '2025.v1.0',
        'environment' => 'development',
        'debug' => true,
        'timezone' => 'Europe/Bratislava',
        'charset' => 'UTF-8',
        'locale' => 'sk_SK',
        'url' => 'http://localhost/renaltales',
    ],

    // Database Settings
    'database' => [
        'default' => 'mysql',
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', 'mariadb114.r6.websupport.sk'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'SvwfeoXW'),
                'username' => env('DB_USERNAME', 'by80b9pH'),
                'password' => env('DB_PASSWORD', 'WsVZOl#;D07ju~0@_dF@'),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
                'prefix' => env('DB_PREFIX', ''),
                'strict' => false, // More lenient for development
                'engine' => null,
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            ],
        ],
    ],

    // Security Settings (Relaxed for development)
    'security' => [
        'csrf_protection' => true,
        'session_lifetime' => 14400, // 4 hours for development
        'password_hash_algo' => PASSWORD_DEFAULT,
        'password_min_length' => 6, // Shorter for development
        'max_login_attempts' => 10, // More attempts for development
        'lockout_duration' => 300, // 5 minutes
        'secure_cookies' => false, // HTTP allowed in development
        'rate_limiting' => false, // Disabled for development
    ],

    // Logging Settings (Verbose for development)
    'logging' => [
        'default' => 'file',
        'channels' => [
            'file' => [
                'driver' => 'file',
                'path' => dirname(__DIR__, 2) . '/storage/logs/development.log',
                'level' => 'debug', // All messages
                'max_files' => 10,
                'max_size' => '50MB',
                'format' => '[%datetime%] %channel%.%level_name%: %message% %context% %extra%',
            ],
            'error' => [
                'driver' => 'file',
                'path' => dirname(__DIR__, 2) . '/storage/logs/error.log',
                'level' => 'error',
            ],
            'sql' => [
                'driver' => 'file',
                'path' => dirname(__DIR__, 2) . '/storage/logs/sql.log',
                'level' => 'debug',
            ],
        ],
    ],

    // Cache Settings (Simple file cache for development)
    'cache' => [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => dirname(__DIR__, 2) . '/storage/cache',
                'ttl' => 300, // 5 minutes
            ],
        ],
        'prefix' => 'renaltales_dev_',
    ],

    // Session Settings
    'session' => [
        'driver' => 'file',
        'lifetime' => 240, // 4 hours in minutes
        'expire_on_close' => false,
        'encrypt' => false,
        'files' => dirname(__DIR__, 2) . '/storage/sessions',
        'connection' => null,
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100],
        'cookie' => 'renal_tales_dev_session',
        'path' => '/',
        'domain' => null,
        'secure' => false, // HTTP allowed in development
        'http_only' => true,
        'same_site' => 'lax',
    ],

    // Mail Settings (Log driver for development)
    'mail' => [
        'default' => 'log',
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
            'log' => [
                'transport' => 'log',
                'channel' => 'mail',
                'path' => dirname(__DIR__, 2) . '/storage/logs/mail.log',
            ],
        ],
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'noreply@renaltales.local'),
            'name' => env('MAIL_FROM_NAME', 'Renal Tales Dev'),
        ],
    ],

    // File Upload Settings (Relaxed for development)
    'upload' => [
        'max_file_size' => '10MB',
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'csv'],
        'upload_path' => dirname(__DIR__, 2) . '/storage/uploads',
        'temp_path' => dirname(__DIR__, 2) . '/storage/temp',
    ],

    // Error Handling
    'errors' => [
        'display_errors' => true,
        'log_errors' => true,
        'error_reporting' => E_ALL,
        'whoops' => true, // Enable Whoops error handler
    ],

    // Development Tools
    'dev_tools' => [
        'sql_logging' => true,
        'query_debugging' => true,
        'profiling' => true,
        'debug_bar' => true,
        'hot_reload' => true,
    ],

    // Asset Settings
    'assets' => [
        'minify' => false,
        'combine' => false,
        'cache_busting' => false,
        'version' => time(), // Always fresh assets
    ],

    // API Settings
    'api' => [
        'rate_limit' => 1000, // Higher limit for development
        'cors' => [
            'enabled' => true,
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['*'],
        ],
    ],

    // Language Settings
    'language' => [
        'default' => 'sk',
        'fallback' => 'en',
        'supported' => [
            'sk', 'cs', 'de', 'en', 'es', 'fr', 'it', 'ru', 'pl', 'hu',
        ],
        'path' => dirname(__DIR__, 2) . '/resources/lang/',
        'cache' => false, // Disable caching for development
    ],

    // View Settings
    'view' => [
        'paths' => [
            dirname(__DIR__, 2) . '/resources/views',
            dirname(__DIR__, 2) . '/views',
        ],
        'compiled' => dirname(__DIR__, 2) . '/storage/cache/views',
        'cache' => false, // Disable view caching for development
    ],
];
