<?php

declare(strict_types=1);

namespace RenalTales\Tests\Context;

use Behat\Behat\Context\Context;
use RenalTales\Core\Application;
use RenalTales\Services\LanguageService;

/**
 * Language context for Behat tests
 */
class LanguageContext implements Context
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
     * @var string Current language code
     */
    private string $currentLanguage;

    /**
     * Initialize context
     */
    public function __construct()
    {
        $this->app = new Application();
        $this->app->bootstrap();
        $this->languageService = $this->app->get(LanguageService::class);
        $this->currentLanguage = $this->languageService->getCurrentLanguageCode();
    }

    /**
     * @Given the current language is :code
     */
    public function theCurrentLanguageIs(string $code): void
    {
        $result = $this->languageService->switchToLanguage($code);

        if (!$result) {
            throw new \Exception("Failed to switch to language '$code'");
        }

        $this->currentLanguage = $code;
    }

    /**
     * @Given the default language is :code
     */
    public function theDefaultLanguageIs(string $code): void
    {
        $defaultLanguage = $this->languageService->getDefaultLanguage();

        if ($defaultLanguage->getCode() !== $code) {
            throw new \Exception("Default language is not '$code'");
        }
    }

    /**
     * @When I switch to language :code
     */
    public function iSwitchToLanguage(string $code): void
    {
        $result = $this->languageService->switchToLanguage($code);

        if ($result) {
            $this->currentLanguage = $code;
        }
    }

    /**
     * @When I request the current language
     */
    public function iRequestTheCurrentLanguage(): void
    {
        $this->currentLanguage = $this->languageService->getCurrentLanguageCode();
    }

    /**
     * @When I request all active languages
     */
    public function iRequestAllActiveLanguages(): void
    {
        $languages = $this->languageService->getActiveLanguages();

        if (empty($languages)) {
            throw new \Exception('No active languages found');
        }
    }

    /**
     * @Then the current language should be :code
     */
    public function theCurrentLanguageShouldBe(string $code): void
    {
        $actualCode = $this->languageService->getCurrentLanguageCode();

        if ($actualCode !== $code) {
            throw new \Exception("Expected current language to be '$code' but got '$actualCode'");
        }
    }

    /**
     * @Then the language :code should be valid
     */
    public function theLanguageShouldBeValid(string $code): void
    {
        $isValid = $this->languageService->isValidLanguageCode($code);

        if (!$isValid) {
            throw new \Exception("Language code '$code' should be valid");
        }
    }

    /**
     * @Then the language :code should be invalid
     */
    public function theLanguageShouldBeInvalid(string $code): void
    {
        $isValid = $this->languageService->isValidLanguageCode($code);

        if ($isValid) {
            throw new \Exception("Language code '$code' should be invalid");
        }
    }

    /**
     * @Then I should be able to switch to :code
     */
    public function iShouldBeAbleToSwitchTo(string $code): void
    {
        $result = $this->languageService->switchToLanguage($code);

        if (!$result) {
            throw new \Exception("Should be able to switch to language '$code'");
        }

        $this->currentLanguage = $code;
    }

    /**
     * @Then I should not be able to switch to :code
     */
    public function iShouldNotBeAbleToSwitchTo(string $code): void
    {
        $result = $this->languageService->switchToLanguage($code);

        if ($result) {
            throw new \Exception("Should not be able to switch to language '$code'");
        }
    }

    /**
     * @Then the system should have at least :count active languages
     */
    public function theSystemShouldHaveAtLeastActiveLanguages(int $count): void
    {
        $languages = $this->languageService->getActiveLanguages();
        $actualCount = count($languages);

        if ($actualCount < $count) {
            throw new \Exception("Expected at least $count active languages but found $actualCount");
        }
    }

    /**
     * @Then the language preference should persist
     */
    public function theLanguagePreferenceShouldPersist(): void
    {
        $currentCode = $this->languageService->getCurrentLanguageCode();

        // Create a new service instance to test persistence
        $newService = $this->app->get(LanguageService::class);
        $persistedCode = $newService->getCurrentLanguageCode();

        if ($currentCode !== $persistedCode) {
            throw new \Exception("Language preference did not persist. Expected '$currentCode' but got '$persistedCode'");
        }
    }
}
