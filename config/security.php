<?php
/**
 * Security Configuration
 * 
 * This file contains security-related configuration settings
 * for authentication, sessions, CSRF protection, and more.
 */

return [
    // Password Hashing
    'password' => [
        'algorithm' => PASSWORD_ARGON2ID,
        'memory_cost' => 1024,
        'time_cost' => 2,
        'threads' => 2,
        'min_length' => 12,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
    ],

    // Session Management
    'session_lifetime' => 3600, // 1 hour in seconds
    'remember_me_lifetime' => 2592000, // 30 days in seconds
    'validate_ip' => true, // Validate IP address in session
    'validate_user_agent' => true, // Validate User Agent in session
    'session_regenerate_interval' => 1800, // 30 minutes

    // Login Throttling
    'max_login_attempts' => 5,
    'lockout_time' => 900, // 15 minutes in seconds
    'progressive_lockout' => true, // Increase lockout time with more attempts
    'ip_lockout_enabled' => true,
    'user_lockout_enabled' => true,

    // Rate Limiting
    'api_rate_limit' => 100, // requests per hour
    'api_rate_window' => 3600, // 1 hour in seconds
    'general_rate_limit' => 1000, // general requests per hour
    'general_rate_window' => 3600,

    // CSRF Protection
    'csrf_token_lifetime' => 3600, // 1 hour in seconds
    'csrf_regenerate_on_auth' => true,
    'skip_csrf_routes' => [
        '/webhooks/*',
        '/api/public/*'
    ],

    // Route Permissions
    'route_permissions' => [
        '/' => 'public',
        '/login' => 'public',
        '/register' => 'public',
        '/forgot-password' => 'public',
        '/reset-password' => 'public',
        '/verify-email' => 'public',
        '/api/public/*' => 'public',
        
        '/dashboard' => 'authenticated',
        '/profile' => 'authenticated',
        '/stories/create' => 'create_stories',
        '/stories/*/edit' => 'create_stories',
        '/stories/*/translate' => 'translate_stories',
        
        '/admin/*' => 'admin',
        '/moderate/*' => 'moderate_content',
        
        '/api/admin/*' => 'admin',
        '/api/moderate/*' => 'moderate_content',
        '/api/translate/*' => 'translate_stories',
    ],

    // 2FA Requirements
    'require_2fa_routes' => [
        '/admin/*',
        '/user/delete',
        '/settings/security',
        '/api/admin/*'
    ],

    // Content Security Policy
    'csp' => [
        'default-src' => "'self'",
        'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
        'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com",
        'img-src' => "'self' data: https: blob:",
        'font-src' => "'self' https://fonts.gstatic.com",
        'connect-src' => "'self'",
        'frame-ancestors' => "'none'",
        'base-uri' => "'self'",
        'form-action' => "'self'"
    ],

    // Security Headers
    'security_headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload'
    ],

    // IP Filtering
    'ip_whitelist' => [
        // Add trusted IP addresses here
        // '127.0.0.1',
        // '::1'
    ],
    
    'ip_blacklist' => [
        // Add blocked IP addresses here
    ],

    // Encryption
    'encryption_key' => env('ENCRYPTION_KEY'), // Set in .env file
    'cipher' => 'AES-256-GCM',

    // File Upload Security
    'upload' => [
        'max_file_size' => 10485760, // 10MB in bytes
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ],
        'virus_scanning_enabled' => false, // Enable if ClamAV is available
        'quarantine_path' => storage_path('quarantine')
    ],

    // Email Security
    'email' => [
        'verify_ssl' => true,
        'allow_self_signed' => false,
        'dmarc_check' => true,
        'spf_check' => true
    ],

    // Logging
    'security_logging' => [
        'enabled' => true,
        'log_failed_logins' => true,
        'log_successful_logins' => true,
        'log_password_changes' => true,
        'log_permission_changes' => true,
        'log_2fa_events' => true,
        'log_session_events' => true,
        'retention_days' => 90
    ],

    // Backup and Recovery
    'backup' => [
        'encrypt_backups' => true,
        'backup_encryption_key' => env('BACKUP_ENCRYPTION_KEY'),
        'verify_backup_integrity' => true
    ],

    // Development/Debug Settings
    'development' => [
        'disable_2fa' => env('DISABLE_2FA', false),
        'disable_rate_limiting' => env('DISABLE_RATE_LIMITING', false),
        'disable_csrf' => env('DISABLE_CSRF', false),
        'allow_weak_passwords' => env('ALLOW_WEAK_PASSWORDS', false)
    ],

    // Cookie Settings
    'cookie' => [
        'secure' => true, // Only send over HTTPS
        'httponly' => true, // Not accessible via JavaScript
        'samesite' => 'Strict', // CSRF protection
        'domain' => null, // Use current domain
        'path' => '/'
    ],

    // API Security
    'api' => [
        'require_https' => true,
        'cors_enabled' => false,
        'cors_origins' => [],
        'cors_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'cors_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'api_key_header' => 'X-API-Key',
        'rate_limit_header' => 'X-RateLimit-Limit',
        'rate_limit_remaining_header' => 'X-RateLimit-Remaining'
    ],

    // Maintenance Mode
    'maintenance' => [
        'enabled' => false,
        'allowed_ips' => [
            '127.0.0.1',
            '::1'
        ],
        'secret' => env('MAINTENANCE_SECRET'),
        'template' => 'maintenance.html'
    ],

    // Security Monitoring
    'monitoring' => [
        'failed_login_threshold' => 10, // Alert after X failed logins
        'suspicious_activity_threshold' => 5,
        'automated_ban_threshold' => 20,
        'alert_email' => env('SECURITY_ALERT_EMAIL'),
        'webhook_url' => env('SECURITY_WEBHOOK_URL')
    ]
];
