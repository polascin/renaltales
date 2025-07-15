<?php

declare(strict_types=1);

namespace RenalTales\Tests\Integration\Services;

use RenalTales\Tests\TestCase;
use RenalTales\Services\LanguageService;
use RenalTales\Entities\Language;

/**
 * Integration tests for the LanguageService class
 */
class LanguageServiceTest extends TestCase
{
    private LanguageService $languageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->languageService = $this->getService(LanguageService::class);
    }

    /**
     * Test that the language service can be instantiated
     */
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(LanguageService::class, $this->languageService);
    }

    /**
     * Test that all languages can be retrieved
     */
    public function testCanRetrieveAllLanguages(): void
    {
        $languages = $this->languageService->getAllLanguages();
        
        $this->assertIsArray($languages);
        $this->assertNotEmpty($languages);
        $this->assertCollectionContainsOnly(Language::class, $languages);
    }

    /**
     * Test that active languages can be retrieved
     */
    public function testCanRetrieveActiveLanguages(): void
    {
        $languages = $this->languageService->getActiveLanguages();
        
        $this->assertIsArray($languages);
        $this->assertNotEmpty($languages);
        $this->assertCollectionContainsOnly(Language::class, $languages);
        
        // All returned languages should be active
        foreach ($languages as $language) {
            $this->assertTrue($language->isActive());
        }
    }

    /**
     * Test that a language can be retrieved by code
     */
    public function testCanRetrieveLanguageByCode(): void
    {
        $language = $this->languageService->getLanguageByCode('en');
        
        $this->assertInstanceOf(Language::class, $language);
        $this->assertEquals('en', $language->getCode());
        $this->assertEquals('English', $language->getName());
    }

    /**
     * Test that null is returned for non-existent language code
     */
    public function testReturnsNullForNonExistentLanguageCode(): void
    {
        $language = $this->languageService->getLanguageByCode('nonexistent');
        
        $this->assertNull($language);
    }

    /**
     * Test that the default language can be retrieved
     */
    public function testCanRetrieveDefaultLanguage(): void
    {
        $language = $this->languageService->getDefaultLanguage();
        
        $this->assertInstanceOf(Language::class, $language);
        $this->assertTrue($language->isDefault());
        $this->assertEquals('en', $language->getCode());
    }

    /**
     * Test that a language can be set as current
     */
    public function testCanSetCurrentLanguage(): void
    {
        $language = $this->languageService->getLanguageByCode('sk');
        $this->assertInstanceOf(Language::class, $language);
        
        $result = $this->languageService->setCurrentLanguage($language);
        
        $this->assertTrue($result);
        $this->assertEquals('sk', $this->languageService->getCurrentLanguageCode());
    }

    /**
     * Test that current language code can be retrieved
     */
    public function testCanRetrieveCurrentLanguageCode(): void
    {
        $code = $this->languageService->getCurrentLanguageCode();
        
        $this->assertIsString($code);
        $this->assertValidLanguageCode($code);
    }

    /**
     * Test that current language can be retrieved
     */
    public function testCanRetrieveCurrentLanguage(): void
    {
        $language = $this->languageService->getCurrentLanguage();
        
        $this->assertInstanceOf(Language::class, $language);
        $this->assertTrue($language->isActive());
    }

    /**
     * Test that language code can be validated
     */
    public function testCanValidateLanguageCode(): void
    {
        $this->assertTrue($this->languageService->isValidLanguageCode('en'));
        $this->assertTrue($this->languageService->isValidLanguageCode('sk'));
        $this->assertFalse($this->languageService->isValidLanguageCode('nonexistent'));
        $this->assertFalse($this->languageService->isValidLanguageCode(''));
    }

    /**
     * Test that language can be switched
     */
    public function testCanSwitchLanguage(): void
    {
        $originalCode = $this->languageService->getCurrentLanguageCode();
        
        $result = $this->languageService->switchToLanguage('sk');
        
        $this->assertTrue($result);
        $this->assertEquals('sk', $this->languageService->getCurrentLanguageCode());
        $this->assertNotEquals($originalCode, $this->languageService->getCurrentLanguageCode());
    }

    /**
     * Test that switching to invalid language returns false
     */
    public function testSwitchingToInvalidLanguageReturnsFalse(): void
    {
        $originalCode = $this->languageService->getCurrentLanguageCode();
        
        $result = $this->languageService->switchToLanguage('nonexistent');
        
        $this->assertFalse($result);
        $this->assertEquals($originalCode, $this->languageService->getCurrentLanguageCode());
    }

    /**
     * Test that language preferences are maintained across requests
     */
    public function testLanguagePreferencesAreMaintained(): void
    {
        $this->languageService->switchToLanguage('de');
        $this->assertEquals('de', $this->languageService->getCurrentLanguageCode());
        
        // Simulate new request by creating new service instance
        $newService = $this->getService(LanguageService::class);
        $this->assertEquals('de', $newService->getCurrentLanguageCode());
    }

    /**
     * Test that language data is properly cached
     */
    public function testLanguageDataIsCached(): void
    {
        // First call should hit database
        $languages1 = $this->languageService->getAllLanguages();
        
        // Second call should use cache
        $languages2 = $this->languageService->getAllLanguages();
        
        $this->assertEquals($languages1, $languages2);
        $this->assertSame(count($languages1), count($languages2));
    }

    /**
     * Test that language can be created
     */
    public function testCanCreateLanguage(): void
    {
        $data = [
            'code' => 'fr',
            'name' => 'French',
            'nativeName' => 'Français',
            'isActive' => true,
            'isDefault' => false
        ];
        
        $language = $this->languageService->createLanguage($data);
        
        $this->assertInstanceOf(Language::class, $language);
        $this->assertEquals('fr', $language->getCode());
        $this->assertEquals('French', $language->getName());
        $this->assertEquals('Français', $language->getNativeName());
        $this->assertTrue($language->isActive());
        $this->assertFalse($language->isDefault());
        
        // Verify it was persisted
        $this->assertEntityExists(Language::class, ['code' => 'fr']);
    }

    /**
     * Test that language can be updated
     */
    public function testCanUpdateLanguage(): void
    {
        $language = $this->languageService->getLanguageByCode('en');
        $this->assertInstanceOf(Language::class, $language);
        
        $data = [
            'name' => 'Updated English',
            'nativeName' => 'Updated English',
        ];
        
        $updatedLanguage = $this->languageService->updateLanguage($language, $data);
        
        $this->assertInstanceOf(Language::class, $updatedLanguage);
        $this->assertEquals('Updated English', $updatedLanguage->getName());
        $this->assertEquals('Updated English', $updatedLanguage->getNativeName());
        $this->assertEquals('en', $updatedLanguage->getCode()); // Should remain unchanged
    }

    /**
     * Test that language can be deleted
     */
    public function testCanDeleteLanguage(): void
    {
        $language = $this->languageService->getLanguageByCode('cs');
        $this->assertInstanceOf(Language::class, $language);
        
        $result = $this->languageService->deleteLanguage($language);
        
        $this->assertTrue($result);
        $this->assertEntityNotExists(Language::class, ['code' => 'cs']);
    }

    /**
     * Test that default language cannot be deleted
     */
    public function testCannotDeleteDefaultLanguage(): void
    {
        $defaultLanguage = $this->languageService->getDefaultLanguage();
        $this->assertInstanceOf(Language::class, $defaultLanguage);
        
        $result = $this->languageService->deleteLanguage($defaultLanguage);
        
        $this->assertFalse($result);
        $this->assertEntityExists(Language::class, ['code' => $defaultLanguage->getCode()]);
    }
}
