<?php
/**
 * RenalTales Configuration File
 * Central configuration for the framework-less PHP application
 */

return [
    // Application configuration
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'RenalTales',
        'version' => '1.0.0',
        'env' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'key' => $_ENV['APP_KEY'] ?? 'base64:7rXQqgJ3jYZkHkLfRqJnCZBKYvZm3nVvTrGcQzRkL8M=',
        'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    ],

    // Database configuration
    'database' => [
        'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_DATABASE'] ?? 'renaltales',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        'port' => $_ENV['DB_PORT'] ?? '3306',
    ],

    // Security configuration
    'security' => [
        'encryption_key' => $_ENV['ENCRYPTION_KEY'] ?? 'your-32-character-secret-key-here',
        'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'your-jwt-secret-key-here',
        'csrf_token_expiry' => (int)($_ENV['CSRF_TOKEN_EXPIRY'] ?? 3600),
        'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 86400),
        'max_login_attempts' => (int)($_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5),
        'login_lockout_time' => (int)($_ENV['LOGIN_LOCKOUT_TIME'] ?? 900),
        'password_min_length' => (int)($_ENV['PASSWORD_MIN_LENGTH'] ?? 12),
        'require_2fa' => filter_var($_ENV['REQUIRE_2FA'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    ],

    // Email configuration
    'mail' => [
        'host' => $_ENV['MAIL_HOST'] ?? 'smtp.m1.websupport.sk',
        'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
        'username' => $_ENV['MAIL_USERNAME'] ?? 'webmaster@ladvina.eu',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'webmaster@ladvina.eu',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'RenalTales Webmaster',
    ],

    // Language configuration
    'languages' => [
        'default' => $_ENV['DEFAULT_LANGUAGE'] ?? 'sk',
        'fallback' => $_ENV['FALLBACK_LANGUAGE'] ?? 'en',
        'detect_from_browser' => filter_var($_ENV['DETECT_BROWSER_LANGUAGE'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        // Note: Supported languages are now automatically detected from the i18n directory
        // No need to manually maintain this list - all .php files in i18n/ are automatically included
    ],

    // Rate limiting
    'rate_limit' => [
        'requests' => (int)($_ENV['RATE_LIMIT_REQUESTS'] ?? 60),
        'window' => (int)($_ENV['RATE_LIMIT_WINDOW'] ?? 60),
    ],

    // File upload settings
    'uploads' => [
        'max_file_size' => (int)($_ENV['MAX_FILE_SIZE'] ?? 5 * 1024 * 1024),
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'txt'],
    ],

    // Performance settings
    'performance' => [
        'enable_gzip' => filter_var($_ENV['ENABLE_GZIP'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        'enable_etag' => filter_var($_ENV['ENABLE_ETAG'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        'static_cache_ttl' => (int)($_ENV['STATIC_CACHE_TTL'] ?? 86400),
        'cache_enabled' => filter_var($_ENV['CACHE_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        'cache_lifetime' => (int)($_ENV['CACHE_LIFETIME'] ?? 3600),
    ],

    // Pagination settings
    'pagination' => [
        'stories_per_page' => (int)($_ENV['STORIES_PER_PAGE'] ?? 12),
        'comments_per_page' => (int)($_ENV['COMMENTS_PER_PAGE'] ?? 20),
        'users_per_page' => (int)($_ENV['USERS_PER_PAGE'] ?? 25),
    ],

    // Logging configuration
    'logging' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'debug',
        'max_size' => (int)($_ENV['LOG_MAX_SIZE'] ?? 10 * 1024 * 1024),
        'max_files' => (int)($_ENV['LOG_MAX_FILES'] ?? 5),
    ],

    // Content moderation
    'moderation' => [
        'auto_approve_stories' => filter_var($_ENV['AUTO_APPROVE_STORIES'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
        'auto_approve_comments' => filter_var($_ENV['AUTO_APPROVE_COMMENTS'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        'spam_detection_enabled' => filter_var($_ENV['SPAM_DETECTION_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        'profanity_filter_enabled' => filter_var($_ENV['PROFANITY_FILTER_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
    ],

    // Supported languages for story translations
    'story_languages' => [
    'en' => 'English',
    'sk' => 'Slovenčina',
    'es' => 'Español',
    'cs' => 'Čeština',
    'de' => 'Deutsch',
    'pl' => 'Polski',
    'hu' => 'Magyar',
    'uk' => 'Українська',
    'ru' => 'Русский',
    'it' => 'Italiano',
    'nl' => 'Nederlands',
    'fr' => 'Français',
    'pt' => 'Português',
    'ro' => 'Română',
    'bg' => 'Български',
    'sl' => 'Slovenščina',
    'hr' => 'Hrvatski',
    'sr' => 'Српски',
    'mk' => 'Македонски',
    'sq' => 'Shqip',
    'el' => 'Ελληνικά',
    'da' => 'Dansk',
    'no' => 'Norsk',
    'sv' => 'Svenska',
    'fi' => 'Suomi',
    'is' => 'Íslenska',
    'et' => 'Eesti',
    'lv' => 'Latviešu',
    'lt' => 'Lietuvių',
    'tr' => 'Türkçe',
    'eo' => 'Esperanto',
    'ja' => '日本語',
    'zh' => '中文',
    'ko' => '한국어',
    'ar' => 'العربية',
    'hi' => 'हिन्दी',
    'th' => 'ไทย',
    'vi' => 'Tiếng Việt',
    'id' => 'Bahasa Indonesia',
    'ms' => 'Bahasa Melayu',
    'tl' => 'Filipino',
    'sw' => 'Kiswahili',
    'am' => 'አማርኛ',
    'yo' => 'Yorùbá',
    'zu' => 'isiZulu'
    ],

    // Story categories
    'story_categories' => [
    'general' => 'General Stories',
    'diagnosis' => 'Diagnosis Stories',
    'dialysis' => 'Life on Dialysis',
    'pre_transplant' => 'Pre-Transplant',
    'post_transplant' => 'Post-Transplant',
    'lifestyle' => 'Lifestyle Changes',
    'nutrition' => 'Nutrition & Diet',
    'mental_health' => 'Mental Health',
    'success_stories' => 'Success Stories',
    'family' => 'Family Support',
    'coping' => 'Coping Strategies',
    'medical' => 'Medical Experiences',
    'hope' => 'Hope & Inspiration',
    'challenges' => 'Daily Challenges',
    'community' => 'Community Support'
    ],

    // User roles and permissions
    'user_roles' => [
        'guest' => [
            'name' => 'Guest',
            'permissions' => ['read_public_stories', 'view_public_profiles']
        ],
        'user' => [
            'name' => 'Registered User',
            'permissions' => [
                'read_public_stories', 
                'read_member_stories', 
                'create_stories', 
                'edit_own_stories',
                'comment_stories',
                'view_profiles',
                'edit_own_profile'
            ]
        ],
        'verified_user' => [
            'name' => 'Verified User',
            'permissions' => [
                'read_public_stories', 
                'read_member_stories',
                'read_verified_stories',
                'create_stories', 
                'edit_own_stories',
                'comment_stories',
                'view_profiles',
                'edit_own_profile'
            ]
        ],
        'translator' => [
            'name' => 'Translator',
            'permissions' => [
                'read_public_stories', 
                'read_member_stories',
                'read_verified_stories',
                'create_stories', 
                'edit_own_stories',
                'comment_stories',
                'view_profiles',
                'edit_own_profile',
                'translate_stories',
                'edit_translations'
            ]
        ],
        'moderator' => [
            'name' => 'Moderator',
            'permissions' => [
                'read_public_stories', 
                'read_member_stories',
                'read_verified_stories',
                'create_stories', 
                'edit_own_stories',
                'comment_stories',
                'view_profiles',
                'edit_own_profile',
                'translate_stories',
                'edit_translations',
                'moderate_stories',
                'moderate_comments',
                'view_pending_content',
                'approve_reject_content'
            ]
        ],
        'admin' => [
            'name' => 'Administrator',
            'permissions' => ['all'] // Admin has all permissions
        ]
    ],

    // Content access levels
    'access_levels' => [
        'public' => 'Public (no registration required)',
        'registered' => 'Registered users only',
        'verified' => 'Verified users only',
        'premium' => 'Premium members only',
        'translator' => 'Translators and above',
        'moderator' => 'Moderators and above',
        'admin' => 'Administrators only'
    ],
];
