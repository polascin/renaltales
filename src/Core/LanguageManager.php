<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Models\LanguageModel;

/**
 * Language Manager
 *
 * Coordinates the interaction between LanguageDetector and LanguageModel
 * Provides a unified interface for language management
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class LanguageManager
{
    private LanguageModel $languageModel;
    private LanguageDetector $languageDetector;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->languageModel = new LanguageModel();
        $this->languageDetector = new LanguageDetector($this->languageModel);
        
        // Initialize with detected language
        $detectedLanguage = $this->languageDetector->detectLanguage();
        $this->languageModel->setLanguage($detectedLanguage);
    }

    /**
     * Get the language model instance
     */
    public function getLanguageModel(): LanguageModel
    {
        return $this->languageModel;
    }

    /**
     * Get the language detector instance
     */
    public function getLanguageDetector(): LanguageDetector
    {
        return $this->languageDetector;
    }

    /**
     * Set language with proper detection coordination
     */
    public function setLanguage(string $language): bool
    {
        if ($this->languageModel->setLanguage($language)) {
            // Update user preferences after successful language change
            $this->languageDetector->setSessionLanguage($language);
            $this->languageDetector->setCookieLanguage($language);
            return true;
        }
        
        return false;
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return $this->languageModel->getCurrentLanguage();
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->languageModel->getSupportedLanguages();
    }

    /**
     * Check if language is supported
     */
    public function isSupported(string $language): bool
    {
        return $this->languageModel->isSupported($language);
    }

    /**
     * Get translated text
     */
    public function getText(string $key, array $parameters = [], string $fallback = ''): string
    {
        return $this->languageModel->getText($key, $parameters, $fallback);
    }

    /**
     * Get all translations
     */
    public function getAllTexts(): array
    {
        return $this->languageModel->getAllTexts();
    }

    /**
     * Re-detect and set language (useful for runtime changes)
     */
    public function redetectLanguage(): string
    {
        $detectedLanguage = $this->languageDetector->detectLanguage();
        $this->languageModel->setLanguage($detectedLanguage);
        return $detectedLanguage;
    }
}
