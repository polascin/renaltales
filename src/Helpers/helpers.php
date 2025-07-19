<?php

/**
 * Translation Helper Functions
 *
 * Provides global helper functions for easy translation access in templates and views.
 *
 * @package RenalTales\Helpers
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

use RenalTales\Helpers\Translation;

if (!function_exists('__')) {
    /**
     * Get translated text using simple key-based lookup
     *
     * @param string $key Translation key
     * @param string $default Default text if key not found
     * @param array<string, string|int|float> $parameters Parameters for replacement
     * @return string Translated text
     */
    function __(string $key, string $default = '', array $parameters = []): string
    {
        global $translation;

        if (!$translation instanceof Translation) {
            // Initialize translation if not available
            $translation = new Translation();
        }

        return $translation->get($key, $default, $parameters);
    }
}

if (!function_exists('trans')) {
    /**
     * Alias for __ function
     *
     * @param string $key Translation key
     * @param string $default Default text if key not found
     * @param array<string, string|int|float> $parameters Parameters for replacement
     * @return string Translated text
     */
    function trans(string $key, string $default = '', array $parameters = []): string
    {
        return __($key, $default, $parameters);
    }
}

if (!function_exists('setLanguage')) {
    /**
     * Set the current language globally
     *
     * @param string $language Language code to set
     * @return bool True if successful, false otherwise
     */
    function setLanguage(string $language): bool
    {
        global $translation;

        if (!$translation instanceof Translation) {
            $translation = new Translation();
        }

        return $translation->setLanguage($language);
    }
}

if (!function_exists('getCurrentLanguage')) {
    /**
     * Get the current language code
     *
     * @return string Current language code
     */
    function getCurrentLanguage(): string
    {
        global $translation;

        if (!$translation instanceof Translation) {
            $translation = new Translation();
        }

        return $translation->getCurrentLanguage();
    }
}

if (!function_exists('getSupportedLanguages')) {
    /**
     * Get all supported languages
     *
     * @return array<string> Array of supported language codes
     */
    function getSupportedLanguages(): array
    {
        global $translation;

        if (!$translation instanceof Translation) {
            $translation = new Translation();
        }

        return $translation->getSupportedLanguages();
    }
}

if (!function_exists('switchLanguage')) {
    /**
     * Switch to a different language
     *
     * @param string $language Language code to switch to
     * @return bool True if successful, false otherwise
     */
    function switchLanguage(string $language): bool
    {
        global $translation;

        if (!$translation instanceof Translation) {
            $translation = new Translation();
        }

        return $translation->switchTo($language);
    }
}

if (!function_exists('detectLanguage')) {
    /**
     * Detect user's preferred language
     *
     * @param string $default Default language if none detected
     * @return string Detected language code
     */
    function detectLanguage(string $default = 'en'): string
    {
        global $translation;

        if (!$translation instanceof Translation) {
            $translation = new Translation();
        }

        return $translation->detectLanguage($default);
    }
}
