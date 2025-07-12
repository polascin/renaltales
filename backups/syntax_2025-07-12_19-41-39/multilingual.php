<?php

declare(strict_types=1);

/**
 * Multilingual System Configuration
 * 
 * Central configuration for the enhanced multilingual system
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

// Define language path if not already defined
if (!defined('LANGUAGE_PATH')) {
    define('LANGUAGE_PATH', __DIR__ . '/../resources/lang/');
}

// Multilingual system configuration
return [
    
    /**
     * Core Settings
     */
    'fallback_language' => 'en',
    'default_language' => 'en',
    'auto_detect' => true,
    'case_sensitive' => false,
    
    /**
     * Session and Cookie Settings
     */
    'session_key' => 'user_language',
    'cookie_name' => 'language_preference',
    'cookie_lifetime' => 86400 * 30, // 30 days
    'cookie_domain' => '',
    'cookie_secure' => false, // Set to true for HTTPS
    'cookie_httponly' => true,
    
    /**
     * Detection Settings
     */
    'detection_methods' => [
        'url_parameter' => true,    // ?lang=en
        'session' => true,          // Session storage
        'cookie' => true,           // Cookie storage
        'browser' => true,          // Accept-Language header
        'geolocation' => true,      // IP-based geolocation
    ],
    
    /**
     * Detection Priority Order
     */
    'detection_priority' => [
        'url_parameter',
        'session',
        'cookie',
        'browser',
        'geolocation',
        'fallback'
    ],
    
    /**
     * Cache Settings
     */
    'cache_enabled' => true,
    'cache_lifetime' => 1800, // 30 minutes
    'cache_prefix' => 'multilingual_',
    
    /**
     * Security Settings
     */
    'security_enabled' => true,
    'allowed_languages_only' => true,
    'sanitize_input' => true,
    'max_language_code_length' => 10,
    
    /**
     * URL and Display Settings
     */
    'url_parameter' => 'lang',
    'show_flags' => true,
    'show_native_names' => true,
    'flag_base_path' => 'assets/flags/',
    'flag_extension' => '.webp',
    'flag_fallback_extension' => '.png',
    
    /**
     * Translation Settings
     */
    'parameter_pattern' => '/\{([a-zA-Z0-9_]+)\}/',
    'context_separator' => '.',
    'pluralization_enabled' => true,
    'interpolation_enabled' => true,
    
    /**
     * Language File Settings
     */
    'language_file_extension' => '.php',
    'context_directories' => true,
    'nested_keys' => true,
    'load_all_contexts' => false,
    
    /**
     * Performance Settings
     */
    'lazy_loading' => true,
    'preload_common' => true,
    'common_keys' => [
        'common.welcome',
        'common.hello',
        'common.goodbye',
        'common.yes',
        'common.no',
        'common.save',
        'common.cancel',
        'common.submit',
        'common.back',
        'common.next',
        'common.previous'
    ],
    
    /**
     * Debug and Development
     */
    'debug_mode' => false,
    'log_missing_translations' => true,
    'log_detection_failures' => true,
    'show_missing_key_warnings' => false,
    
    /**
     * Language Support Configuration
     */
    'supported_languages' => [
        // Core languages
        'en' => [
            'name' => 'English',
            'native_name' => 'English',
            'flag' => 'gb',
            'direction' => 'ltr',
            'enabled' => true,
            'priority' => 1
        ],
        'sk' => [
            'name' => 'Slovak',
            'native_name' => 'Slovenčina',
            'flag' => 'sk',
            'direction' => 'ltr',
            'enabled' => true,
            'priority' => 2
        ],
        'cs' => [
            'name' => 'Czech',
            'native_name' => 'Čeština',
            'flag' => 'cz',
            'direction' => 'ltr',
            'enabled' => true,
            'priority' => 3
        ],
        'de' => [
            'name' => 'German',
            'native_name' => 'Deutsch',
            'flag' => 'de',
            'direction' => 'ltr',
            'enabled' => true,
            'priority' => 4
        ],
        'fr' => [
            'name' => 'French',
            'native_name' => 'Français',
            'flag' => 'fr',
            'direction' => 'ltr',
            'enabled' => true,
            'priority' => 5
        ],
        'es' => [
            'name' => 'Spanish',
            'native_name' => 'Español',
            'flag' => 'es',
            'direction' => 'ltr',
            'enabled' => true,
            'priority' => 6
        ],
        'it' => [
            'name' => 'Italian',
            'native_name' => 'Italiano',
            'flag' => 'it',
            'direction' => 'ltr',
            'enabled' => true,
            'priority' => 7
        ],
        'pl' => [
            'name' => 'Polish',
            'native_name' => 'Polski',
            'flag' => 'pl',
            'direction' => 'ltr',
            'enabled' => true,
            'priority' => 8
        ],
        'ru' => [
            'name' => 'Russian',
            'native_name' => 'Русский',
            'flag' => 'ru',
            'direction' => 'ltr',
            'enabled' => true,
            'priority' => 9
        ],
        'uk' => [
            'name' => 'Ukrainian',
            'native_name' => 'Українська',
            'flag' => 'ua',
            'direction' => 'ltr',
            'enabled' => true,
            'priority' => 10
        ]
        // Additional languages can be added here
        // The system will automatically discover language files
        // in the resources/lang/ directory
    ],
    
    /**
     * RTL Language Support
     */
    'rtl_languages' => [
        'ar', 'fa', 'he', 'ur', 'ps', 'sd', 'ug', 'dv'
    ],
    
    /**
     * Country to Language Mapping
     * Used for geolocation-based detection
     */
    'country_language_map' => [
        'SK' => 'sk',
        'CZ' => 'cs',
        'DE' => 'de',
        'AT' => 'de',
        'CH' => 'de',
        'FR' => 'fr',
        'BE' => 'fr',
        'ES' => 'es',
        'IT' => 'it',
        'PL' => 'pl',
        'RU' => 'ru',
        'UA' => 'uk',
        'US' => 'en',
        'GB' => 'en',
        'CA' => 'en',
        'AU' => 'en',
        'NZ' => 'en',
        'ZA' => 'en'
    ],
    
    /**
     * Browser Language Priorities
     * Languages to prioritize when detecting from browser
     */
    'browser_priorities' => [
        'en', 'sk', 'cs', 'de', 'fr', 'es', 'it', 'pl', 'ru', 'uk'
    ],
    
    /**
     * Advanced Settings
     */
    'memory_limit_mb' => 128,
    'max_translations_per_request' => 10000,
    'translation_file_max_size_mb' => 5,
    'enable_translation_versioning' => false,
    'enable_usage_analytics' => false,
    
    /**
     * Integration Settings
     */
    'enable_legacy_support' => true,
    'legacy_function_names' => [
        'getText' => 't',
        'getPlural' => 'tn',
        'getCurrentLanguage' => 'getCurrentLang'
    ],
    
    /**
     * Development and Migration Settings
     */
    'show_deprecation_warnings' => false,
    'log_legacy_usage' => false,
    'migration_mode' => false
];
