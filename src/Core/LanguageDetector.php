<?php

declare(strict_types=1);

namespace RenalTales\Core;

/**
 * Language Detector
 *
 * Detects and manages user language preferences
 *
 * @author Ľubomír Polaščín
 * @version 2025.v3.0dev
 */
class LanguageDetector
{
    private array $supportedLanguages;
    private string $defaultLanguage;

    /**
     * Constructor
     */
    public function __construct(array $supportedLanguages = ['en', 'sk'], string $defaultLanguage = 'en')
    {
        $this->supportedLanguages = $supportedLanguages;
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * Detect user's preferred language
     */
    public function detectLanguage(): string
    {
        // Prioritize session
        if (isset($_SESSION['language']) && $this->isSupported($_SESSION['language'])) {
            return $_SESSION['language'];
        }

        // Fall back to cookie
        if (isset($_COOKIE['language']) && $this->isSupported($_COOKIE['language'])) {
            return $_COOKIE['language'];
        }

        // Last resort: browser's Accept-Language header
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

            foreach ($languages as $language) {
                $language = substr(trim(explode(';', $language)[0]), 0, 2);

                if ($this->isSupported($language)) {
                    return $language;
                }
            }
        }

        return $this->defaultLanguage;
    }

    /**
     * Check if language is supported
     */
    public function isSupported(string $language): bool
    {
        return in_array($language, $this->supportedLanguages, true);
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Set language preference
     */
    public function setLanguage(string $language): bool
    {
        if (!$this->isSupported($language)) {
            return false;
        }

        // Save to session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['language'] = $language;
        }

        // Save to cookie (30 days)
        setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/');

        return true;
    }
}
