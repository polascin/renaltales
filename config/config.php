<?php

return [
    'app' => [
        'name' => 'RenalTales',
        'env' => 'development', // 'production' in production
        'debug' => true, // false in production
        'url' => 'http://localhost/renaltales.test/', // Set in .env', // Update in production
        'timezone' => 'UTC',
    ],
    
    'database' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'renaltales',
        'username' => '', // Set in .env
        'password' => '', // Set in .env
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    
    'security' => [
        'session_lifetime' => 7200, // 2 hours
        'password_min_length' => 12,
        'require_2fa' => true,
        'csrf_lifetime' => 3600, // 1 hour
    ],
    
    'languages' => [
        'supported' => [
            'en', 'sk', 'cs', 'de', 'pl', 'hu', 'uk', 'ru', 'it', 'nl', 
            'fr', 'es', 'pt', 'ro', 'bg', 'sl', 'hr', 'sr', 'mk', 'sq', 
            'el', 'da', 'no', 'sv', 'fi', 'is', 'et', 'lv', 'lt', 'tr', 
            'eo', 'ja', 'zh', 'ko', 'ar', 'hi', 'th', 'vi', 'id', 'ms', 
            'tl', 'sw', 'am', 'yo', 'zu'
        ],
        'default' => 'sk',
        'fallback' => 'en',
        'detect_from_browser' => true,
    ],
    
    'stories' => [
        'categories' => [
            'general',
            'dialysis',
            'pre_transplant',
            'post_transplant',
            'lifestyle',
            'nutrition',
            'mental_health',
            'success_stories',
        ],
        'access_levels' => [
            'public',
            'registered',
            'verified',
            'premium',
        ],
    ],
    
    'roles' => [
        'user',
        'verified_user',
        'translator',
        'moderator',
        'admin',
    ],
    
    'mail' => [
        'from_address' => 'webmaster@ladvina.eu', // Set in .env
        'from_name' => 'RenalTales Webmaster', // Set in .env
        'smtp_host' => 'smtp.m1.websupport.sk', // Set in .env
        'smtp_port' => 587,
        'smtp_username' => 'webmaster@ladvina.eu', // Set in .env
        'smtp_password' => 'pOkrutka_3779g', // Set in .env
        'smtp_encryption' => 'tls',
    ],
];
