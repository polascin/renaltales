<?php

declare(strict_types=1);

namespace RenalTales\Services;

use RenalTales\Repositories\CachedLanguageRepository;
use RenalTales\Core\SessionManager;
use RenalTales\Models\LanguageModel;

/**
 * Language Service
 *
 * Handles language-related business logic and operations.
 * Acts as an intermediary between controllers and repositories.
 *
 * @package RenalTales\Services
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class LanguageService
{
    /**
     * @var LanguageRepository The language repository
     */
    private CachedLanguageRepository $languageRepository;

    /**
     * @var SessionManager The session manager
     */
    private SessionManager $sessionManager;

    /**
     * @var LanguageModel The language model
     */
    private LanguageModel $languageModel;

    /**
     * Constructor
     *
     * @param CachedLanguageRepository $languageRepository The cached language repository
     * @param SessionManager $sessionManager The session manager
     * @param LanguageModel $languageModel The language model
     */
    public function __construct(
        CachedLanguageRepository $languageRepository,
        SessionManager $sessionManager,
        LanguageModel $languageModel
    ) {
        $this->languageRepository = $languageRepository;
        $this->sessionManager = $sessionManager;
        $this->languageModel = $languageModel;
    }

    /**
     * Get all supported languages
     *
     * @return array<string> Array of supported language codes
     */
    public function getSupportedLanguages(): array
    {
        return $this->languageRepository->findAll();
    }

    /**
     * Get the current language
     *
     * @return string The current language code
     */
    public function getCurrentLanguage(): string
    {
        return $this->languageModel->getCurrentLanguage();
    }

    /**
     * Set the current language
     *
     * @param string $language The language code to set
     * @return bool True if successful, false otherwise
     */
    public function setLanguage(string $language): bool
    {
        if (!$this->isLanguageSupported($language)) {
            return false;
        }

        $result = $this->languageModel->setLanguage($language);
        
        if ($result) {
            // Store in session for persistence
            $this->sessionManager->set('language', $language);
        }

        return $result;
    }

    /**
     * Check if a language is supported
     *
     * @param string $language The language code to check
     * @return bool True if supported, false otherwise
     */
    public function isLanguageSupported(string $language): bool
    {
        return $this->languageRepository->exists($language);
    }

    /**
     * Get translated text
     *
     * @param string $key The translation key
     * @param array<string, string|int|float> $parameters Parameters for replacement
     * @param string $fallback Fallback text if key not found
     * @return string The translated text
     */
    public function getText(string $key, array $parameters = [], string $fallback = ''): string
    {
        return $this->languageModel->getText($key, $parameters, $fallback);
    }

    /**
     * Get all texts for current language
     *
     * @return array<string, string> Array of translations
     */
    public function getAllTexts(): array
    {
        return $this->languageModel->getAllTexts();
    }

    /**
     * Get language name in native language
     *
     * @param string $language The language code
     * @return string The native language name
     */
    public function getLanguageName(string $language): string
    {
        return $this->languageModel->getLanguageName($language);
    }

    /**
     * Get language flag code
     *
     * @param string $language The language code
     * @return string The flag code
     */
    public function getFlagCode(string $language): string
    {
        return $this->languageModel->getFlagCode($language);
    }

    /**
     * Get supported languages with their native names
     *
     * @return array<string, string> Array of language codes and native names
     */
    public function getSupportedLanguagesWithNames(): array
    {
        $languages = $this->getSupportedLanguages();
        $result = [];

        foreach ($languages as $language) {
            $result[$language] = $this->getLanguageName($language);
        }

        return $result;
    }

    /**
     * Get the number of supported languages
     *
     * @return int Number of supported languages
     */
    public function getNumberOfSupportedLanguages(): int
    {
        return $this->languageRepository->count();
    }

    /**
     * Detect the best language for the user
     *
     * @param string $defaultLanguage Default language to use
     * @return string The detected language code
     */
    public function detectLanguage(string $defaultLanguage = 'en'): string
    {
        return $this->languageModel->detectLanguage($defaultLanguage);
    }

    /**
     * Switch to a different language
     *
     * @param string $language The language code to switch to
     * @return bool True if successful, false otherwise
     */
    public function switchLanguage(string $language): bool
    {
        if ($this->setLanguage($language)) {
            // Log the language change
            error_log("Language switched to: {$language}");
            return true;
        }

        return false;
    }

    /**
     * Get language statistics
     *
     * @return array<string, mixed> Language statistics
     */
    public function getLanguageStats(): array
    {
        return [
            'total_languages' => $this->getNumberOfSupportedLanguages(),
            'current_language' => $this->getCurrentLanguage(),
            'supported_languages' => $this->getSupportedLanguages(),
            'language_names' => $this->getSupportedLanguagesWithNames(),
        ];
    }
}
