<?php

declare(strict_types=1);

namespace RenalTales\Services;

use RenalTales\Models\Translation;
use RenalTales\Models\Language;
use RenalTales\Services\TranslationCache;

/**
 * Translation Service
 * 
 * Main service for handling translations with caching and fallback support
 */
class TranslationService
{
    private Translation $translationModel;
    private Language $languageModel;
    private TranslationCache $cache;
    private string $currentLanguage;
    private string $fallbackLanguage;
    private array $supportedLanguages;

    public function __construct()
    {
        $this->translationModel = new Translation();
        $this->languageModel = new Language();
        $this->cache = new TranslationCache();
        
        // Initialize languages
        $this->initializeLanguages();
        
        // Set current language from session or default
        $this->setCurrentLanguage($this->getCurrentLanguageFromSession());
    }

    /**
     * Initialize supported languages and fallback
     */
    private function initializeLanguages(): void
    {
        $this->supportedLanguages = $this->languageModel->getSupportedLanguageCodes();
        $this->fallbackLanguage = $this->getFallbackLanguageCode();
    }

    /**
     * Get current language from session or default
     */
    private function getCurrentLanguageFromSession(): string
    {
        if (isset($_SESSION['language']) && in_array($_SESSION['language'], $this->supportedLanguages)) {
            return $_SESSION['language'];
        }
        
        $defaultLang = $this->languageModel->getDefaultLanguage();
        return $defaultLang['code'] ?? 'en';
    }

    /**
     * Get fallback language code
     */
    private function getFallbackLanguageCode(): string
    {
        $fallbackLang = $this->languageModel->getFallbackLanguage();
        return $fallbackLang['code'] ?? 'en';
    }

