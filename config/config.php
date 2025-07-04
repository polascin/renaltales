<?php
/**
 * RenalTales Configuration File
 * Central configuration for the framework-less PHP application
 */

// Prevent direct access
if (!defined('ROOT_PATH')) {
    die('Direct access not allowed');
}

// Environment configuration
define('APP_ENV', 'development'); // development, production
define('APP_DEBUG', APP_ENV === 'development');
define('APP_NAME', 'RenalTales');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/renaltales');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'renaltales');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security configuration
define('ENCRYPTION_KEY', 'your-32-character-secret-key-here'); // Change this!
define('JWT_SECRET', 'your-jwt-secret-key-here'); // Change this!
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour
define('SESSION_LIFETIME', 86400); // 24 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('PASSWORD_MIN_LENGTH', 12);
define('REQUIRE_2FA', false); // Set to true for production

// Rate limiting
define('RATE_LIMIT_REQUESTS', 60); // requests per minute
define('RATE_LIMIT_WINDOW', 60); // window in seconds

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt']);

// Email configuration
define('SMTP_HOST', 'smtp.m1.websupport.sk');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'webmaster@ladvina.eu');
define('SMTP_PASSWORD', ''); // Set in production
define('SMTP_FROM_EMAIL', 'webmaster@ladvina.eu');
define('SMTP_FROM_NAME', 'RenalTales Webmaster');
define('SMTP_ENCRYPTION', 'tls');

// Language configuration
define('DEFAULT_LANGUAGE', 'sk');
define('FALLBACK_LANGUAGE', 'en');
define('DETECT_BROWSER_LANGUAGE', true);

// Supported languages for story translations
$GLOBALS['SUPPORTED_STORY_LANGUAGES'] = [
    'en' => 'English',
    'sk' => 'Slovenčina',
    'cs' => 'Čeština',
    'de' => 'Deutsch',
    'pl' => 'Polski',
    'hu' => 'Magyar',
    'uk' => 'Українська',
    'ru' => 'Русский',
    'it' => 'Italiano',
    'nl' => 'Nederlands',
    'fr' => 'Français',
    'es' => 'Español',
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
];

// Story categories
$GLOBALS['STORY_CATEGORIES'] = [
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
];

// User roles and permissions
$GLOBALS['USER_ROLES'] = [
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
];

// Content access levels
$GLOBALS['ACCESS_LEVELS'] = [
    'public' => 'Public (no registration required)',
    'registered' => 'Registered users only',
    'verified' => 'Verified users only',
    'premium' => 'Premium members only',
    'translator' => 'Translators and above',
    'moderator' => 'Moderators and above',
    'admin' => 'Administrators only'
];

// Pagination settings
define('STORIES_PER_PAGE', 12);
define('COMMENTS_PER_PAGE', 20);
define('USERS_PER_PAGE', 25);

// Cache settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour

// Logging configuration
define('LOG_LEVEL', APP_DEBUG ? 'debug' : 'error');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_MAX_FILES', 5);

// Application paths
define('VIEWS_PATH', APP_PATH . '/Views');
define('MODELS_PATH', APP_PATH . '/Models');
define('CONTROLLERS_PATH', APP_PATH . '/Controllers');
define('SERVICES_PATH', APP_PATH . '/Services');
define('MIDDLEWARE_PATH', APP_PATH . '/Middleware');
define('LOCALE_PATH', ROOT_PATH . '/locale');
define('UPLOADS_PATH', STORAGE_PATH . '/uploads');
define('CACHE_PATH', STORAGE_PATH . '/cache');
define('LOGS_PATH', STORAGE_PATH . '/logs');

// Content moderation
define('AUTO_APPROVE_STORIES', false); // New stories require approval
define('AUTO_APPROVE_COMMENTS', true); // Comments are auto-approved
define('SPAM_DETECTION_ENABLED', true);
define('PROFANITY_FILTER_ENABLED', true);

// Performance settings
define('ENABLE_GZIP', true);
define('ENABLE_ETAG', true);
define('STATIC_CACHE_TTL', 86400); // 24 hours for static assets
