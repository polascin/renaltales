<?php

declare(strict_types=1);

namespace Core\Contracts;

/**
 * Language Interface
 * 
 * Defines the contract for language management implementations
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */
interface LanguageInterface {
    
    /**
     * Detect user's preferred language
     * 
     * @return string Language code
     */
    public function detectLanguage(): string;
    
    /**
     * Get current active language
     * 
     * @return string Current language code
     */
    public function getCurrentLanguage(): string;
    
    /**
     * Set language preference
     * 
     * @param string $language Language code
     * @return bool Success status
     */
    public function setLanguage(string $language): bool;
    
    /**
     * Check if language is supported
     * 
     * @param string $language Language code
     * @return bool Support status
     */
    public function isSupported(string $language): bool;
    
    /**
     * Get list of supported languages
     * 
     * @return array Language codes
     */
    public function getSupportedLanguages(): array;
    
    /**
     * Get display name for language
     * 
     * @param string $language Language code
     * @return string Display name
     */
    public function getLanguageName(string $language): string;
    
    /**
     * Get native name for language
     * 
     * @param string $language Language code
     * @return string Native name
     */
    public function getNativeName(string $language): string;
    
    /**
     * Check if language uses RTL writing
     * 
     * @param string $language Language code
     * @return bool RTL status
     */
    public function isRTL(string $language): bool;
    
    /**
     * Get text direction for language
     * 
     * @param string $language Language code
     * @return string Direction (ltr/rtl)
     */
    public function getDirection(string $language): string;
    
    /**
     * Get flag code for language
     * 
     * @param string $language Language code
     * @return string Flag code
     */
    public function getFlagCode(string $language): string;
    
    /**
     * Get flag path for language
     * 
     * @param string $language Language code
     * @param string $basePath Base path for flags
     * @param string $extension File extension
     * @return string Flag file path
     */
    public function getFlagPath(string $language, string $basePath = 'assets/flags/', string $extension = '.webp'): string;
    
    /**
     * Get available languages with info
     * 
     * @return array Available languages
     */
    public function getAvailableLanguages(): array;
    
    /**
     * Get language information
     * 
     * @param string $language Language code
     * @return array Language information
     */
    public function getLanguageInfo(string $language): array;
    
    /**
     * Get language flag URL
     * 
     * @param string $language Language code
     * @return string Flag URL
     */
    public function getLanguageFlag(string $language): string;
    
    /**
     * Get language native name
     * 
     * @param string $language Language code
     * @return string Native name
     */
    public function getLanguageNativeName(string $language): string;
    
    /**
     * Save language to session
     * 
     * @param string $language Language code
     * @return bool Success status
     */
    public function saveLanguageToSession(string $language): bool;
    
    /**
     * Save language to cookie
     * 
     * @param string $language Language code
     * @return bool Success status
     */
    public function saveLanguageToCookie(string $language): bool;
    
    /**
     * Check if language is supported (alias for isSupported)
     * 
     * @param string $language Language code
     * @return bool Support status
     */
    public function isLanguageSupported(string $language): bool;
    
    /**
     * Get system statistics
     * 
     * @return array System stats
     */
    public function getSystemStats(): array;
    
    /**
     * Clear cache
     * 
     * @return void
     */
    public function clearCache(): void;
}
