<?php

declare(strict_types=1);

namespace RenalTales\Helpers;

/**
 * Simple Translation Class
 *
 * Provides streamlined translation functionality without complex caching.
 * Loads language files directly and provides simple key-based translation.
 *
 * @package RenalTales\Helpers
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class Translation
{
    /**
     * @var array<string, string> Loaded translations for current language
     */
    protected array $translations = [];

    /**
     * @var string Current language code
     */
    protected string $language;

    /**
     * @var string Path to translation files
     */
    protected string $translationPath;

    /**
     * @var array<string> List of supported languages
     */
    protected array $supportedLanguages = [];

    /**
     * Constructor
     *
     * @param string $language Initial language code
     * @param string|null $translationPath Path to translation files (optional)
     */
    public function __construct(string $language = 'en', ?string $translationPath = null)
    {
        $this->translationPath = $translationPath ?? $this->getDefaultTranslationPath();
        $this->loadSupportedLanguages();
        $this->setLanguage($language);
    }

    /**
     * Get the default translation path
     *
     * @return string Default path to translation files
     */
    protected function getDefaultTranslationPath(): string
    {
        $appRoot = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2);
        return $appRoot . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang';
    }

    /**
     * Load supported languages from available files
     */
    protected function loadSupportedLanguages(): void
    {
        if (!is_dir($this->translationPath)) {
            $this->supportedLanguages = ['en'];
            return;
        }

        $files = glob($this->translationPath . DIRECTORY_SEPARATOR . '*.php');
        $this->supportedLanguages = [];

        foreach ($files as $file) {
            $language = basename($file, '.php');
            if ($this->isValidLanguageCode($language)) {
                $this->supportedLanguages[] = $language;
            }
        }

        // Ensure English is always available as fallback
        if (!in_array('en', $this->supportedLanguages, true)) {
            $this->supportedLanguages[] = 'en';
        }
    }

    /**
     * Check if language code is valid
     *
     * @param string $language Language code to validate
     * @return bool True if valid, false otherwise
     */
    protected function isValidLanguageCode(string $language): bool
    {
        return preg_match('/^[a-z]{2}(-[a-z]{2,3})?$/i', $language) === 1;
    }

    /**
     * Load translations for a specific language
     *
     * @param string $language Language code to load
     */
    protected function loadLanguage(string $language): void
    {
        $filePath = $this->translationPath . DIRECTORY_SEPARATOR . $language . '.php';

        if (file_exists($filePath)) {
            $translations = include $filePath;
            $this->translations = is_array($translations) ? $translations : [];
        } else {
            // Try to load English as fallback
            $fallbackPath = $this->translationPath . DIRECTORY_SEPARATOR . 'en.php';
            if (file_exists($fallbackPath) && $language !== 'en') {
                $translations = include $fallbackPath;
                $this->translations = is_array($translations) ? $translations : [];
                error_log("Translation: Language file not found for '{$language}', using English fallback");
            } else {
                $this->translations = [];
                error_log("Translation: No language file found for '{$language}' and no English fallback available");
            }
        }
    }

    /**
     * Get translated text by key
     *
     * @param string $key Translation key
     * @param string $default Default text if key not found
     * @param array<string, string|int|float> $parameters Parameters for replacement
     * @return string Translated text
     */
    public function get(string $key, string $default = '', array $parameters = []): string
    {
        $text = $this->translations[$key] ?? $default ?: $key;

        // Replace parameters in the format {param}
        foreach ($parameters as $param => $value) {
            $text = str_replace('{' . $param . '}', (string)$value, $text);
        }

        return $text;
    }

    /**
     * Set the current language
     *
     * @param string $language Language code to set
     * @return bool True if successful, false if language not supported
     */
    public function setLanguage(string $language): bool
    {
        if (!$this->isLanguageSupported($language)) {
            error_log("Translation: Unsupported language '{$language}', keeping current language '{$this->language}'");
            return false;
        }

        $this->language = $language;
        $this->loadLanguage($language);

        // Store in session if available
        $this->persistLanguageChoice($language);

        return true;
    }

    /**
     * Get the current language code
     *
     * @return string Current language code
     */
    public function getCurrentLanguage(): string
    {
        return $this->language;
    }

    /**
     * Check if a language is supported
     *
     * @param string $language Language code to check
     * @return bool True if supported, false otherwise
     */
    public function isLanguageSupported(string $language): bool
    {
        return in_array($language, $this->supportedLanguages, true);
    }

    /**
     * Get all supported languages
     *
     * @return array<string> Array of supported language codes
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Get all translations for current language
     *
     * @return array<string, string> All translations
     */
    public function getAllTranslations(): array
    {
        return $this->translations;
    }

    /**
     * Detect user's preferred language from session, cookie, or browser
     *
     * @param string $default Default language if none detected
     * @return string Detected language code
     */
    public function detectLanguage(string $default = 'en'): string
    {
        // Check session first
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
            $sessionLang = $_SESSION['language'];
            if ($this->isLanguageSupported($sessionLang)) {
                return $sessionLang;
            }
        }

        // Check cookie
        if (isset($_COOKIE['language'])) {
            $cookieLang = $_COOKIE['language'];
            if ($this->isLanguageSupported($cookieLang)) {
                return $cookieLang;
            }
        }

        // Check browser Accept-Language header
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($acceptLanguages as $lang) {
                // Extract language code from format like "en-US;q=0.8"
                $lang = strtolower(trim(explode(';', $lang)[0]));
                $lang = substr($lang, 0, 2); // Take only first 2 characters

                if ($this->isLanguageSupported($lang)) {
                    return $lang;
                }
            }
        }

        return $default;
    }

    /**
     * Persist language choice in session and cookie
     *
     * @param string $language Language code to persist
     */
    protected function persistLanguageChoice(string $language): void
    {
        // Store in session only if session is active
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['language'] = $language;
        }

        // Store in cookie only if headers haven't been sent yet
        if (!headers_sent()) {
            setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }
    }

    /**
     * Switch to a different language
     *
     * @param string $language Language code to switch to
     * @return bool True if successful, false otherwise
     */
    public function switchTo(string $language): bool
    {
        if ($this->setLanguage($language)) {
            error_log("Translation: Language switched to '{$language}'");
            return true;
        }

        return false;
    }

    /**
     * Clear language preferences (reset to default)
     *
     * @param string $default Default language to reset to
     */
    public function clearPreferences(string $default = 'en'): void
    {
        // Clear session
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
            unset($_SESSION['language']);
        }

        // Clear cookie
        setcookie('language', '', time() - 3600, '/');

        // Reset to default
        $this->setLanguage($default);
    }

    /**
     * Alias for get() method for backwards compatibility
     *
     * @param string $key Translation key
     * @param string $default Default text if key not found
     * @param array<string, string|int|float> $parameters Parameters for replacement
     * @return string Translated text
     */
    public function translate(string $key, string $default = '', array $parameters = []): string
    {
        return $this->get($key, $default, $parameters);
    }
}