    /**
     * Set current language
     */
    public function setCurrentLanguage(string $languageCode): bool
    {
        if (!in_array($languageCode, $this->supportedLanguages)) {
            return false;
        }
        
        $this->currentLanguage = $languageCode;
        $_SESSION['language'] = $languageCode;
        
        return true;
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * Get translation with caching and fallback
     */
    public function translate(string $key, string $group = 'default', array $parameters = []): string
    {
        // Try to get from cache first
        $cacheKey = "translation_{$group}_{$key}";
        $cachedTranslation = $this->cache->get($this->currentLanguage, $cacheKey);
        
        if ($cachedTranslation !== null) {
            $translation = $cachedTranslation['text'];
        } else {
            // Get from database
            $translation = $this->translationModel->getTranslation($key, $this->currentLanguage, $group);
            
            // Fallback to default language if not found
            if ($translation === null && $this->currentLanguage !== $this->fallbackLanguage) {
                $translation = $this->translationModel->getTranslation($key, $this->fallbackLanguage, $group);
            }
            
            // If still not found, return the key itself
            if ($translation === null) {
                $translation = $key;
            }
            
            // Cache the result
            $this->cache->set($this->currentLanguage, $cacheKey, ['text' => $translation]);
        }
        
        // Replace parameters if provided
        if (!empty($parameters)) {
            $translation = $this->replaceParameters($translation, $parameters);
        }
        
        return $translation;
    }

    /**
     * Replace parameters in translation
     */
    private function replaceParameters(string $translation, array $parameters): string
    {
        foreach ($parameters as $key => $value) {
            $translation = str_replace(':' . $key, $value, $translation);
        }
        
        return $translation;
    }

    /**
     * Get all translations for current language
     */
    public function getAllTranslations(string $group = null): array
    {
        $cacheKey = $group ? "group_{$group}" : "all_translations";
        
        // Try cache first
        $cached = $this->cache->get($this->currentLanguage, $cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        // Get from database
        $translations = $this->translationModel->getAllTranslations($this->currentLanguage, $group);
        
        // Format the result
        $result = [];
        foreach ($translations as $translation) {
            if ($group === null) {
                $result[$translation['group_name']][$translation['key_name']] = $translation['translation_text'];
            } else {
                $result[$translation['key_name']] = $translation['translation_text'];
            }
        }
        
        // Cache the result
        $this->cache->set($this->currentLanguage, $cacheKey, $result);
        
        return $result;
    }

    /**
     * Get available languages
     */
    public function getAvailableLanguages(): array
    {
        return $this->languageModel->getActiveLanguages();
    }

    /**
     * Get language by code
     */
    public function getLanguageByCode(string $code): ?array
    {
        return $this->languageModel->getLanguageByCode($code);
    }

    /**
     * Check if language is supported
     */
    public function isLanguageSupported(string $code): bool
    {
        return in_array($code, $this->supportedLanguages);
    }

    /**
     * Save translation
     */
    public function saveTranslation(string $key, string $text, string $group = 'default', string $languageCode = null): bool
    {
        $languageCode = $languageCode ?? $this->currentLanguage;
        
        $success = $this->translationModel->saveTranslation($key, $languageCode, $text, $group);
        
        if ($success) {
            // Clear cache for this language
            $this->cache->clearLanguageCache($languageCode);
        }
        
        return $success;
    }

    /**
     * Delete translation
     */
    public function deleteTranslation(string $key, string $group = 'default', string $languageCode = null): bool
    {
        $languageCode = $languageCode ?? $this->currentLanguage;
        
        $success = $this->translationModel->deleteTranslation($key, $languageCode, $group);
        
        if ($success) {
            // Clear cache for this language
            $this->cache->clearLanguageCache($languageCode);
        }
        
        return $success;
    }

    /**
     * Import translations from array
     */
    public function importTranslations(array $translations, string $languageCode = null): bool
    {
        $languageCode = $languageCode ?? $this->currentLanguage;
        
        $success = $this->translationModel->importTranslations($translations, $languageCode);
        
        if ($success) {
            // Clear cache for this language
            $this->cache->clearLanguageCache($languageCode);
        }
        
        return $success;
    }

    /**
     * Export translations
     */
    public function exportTranslations(string $languageCode = null, string $group = null): array
    {
        $languageCode = $languageCode ?? $this->currentLanguage;
        
        return $this->translationModel->exportTranslations($languageCode, $group);
    }

    /**
     * Get translation groups
     */
    public function getTranslationGroups(): array
    {
        return $this->translationModel->getGroups();
    }

    /**
     * Search translations
     */
    public function searchTranslations(string $search, string $languageCode = null): array
    {
        $languageCode = $languageCode ?? $this->currentLanguage;
        
        return $this->translationModel->searchTranslations($search, $languageCode);
    }

    /**
     * Get translation statistics
     */
    public function getTranslationStatistics(): array
    {
        return $this->translationModel->getStatistics();
    }

    /**
     * Get language statistics
     */
    public function getLanguageStatistics(): array
    {
        return $this->languageModel->getLanguageStatistics();
    }

    /**
     * Warm up cache for current language
     */
    public function warmUpCache(): bool
    {
        return $this->cache->warmUpLanguageCache($this->currentLanguage);
    }

    /**
     * Clear cache for current language
     */
    public function clearCache(): bool
    {
        return $this->cache->clearLanguageCache($this->currentLanguage);
    }

    /**
     * Clear all cache
     */
    public function clearAllCache(): bool
    {
        return $this->cache->clearAllCache();
    }

    /**
     * Get cache statistics
     */
    public function getCacheStatistics(): array
    {
        return $this->cache->getCacheStatistics();
    }

    /**
     * Clean expired cache
     */
    public function cleanExpiredCache(): bool
    {
        return $this->cache->cleanExpiredCache();
    }

    /**
     * Get current language direction (ltr/rtl)
     */
    public function getCurrentLanguageDirection(): string
    {
        $language = $this->getLanguageByCode($this->currentLanguage);
        return $language['direction'] ?? 'ltr';
    }

    /**
     * Get current language name
     */
    public function getCurrentLanguageName(): string
    {
        $language = $this->getLanguageByCode($this->currentLanguage);
        return $language['name'] ?? 'Unknown';
    }

    /**
     * Get current language native name
     */
    public function getCurrentLanguageNativeName(): string
    {
        $language = $this->getLanguageByCode($this->currentLanguage);
        return $language['native_name'] ?? 'Unknown';
    }

    /**
     * Get current language flag
     */
    public function getCurrentLanguageFlag(): string
    {
        $language = $this->getLanguageByCode($this->currentLanguage);
        return $language['flag_icon'] ?? '';
    }

    /**
     * Check if current language is RTL
     */
    public function isCurrentLanguageRTL(): bool
    {
        return $this->getCurrentLanguageDirection() === 'rtl';
    }

    /**
     * Get fallback language
     */
    public function getFallbackLanguage(): string
    {
        return $this->fallbackLanguage;
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Refresh supported languages from database
     */
    public function refreshSupportedLanguages(): void
    {
        $this->supportedLanguages = $this->languageModel->getSupportedLanguageCodes();
    }
}
