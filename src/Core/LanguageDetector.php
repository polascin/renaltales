<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Models\LanguageModel;

/**
 * Language Detector
 *
 * Handles user language preference detection and delegates language loading
 * and support checks to LanguageModel
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class LanguageDetector
{
    private LanguageModel $languageModel;

    /**
     * Constructor
     */
    public function __construct(LanguageModel $languageModel)
    {
        $this->languageModel = $languageModel;
    }

    /**
     * Detect user's preferred language.
     * Priority: session > cookie > browser > default
     *
     * @return string
     */
    public function detectLanguage(): string
    {
        $sessionLang = $this->getSessionLanguage();
        if ($sessionLang !== null && $this->languageModel->isSupported($sessionLang)) {
            return $sessionLang;
        }

        $cookieLang = $this->getCookieLanguage();
        if ($cookieLang !== null && $this->languageModel->isSupported($cookieLang)) {
            return $cookieLang;
        }

        $browserLang = $this->getBrowserLanguage();
        if ($browserLang !== null && $this->languageModel->isSupported($browserLang)) {
            return $browserLang;
        }

        return $this->getDefaultLanguage();
    }

    /**
     * Get language from session
     */
    private function getSessionLanguage(): ?string
    {
        return (isset($_SESSION['language']) && is_string($_SESSION['language'])) ? $_SESSION['language'] : null;
    }

    /**
     * Get language from cookie
     */
    private function getCookieLanguage(): ?string
    {
        return (isset($_COOKIE['language']) && is_string($_COOKIE['language'])) ? $_COOKIE['language'] : null;
    }

    /**
     * Get language from browser Accept-Language header
     */
    private function getBrowserLanguage(): ?string
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }
        
        $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($languages as $lang) {
            $lang = substr(trim(explode(';', $lang)[0]), 0, 2);
            if ($this->languageModel->isSupported($lang)) {
                return $lang;
            }
        }
        return null;
    }

    /**
     * Get default language
     */
    private function getDefaultLanguage(): string
    {
        return defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en';
    }

    /**
     * Set language preference in session
     */
    public function setSessionLanguage(string $language): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['language'] = $language;
        }
    }

    /**
     * Set language preference in cookie (30 days)
     */
    public function setCookieLanguage(string $language): void
    {
        // Only set cookies in web context, not CLI
        if (php_sapi_name() !== 'cli' && !headers_sent()) {
            setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/');
        }
    }
}
