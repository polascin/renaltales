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
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
                'prefix' => env('DB_PREFIX', ''),
                'strict' => false, // More lenient for development
                'engine' => null,
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::ATTR_PERSISTENT => true,
                ],
            ],
        ],
        'pool' => [
            'min_connections' => 5,
            'max_connections' => 20,
            'idle_timeout' => 60,
        ],
    ],

    // Security Settings (Hardened for production)
    'security' => [
        'csrf_protection' => true,
        'session_lifetime' => 3600, // 1 hour
        'password_hash_algo' => PASSWORD_DEFAULT,
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'lockout_duration' => 1800, // 30 minutes
        'secure_cookies' => true,
        'rate_limiting' => true,
        'brute_force_protection' => true,
        'ip_whitelist' => env('IP_WHITELIST', ''),
        'content_security_policy' => [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline'",
            'style-src' => "'self' 'unsafe-inline'",
            'img-src' => "'self' data:",
            'font-src' => "'self'",
            'connect-src' => "'self'",
            'frame-ancestors' => "'none'",
        ],
    ],

    // Logging Settings (Minimal for production)
    'logging' => [
        'default' => 'file',
        'channels' => [
            'file' => [
                'driver' => 'file',
                'path' => dirname(__DIR__, 2) . '/storage/logs/production.log',
                'level' => 'error', // Only errors and above
                'max_files' => 30,
                'max_size' => '100MB',
                'format' => '[%datetime%] %level_name%: %message% %context%',
            ],
            'error' => [
                'driver' => 'file',
                'path' => dirname(__DIR__, 2) . '/storage/logs/error.log',
                'level' => 'error',
            ],
            'security' => [
                'driver' => 'file',
                'path' => dirname(__DIR__, 2) . '/storage/logs/security.log',
                'level' => 'warning',
            ],
        ],
    ],

    // Cache Settings (Redis for production)
    'cache' => [
        'default' => 'redis',
        'stores' => [
            'redis' => [
                'driver' => 'redis',
                'connection' => 'cache',
                'prefix' => 'renaltales_prod_',
                'ttl' => 3600, // 1 hour default
            ],
            'file' => [
                'driver' => 'file',
                'path' => dirname(__DIR__, 2) . '/storage/cache',
                'ttl' => 3600,
            ],
        ],
        'redis' => [
            'client' => 'phpredis',
            'options' => [
                'cluster' => 'redis',
                'prefix' => 'renaltales_cache_',
            ],
            'cache' => [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', '6379'),
                'database' => env('REDIS_CACHE_DB', '1'),
                'persistent' => true,
                'timeout' => 5,
                'retry_interval' => 100,
                'read_timeout' => 60,
            ],
        ],
    ],

    // Session Settings (Redis for production)
    'session' => [
        'driver' => 'redis',
        'lifetime' => 60, // 1 hour in minutes
        'expire_on_close' => false,
        'encrypt' => true,
        'files' => dirname(__DIR__, 2) . '/storage/sessions',
        'connection' => 'session',
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100],
        'cookie' => 'renal_tales_session',
        'path' => '/',
        'domain' => env('SESSION_DOMAIN', null),
        'secure' => true, // HTTPS only
        'http_only' => true,
        'same_site' => 'strict',
    ],

    // Redis Configuration
    'redis' => [
        'client' => 'phpredis',
        'options' => [
            'cluster' => 'redis',
            'prefix' => 'renaltales_',
        ],
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
        'session' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '2'),
        ],
    ],

    // Mail Settings (SMTP for production)
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
                'timeout' => 30,
                'stream' => [
                    'ssl' => [
                        'verify_peer' => true,
                        'verify_peer_name' => true,
                        'allow_self_signed' => false,
                    ],
                ],
            ],
        ],
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'noreply@renaltales.com'),
            'name' => env('MAIL_FROM_NAME', 'Renal Tales'),
        ],
        'queue' => true, // Queue emails for better performance
    ],

    // File Upload Settings (Restricted for production)
    'upload' => [
        'max_file_size' => '5MB',
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'upload_path' => dirname(__DIR__, 2) . '/storage/uploads',
        'temp_path' => dirname(__DIR__, 2) . '/storage/temp',
        'scan_for_viruses' => true,
        'image_optimization' => true,
        'watermark' => true,
    ],

    // Error Handling (Production-safe)
    'errors' => [
        'display_errors' => false,
        'log_errors' => true,
        'error_reporting' => E_ALL & ~E_NOTICE & ~E_DEPRECATED,
        'whoops' => false,
        'error_pages' => [
            404 => '/errors/404.php',
            500 => '/errors/500.php',
            503 => '/errors/503.php',
        ],
    ],

    // Production Optimizations
    'optimizations' => [
        'opcache' => true,
        'gzip_compression' => true,
        'etag_generation' => true,
        'static_file_caching' => true,
        'database_query_cache' => true,
        'view_caching' => true,
        'route_caching' => true,
        'config_caching' => true,
    ],

    // Asset Settings (Optimized for production)
    'assets' => [
        'minify' => true,
        'combine' => true,
        'cache_busting' => true,
        'version' => env('ASSET_VERSION', '1.0.0'),
        'cdn_url' => env('CDN_URL', null),
        'preload' => [
            'css' => true,
            'js' => true,
            'fonts' => true,
        ],
    ],

    // API Settings (Restricted for production)
    'api' => [
        'rate_limit' => 100, // requests per minute
        'cors' => [
            'enabled' => true,
            'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'https://renaltales.com')),
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        ],
        'throttle' => [
            'enabled' => true,
            'max_requests' => 1000,
            'window' => 3600, // per hour
        ],
    ],

    // Language Settings (Cached for production)
    'language' => [
        'default' => 'sk',
        'fallback' => 'en',
        'supported' => [
            'sk', 'cs', 'de', 'en', 'es', 'fr', 'it', 'ru', 'pl', 'hu',
        ],
        'path' => dirname(__DIR__, 2) . '/resources/lang/',
        'cache' => true,
        'cache_ttl' => 86400, // 24 hours
    ],

    // View Settings (Cached for production)
    'view' => [
        'paths' => [
            dirname(__DIR__, 2) . '/resources/views',
            dirname(__DIR__, 2) . '/views',
        ],
        'compiled' => dirname(__DIR__, 2) . '/storage/cache/views',
        'cache' => true,
    ],

    // Monitoring Settings
    'monitoring' => [
        'enabled' => true,
        'health_check' => [
            'enabled' => true,
            'endpoint' => '/health',
            'checks' => [
                'database' => true,
                'cache' => true,
                'storage' => true,
                'mail' => true,
            ],
        ],
        'metrics' => [
            'enabled' => true,
            'endpoint' => '/metrics',
            'auth_required' => true,
        ],
        'alerts' => [
            'enabled' => true,
            'email' => env('ALERT_EMAIL', 'admin@renaltales.com'),
            'slack_webhook' => env('SLACK_WEBHOOK', null),
        ],
    ],

    // Backup Settings
    'backup' => [
        'enabled' => true,
        'schedule' => [
            'database' => '0 2 * * *', // Daily at 2 AM
            'files' => '0 3 * * 0', // Weekly on Sunday at 3 AM
        ],
        'retention' => [
            'daily' => 7,
            'weekly' => 4,
            'monthly' => 12,
        ],
        'storage' => [
            'local' => '/var/backups/renaltales',
            's3' => env('BACKUP_S3_BUCKET', null),
        ],
    ],

    // Performance Settings
    'performance' => [
        'page_cache' => true,
        'query_cache' => true,
        'object_cache' => true,
        'compression' => [
            'enabled' => true,
            'level' => 6,
            'types' => ['text/html', 'text/css', 'application/javascript', 'application/json'],
        ],
        'lazy_loading' => true,
        'image_optimization' => true,
    ],

    // Security Headers
    'security_headers' => [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ],
];
