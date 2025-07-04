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
        $this->supportedLanguages = $config->get('languages.supported');
        $this->currentLanguage = $config->get('languages.default');
    }

    public function initialize(): void
    {
        // Load language from session if set
        if (isset($_SESSION['language']) && $this->isSupported($_SESSION['language'])) {
            $this->currentLanguage = $_SESSION['language'];
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
        $path = dirname(__DIR__, 2) . "/translations/{$code}.php";
        
        if (file_exists($path)) {
            $this->translations = require $path;
        } else {
            // Fall back to default language if translation file doesn't exist
            $fallbackCode = $this->config->get('languages.fallback');
            $fallbackPath = dirname(__DIR__, 2) . "/translations/{$fallbackCode}.php";
            
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
            'en' => 'English',
            'sk' => 'Slovenčina',
            'cs' => 'Čeština',
            'de' => 'Deutsch',
            'pl' => 'Polski',
            'hu' => 'Magyar',
            'uk' => 'Українська',
            'ru' => 'Русский',
            'it' => 'Italiano',
            'nl' => 'Nederlands',
            'fr' => 'Français',
            'es' => 'Español',
            'pt' => 'Português',
            'ro' => 'Română',
            'bg' => 'Български',
            'sl' => 'Slovenščina',
            'hr' => 'Hrvatski',
            'sr' => 'Српски',
            'mk' => 'Македонски',
            'sq' => 'Shqip',
            'el' => 'Ελληνικά',
            'da' => 'Dansk',
            'no' => 'Norsk',
            'sv' => 'Svenska',
            'fi' => 'Suomi',
            'is' => 'Íslenska',
            'et' => 'Eesti',
            'lv' => 'Latviešu',
            'lt' => 'Lietuvių',
            'tr' => 'Türkçe',
            'eo' => 'Esperanto',
            'ja' => '日本語',
            'zh' => '中文',
            'ko' => '한국어',
            'ar' => 'العربية',
            'hi' => 'हिन्दी',
            'th' => 'ไทย',
            'vi' => 'Tiếng Việt',
            'id' => 'Bahasa Indonesia',
            'ms' => 'Bahasa Melayu',
            'tl' => 'Tagalog',
            'sw' => 'Kiswahili',
            'am' => 'አማርኛ',
            'yo' => 'Yorùbá',
            'zu' => 'isiZulu'
        ];

        return $names[$code] ?? $code;
    }
}
