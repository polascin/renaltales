<?php

/**
 * Security Configuration
 * 
 * Configuration for AdminSecurityManager and other security components
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

return [
    'admin_security' => [
        // Admin session timeout in seconds (1 hour)
        'session_timeout' => 3600,
        
        // Admin session regeneration interval in seconds (3 minutes)
        'regeneration_interval' => 180,
        
        // Maximum concurrent admin sessions per user
        'max_concurrent_sessions' => 2,
        
        // Require two-factor authentication for admin users
        'require_2fa' => true,
        
        // Enable IP whitelisting for admin access
        'ip_whitelisting' => false,
        
        // Allowed IP addresses for admin access (when whitelisting is enabled)
        'allowed_ips' => [
            // '127.0.0.1',
            // '192.168.1.100',
        ],
    ],
    
    'csrf' => [
        // CSRF token expiration time in seconds
        'expire_time' => 3600,
        
        // Regenerate CSRF token on each use
        'regenerate_on_use' => true,
    ],
    
    'session' => [
        // General session timeout in seconds
        'timeout' => 7200, // 2 hours
        
        // Session regeneration interval in seconds
        'regeneration_interval' => 600, // 10 minutes
        
        // Cookie settings
        'cookie' => [
            'secure' => true, // Only over HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ],
    ],
    
    'password' => [
        // Minimum password length
        'min_length' => 8,
        
        // Maximum password length
        'max_length' => 128,
        
        // Require uppercase letters
        'require_uppercase' => true,
        
        // Require lowercase letters
        'require_lowercase' => true,
        
        // Require numbers
        'require_numbers' => true,
        
        // Require special characters
        'require_special' => true,
        
        // Password history to prevent reuse
        'history_count' => 5,
    ],
    
    'rate_limiting' => [
        // Maximum login attempts per IP per hour
        'max_login_attempts' => 5,
        
        // Maximum API requests per IP per minute
        'max_api_requests' => 60,
        
        // Lockout duration in seconds
        'lockout_duration' => 1800, // 30 minutes
    ],
    
    'security_headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ],
    
    'content_security_policy' => [
        'default-src' => "'self'",
        'script-src' => "'self' 'unsafe-inline' https://cdnjs.cloudflare.com",
        'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
        'font-src' => "'self' https://fonts.gstatic.com",
        'img-src' => "'self' data: https:",
        'connect-src' => "'self'",
        'frame-src' => "'none'",
        'object-src' => "'none'",
        'base-uri' => "'self'",
    ],
    
    'monitoring' => [
        // Enable security event logging
        'enable_logging' => true,
        
        // Log file path (relative to application root)
        'log_file' => 'storage/logs/security.log',
        
        // Alert thresholds
        'alert_thresholds' => [
            'failed_logins' => 10, // Alert after 10 failed logins from same IP
            'privilege_escalation' => 3, // Alert after 3 privilege escalation attempts
            'unusual_patterns' => 5, // Alert after 5 unusual access patterns
        ],
    ],
];
