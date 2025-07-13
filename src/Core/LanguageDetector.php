<?php

declare(strict_types=1);

namespace RenalTales\Core;

/**
 * Language Detector
 *
 * Detects and manages user language preferences
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
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
