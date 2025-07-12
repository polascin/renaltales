<?php

declare(strict_types=1);

namespace RenalTales\Models;

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../Core/LanguageDetector.php';

use RenalTales\Core\LanguageDetector;
use Exception;

/**
 * LanguageModel - Model for language operations
 * 
 * Handles language detection, loading, and management
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

class LanguageModel extends BaseModel {
    
    protected mixed $languageDetector;
    protected ?string $currentLanguage;
    protected ?string $currentLanguageName;
    protected array $texts = [];
    protected bool $initialized = false;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Call parent constructor to initialize database connection
        parent::__construct();
        
        // Initialize language detector
        try {
            if (class_exists('LanguageDetector')) {
                $this->languageDetector = new LanguageDetector();
                // Use getCurrentLanguage() instead of detectLanguage() to respect existing preferences
                $this->currentLanguage = $this->languageDetector->getCurrentLanguage();
                $this->currentLanguageName = $this->languageDetector->getLanguageName($this->currentLanguage);
            } else {
                $this->currentLanguage = 'en';
                $this->currentLanguageName = 'English';
            }
        } catch (Exception $e) {
            error_log('LanguageModel: Failed to initialize language detector: ' . $e->getMessage());
            $this->currentLanguage = 'en';
            $this->currentLanguageName = 'English';
        }
        
        $this->loadLanguageTexts();
        $this->initialized = true;
    }
    
    /**
     * Load language texts with fallback
     */
    private function loadLanguageTexts(): void {
        // Prevent redundant loading if already initialized
        if ($this->initialized && !empty($this->texts)) {
            return;
        }
        
        // Initialize basic fallback texts
        $this->texts = [
            'app_title' => 'Renal Tales',
            'welcome' => 'Welcome',
            'application_error' => 'Application Error',
            'service_unavailable' => 'Service Temporarily Unavailable',
            'try_again_later' => 'Please try again later.',
            'error' => 'Error',
            'file' => 'File',
            'line' => 'Line',
            'stack_trace' => 'Stack Trace',
            'initialization_failed' => 'Application initialization failed',
            'current_language' => 'Current language',
            'debug_mode_enabled' => 'Debug mode is enabled.',
        ];
        
        // Load English as base language
        $englishLanguageFile = LANGUAGE_PATH . 'en.php';
        if (file_exists($englishLanguageFile)) {
            $englishTexts = require $englishLanguageFile;
            if (is_array($englishTexts)) {
                $this->texts = array_merge($this->texts, $englishTexts);
            }
        }
        
        // Load current language if different from English
        if ($this->currentLanguage !== 'en') {
            $languageFile = LANGUAGE_PATH . $this->currentLanguage . '.php';
            if (file_exists($languageFile)) {
                $currentLanguageTexts = require $languageFile;
                if (is_array($currentLanguageTexts)) {
                    $this->texts = array_merge($this->texts, $currentLanguageTexts);
                }
            }
        }
    }
    
    /**
     * Get translated text with fallback
     * 
     * @param string $key
     * @param string $fallback
     * @return string
     */
    public function getText(string $key, string $fallback = ''): string {
        return isset($this->texts[$key]) ? $this->texts[$key] : $fallback;
    }
    
    /**
     * Get all texts
     * 
     * @return array
     */
    public function getAllTexts(): array {
        return $this->texts;
    }
    
    /**
     * Get current language
     * 
     * @return string
     */
    public function getCurrentLanguage(): string {
        return $this->currentLanguage ?? 'en';
    }
    
    /**
     * Get current language name
     * 
     * @return string
     */
    public function getCurrentLanguageName(): string {
        return $this->currentLanguageName ?? 'English';
    }
    
    /**
     * Get language detector instance
     * 
     * @return mixed
     */
    public function getLanguageDetector(): mixed {
        return $this->languageDetector;
    }
    
    /**
     * Get supported languages
     * 
     * @return array
     */
    public function getSupportedLanguages(): array {
        if ($this->languageDetector) {
            return $this->languageDetector->getSupportedLanguages();
        }
        return ['en'];
    }
    
    /**
     * Get language name by code
     * 
     * @param string $langCode
     * @return string
     */
    public function getLanguageName(string $langCode): string {
        if ($this->languageDetector) {
            return $this->languageDetector->getLanguageName($langCode);
        }
        return $langCode === 'en' ? 'English' : $langCode;
    }
    
    /**
     * Get flag path for language
     * 
     * @param string $langCode
     * @return string
     */
    public function getFlagPath(string $langCode): string {
        if ($this->languageDetector) {
            return $this->languageDetector->getFlagPath($langCode);
        }
        return '';
    }
    
    /**
     * Check if the language model is properly initialized
     * 
     * @return bool
     */
    public function isInitialized(): bool {
        return $this->initialized;
    }
    
    /**
     * Verify that texts are properly loaded for the selected language
     * 
     * @return bool
     */
    public function areTextsLoaded(): bool {
        // Check if we have basic required texts loaded
        $requiredTexts = ['app_title', 'welcome', 'error'];
        
        foreach ($requiredTexts as $key) {
            if (!isset($this->texts[$key]) || empty($this->texts[$key])) {
                return false;
            }
        }
        
        // Check if current language file was loaded (if not English)
        if ($this->currentLanguage !== 'en') {
            $languageFile = LANGUAGE_PATH . $this->currentLanguage . '.php';
            if (file_exists($languageFile)) {
                // If file exists, verify that we have more texts than just the basic fallback
                return count($this->texts) > 10; // Basic fallback has ~13 texts
            }
        }
        
        return true;
    }
    
    /**
     * Force reload of language texts (useful for language switching)
     * 
     * @return void
     */
    public function reloadTexts(): void {
        $this->texts = [];
        $this->loadLanguageTexts();
    }
    
    /**
     * Validate method (required by BaseModel)
     * 
     * @param array $data
     * @return array
     */
    protected function validate(array $data): array {
        // No validation needed for language model
        return [];
    }
}
