<?php
/**
 * Language Class
 * Handles language detection, switching, and integration
 */

class Language {
    private $supportedLanguages;
    private $defaultLanguage;
    private $fallbackLanguage;
    private $currentLanguage;
    private $detectBrowserLanguage;

    public function __construct() {
        $config = $GLOBALS['CONFIG'];
        $this->supportedLanguages = $this->scanI18nDirectory();
        $this->defaultLanguage = $config['languages']['default'];
        $this->fallbackLanguage = $config['languages']['fallback'];
        $this->detectBrowserLanguage = $config['languages']['detect_from_browser'];
        $this->detectLanguage();
    }

    public function detectLanguage() {
        if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $this->supportedLanguages)) {
            $this->currentLanguage = $_GET['lang'];
            $_SESSION['language'] = $this->currentLanguage;
        } elseif (isset($_SESSION['language'])) {
            $this->currentLanguage = $_SESSION['language'];
        } elseif ($this->detectBrowserLanguage) {
            $this->detectFromBrowser();
        } else {
            $this->currentLanguage = $this->defaultLanguage;
        }
    }

    private function detectFromBrowser() {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $this->currentLanguage = $this->fallbackLanguage;
            return;
        }
        
        $browserLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($browserLanguages as $lang) {
            $lang = substr($lang, 0, 2);
            if (array_key_exists($lang, $this->supportedLanguages)) {
                $this->currentLanguage = $lang;
                return;
            }
        }
        $this->currentLanguage = $this->fallbackLanguage;
    }

    public function getCurrentLanguage() {
        return $this->currentLanguage;
    }

    public function switchLanguage($lang) {
        if (array_key_exists($lang, $this->supportedLanguages)) {
            $this->currentLanguage = $lang;
            $_SESSION['language'] = $lang;
        }
    }

    public function getSupportedLanguages() {
        return $this->supportedLanguages;
    }

    /**
     * Scan the i18n directory and return a list of supported languages
     * @return array
     */
    private function scanI18nDirectory() {
        $languages = [];
        $i18nPath = defined('ROOT_PATH') ? ROOT_PATH . '/i18n' : __DIR__ . '/../../i18n';
        
        if (!is_dir($i18nPath)) {
            // Fallback to a basic set if i18n directory doesn't exist
            return [
                'en' => 'English',
                'sk' => 'Slovenčina',
                'es' => 'Español'
            ];
        }
        
        $files = scandir($i18nPath);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $langCode = pathinfo($file, PATHINFO_FILENAME);
                $languages[$langCode] = $this->getLanguageName($langCode);
            }
        }
        
        return $languages;
    }

    /**
     * Get the display name for a language code
     * @param string $langCode
     * @return string
     */
    private function getLanguageName($langCode) {
        $languageNames = [
            'en' => 'English',
            'sk' => 'Slovenčina',
            'es' => 'Español',
            'cs' => 'Čeština',
            'de' => 'Deutsch',
            'pl' => 'Polski',
            'hu' => 'Magyar',
            'uk' => 'Українська',
            'ru' => 'Русский',
            'it' => 'Italiano',
            'nl' => 'Nederlands',
            'fr' => 'Français',
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
            'tl' => 'Filipino',
            'sw' => 'Kiswahili',
            'am' => 'አማርኛ',
            'yo' => 'Yorùbá',
            'zu' => 'isiZulu'
        ];
        
        return isset($languageNames[$langCode]) ? $languageNames[$langCode] : ucfirst($langCode);
    }

    private $translations = [];
    
    public function translate($key) {
        // Load translations if not already loaded
        if (empty($this->translations)) {
            $this->loadTranslations();
        }
        
        return $this->translations[$key] ?? $key;
    }
    
    private function loadTranslations() {
        $i18nPath = defined('ROOT_PATH') ? ROOT_PATH . '/i18n' : __DIR__ . '/../../i18n';
        $translationFile = $i18nPath . '/' . $this->currentLanguage . '.php';
        
        if (file_exists($translationFile)) {
            $this->translations = require $translationFile;
        } else {
            // Fall back to default language
            $fallbackFile = $i18nPath . '/' . $this->fallbackLanguage . '.php';
            if (file_exists($fallbackFile)) {
                $this->translations = require $fallbackFile;
            }
        }
    }

    public static function getFlagIcon($langCode) {
        // This assumes you have flag icons named as 'xx.png' where 'xx' is the language code
        return '/assets/images/flags/' . $langCode . '.png';
    }
}

