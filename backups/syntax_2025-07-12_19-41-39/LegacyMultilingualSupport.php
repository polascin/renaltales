<?php

declare(strict_types=1);

require_once 'core/MultilingualServiceProvider.php';

/**
 * Legacy Multilingual Compatibility Helper
 * 
 * Provides backward compatibility with existing LanguageDetector and LanguageModel usage
 * Allows gradual migration to new system
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

/**
 * Legacy LanguageDetector compatibility class
 */
class LanguageDetector {
    
    private static ?MultilingualServiceProvider $multilingual = null;
    
    /**
     * Initialize legacy compatibility
     */
    private static function init(): void {
        if (self::$multilingual === null) {
            $config = require_once 'config/multilingual.php';
            self::$multilingual = MultilingualServiceProvider::getInstance($config);
            self::$multilingual->initialize();
        }
    }
    
    /**
     * Detect user's preferred language
     */
    public function detectLanguage(): string {
        self::init();
        return self::$multilingual->getCurrentLanguage();
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage(): string {
        self::init();
        return self::$multilingual->getCurrentLanguage();
    }
    
    /**
     * Set language
     */
    public function setLanguage(string $language): bool {
        self::init();
        return self::$multilingual->setLanguage($language);
    }
    
    /**
     * Check if language is RTL
     */
    public function isRTL(string $language = null): bool {
        self::init();
        return self::$multilingual->isRTL($language);
    }
    
    /**
     * Get language direction
     */
    public function getDirection(string $language = null): string {
        self::init();
        return self::$multilingual->getLanguageDirection($language);
    }
    
    /**
     * Get language flag
     */
    public function getFlag(string $language): string {
        self::init();
        return self::$multilingual->getLanguageFlag($language);
    }
    
    /**
     * Get language name
     */
    public function getLanguageName(string $language): string {
        self::init();
        $info = self::$multilingual->getLanguageInfo($language);
        return $info['name'] ?? $language;
    }
    
    /**
     * Get native language name
     */
    public function getNativeName(string $language): string {
        self::init();
        return self::$multilingual->getLanguageNativeName($language);
    }
    
    /**
     * Get available languages
     */
    public function getAvailableLanguages(): array {
        self::init();
        return self::$multilingual->getAvailableLanguages();
    }
    
    /**
     * Legacy method: Get language list
     */
    public function getLanguageList(): array {
        return $this->getAvailableLanguages();
    }
    
    /**
     * Legacy method: Save language preference
     */
    public function saveLanguagePreference(string $language): bool {
        self::init();
        return self::$multilingual->setLanguage($language);
    }
}

/**
 * Legacy LanguageModel compatibility class
 */
class LanguageModel {
    
    private static ?MultilingualServiceProvider $multilingual = null;
    
    /**
     * Initialize legacy compatibility
     */
    private static function init(): void {
        if (self::$multilingual === null) {
            $config = require_once 'config/multilingual.php';
            self::$multilingual = MultilingualServiceProvider::getInstance($config);
            self::$multilingual->initialize();
        }
    }
    
    /**
     * Get translated text
     */
    public function getText(string $key, array $parameters = [], string $fallback = ''): string {
        self::init();
        return self::$multilingual->getText($key, $fallback, $parameters);
    }
    
    /**
     * Get plural translation
     */
    public function getPlural(string $key, int $count, array $parameters = []): string {
        self::init();
        $parameters['count'] = $count;
        return self::$multilingual->getPlural($key, $count, $parameters);
    }
    
