<?php

declare(strict_types=1);

namespace RenalTales\Models;

use RenalTales\Core\Contracts\LanguageInterface;

/**
 * Language Model
 *
 * Handles language-related operations and translations
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class LanguageModel
{
    private string $currentLanguage = 'en';
    private array $supportedLanguages = [];
    private array $translations = [];
    private string $languagePath;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->languagePath = defined('LANGUAGE_PATH') ? LANGUAGE_PATH : dirname(__DIR__, 2) . '/resources/lang/';
        $this->loadSupportedLanguages();
        $this->currentLanguage = $this->detectLanguage();
        $this->loadTranslations($this->currentLanguage);
    }

    /**
     * Detect user's preferred language
     */
    public function detectLanguage(): string
    {
        // Check session first
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
            $sessionLang = $_SESSION['language'];
            if ($this->isSupported($sessionLang)) {
                return $sessionLang;
            }
        }

        // Check cookie
        if (isset($_COOKIE['language'])) {
            $cookieLang = $_COOKIE['language'];
            if ($this->isSupported($cookieLang)) {
                return $cookieLang;
            }
        }

        // Check browser Accept-Language header
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $languages = explode(',', $acceptLang);

            foreach ($languages as $lang) {
                $lang = trim(explode(';', $lang)[0]);
                $lang = substr($lang, 0, 2); // Get primary language code

                if ($this->isSupported($lang)) {
                    return $lang;
                }
            }
        }

        // Default to defined constant or English
        return defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en';
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * Set current language
     */
    public function setLanguage(string $language): bool
    {
        if (!$this->isSupported($language)) {
            return false;
        }

        $this->currentLanguage = $language;
        $this->loadTranslations($language);

        // Save to session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['language'] = $language;
        }

        // Save to cookie (30 days)
        setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/');

        return true;
    }

    /**
     * Check if language is supported
     */
    public function isSupported(string $language): bool
    {
        return in_array($language, $this->supportedLanguages, true);
    }

    /**
     * Get translated text
     */
    public function getText(string $key, array $parameters = [], string $fallback = ''): string
    {
        $text = $this->translations[$key] ?? $fallback ?: $key;

        // Replace parameters
        foreach ($parameters as $param => $value) {
            $text = str_replace("{{$param}}", (string)$value, $text);
        }

        return $text;
    }

    /**
     * Get all translations for current language
     */
    public function getAllTexts(): array
    {
        return $this->translations;
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Load supported languages from directory
     */
    private function loadSupportedLanguages(): void
    {
        if (!is_dir($this->languagePath)) {
            $this->supportedLanguages = ['en'];
            return;
        }

        $files = glob($this->languagePath . '*.php');
        $languages = [];

        foreach ($files as $file) {
            $language = basename($file, '.php');
            if (preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $language)) {
                $languages[] = $language;
            }
        }

        $this->supportedLanguages = $languages ?: ['en'];
    }

    /**
     * Load translations for specific language
     */
    private function loadTranslations(string $language): void
    {
        $filePath = $this->languagePath . $language . '.php';

        if (file_exists($filePath)) {
            $translations = include $filePath;
            $this->translations = is_array($translations) ? $translations : [];
        } else {
            // Try to load default language
            $defaultPath = $this->languagePath . 'en.php';
            if (file_exists($defaultPath)) {
                $translations = include $defaultPath;
                $this->translations = is_array($translations) ? $translations : [];
            } else {
                $this->translations = [];
            }
        }
    }
}
