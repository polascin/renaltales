<?php

declare(strict_types=1);

namespace Core\Contracts;

/**
 * Translation Interface
 * 
 * Defines the contract for translation management implementations
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */
interface TranslationInterface {
    
    /**
     * Get translated text
     * 
     * @param string $key Translation key
     * @param string $fallback Fallback text
     * @param array $parameters Replacement parameters
     * @return string Translated text
     */
    public function getText(string $key, string $fallback = '', array $parameters = []): string;
    
    /**
     * Get translated text with pluralization
     * 
     * @param string $key Translation key
     * @param int $count Count for pluralization
     * @param array $parameters Replacement parameters
     * @param string $fallback Fallback text
     * @return string Translated text
     */
    public function getPlural(string $key, int $count, array $parameters = [], string $fallback = ''): string;
    
    /**
     * Get all translations for current language
     * 
     * @return array All translations
     */
    public function getAllTexts(): array;
    
    /**
     * Check if translation key exists
     * 
     * @param string $key Translation key
     * @return bool Key existence
     */
    public function hasText(string $key): bool;
    
    /**
     * Load translations for language
     * 
     * @param string $language Language code
     * @return bool Success status
     */
    public function loadTranslations(string $language): bool;
    
    /**
     * Set translation for key
     * 
     * @param string $key Translation key
     * @param string $value Translation value
     * @return bool Success status
     */
    public function setText(string $key, string $value): bool;
    
    /**
     * Get translation with context
     * 
     * @param string $key Translation key
     * @param string $context Translation context
     * @param string $fallback Fallback text
     * @param array $parameters Replacement parameters
     * @return string Translated text
     */
    public function getTextWithContext(string $key, string $context, string $fallback = '', array $parameters = []): string;
    
    /**
     * Set current language
     * 
     * @param string $language Language code
     * @return bool Success status
     */
    public function setLanguage(string $language): bool;
    
    /**
     * Get current language
     * 
     * @return string Current language
     */
    public function getCurrentLanguage(): string;
    
    /**
     * Get memory usage information
     * 
     * @return array Memory usage stats
     */
    public function getMemoryUsage(): array;
    
    /**
     * Clear translation cache
     * 
     * @return void
     */
    public function clearCache(): void;
}
