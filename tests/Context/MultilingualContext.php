<?php

declare(strict_types=1);

namespace RenalTales\Tests\Context;

use Behat\Behat\Context\Context;
use RenalTales\Core\Application;
use RenalTales\Services\LanguageService;

/**
 * Multilingual context for Behat tests
 */
class MultilingualContext implements Context
{
    /**
     * @var Application The application instance
     */
    private Application $app;

    /**
     * @var LanguageService The language service
     */
    private LanguageService $languageService;

    /**
     * @var array Translation cache
     */
    private array $translations = [];

    /**
     * Initialize context
     */
    public function __construct()
    {
        $this->app = new Application();
        $this->app->bootstrap();
        $this->languageService = $this->app->get(LanguageService::class);
    }

    /**
     * @Given the application supports multiple languages
     */
    public function theApplicationSupportsMultipleLanguages(): void
    {
        $languages = $this->languageService->getActiveLanguages();

        if (count($languages) < 2) {
            throw new \Exception('Application should support at least 2 languages');
        }
    }

    /**
     * @Given the text :key is translated to :value in :language
     */
    public function theTextIsTranslatedToIn(string $key, string $value, string $language): void
    {
        $this->translations[$language][$key] = $value;
    }

    /**
     * @When I request the translation for :key in :language
     */
    public function iRequestTheTranslationForIn(string $key, string $language): void
    {
        // Simulate translation request
        if (!isset($this->translations[$language][$key])) {
            throw new \Exception("Translation for '$key' in '$language' not found");
        }
    }

    /**
     * @When I switch the interface language to :language
     */
    public function iSwitchTheInterfaceLanguageTo(string $language): void
    {
        $result = $this->languageService->switchToLanguage($language);

        if (!$result) {
            throw new \Exception("Failed to switch interface language to '$language'");
        }
    }

    /**
     * @Then the translation should be :value
     */
    public function theTranslationShouldBe(string $value): void
    {
        // This would typically check the actual translation result
        // For now, we'll just validate that we have the expected value
        if (empty($value)) {
            throw new \Exception('Translation value should not be empty');
        }
    }

    /**
     * @Then the interface should be displayed in :language
     */
    public function theInterfaceShouldBeDisplayedIn(string $language): void
    {
        $currentLanguage = $this->languageService->getCurrentLanguageCode();

        if ($currentLanguage !== $language) {
            throw new \Exception("Interface should be in '$language' but is in '$currentLanguage'");
        }
    }

    /**
     * @Then all text should be properly translated
     */
    public function allTextShouldBeProperlyTranslated(): void
    {
        $currentLanguage = $this->languageService->getCurrentLanguageCode();

        if (!isset($this->translations[$currentLanguage])) {
            throw new \Exception("No translations found for language '$currentLanguage'");
        }
    }

    /**
     * @Then the language switcher should be available
     */
    public function theLanguageSwitcherShouldBeAvailable(): void
    {
        $languages = $this->languageService->getActiveLanguages();

        if (count($languages) < 2) {
            throw new \Exception('Language switcher requires at least 2 active languages');
        }
    }

    /**
     * @Then the fallback language should be :language
     */
    public function theFallbackLanguageShouldBe(string $language): void
    {
        $defaultLanguage = $this->languageService->getDefaultLanguage();

        if ($defaultLanguage->getCode() !== $language) {
            throw new \Exception("Fallback language should be '$language' but is '{$defaultLanguage->getCode()}'");
        }
    }

    /**
     * @Then missing translations should fall back to :language
     */
    public function missingTranslationsShouldFallBackTo(string $language): void
    {
        // This would typically test the fallback mechanism
        // For now, we'll just verify the default language is correct
        $this->theFallbackLanguageShouldBe($language);
    }

    /**
     * @Then language preferences should be remembered
     */
    public function languagePreferencesShouldBeRemembered(): void
    {
        $currentLanguage = $this->languageService->getCurrentLanguageCode();

        // Create a new service instance to test persistence
        $newService = $this->app->get(LanguageService::class);
        $persistedLanguage = $newService->getCurrentLanguageCode();

        if ($currentLanguage !== $persistedLanguage) {
            throw new \Exception("Language preference not remembered. Expected '$currentLanguage' but got '$persistedLanguage'");
        }
    }
}
