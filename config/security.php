<?php

declare(strict_types=1);

/**
 * Security Configuration for RenalTales
 *
 * This file contains configuration for various security features including
 * CSRF protection, XSS prevention, rate limiting, and password hashing.
 *
 * @package RenalTales
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */

return [
    // CSRF Protection Settings
    'csrf' => [
        'enabled' => true,
        'token_name' => '_csrf_token',
        'expire_time' => 3600, // 1 hour
        'regenerate_on_use' => true,
        'same_site' => 'strict',
        'secure' => filter_var($_ENV['APP_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'http_only' => true,
    ],

    // XSS Protection Settings
    'xss' => [
        'enabled' => true,
        'allowed_tags' => [
            'p', 'br', 'strong', 'em', 'u', 'ol', 'ul', 'li', 'a', 'img',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'code', 'pre'
        ],
        'allowed_attributes' => [
            'a' => ['href', 'title', 'target'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'blockquote' => ['cite'],
            'code' => ['class'],
            'pre' => ['class']
        ],
        'auto_sanitize' => true,
        'strip_dangerous_tags' => true,
    ],

    // Rate Limiting Configuration
    'rate_limiting' => [
        'enabled' => true,
        'storage' => 'file', // file, redis, database
        'storage_path' => APP_ROOT . '/storage/rate_limits',
        'limits' => [
            'login' => [
                'requests' => 5,
                'window' => 300, // 5 minutes
                'lockout_duration' => 900, // 15 minutes
            ],
            'api' => [
                'requests' => 60,
                'window' => 60, // 1 minute
                'lockout_duration' => 300, // 5 minutes
            ],
            'password_reset' => [
                'requests' => 3,
                'window' => 3600, // 1 hour
                'lockout_duration' => 3600, // 1 hour
            ],
            'form_submission' => [
                'requests' => 20,
                'window' => 300, // 5 minutes
                'lockout_duration' => 600, // 10 minutes
            ],
        ],
    ],

    // Password Hashing Configuration (Argon2)
    'password_hashing' => [
        'algorithm' => PASSWORD_ARGON2ID,
        'options' => [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 parallel threads
        ],
        'legacy_support' => true, // Support for bcrypt migration
        'min_length' => 8,
        'max_length' => 128,
        'require_special_chars' => true,
        'require_numbers' => true,
        'require_uppercase' => true,
        'require_lowercase' => true,
    ],

    // Content Security Policy
    'csp' => [
        'enabled' => true,
        'report_only' => false,
        'directives' => [
            'default-src' => ["'self'"],
            'script-src' => [
                "'self'",
                "'unsafe-inline'", // Remove in production
                'https://cdn.jsdelivr.net',
                'https://cdnjs.cloudflare.com',
                'https://cdn.tiny.cloud'
            ],
            'style-src' => [
                "'self'",
                "'unsafe-inline'",
                'https://cdn.jsdelivr.net',
                'https://cdnjs.cloudflare.com'
            ],
            'img-src' => [
                "'self'",
                'data:',
                'https:'
            ],
            'font-src' => [
                "'self'",
                'https://cdnjs.cloudflare.com'
            ],
            'connect-src' => ["'self'"],
            'media-src' => ["'self'"],
            'object-src' => ["'none'"],
            'child-src' => ["'none'"],
            'worker-src' => ["'none'"],
            'frame-ancestors' => ["'none'"],
            'form-action' => ["'self'"],
            'base-uri' => ["'self'"],
            'manifest-src' => ["'self'"],
            'report-uri' => ['/security/csp-report'],
        ],
    ],

    // Security Headers
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=(), payment=(), usb=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        'Cross-Origin-Embedder-Policy' => 'require-corp',
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin',
    ],

    // Session Security
    'session' => [
        'cookie_secure' => filter_var($_ENV['APP_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
        'regenerate_id' => true,
        'fingerprint_user_agent' => true,
        'fingerprint_ip' => true,
        'timeout' => 1800, // 30 minutes
        'absolute_timeout' => 7200, // 2 hours
    ],

    // Input Validation
    'input_validation' => [
        'max_input_length' => 10000,
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'sanitize_html' => true,
        'validate_utf8' => true,
        'trim_whitespace' => true,
    ],

    // Logging and Monitoring
    'logging' => [
        'enabled' => true,
        'log_file' => APP_ROOT . '/storage/logs/security.log',
        'log_level' => 'INFO',
        'log_successful_logins' => true,
        'log_failed_logins' => true,
        'log_csrf_violations' => true,
        'log_rate_limit_violations' => true,
        'log_xss_attempts' => true,
        'max_log_size' => 100 * 1024 * 1024, // 100MB
        'log_rotation' => true,
    ],

    // IP Filtering
    'ip_filtering' => [
        'enabled' => false,
        'whitelist' => [],
        'blacklist' => [],
        'check_proxies' => true,
        'trusted_proxies' => [],
    ],

    // Two-Factor Authentication (for future implementation)
    'two_factor' => [
        'enabled' => false,
        'issuer' => 'RenalTales',
        'algorithm' => 'sha1',
        'digits' => 6,
        'period' => 30,
        'window' => 1,
    ],

    // Backup and Recovery
    'backup' => [
        'encrypt_backups' => true,
        'backup_key' => $_ENV['BACKUP_KEY'] ?? '',
        'retention_days' => 30,
    ],
];
