<?php

declare(strict_types=1);

require_once 'Contracts/TranslationInterface.php';

use Core\Contracts\TranslationInterface;

/**
 * Translation Manager
 * 
 * Enhanced translation management with pluralization, context, and caching
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */
class TranslationManager implements TranslationInterface {
    
    /**
     * Configuration constants
     */
    private const FALLBACK_LANGUAGE = 'en';
    private const CACHE_LIFETIME = 1800; // 30 minutes
    private const PARAMETER_PATTERN = '/\{([a-zA-Z0-9_]+)\}/';
    
    /**
     * Current language
     */
    private string $currentLanguage;
    
    /**
     * Loaded translations
     */
    private array $translations = [];
    
    /**
     * Fallback translations
     */
    private array $fallbackTranslations = [];
    
    /**
     * Cache for processed translations
     */
    private array $cache = [];
    
    /**
     * Constructor
     */
    public function __construct(string $language = self::FALLBACK_LANGUAGE) {
        $this->currentLanguage = $language;
        $this->loadFallbackTranslations();
        $this->loadTranslations($language);
    }
    
    /**
     * Get translated text
     */
    public function getText(string $key, string $fallback = '', array $parameters = []): string {
        $cacheKey = $this->getCacheKey($key, $parameters);
        
        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $text = $this->getTranslationText($key, $fallback);
        $text = $this->processParameters($text, $parameters);
        
        // Cache the result
        $this->cache[$cacheKey] = $text;
        
        return $text;
    }
    
    /**
     * Get translated text with pluralization
     */
    public function getPlural(string $key, int $count, array $parameters = [], string $fallback = ''): string {
        $pluralKey = $this->getPluralKey($key, $count);
        $parameters['count'] = $count;
        
        return $this->getText($pluralKey, $fallback, $parameters);
    }
    
    /**
     * Get all translations for current language
     */
    public function getAllTexts(): array {
        return $this->translations;
    }
    
    /**
     * Check if translation key exists
     */
    public function hasText(string $key): bool {
        return isset($this->translations[$key]) || isset($this->fallbackTranslations[$key]);
    }
    
    /**
     * Load translations for language
     */
    public function loadTranslations(string $language): bool {
        try {
            $this->currentLanguage = $language;
            
            // Load base language file
            $baseFile = $this->getLanguageFilePath($language);
            if (file_exists($baseFile)) {
                $translations = require $baseFile;
                if (is_array($translations)) {
                    $this->translations = $translations;
                }
            }
            
            // Load contextual translations if they exist
            $this->loadContextualTranslations($language);
            
            return true;
            
        } catch(Exception $e) 
            error_log('TranslationManager: Failed to load translations for ' . $language . ': ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Set translation for key
     */
    public function setText(string $key, string $value): bool {
        try {
            $this->translations[$key] = $value;
            
            // Clear related cache entries
            $this->clearCacheForKey($key);
            
            return true;
            
        } catch(Exception $e) 
            error_log('TranslationManager: Failed to set text for key ' . $key . ': ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Get translation with context
     */
    public function getTextWithContext(string $key, string $context, string $fallback = '', array $parameters = []): string {
        $contextKey = $context . '.' . $key;
        
        // Try context-specific key first
        if ($this->hasText($contextKey)) {
            return $this->getText($contextKey, $fallback, $parameters);
        }
        
        // Fall back to regular key
        return $this->getText($key, $fallback, $parameters);
    }
    
    /**
     * Change current language
     */
    public function setLanguage(string $language): bool {
        if ($language === $this->currentLanguage) {
            return true;
        }
        
        // Clear cache when language changes
        $this->cache = [];
        
        return $this->loadTranslations($language);
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage(): string {
        return $this->currentLanguage;
    }
    
    /**
     * Get translation text with fallback
     */
    private function getTranslationText(string $key, string $fallback): string {
        // Check current language translations
        if (isset($this->translations[$key])) {
            return $this->translations[$key];
        }
        
        // Check fallback language translations
        if (isset($this->fallbackTranslations[$key])) {
            return $this->fallbackTranslations[$key];
        }
        
        // Return provided fallback or key
        return !empty($fallback) ? $fallback : $key;
    }
    
    /**
     * Process parameters in text
     */
    private function processParameters(string $text, array $parameters): string {
        if (empty($parameters)) {
            return $text;
        }
        
        return preg_replace_callback(self::PARAMETER_PATTERN, function($matches) use ($parameters) {
            $param = $matches[1];
            return isset($parameters[$param]) ? (string)$parameters[$param] : $matches[0];
        }, $text);
    }
    
    /**
     * Get plural key based on count
     */
    private function getPluralKey(string $key, int $count): string {
        // Simple English pluralization rules
        // This can be extended for other languages
        if ($count === 0) {
            return $key . '_zero';
        } elseif ($count === 1) {
            return $key . '_one';
        } else {
            return $key . '_other';
        }
    }
    
    /**
     * Load fallback translations
     */
    private function loadFallbackTranslations(): void {
        try {
            $fallbackFile = $this->getLanguageFilePath(self::FALLBACK_LANGUAGE);
            if (file_exists($fallbackFile)) {
                $translations = require $fallbackFile;
                if (is_array($translations)) {
                    $this->fallbackTranslations = $translations;
                }
            }
            
        } catch(Exception $e) 
            error_log('TranslationManager: Failed to load fallback translations: ' . $e->getMessage());
        
    }
    
    /**
     * Load contextual translations
     */
    private function loadContextualTranslations(string $language): void {
        try {
            // Look for context-specific files
            $contextDir = $this->getLanguageDirectory() . $language . '/';
            if (is_dir($contextDir)) {
                $contextFiles = glob($contextDir . '*.php');
                foreach ($contextFiles as $file) {
                    $context = basename($file, '.php');
                    $contextTranslations = require $file;
                    
                    if (is_array($contextTranslations)) {
                        // Prefix keys with context
                        foreach ($contextTranslations as $key => $value) {
                            $this->translations[$context . '.' . $key] = $value;
                        }
                    }
                }
            }
            
        } catch(Exception $e) 
            error_log('TranslationManager: Failed to load contextual translations: ' . $e->getMessage());
        
    }
    
    /**
     * Get language file path
     */
    private function getLanguageFilePath(string $language): string {
        return $this->getLanguageDirectory() . $language . '.php';
    }
    
    /**
     * Get language directory
     */
    private function getLanguageDirectory(): string {
        return defined('LANGUAGE_PATH') ? LANGUAGE_PATH : 'resources/lang/';
    }
    
    /**
     * Get cache key for translation
     */
    private function getCacheKey(string $key, array $parameters): string {
        return md5($this->currentLanguage . ':' . $key . ':' . serialize($parameters));
    }
    
    /**
     * Clear cache for specific key
     */
    private function clearCacheForKey(string $key): void {
        $keyPrefix = md5($this->currentLanguage . ':' . $key . ':');
        foreach ($this->cache as $cacheKey => $value) {
            if (strpos($cacheKey, $keyPrefix) === 0) {
                unset($this->cache[$cacheKey]);
            }
        }
    }
    
    /**
     * Get memory usage info
     */
    public function getMemoryUsage(): array {
        return [
            'translations_count' => count($this->translations),
            'fallback_count' => count($this->fallbackTranslations),
            'cache_count' => count($this->cache),
            'memory_usage' => memory_get_usage(true)
        ];
    }
    
    /**
     * Clear all cache
     */
    public function clearCache(): void {
        $this->cache = [];
    }
}
