<?php

declare(strict_types=1);

require_once 'Contracts/LanguageInterface.php';
require_once 'Contracts/TranslationInterface.php';
require_once 'LanguageManager.php';
require_once 'TranslationManager.php';

use Core\Contracts\LanguageInterface;
use Core\Contracts\TranslationInterface;

/**
 * Multilingual Service Provider
 * 
 * Central service provider for all multilingual operations
 * Coordinates between LanguageManager and TranslationManager
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */
class MultilingualServiceProvider {
    
    /**
     * Language manager instance
     */
    private LanguageInterface $languageManager;
    
    /**
     * Translation manager instance
     */
    private TranslationInterface $translationManager;
    
    /**
     * Service configuration
     */
    private array $config;
    
    /**
     * Singleton instance
     */
    private static ?self $instance = null;
    
    /**
     * Constructor
     */
    private function __construct(array $config = []) {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->initializeServices();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(array $config = []): self {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        
        return self::$instance;
    }
    
    /**
     * Initialize the multilingual system
     */
    public function initialize(string $defaultLanguage = null): bool {
        try {
            // Get current language or set default
            if ($defaultLanguage) {
                $language = $defaultLanguage;
            } else {
                // Use getCurrentLanguage() to respect existing preferences
                $language = $this->languageManager->getCurrentLanguage();
            }
            
            // Set current language
            $this->setLanguage($language);
            
            return true;
            
        } catch(Exception $e) 
            error_log('MultilingualServiceProvider: Initialization failed: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Set current language
     */
    public function setLanguage(string $language): bool {
        try {
            // Validate language
            if (!$this->languageManager->isLanguageSupported($language)) {
                $language = $this->config['fallback_language'];
            }
            
            // Set language in both managers
            $languageSet = $this->languageManager->setLanguage($language);
            $translationSet = $this->translationManager->setLanguage($language);
            
            // Update session and cookie
            $this->languageManager->saveLanguageToSession($language);
            $this->languageManager->saveLanguageToCookie($language);
            
            return $languageSet && $translationSet;
            
        } catch(Exception $e) 
            error_log('MultilingualServiceProvider: Failed to set language: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage(): string {
        return $this->languageManager->getCurrentLanguage();
    }
    
    /**
     * Get translated text
     */
    public function getText(string $key, string $fallback = '', array $parameters = []): string {
        return $this->translationManager->getText($key, $fallback, $parameters);
    }
    
    /**
     * Get translated text with pluralization
     */
    public function getPlural(string $key, int $count, array $parameters = [], string $fallback = ''): string {
        return $this->translationManager->getPlural($key, $count, $parameters, $fallback);
    }
    
    /**
     * Get translated text with context
     */
    public function getTextWithContext(string $key, string $context, string $fallback = '', array $parameters = []): string {
        return $this->translationManager->getTextWithContext($key, $context, $fallback, $parameters);
    }
    
    /**
     * Get all available languages
     */
    public function getAvailableLanguages(): array {
        return $this->languageManager->getAvailableLanguages();
    }
    
    /**
     * Get language information
     */
    public function getLanguageInfo(string $language): array {
        return $this->languageManager->getLanguageInfo($language);
    }
    
    /**
     * Check if language is RTL
     */
    public function isRTL(string $language = null): bool {
        $lang = $language ?? $this->getCurrentLanguage();
        return $this->languageManager->isRTL($lang);
    }
    
    /**
     * Get language flag
     */
    public function getLanguageFlag(string $language): string {
        return $this->languageManager->getLanguageFlag($language);
    }
    
    /**
     * Get language native name
     */
    public function getLanguageNativeName(string $language): string {
        return $this->languageManager->getLanguageNativeName($language);
    }
    
    /**
     * Get language direction (ltr/rtl)
     */
    public function getLanguageDirection(string $language = null): string {
        $lang = $language ?? $this->getCurrentLanguage();
        return $this->isRTL($lang) ? 'rtl' : 'ltr';
    }
    
    /**
     * Generate language selection HTML
     */
    public function renderLanguageSelector(array $options = []): string {
        $defaults = [
            'show_flags' => true,
            'show_native_names' => true,
            'css_class' => 'language-selector',
            'current_first' => true
        ];
        
        $config = array_merge($defaults, $options);
        $languages = $this->getAvailableLanguages();
        $current = $this->getCurrentLanguage();
        
        // Sort languages - current first if requested
        if ($config['current_first'] && isset($languages[$current])) {
            $currentLang = [$current => $languages[$current]];
            unset($languages[$current]);
            $languages = $currentLang + $languages;
        }
        
        $html = '<div class="' . htmlspecialchars($config['css_class']) . '">';
        
        foreach ($languages as $code => $info) {
            $isActive = $code === $current;
            $activeClass = $isActive ? ' active' : '';
            
            $html .= '<a href="?lang=' . urlencode($code) . '" class="language-option' . $activeClass . '">';
            
            if ($config['show_flags']) {
                $flag = $this->getLanguageFlag($code);
                if ($flag) {
                    $html .= '<img src="' . htmlspecialchars($flag) . '" alt="' . htmlspecialchars($code) . '" class="language-flag">';
                }
            }
            
            if ($config['show_native_names']) {
                $name = $this->getLanguageNativeName($code);
                $html .= '<span class="language-name">' . htmlspecialchars($name) . '</span>';
            } else {
                $html .= '<span class="language-code">' . htmlspecialchars(strtoupper($code)) . '</span>';
            }
            
            $html .= '</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get system statistics
     */
    public function getSystemStats(): array {
        $languageStats = $this->languageManager->getSystemStats();
        $translationStats = $this->translationManager->getMemoryUsage();
        
        return [
            'current_language' => $this->getCurrentLanguage(),
            'supported_languages' => count($this->getAvailableLanguages()),
            'language_manager' => $languageStats,
            'translation_manager' => $translationStats,
            'total_memory' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
    
    /**
     * Clear all caches
     */
    public function clearCache(): void {
        $this->languageManager->clearCache();
        $this->translationManager->clearCache();
    }
    
    /**
     * Refresh language data
     */
    public function refresh(): bool {
        try {
            $this->clearCache();
            $currentLanguage = $this->getCurrentLanguage();
            
            // Reinitialize services
            $this->initializeServices();
            
            // Restore current language
            return $this->setLanguage($currentLanguage);
            
        } catch(Exception $e) 
            error_log('MultilingualServiceProvider: Refresh failed: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Check system health
     */
    public function healthCheck(): array {
        $issues = [];
        $status = 'healthy';
        
        try {
            // Check language manager
            if (!$this->languageManager->getCurrentLanguage()) {
                $issues[] = 'Language manager has no current language set';
                $status = 'warning';
            }
            
            // Check translation manager
            if (!$this->translationManager->hasText('common.welcome')) {
                $issues[] = 'Translation manager missing basic translations';
                $status = 'warning';
            }
            
            // Check available languages
            $languages = $this->getAvailableLanguages();
            if (empty($languages)) {
                $issues[] = 'No available languages found';
                $status = 'error';
            }
            
            // Check memory usage
            $memory = memory_get_usage(true);
            if ($memory > 128 * 1024 * 1024) { // 128MB
                $issues[] = 'High memory usage detected';
                $status = 'warning';
            }
            
        } catch(Exception $e) 
    error_log('Exception in MultilingualServiceProvider.php: ' . $e->getMessage());
            $issues[] = 'Health check failed: ' . $e->getMessage();
            $status = 'error';
        
        
        return [
            'status' => $status,
            'issues' => $issues,
            'timestamp' => date('Y-m-d H:i:s'),
            'stats' => $this->getSystemStats()
        ];
    }
    
    /**
     * Initialize services
     */
    private function initializeServices(): void {
        $this->languageManager = new LanguageManager($this->config);
        $this->translationManager = new TranslationManager($this->config['fallback_language']);
    }
    
    /**
     * Get default configuration
     */
    private function getDefaultConfig(): array {
        return [
            'fallback_language' => 'en',
            'session_key' => 'language',
            'cookie_name' => 'language',
            'cookie_lifetime' => 86400 * 30, // 30 days
            'auto_detect' => true,
            'cache_enabled' => true,
            'cache_lifetime' => 1800, // 30 minutes
            'security_enabled' => true
        ];
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize a singleton.');
    }
}