    /**
     * Check if translation exists
     */
    public function hasTranslation(string $key): bool {
        self::init();
        // Access translation manager through reflection or implement hasText in service provider
        return !empty(self::$multilingual->getText($key));
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage(): string {
        self::init();
        return self::$multilingual->getCurrentLanguage();
    }
    
    /**
     * Set language
     */
    public function setLanguage(string $language): bool {
        self::init();
        return self::$multilingual->setLanguage($language);
    }
    
    /**
     * Legacy method: Get text with context
     */
    public function getContextText(string $context, string $key, array $parameters = []): string {
        self::init();
        return self::$multilingual->getTextWithContext($key, $context, '', $parameters);
    }
    
    /**
     * Legacy method: Load language file
     */
    public function loadLanguage(string $language): bool {
        self::init();
        return self::$multilingual->setLanguage($language);
    }
    
    /**
     * Legacy method: Get all translations
     */
    public function getAllTranslations(): array {
        self::init();
        // This would need to be implemented in the service provider
        return [];
    }
}

/**
 * Global helper functions for backward compatibility
 */

/**
 * Global translation function
 */
if (!function_exists('t')) {
    function t(string $key, array $parameters = [], string $fallback = ''): string {
        static $multilingual = null;
        
        if ($multilingual === null) {
            $config = require_once 'config/multilingual.php';
            $multilingual = MultilingualServiceProvider::getInstance($config);
            $multilingual->initialize();
        }
        
        return $multilingual->getText($key, $fallback, $parameters);
    }
}

/**
 * Global plural translation function
 */
if (!function_exists('tn')) {
    function tn(string $key, int $count, array $parameters = []): string {
        static $multilingual = null;
        
        if ($multilingual === null) {
            $config = require_once 'config/multilingual.php';
            $multilingual = MultilingualServiceProvider::getInstance($config);
            $multilingual->initialize();
        }
        
        $parameters['count'] = $count;
        return $multilingual->getPlural($key, $count, $parameters);
    }
}

/**
 * Get current language
 */
if (!function_exists('getCurrentLang')) {
    function getCurrentLang(): string {
        static $multilingual = null;
        
        if ($multilingual === null) {
            $config = require_once 'config/multilingual.php';
            $multilingual = MultilingualServiceProvider::getInstance($config);
            $multilingual->initialize();
        }
        
        return $multilingual->getCurrentLanguage();
    }
}

/**
 * Check if current language is RTL
 */
if (!function_exists('isRTL')) {
    function isRTL(string $language = null): bool {
        static $multilingual = null;
        
        if ($multilingual === null) {
            $config = require_once 'config/multilingual.php';
            $multilingual = MultilingualServiceProvider::getInstance($config);
            $multilingual->initialize();
        }
        
        return $multilingual->isRTL($language);
    }
}

/**
 * Get language direction
 */
if (!function_exists('getLanguageDirection')) {
    function getLanguageDirection(string $language = null): string {
        static $multilingual = null;
        
        if ($multilingual === null) {
            $config = require_once 'config/multilingual.php';
            $multilingual = MultilingualServiceProvider::getInstance($config);
            $multilingual->initialize();
        }
        
        return $multilingual->getLanguageDirection($language);
    }
}

/**
 * Legacy ApplicationView compatibility methods
 */
trait LegacyMultilingualTrait {
    
    /**
     * Legacy method: Get language detector
     */
    protected function getLanguageDetector(): LanguageDetector {
        return new LanguageDetector();
    }
    
    /**
     * Legacy method: Get language model
     */
    protected function getLanguageModel(): LanguageModel {
        return new LanguageModel();
    }
    
    /**
     * Legacy method: Render language flags
     */
    protected function renderLanguageFlags(): string {
        $config = require_once 'config/multilingual.php';
        $multilingual = MultilingualServiceProvider::getInstance($config);
        $multilingual->initialize();
        
        return $multilingual->renderLanguageSelector([
            'show_flags' => true,
            'show_native_names' => false,
            'css_class' => 'language-flags'
        ]);
    }
    
    /**
     * Legacy method: Render language selection
     */
    protected function renderLanguageSelection(): string {
        $config = require_once 'config/multilingual.php';
        $multilingual = MultilingualServiceProvider::getInstance($config);
        $multilingual->initialize();
        
        return $multilingual->renderLanguageSelector([
            'show_flags' => true,
            'show_native_names' => true,
            'css_class' => 'language-selection'
        ]);
    }
    
    /**
     * Legacy method: Get current language
     */
    protected function getCurrentLanguage(): string {
        return getCurrentLang();
    }
    
    /**
     * Legacy method: Set language
     */
    protected function setLanguage(string $language): bool {
        $config = require_once 'config/multilingual.php';
        $multilingual = MultilingualServiceProvider::getInstance($config);
        return $multilingual->setLanguage($language);
    }
}

/**
 * Migration notice for developers
 */
class MultilingualMigrationNotice {
    
    /**
     * Show deprecation warning
     */
    public static function showDeprecationWarning(string $oldMethod, string $newMethod): void {
        $config = require_once 'config/multilingual.php';
        if (isset($config['show_deprecation_warnings']) && $config['show_deprecation_warnings']) {
            error_log("DEPRECATED: $oldMethod is deprecated. Use $newMethod instead.");
        }
    }
    
    /**
     * Log usage for migration tracking
     */
    public static function logUsage(string $method, string $file, int $line): void {
        $config = require_once 'config/multilingual.php';
        if (isset($config['log_legacy_usage']) && $config['log_legacy_usage']) {
            $logEntry = date('Y-m-d H:i:s') . " - Legacy method '$method' used in $file:$line\n";
            file_put_contents('legacy_multilingual_usage.log', $logEntry, FILE_APPEND);
        }
    }
}
