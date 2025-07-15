<?php

declare(strict_types=1);

namespace RenalTales\Tests\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use RenalTales\Core\Application;
use RenalTales\Entities\Language;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Database context for Behat tests
 */
class DatabaseContext implements Context
{
    /**
     * @var Application The application instance
     */
    private Application $app;

    /**
     * @var EntityManagerInterface The entity manager
     */
    private EntityManagerInterface $entityManager;

    /**
     * Initialize context
     */
    public function __construct()
    {
        $this->app = new Application();
        $this->app->bootstrap();
        $this->entityManager = $this->app->get(EntityManagerInterface::class);
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(BeforeScenarioScope $scope): void
    {
        $this->setupDatabase();
    }

    /**
     * @AfterScenario
     */
    public function afterScenario(AfterScenarioScope $scope): void
    {
        $this->teardownDatabase();
    }

    /**
     * Set up the database schema
     */
    private function setupDatabase(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        
        // Drop existing schema and create new one
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
        
        // Seed basic data
        $this->seedBasicData();
    }

    /**
     * Tear down the database
     */
    private function teardownDatabase(): void
    {
        $this->entityManager->clear();
    }

    /**
     * Seed basic test data
     */
    private function seedBasicData(): void
    {
        $languages = [
            ['en', 'English', 'English', true],
            ['sk', 'Slovak', 'Slovenčina', false],
            ['de', 'German', 'Deutsch', false],
        ];

        foreach ($languages as $langData) {
            $language = new Language();
            $language->setCode($langData[0]);
            $language->setName($langData[1]);
            $language->setNativeName($langData[2]);
            $language->setIsDefault($langData[3]);
            $language->setIsActive(true);
            
            $this->entityManager->persist($language);
        }

        $this->entityManager->flush();
    }

    /**
     * @Given there are languages in the database
     */
    public function thereAreLanguagesInTheDatabase(): void
    {
        $languages = $this->entityManager->getRepository(Language::class)->findAll();
        
        if (empty($languages)) {
            throw new \Exception('No languages found in database');
        }
    }

    /**
     * @Given there is a language with code :code
     */
    public function thereIsALanguageWithCode(string $code): void
    {
        $language = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => $code]);
        
        if (!$language) {
            throw new \Exception("Language with code '$code' not found");
        }
    }

    /**
     * @Given there is no language with code :code
     */
    public function thereIsNoLanguageWithCode(string $code): void
    {
        $language = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => $code]);
        
        if ($language) {
            throw new \Exception("Language with code '$code' should not exist");
        }
    }

    /**
     * @When I create a language with code :code and name :name
     */
    public function iCreateALanguageWithCodeAndName(string $code, string $name): void
    {
        $language = new Language();
        $language->setCode($code);
        $language->setName($name);
        $language->setNativeName($name);
        $language->setIsDefault(false);
        $language->setIsActive(true);
        
        $this->entityManager->persist($language);
        $this->entityManager->flush();
    }

    /**
     * @When I delete the language with code :code
     */
    public function iDeleteTheLanguageWithCode(string $code): void
    {
        $language = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => $code]);
        
        if ($language) {
            $this->entityManager->remove($language);
            $this->entityManager->flush();
        }
    }

    /**
     * @Then the language with code :code should exist
     */
    public function theLanguageWithCodeShouldExist(string $code): void
    {
        $language = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => $code]);
        
        if (!$language) {
            throw new \Exception("Language with code '$code' should exist");
        }
    }

    /**
     * @Then the language with code :code should not exist
     */
    public function theLanguageWithCodeShouldNotExist(string $code): void
    {
        $language = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => $code]);
        
        if ($language) {
            throw new \Exception("Language with code '$code' should not exist");
        }
    }

    /**
     * @Then there should be :count languages in the database
     */
    public function thereShouldBeLanguagesInTheDatabase(int $count): void
    {
        $actualCount = $this->entityManager->getRepository(Language::class)->count([]);
        
        if ($actualCount !== $count) {
            throw new \Exception("Expected $count languages but found $actualCount");
        }
    }

    /**
     * @Then the language :code should be active
     */
    public function theLanguageShouldBeActive(string $code): void
    {
        $language = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => $code]);
        
        if (!$language) {
            throw new \Exception("Language with code '$code' not found");
        }
        
        if (!$language->isActive()) {
            throw new \Exception("Language '$code' should be active");
        }
    }

    /**
     * @Then the language :code should be the default language
     */
    public function theLanguageShouldBeTheDefaultLanguage(string $code): void
    {
        $language = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => $code]);
        
        if (!$language) {
            throw new \Exception("Language with code '$code' not found");
        }
        
        if (!$language->isDefault()) {
            throw new \Exception("Language '$code' should be the default language");
        }
    }
}
