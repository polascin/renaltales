<?php

declare(strict_types=1);

/**
 * Comprehensive Language Model Test Suite
 *
 * This test suite thoroughly validates the LanguageModel functionality
 * to ensure it works correctly after replacing LanguageManager with LanguageModel.
 *
 * @author Ľubomír Polaščín
 * @version 2025.v3.0dev
 */

// Define constants first
define('APP_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

// Include the LanguageModel
require_once APP_ROOT . '/src/Models/LanguageModel.php';

use RenalTales\Models\LanguageModel;

class LanguageModelTest {
    private array $testResults = [];
    private int $totalTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;
    private array $errors = [];
    private array $warnings = [];

    /**
     * Run all tests
     */
    public function runAllTests(): void {
        echo "=== LANGUAGE MODEL TEST SUITE ===\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

        // Start session for session-related tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Run all test categories
        $this->testInitialization();
        $this->testSupportedLanguages();
        $this->testCurrentLanguage();
        $this->testLanguageDetection();
        $this->testLanguageSetting();
        $this->testTextRetrieval();
        $this->testParameterSubstitution();
        $this->testFallbackBehavior();
        $this->testLanguageInformation();
        $this->testSessionIntegration();
        $this->testCookieIntegration();
        $this->testEdgeCases();
        $this->testPerformance();
        $this->testBackwardCompatibility();

        $this->displayResults();
    }

    /**
     * Test 1: Initialization Tests
     */
    private function testInitialization(): void {
        echo "1. INITIALIZATION TESTS\n";
        echo "=======================\n";

        // Test 1.1: Basic initialization
        try {
            $languageModel = new LanguageModel();
            $this->recordTest("Basic initialization", true, "LanguageModel initialized successfully");
        } catch (Exception $e) {
            $this->recordTest("Basic initialization", false, "Failed to initialize LanguageModel: " . $e->getMessage());
        }

        // Test 1.2: Initialization with custom path
        try {
            $customPath = APP_ROOT . '/resources/lang/';
            $languageModel = new LanguageModel($customPath);
            $this->recordTest("Custom path initialization", true, "LanguageModel initialized with custom path");
        } catch (Exception $e) {
            $this->recordTest("Custom path initialization", false, "Failed with custom path: " . $e->getMessage());
        }

        // Test 1.3: Initialization with default language
        try {
            $languageModel = new LanguageModel(null, 'en');
            $currentLang = $languageModel->getCurrentLanguage();
            $this->recordTest("Default language initialization", $currentLang === 'en', "Current language: $currentLang");
        } catch (Exception $e) {
            $this->recordTest("Default language initialization", false, "Failed: " . $e->getMessage());
        }

        echo "\n";
    }

    /**
     * Test 2: Supported Languages Tests
     */
    private function testSupportedLanguages(): void {
        echo "2. SUPPORTED LANGUAGES TESTS\n";
        echo "============================\n";

        $languageModel = new LanguageModel();

        // Test 2.1: Get supported languages
        $supportedLanguages = $languageModel->getSupportedLanguages();
        $this->recordTest("Get supported languages", is_array($supportedLanguages) && !empty($supportedLanguages), 
            "Found " . count($supportedLanguages) . " supported languages");

        // Test 2.2: Essential languages present
        $essentialLanguages = ['en', 'en-us', 'sk', 'cs', 'de'];
        $missingLanguages = [];
        
        foreach ($essentialLanguages as $lang) {
            if (!in_array($lang, $supportedLanguages)) {
                $missingLanguages[] = $lang;
            }
        }
        
        $this->recordTest("Essential languages present", empty($missingLanguages), 
            empty($missingLanguages) ? "All essential languages found" : "Missing: " . implode(', ', $missingLanguages));

        // Test 2.3: Language code format validation
        $invalidCodes = [];
        foreach ($supportedLanguages as $lang) {
            if (!preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $lang)) {
                $invalidCodes[] = $lang;
            }
        }
        
        $this->recordTest("Language code format validation", empty($invalidCodes), 
            empty($invalidCodes) ? "All language codes valid" : "Invalid codes: " . implode(', ', $invalidCodes));

        echo "\n";
    }

    /**
     * Test 3: Current Language Tests
     */
    private function testCurrentLanguage(): void {
        echo "3. CURRENT LANGUAGE TESTS\n";
        echo "=========================\n";

        $languageModel = new LanguageModel();

        // Test 3.1: Get current language
        $currentLanguage = $languageModel->getCurrentLanguage();
        $this->recordTest("Get current language", is_string($currentLanguage) && !empty($currentLanguage), 
            "Current language: $currentLanguage");

        // Test 3.2: Current language is supported
        $supportedLanguages = $languageModel->getSupportedLanguages();
        $this->recordTest("Current language is supported", in_array($currentLanguage, $supportedLanguages), 
            "Current language '$currentLanguage' is " . (in_array($currentLanguage, $supportedLanguages) ? 'supported' : 'not supported'));

        // Test 3.3: Language detection consistency
        $detectedLanguage = $languageModel->detectLanguage();
        $this->recordTest("Language detection consistency", $currentLanguage === $detectedLanguage, 
            "Current: $currentLanguage, Detected: $detectedLanguage");

        echo "\n";
    }

    /**
     * Test 4: Language Detection Tests
     */
    private function testLanguageDetection(): void {
        echo "4. LANGUAGE DETECTION TESTS\n";
        echo "===========================\n";

        $languageModel = new LanguageModel();

        // Test 4.1: Default language detection
        $detectedLanguage = $languageModel->detectLanguage('en');
        $this->recordTest("Default language detection", !empty($detectedLanguage), 
            "Detected language: $detectedLanguage");

        // Test 4.2: Session language priority
        $_SESSION['language'] = 'sk';
        $detectedLanguage = $languageModel->detectLanguage('en');
        $this->recordTest("Session language priority", $detectedLanguage === 'sk', 
            "Session language detected: $detectedLanguage");

        // Test 4.3: Cookie language priority (simulate)
        unset($_SESSION['language']);
        $_COOKIE['language'] = 'cs';
        $detectedLanguage = $languageModel->detectLanguage('en');
        $this->recordTest("Cookie language priority", $detectedLanguage === 'cs', 
            "Cookie language detected: $detectedLanguage");

        // Test 4.4: Fallback to default
        unset($_COOKIE['language']);
        $detectedLanguage = $languageModel->detectLanguage('de');
        $this->recordTest("Fallback to default", $detectedLanguage === 'de', 
            "Fallback language: $detectedLanguage");

        echo "\n";
    }

    /**
     * Test 5: Language Setting Tests
     */
    private function testLanguageSetting(): void {
        echo "5. LANGUAGE SETTING TESTS\n";
        echo "=========================\n";

        $languageModel = new LanguageModel();

        // Test 5.1: Set valid language
        $result = $languageModel->setLanguage('en');
        $currentLanguage = $languageModel->getCurrentLanguage();
        $this->recordTest("Set valid language", $result && $currentLanguage === 'en', 
            "Language set to: $currentLanguage");

        // Test 5.2: Set another valid language
        $result = $languageModel->setLanguage('sk');
        $currentLanguage = $languageModel->getCurrentLanguage();
        $this->recordTest("Set another valid language", $result && $currentLanguage === 'sk', 
            "Language changed to: $currentLanguage");

        // Test 5.3: Set invalid language
        $result = $languageModel->setLanguage('invalid');
        $currentLanguage = $languageModel->getCurrentLanguage();
        $this->recordTest("Set invalid language", !$result && $currentLanguage === 'sk', 
            "Invalid language rejected, current: $currentLanguage");

        // Test 5.4: Language support check
        $this->recordTest("Language support check - valid", $languageModel->isSupported('en'), 
            "English is supported");
        $this->recordTest("Language support check - invalid", !$languageModel->isSupported('invalid'), 
            "Invalid language is not supported");

        echo "\n";
    }

    /**
     * Test 6: Text Retrieval Tests
     */
    private function testTextRetrieval(): void {
        echo "6. TEXT RETRIEVAL TESTS\n";
        echo "=======================\n";

        $languageModel = new LanguageModel();
        $languageModel->setLanguage('en');

        // Test 6.1: Get existing text
        $text = $languageModel->getText('welcome');
        $this->recordTest("Get existing text", !empty($text) && $text !== 'welcome', 
            "Retrieved text: $text");

        // Test 6.2: Get non-existing text (returns key)
        $text = $languageModel->getText('non_existing_key');
        $this->recordTest("Get non-existing text", $text === 'non_existing_key', 
            "Non-existing key returned: $text");

        // Test 6.3: Get text with fallback
        $text = $languageModel->getText('non_existing_key', [], 'Default fallback');
        $this->recordTest("Get text with fallback", $text === 'Default fallback', 
            "Fallback text: $text");

        // Test 6.4: Get all texts
        $allTexts = $languageModel->getAllTexts();
        $this->recordTest("Get all texts", is_array($allTexts) && !empty($allTexts), 
            "Retrieved " . count($allTexts) . " translations");

        echo "\n";
    }

    /**
     * Test 7: Parameter Substitution Tests
     */
    private function testParameterSubstitution(): void {
        echo "7. PARAMETER SUBSTITUTION TESTS\n";
        echo "===============================\n";

        $languageModel = new LanguageModel();
        $languageModel->setLanguage('en');

        // Test 7.1: Simple parameter substitution
        $text = $languageModel->getText('Test {name}', ['name' => 'John']);
        $this->recordTest("Simple parameter substitution", $text === 'Test John', 
            "Result: $text");

        // Test 7.2: Multiple parameter substitution
        $text = $languageModel->getText('Hello {name}, you have {count} messages', 
            ['name' => 'Alice', 'count' => '5']);
        $this->recordTest("Multiple parameter substitution", $text === 'Hello Alice, you have 5 messages', 
            "Result: $text");

        // Test 7.3: Parameter with no replacement
        $text = $languageModel->getText('Hello {name}', []);
        $this->recordTest("Parameter with no replacement", $text === 'Hello {name}', 
            "Result: $text");

        // Test 7.4: Mixed parameter types
        $text = $languageModel->getText('User {id}: {name} - Score: {score}', 
            ['id' => 123, 'name' => 'Bob', 'score' => 95.5]);
        $this->recordTest("Mixed parameter types", $text === 'User 123: Bob - Score: 95.5', 
            "Result: $text");

        echo "\n";
    }

    /**
     * Test 8: Fallback Behavior Tests
     */
    private function testFallbackBehavior(): void {
        echo "8. FALLBACK BEHAVIOR TESTS\n";
        echo "==========================\n";

        // Test 8.1: Fallback to English for non-existent language file
        try {
            $languageModel = new LanguageModel(null, 'non_existent_lang');
            $currentLanguage = $languageModel->getCurrentLanguage();
            $this->recordTest("Fallback to English", $currentLanguage === 'en', 
                "Fallback language: $currentLanguage");
        } catch (Exception $e) {
            $this->recordTest("Fallback to English", false, "Exception: " . $e->getMessage());
        }

        // Test 8.2: Fallback with invalid language path
        try {
            $languageModel = new LanguageModel('/invalid/path/');
            $supportedLanguages = $languageModel->getSupportedLanguages();
            $this->recordTest("Invalid path fallback", in_array('en', $supportedLanguages), 
                "Fallback to default languages");
        } catch (Exception $e) {
            $this->recordTest("Invalid path fallback", false, "Exception: " . $e->getMessage());
        }

        echo "\n";
    }

    /**
     * Test 9: Language Information Tests
     */
    private function testLanguageInformation(): void {
        echo "9. LANGUAGE INFORMATION TESTS\n";
        echo "=============================\n";

        $languageModel = new LanguageModel();

        // Test 9.1: Get language name
        $name = $languageModel->getLanguageName('en');
        $this->recordTest("Get language name", $name === 'English', 
            "English name: $name");

        // Test 9.2: Get native language name
        $nativeName = LanguageModel::getNativeLanguageName('sk');
        $this->recordTest("Get native language name", $nativeName === 'Slovenčina', 
            "Slovak native name: $nativeName");

        // Test 9.3: Get flag code
        $flagCode = $languageModel->getFlagCode('en');
        $this->recordTest("Get flag code", $flagCode === 'gb', 
            "English flag code: $flagCode");

        // Test 9.4: Get US flag code
        $usFlagCode = $languageModel->getFlagCode('en-us');
        $this->recordTest("Get US flag code", $usFlagCode === 'us', 
            "US flag code: $usFlagCode");

        // Test 9.5: Language to country code mapping
        $countryCode = LanguageModel::languageToCountryCode('de');
        $this->recordTest("Language to country code", $countryCode === 'de', 
            "German country code: $countryCode");

        echo "\n";
    }

    /**
     * Test 10: Session Integration Tests
     */
    private function testSessionIntegration(): void {
        echo "10. SESSION INTEGRATION TESTS\n";
        echo "=============================\n";

        $languageModel = new LanguageModel();

        // Test 10.1: Session language persistence
        $languageModel->setLanguage('cs');
        $sessionLanguage = $_SESSION['language'] ?? 'not set';
        $this->recordTest("Session language persistence", $sessionLanguage === 'cs', 
            "Session language: $sessionLanguage");

        // Test 10.2: Session language detection
        $_SESSION['language'] = 'de';
        $languageModel2 = new LanguageModel();
        $currentLanguage = $languageModel2->getCurrentLanguage();
        $this->recordTest("Session language detection", $currentLanguage === 'de', 
            "Detected from session: $currentLanguage");

        echo "\n";
    }

    /**
     * Test 11: Cookie Integration Tests
     */
    private function testCookieIntegration(): void {
        echo "11. COOKIE INTEGRATION TESTS\n";
        echo "============================\n";

        $languageModel = new LanguageModel();

        // Test 11.1: Cookie setting (simulate)
        $result = $languageModel->setLanguage('fr');
        // Note: We can't test actual cookie setting in CLI, but we can verify the method completes
        $this->recordTest("Cookie setting method", $result, 
            "setLanguage method completed successfully");

        // Test 11.2: Cookie language detection (simulate)
        unset($_SESSION['language']);
        $_COOKIE['language'] = 'es';
        $detectedLanguage = $languageModel->detectLanguage('en');
        $this->recordTest("Cookie language detection", $detectedLanguage === 'es', 
            "Detected from cookie: $detectedLanguage");

        echo "\n";
    }

    /**
     * Test 12: Edge Cases Tests
     */
    private function testEdgeCases(): void {
        echo "12. EDGE CASES TESTS\n";
        echo "===================\n";

        $languageModel = new LanguageModel();

        // Test 12.1: Empty key
        $text = $languageModel->getText('');
        $this->recordTest("Empty key", $text === '', 
            "Empty key result: '$text'");

        // Test 12.2: Very long key
        $longKey = str_repeat('a', 1000);
        $text = $languageModel->getText($longKey);
        $this->recordTest("Very long key", $text === $longKey, 
            "Long key handled correctly");

        // Test 12.3: Special characters in key
        $specialKey = 'key.with-special_chars123';
        $text = $languageModel->getText($specialKey);
        $this->recordTest("Special characters in key", $text === $specialKey, 
            "Special characters handled");

        // Test 12.4: Empty language setting
        $result = $languageModel->setLanguage('');
        $this->recordTest("Empty language setting", !$result, 
            "Empty language rejected");

        // Test 12.5: Case sensitivity
        $result1 = $languageModel->setLanguage('EN');
        $result2 = $languageModel->setLanguage('en');
        $this->recordTest("Case sensitivity", !$result1 && $result2, 
            "Case sensitivity handled correctly");

        echo "\n";
    }

    /**
     * Test 13: Performance Tests
     */
    private function testPerformance(): void {
        echo "13. PERFORMANCE TESTS\n";
        echo "====================\n";

        $languageModel = new LanguageModel();

        // Test 13.1: Multiple text retrievals
        $startTime = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $languageModel->getText('welcome');
        }
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->recordTest("Multiple text retrievals", $duration < 1.0, 
            "1000 getText calls took: " . round($duration, 4) . " seconds");

        // Test 13.2: Language switching performance
        $startTime = microtime(true);
        for ($i = 0; $i < 100; $i++) {
            $languageModel->setLanguage('en');
            $languageModel->setLanguage('sk');
        }
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->recordTest("Language switching performance", $duration < 1.0, 
            "100 language switches took: " . round($duration, 4) . " seconds");

        echo "\n";
    }

    /**
     * Test 14: Backward Compatibility Tests
     */
    private function testBackwardCompatibility(): void {
        echo "14. BACKWARD COMPATIBILITY TESTS\n";
        echo "================================\n";

        // Test 14.1: LanguageManager interface compatibility
        $languageModel = new LanguageModel();
        
        $methods = [
            'getAllTexts', 'getSupportedLanguages', 'getCurrentLanguage',
            'setLanguage', 'isSupported', 'getText', 'getLanguageName', 'getFlagCode'
        ];
        
        $missingMethods = [];
        foreach ($methods as $method) {
            if (!method_exists($languageModel, $method)) {
                $missingMethods[] = $method;
            }
        }
        
        $this->recordTest("Required methods present", empty($missingMethods), 
            empty($missingMethods) ? "All required methods present" : "Missing: " . implode(', ', $missingMethods));

        // Test 14.2: Method return types
        $this->recordTest("getAllTexts returns array", is_array($languageModel->getAllTexts()), 
            "getAllTexts return type correct");
        $this->recordTest("getSupportedLanguages returns array", is_array($languageModel->getSupportedLanguages()), 
            "getSupportedLanguages return type correct");
        $this->recordTest("getCurrentLanguage returns string", is_string($languageModel->getCurrentLanguage()), 
            "getCurrentLanguage return type correct");
        $this->recordTest("setLanguage returns bool", is_bool($languageModel->setLanguage('en')), 
            "setLanguage return type correct");
        $this->recordTest("isSupported returns bool", is_bool($languageModel->isSupported('en')), 
            "isSupported return type correct");
        $this->recordTest("getText returns string", is_string($languageModel->getText('welcome')), 
            "getText return type correct");

        echo "\n";
    }

    /**
     * Record test result
     */
    private function recordTest(string $testName, bool $passed, string $message): void {
        $this->totalTests++;
        $result = [
            'name' => $testName,
            'passed' => $passed,
            'message' => $message
        ];
        
        $this->testResults[] = $result;
        
        if ($passed) {
            $this->passedTests++;
            echo "✓ $testName: $message\n";
        } else {
            $this->failedTests++;
            echo "✗ $testName: $message\n";
            $this->errors[] = "$testName: $message";
        }
    }

    /**
     * Display final test results
     */
    private function displayResults(): void {
        echo "=== TEST RESULTS SUMMARY ===\n";
        echo "============================\n";
        echo "Total Tests: {$this->totalTests}\n";
        echo "Passed: {$this->passedTests}\n";
        echo "Failed: {$this->failedTests}\n";
        echo "Success Rate: " . round(($this->passedTests / $this->totalTests) * 100, 2) . "%\n\n";

        if (!empty($this->errors)) {
            echo "FAILED TESTS:\n";
            echo "=============\n";
            foreach ($this->errors as $error) {
                echo "- $error\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "WARNINGS:\n";
            echo "=========\n";
            foreach ($this->warnings as $warning) {
                echo "- $warning\n";
            }
            echo "\n";
        }

        echo "OVERALL STATUS: ";
        if ($this->failedTests === 0) {
            echo "✓ ALL TESTS PASSED - Language functions are working correctly!\n";
        } elseif ($this->failedTests <= 2) {
            echo "⚠ MOSTLY PASSING - Minor issues detected\n";
        } else {
            echo "✗ MULTIPLE FAILURES - Significant issues need attention\n";
        }

        echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";
    }
}

// Run the tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new LanguageModelTest();
    $tester->runAllTests();
}
