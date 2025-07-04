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
        $this->supportedLanguages = $GLOBALS['SUPPORTED_STORY_LANGUAGES'];
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

    public function translate($key) {
        // In a real application, you would load translations from files or a database
        // For demonstration, return the key as is
        return $key;
    }

    public static function getFlagIcon($langCode) {
        // This assumes you have flag icons named as 'xx.png' where 'xx' is the language code
        return '/assets/images/flags/' . $langCode . '.png';
    }
}

