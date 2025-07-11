<?php

/**
 * Security Configuration
 * 
 * Contains all security-related settings for the application
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

return [
    // Security Manager Settings
    'security_manager' => [
        'csrf_expire_time' => 3600, // 1 hour
        'csrf_regenerate_on_use' => true,
        'xss_protection' => true,
        'content_security_policy' => [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tiny.cloud",
            'style-src' => "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            'img-src' => "'self' data: https:",
            'font-src' => "'self' https://cdnjs.cloudflare.com",
            'connect-src' => "'self'",
            'media-src' => "'self'",
            'object-src' => "'none'",
            'child-src' => "'none'",
            'worker-src' => "'none'",
            'frame-ancestors' => "'none'",
            'form-action' => "'self'",
            'base-uri' => "'self'",
            'manifest-src' => "'self'"
        ],
        'security_headers' => [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
        ]
    ],
    
    // Rate Limiting Settings
    'rate_limiting' => [
        'storage' => 'file',
        'storage_path' => APP_DIR . '/storage/rate_limits',
        'default_limit' => 100,
        'default_window' => 3600,
        'burst_limit' => 10,
        'burst_window' => 60,
        'blocked_duration' => 300,
        'endpoints' => [
            'api/stories' => ['limit' => 60, 'window' => 3600],
            'api/upload' => ['limit' => 20, 'window' => 3600],
            'api/comments' => ['limit' => 30, 'window' => 3600],
            'login' => ['limit' => 5, 'window' => 900],
            'register' => ['limit' => 3, 'window' => 3600],
            'password-reset' => ['limit' => 3, 'window' => 3600],
            'contact' => ['limit' => 10, 'window' => 3600]
        ]
    ],
    
    // File Upload Settings
    'file_upload' => [
        'upload_path' => APP_DIR . '/storage/uploads/',
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'],
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ],
        'forbidden_extensions' => [
            'php', 'php3', 'php4', 'php5', 'phtml', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi',
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'htm', 'html'
        ],
        'max_filename_length' => 100,
        'create_thumbnails' => true,
        'thumbnail_size' => 150,
        'virus_scan' => false,
        'quarantine_path' => APP_DIR . '/storage/quarantine/',
        'scan_command' => 'clamscan',
        'image_quality' => 85,
        'resize_large_images' => true,
        'max_image_width' => 1920,
        'max_image_height' => 1080
    ],
    
    // Input Validation Settings
    'input_validation' => [
        'max_string_length' => 10000,
        'password_min_length' => 8,
        'username_min_length' => 3,
        'username_max_length' => 20,
        'sanitize_html' => true,
        'allowed_html_tags' => [
            'p', 'br', 'strong', 'em', 'u', 'ol', 'ul', 'li', 'a', 
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'code', 'pre'
        ]
    ],
    
    // Session Security Settings
    'session' => [
        'cookie_httponly' => true,
        'cookie_secure' => true, // Set to false for HTTP
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'use_trans_sid' => false,
        'regenerate_interval' => 300, // 5 minutes
        'timeout' => 1800, // 30 minutes
        'name' => 'SECURE_SESSION_ID'
    ],
    
    // Database Security Settings
    'database' => [
        'validate_queries' => true,
        'sanitize_parameters' => true,
        'max_param_length' => 10000,
        'log_suspicious_queries' => true
    ],
    
    // Logging Settings
    'logging' => [
        'log_path' => APP_DIR . '/storage/logs/',
        'log_security_events' => true,
        'log_rate_limit_violations' => true,
        'log_file_uploads' => true,
        'log_level' => 'info', // debug, info, warning, error
        'max_log_size' => 10 * 1024 * 1024, // 10MB
        'log_rotation' => true
    ],
    
    // IP Security Settings
    'ip_security' => [
        'check_ip_changes' => false, // Disable for mobile users
        'whitelist' => [],
        'blacklist' => [],
        'block_tor' => false,
        'block_vpn' => false,
        'geolocation_blocking' => false,
        'allowed_countries' => []
    ],
    
    // Password Security Settings
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => false,
        'max_age' => 90 * 24 * 3600, // 90 days
        'history_count' => 5,
        'lockout_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'hash_algorithm' => 'bcrypt',
        'hash_cost' => 12
    ],
    
    // Two-Factor Authentication
    'two_factor' => [
        'enabled' => false,
        'issuer' => 'Renal Tales',
        'digits' => 6,
        'period' => 30,
        'algorithm' => 'sha1',
        'backup_codes' => true,
        'backup_codes_count' => 10
    ],
    
    // API Security Settings
    'api' => [
        'enable_cors' => true,
        'cors_origins' => ['*'],
        'cors_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'cors_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'require_https' => false,
        'api_key_required' => false,
        'jwt_secret' => 'your-jwt-secret-key-here',
        'jwt_algorithm' => 'HS256',
        'jwt_expiration' => 3600
    ],
    
    // Email Security Settings
    'email' => [
        'verify_ssl' => true,
        'allow_self_signed' => false,
        'timeout' => 30,
        'rate_limit' => 5, // emails per minute
        'blacklist_domains' => [],
        'whitelist_domains' => []
    ],
    
    // Content Security Settings
    'content' => [
        'allow_user_html' => true,
        'html_purifier' => true,
        'content_filtering' => true,
        'profanity_filter' => false,
        'spam_detection' => false,
        'virus_scan_uploads' => false
    ],
    
    // Backup and Recovery Settings
    'backup' => [
        'encrypt_backups' => true,
        'backup_retention' => 30, // days
        'backup_location' => APP_DIR . '/storage/backups/',
        'backup_schedule' => 'daily',
        'include_uploads' => true,
        'compression' => true
    ],
    
    // Monitoring and Alerting
    'monitoring' => [
        'enable_alerts' => true,
        'alert_email' => 'admin@renaltales.com',
        'alert_threshold' => 10, // violations per hour
        'log_retention' => 30, // days
        'realtime_monitoring' => false,
        'security_dashboard' => true
    ],
    
    // Development Settings (only for development)
    'development' => [
        'debug_mode' => defined('DEBUG_MODE') && DEBUG_MODE,
        'display_errors' => defined('DEBUG_MODE') && DEBUG_MODE,
        'log_all_queries' => false,
        'bypass_rate_limits' => false,
        'disable_csrf' => false,
        'allow_weak_passwords' => false
    ]
];
