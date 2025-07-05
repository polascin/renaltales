<?php
declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Core\Config;

class LanguageManager
{
    private Config $config;
    private string $currentLanguage;
    private array $supportedLanguages;
    private array $translations = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->supportedLanguages = $this->discoverSupportedLanguages();
        $this->currentLanguage = $config->get('languages.default');
    }

    /**
     * Dynamically discover supported languages by scanning the i18n directory
     */
    private function discoverSupportedLanguages(): array
    {
        $i18nDir = dirname(__DIR__, 2) . '/i18n';
        $supportedLanguages = [];
        
        if (is_dir($i18nDir)) {
            $files = glob($i18nDir . '/*.php');
            foreach ($files as $file) {
                $languageCode = basename($file, '.php');
                // Validate that the file contains proper language data
                if ($this->isValidLanguageFile($file)) {
                    $supportedLanguages[] = $languageCode;
                }
            }
        }
        
        // Sort languages alphabetically
        sort($supportedLanguages);
        
        // Ensure we have at least the default and fallback languages
        $defaultLanguage = $this->config->get('languages.default');
        $fallbackLanguage = $this->config->get('languages.fallback');
        
        if (!in_array($defaultLanguage, $supportedLanguages)) {
            $supportedLanguages[] = $defaultLanguage;
        }
        
        if (!in_array($fallbackLanguage, $supportedLanguages)) {
            $supportedLanguages[] = $fallbackLanguage;
        }
        
        return $supportedLanguages;
    }

    /**
     * Validate that a language file contains proper translation data
     */
    private function isValidLanguageFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        try {
            $translations = require $filePath;
            // Check if it returns an array with at least some translations
            return is_array($translations) && !empty($translations);
        } catch (Exception $e) {
            return false;
        }
    }

    public function initialize(): void
    {
        // Load language from session if set
        if (isset($_SESSION['language']) && $this->isSupported($_SESSION['language'])) {
            $this->currentLanguage = $_SESSION['language'];
        }
        // Load language from cookie if session is not set
        elseif (isset($_COOKIE['language']) && $this->isSupported($_COOKIE['language'])) {
            $this->currentLanguage = $_COOKIE['language'];
            $_SESSION['language'] = $this->currentLanguage; // Update session
        }
        // Detect from browser if enabled
        elseif ($this->config->get('languages.detect_from_browser')) {
            $this->detectLanguage();
        }

        // Load translations for current language
        $this->loadTranslations($this->currentLanguage);
    }

    public function setLanguage(string $code): bool
    {
        if (!$this->isSupported($code)) {
            return false;
        }

        $this->currentLanguage = $code;
        $_SESSION['language'] = $code;
        
        // Reload translations
        $this->loadTranslations($code);
        
        return true;
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }
    
    public function getSupportedLanguagesWithNames(): array
    {
        $result = [];
        foreach ($this->supportedLanguages as $code) {
            $result[$code] = $this->getLanguageName($code);
        }
        return $result;
    }

    public function translate(string $key, array $parameters = []): string
    {
        $translation = $this->translations[$key] ?? $key;
        
        if (!empty($parameters)) {
            foreach ($parameters as $param => $value) {
                $translation = str_replace(":{$param}", $value, $translation);
            }
        }
        
        return $translation;
    }

    private function detectLanguage(): void
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return;
        }

        $browserLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        
        foreach ($browserLanguages as $browserLang) {
            $code = substr($browserLang, 0, 2);
            if ($this->isSupported($code)) {
                $this->currentLanguage = $code;
                return;
            }
        }
    }

    private function loadTranslations(string $code): void
    {
        $path = dirname(__DIR__, 2) . "/i18n/{$code}.php";
        
        if (file_exists($path)) {
            $this->translations = require $path;
        } else {
            // Fall back to default language if translation file doesn't exist
            $fallbackCode = $this->config->get('languages.fallback');
            $fallbackPath = dirname(__DIR__, 2) . "/i18n/{$fallbackCode}.php";
            
            if ($code !== $fallbackCode && file_exists($fallbackPath)) {
                $this->translations = require $fallbackPath;
            }
        }
    }

    private function isSupported(string $code): bool
    {
        return in_array($code, $this->supportedLanguages);
    }

    public function getLanguageName(string $code): string
    {
        $names = [
            'am' => 'አማርኛ',
            'ar' => 'العربية',
            'bg' => 'Български',
            'cs' => 'Čeština',
            'da' => 'Dansk',
            'de' => 'Deutsch',
            'el' => 'Ελληνικά',
            'en' => 'English',
            'eo' => 'Esperanto',
            'es' => 'Español',
            'et' => 'Eesti',
            'fi' => 'Suomi',
            'fr' => 'Français',
            'hi' => 'हिन्दी',
            'hr' => 'Hrvatski',
            'hu' => 'Magyar',
            'id' => 'Bahasa Indonesia',
            'is' => 'Íslenska',
            'it' => 'Italiano',
            'ja' => '日本語',
            'ko' => '한국어',
            'lt' => 'Lietuvių',
            'lv' => 'Latviešu',
            'mk' => 'Македонски',
            'ms' => 'Bahasa Melayu',
            'nl' => 'Nederlands',
            'no' => 'Norsk',
            'pl' => 'Polski',
            'pt' => 'Português',
            'ro' => 'Română',
            'ru' => 'Русский',
            'sk' => 'Slovenčina',
            'sl' => 'Slovenščina',
            'sq' => 'Shqip',
            'sr' => 'Српски',
            'sv' => 'Svenska',
            'sw' => 'Kiswahili',
            'th' => 'ไทย',
            'tl' => 'Tagalog',
            'tr' => 'Türkçe',
            'uk' => 'Українська',
            'vi' => 'Tiếng Việt',
            'yo' => 'Yorùbá',
            'zh' => '中文',
            'zu' => 'isiZulu'
        ];

        return $names[$code] ?? $code;
    }
}
