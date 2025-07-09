<?php

declare(strict_types=1);

namespace RenalTales\Helpers;

use RenalTales\Services\TranslationService;

/**
 * Translation Helper Functions
 * 
 * Provides global helper functions for easy translation usage
 */
class TranslationHelper
{
    private static ?TranslationService $translationService = null;

    /**
     * Get translation service instance
     */
    private static function getTranslationService(): TranslationService
    {
        if (self::$translationService === null) {
            self::$translationService = new TranslationService();
        }
        
        return self::$translationService;
    }

    /**
     * Get translation
     */
    public static function trans(string $key, string $group = 'default', array $parameters = []): string
    {
        return self::getTranslationService()->translate($key, $group, $parameters);
    }

    /**
     * Get current language
     */
    public static function getCurrentLanguage(): string
    {
        return self::getTranslationService()->getCurrentLanguage();
    }

    /**
     * Get available languages
     */
    public static function getAvailableLanguages(): array
    {
        return self::getTranslationService()->getAvailableLanguages();
    }

    /**
     * Set current language
     */
    public static function setLanguage(string $languageCode): bool
    {
        return self::getTranslationService()->setCurrentLanguage($languageCode);
    }

    /**
     * Get current language direction
     */
    public static function getLanguageDirection(): string
    {
        return self::getTranslationService()->getCurrentLanguageDirection();
    }

    /**
     * Check if current language is RTL
     */
    public static function isRTL(): bool
    {
        return self::getTranslationService()->isCurrentLanguageRTL();
    }

    /**
     * Get current language name
     */
    public static function getLanguageName(): string
    {
        return self::getTranslationService()->getCurrentLanguageName();
    }

    /**
     * Get current language native name
     */
    public static function getLanguageNativeName(): string
    {
        return self::getTranslationService()->getCurrentLanguageNativeName();
    }

    /**
     * Get current language flag
     */
    public static function getLanguageFlag(): string
    {
        return self::getTranslationService()->getCurrentLanguageFlag();
    }

    /**
     * Check if language is supported
     */
    public static function isLanguageSupported(string $code): bool
    {
        return self::getTranslationService()->isLanguageSupported($code);
    }

    /**
     * Get language by code
     */
    public static function getLanguageByCode(string $code): ?array
    {
        return self::getTranslationService()->getLanguageByCode($code);
    }
}

// Global helper functions for templates
if (!function_exists('__')) {
    /**
     * Get translation (shorthand)
     */
    function __(string $key, string $group = 'default', array $parameters = []): string
    {
        return TranslationHelper::trans($key, $group, $parameters);
    }
}

if (!function_exists('trans')) {
    /**
     * Get translation (full name)
     */
    function trans(string $key, string $group = 'default', array $parameters = []): string
    {
        return TranslationHelper::trans($key, $group, $parameters);
    }
}

if (!function_exists('current_language')) {
    /**
     * Get current language
     */
    function current_language(): string
    {
        return TranslationHelper::getCurrentLanguage();
    }
}

if (!function_exists('available_languages')) {
    /**
     * Get available languages
     */
    function available_languages(): array
    {
        return TranslationHelper::getAvailableLanguages();
    }
}

if (!function_exists('set_language')) {
    /**
     * Set current language
     */
    function set_language(string $languageCode): bool
    {
        return TranslationHelper::setLanguage($languageCode);
    }
}

if (!function_exists('is_rtl')) {
    /**
     * Check if current language is RTL
     */
    function is_rtl(): bool
    {
        return TranslationHelper::isRTL();
    }
}

if (!function_exists('language_direction')) {
    /**
     * Get language direction
     */
    function language_direction(): string
    {
        return TranslationHelper::getLanguageDirection();
    }
}

if (!function_exists('language_name')) {
    /**
     * Get current language name
     */
    function language_name(): string
    {
        return TranslationHelper::getLanguageName();
    }
}

if (!function_exists('language_native_name')) {
    /**
     * Get current language native name
     */
    function language_native_name(): string
    {
        return TranslationHelper::getLanguageNativeName();
    }
}

if (!function_exists('language_flag')) {
    /**
     * Get current language flag
     */
    function language_flag(): string
    {
        return TranslationHelper::getLanguageFlag();
    }
}
