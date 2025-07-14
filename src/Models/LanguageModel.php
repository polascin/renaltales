<?php

declare(strict_types=1);

namespace RenalTales\Models;

/**
 * Language Model
 *
 * Handles language loading, support checks, and translations
 *
 * @author Ľubomír Polaščín
 * @version 2025.v3.0dev
 */
class LanguageModel
{
    private string $currentLanguage = DEFAULT_LANGUAGE;
    private array $supportedLanguages = [];
    private array $translations = [];
    private string $languagePath;
    private string $languageFlagPath = FLAGS_DIR;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->languagePath = defined('LANGUAGE_PATH') ? LANGUAGE_PATH : dirname(__DIR__) . DS . 'resources' . DS . 'lang';
        // Dynamically load supported languages
        $this->loadSupportedLanguages();
        // Initialize with default language, will be set by LanguageDetector
        $this->currentLanguage = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en';
        $this->loadTranslations($this->currentLanguage);
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
     * Refresh supported languages (reload from directory)
     */
    public function refreshSupportedLanguages(): void
    {
        $this->loadSupportedLanguages();
    }

    /**
     * Load supported languages from directory dynamically
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
            // Ensure language code format is valid (2-3 char codes, with optional region)
            if (preg_match('/^[a-z]{2,3}(-[a-z]{2,3})?$/', $language)) {
                $languages[] = $language;
            }
        }

        // Sort languages alphabetically for consistent ordering
        sort($languages);

        // Fallback to English if none found
        $this->supportedLanguages = $languages ?: ['en'];
    }

    /**
     * Load translations for specific language
     */
    public function loadTranslations(string $language): void
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

    /**
     * Check if language file exists
     */
    public function languageFileExists(string $language): bool
    {
        $filePath = $this->languagePath . $language . '.php';
        return file_exists($filePath);
    }

    /**
     * Get language file path
     */
    public function getLanguageFilePath(string $language): string
    {
        return $this->languagePath . $language . '.php';
    }

    /**
     * Get language directory path
     */
    public function getLanguagePath(): string
    {
        return $this->languagePath;
    }
}
