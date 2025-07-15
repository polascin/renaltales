#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Test Doctrine Database Layer
 *
 * This script demonstrates the Doctrine ORM database layer functionality
 * by creating, reading, updating, and deleting Language entities.
 *
 * @package RenalTales\Scripts
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */

// Define application root
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Load constants first if not already loaded
if (!defined('APP_NAME')) {
    require_once APP_ROOT . DS . 'config' . DS . 'constants.php';
}

// Load bootstrap
require_once APP_ROOT . DS . 'bootstrap.php';

use RenalTales\Core\Application;
use RenalTales\Core\DatabaseManager;
use RenalTales\Entities\Language;
use RenalTales\Repositories\DoctrineLanguageRepository;

try {
    echo "=== Doctrine Database Layer Test ===" . PHP_EOL;
    
    // Initialize application
    $app = new Application();
    $app->bootstrap();
    
    // Get database manager
    $databaseManager = $app->get(DatabaseManager::class);
    $entityManager = $databaseManager->getEntityManager();
    
    // Get language repository
    $languageRepository = $entityManager->getRepository(Language::class);
    
    echo "✓ Database connection established" . PHP_EOL;
    echo "✓ Entity Manager initialized" . PHP_EOL;
    echo "✓ Language Repository loaded" . PHP_EOL;
    
    // Test 1: Create a new language
    echo PHP_EOL . "--- Test 1: Creating new language ---" . PHP_EOL;
    
    $testLanguage = new Language(
        code: 'test',
        name: 'Test Language',
        nativeName: 'Testovací jazyk',
        active: true,
        isDefault: false
    );
    
    $testLanguage->setDirection('ltr');
    $testLanguage->setRegion('TEST');
    $testLanguage->setSortOrder(999);
    
    $entityManager->persist($testLanguage);
    $entityManager->flush();
    
    echo "✓ Test language created with ID: " . $testLanguage->getId() . PHP_EOL;
    
    // Test 2: Find the language
    echo PHP_EOL . "--- Test 2: Finding language ---" . PHP_EOL;
    
    $foundLanguage = $languageRepository->findByCode('test');
    if ($foundLanguage) {
        echo "✓ Found language: " . $foundLanguage->getName() . " (" . $foundLanguage->getCode() . ")" . PHP_EOL;
        echo "  - Native name: " . $foundLanguage->getNativeName() . PHP_EOL;
        echo "  - Direction: " . $foundLanguage->getDirection() . PHP_EOL;
        echo "  - Region: " . $foundLanguage->getRegion() . PHP_EOL;
        echo "  - Sort order: " . $foundLanguage->getSortOrder() . PHP_EOL;
        echo "  - Active: " . ($foundLanguage->isActive() ? 'Yes' : 'No') . PHP_EOL;
        echo "  - Default: " . ($foundLanguage->isDefault() ? 'Yes' : 'No') . PHP_EOL;
        echo "  - Created: " . $foundLanguage->getCreatedAt()->format('Y-m-d H:i:s') . PHP_EOL;
    } else {
        echo "✗ Language not found" . PHP_EOL;
    }
    
    // Test 3: Update the language
    echo PHP_EOL . "--- Test 3: Updating language ---" . PHP_EOL;
    
    if ($foundLanguage) {
        $foundLanguage->setName('Updated Test Language');
        $foundLanguage->setNativeName('Aktualizovaný testovací jazyk');
        $foundLanguage->setActive(false);
        
        $entityManager->flush();
        
        echo "✓ Language updated successfully" . PHP_EOL;
        echo "  - New name: " . $foundLanguage->getName() . PHP_EOL;
        echo "  - New native name: " . $foundLanguage->getNativeName() . PHP_EOL;
        echo "  - Active: " . ($foundLanguage->isActive() ? 'Yes' : 'No') . PHP_EOL;
        echo "  - Updated: " . $foundLanguage->getUpdatedAt()->format('Y-m-d H:i:s') . PHP_EOL;
    }
    
    // Test 4: Query all languages
    echo PHP_EOL . "--- Test 4: Querying all languages ---" . PHP_EOL;
    
    $allLanguages = $languageRepository->findAll();
    echo "✓ Found " . count($allLanguages) . " languages in total" . PHP_EOL;
    
    foreach ($allLanguages as $lang) {
        echo "  - " . $lang->getName() . " (" . $lang->getCode() . ")" . 
             " - Active: " . ($lang->isActive() ? 'Yes' : 'No') . PHP_EOL;
    }
    
    // Test 5: Search languages
    echo PHP_EOL . "--- Test 5: Searching languages ---" . PHP_EOL;
    
    $searchResults = $languageRepository->search('test', false);
    echo "✓ Found " . count($searchResults) . " languages matching 'test'" . PHP_EOL;
    
    foreach ($searchResults as $lang) {
        echo "  - " . $lang->getName() . " (" . $lang->getCode() . ")" . PHP_EOL;
    }
    
    // Test 6: Count languages
    echo PHP_EOL . "--- Test 6: Counting languages ---" . PHP_EOL;
    
    $totalCount = $languageRepository->count();
    $activeCount = $languageRepository->count(['active' => true]);
    $inactiveCount = $languageRepository->count(['active' => false]);
    
    echo "✓ Language statistics:" . PHP_EOL;
    echo "  - Total languages: " . $totalCount . PHP_EOL;
    echo "  - Active languages: " . $activeCount . PHP_EOL;
    echo "  - Inactive languages: " . $inactiveCount . PHP_EOL;
    
    // Test 7: Clean up - Delete test language
    echo PHP_EOL . "--- Test 7: Cleaning up ---" . PHP_EOL;
    
    if ($foundLanguage) {
        $entityManager->remove($foundLanguage);
        $entityManager->flush();
        
        echo "✓ Test language deleted successfully" . PHP_EOL;
        
        // Verify deletion
        $deletedLanguage = $languageRepository->findByCode('test');
        if (!$deletedLanguage) {
            echo "✓ Deletion verified - language no longer exists" . PHP_EOL;
        } else {
            echo "✗ Deletion failed - language still exists" . PHP_EOL;
        }
    }
    
    // Test 8: Entity to array conversion
    echo PHP_EOL . "--- Test 8: Entity serialization ---" . PHP_EOL;
    
    $sampleLanguage = new Language(
        code: 'sample',
        name: 'Sample Language',
        nativeName: 'Vzorový jazyk'
    );
    
    $languageArray = $sampleLanguage->toArray();
    $languageJson = $sampleLanguage->toJson();
    
    echo "✓ Entity to array conversion:" . PHP_EOL;
    echo "  - Array keys: " . implode(', ', array_keys($languageArray)) . PHP_EOL;
    echo "  - JSON length: " . strlen($languageJson) . " characters" . PHP_EOL;
    
    echo PHP_EOL . "=== All tests completed successfully! ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
