<?php

declare(strict_types=1);

/**
 * Language Model Verification Test Suite
 * 
 * This test suite verifies the specific functionality requirements:
 * - The getSupportedLanguages() method returns the correct sorted array
 * - The isSupported() method correctly validates language codes
 * - Language detection and loading still works properly
 * - The class continues to function correctly without filesystem dependencies
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

class LanguageModelVerificationTest {
    private array $testResults = [];
    private int $totalTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;
    private array $errors = [];
    private array $warnings = [];

    /**
     * Run all verification tests
     */
    public function runVerificationTests(): void {
        echo "=== LANGUAGE MODEL VERIFICATION TEST SUITE ===\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

        // Start session for session-related tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Run verification tests
        $this->testGetSupportedLanguages();
        $this->testIsSupported();
        $this->testLanguageDetection();
        $this->testLanguageLoading();
        $this->testFilesystemIndependence();
        $this->testSortedLanguageArray();
        $this->testLanguageValidation();
        $this->testFunctionalityWithoutFiles();

        $this->displayResults();
    }

    /**
     * Test 1: getSupportedLanguages() method verification
     */
    private function testGetSupportedLanguages(): void {
        echo "1. TESTING getSupportedLanguages() METHOD\n";
        echo "=========================================\n";

        $languageModel = new LanguageModel();
        $supportedLanguages = $languageModel->getSupportedLanguages();

        // Test 1.1: Returns array
        $this->recordTest("getSupportedLanguages returns array", 
            is_array($supportedLanguages), 
            "Type: " . gettype($supportedLanguages));

        // Test 1.2: Array is not empty
        $this->recordTest("getSupportedLanguages returns non-empty array", 
            !empty($supportedLanguages), 
            "Count: " . count($supportedLanguages));

        // Test 1.3: Array is sorted
        $sortedLanguages = $supportedLanguages;
        sort($sortedLanguages);
        $this->recordTest("getSupportedLanguages returns sorted array", 
            $supportedLanguages === $sortedLanguages, 
            "Original vs sorted match: " . ($supportedLanguages === $sortedLanguages ? 'YES' : 'NO'));

        // Test 1.4: Contains expected essential languages
        $expectedLanguages = ['en', 'sk', 'cs', 'de', 'fr', 'es', 'it', 'ru', 'zh'];
        $missingLanguages = array_diff($expectedLanguages, $supportedLanguages);
        $this->recordTest("getSupportedLanguages contains essential languages", 
            empty($missingLanguages), 
            empty($missingLanguages) ? "All essential languages present" : "Missing: " . implode(', ', $missingLanguages));

        // Test 1.5: All language codes are valid format
        $invalidCodes = [];
        foreach ($supportedLanguages as $lang) {
            if (!preg_match('/^[a-z]{2,3}(-[a-z]{2,3})?$/', $lang)) {
                $invalidCodes[] = $lang;
            }
        }
        $this->recordTest("getSupportedLanguages returns valid language codes", 
            empty($invalidCodes), 
            empty($invalidCodes) ? "All codes valid" : "Invalid codes: " . implode(', ', $invalidCodes));

        // Test 1.6: No duplicates
        $uniqueLanguages = array_unique($supportedLanguages);
        $this->recordTest("getSupportedLanguages contains no duplicates", 
            count($supportedLanguages) === count($uniqueLanguages), 
            "Original: " . count($supportedLanguages) . ", Unique: " . count($uniqueLanguages));

        echo "\n";
    }

    /**
     * Test 2: isSupported() method verification
     */
    private function testIsSupported(): void {
        echo "2. TESTING isSupported() METHOD\n";
        echo "===============================\n";

        $languageModel = new LanguageModel();
        $supportedLanguages = $languageModel->getSupportedLanguages();

        // Test 2.1: Valid supported languages
        $testLanguages = ['en', 'sk', 'cs', 'de', 'fr', 'es'];
        foreach ($testLanguages as $lang) {
            $isSupported = $languageModel->isSupported($lang);
            $this->recordTest("isSupported($lang) returns true", 
                $isSupported, 
                "Language '$lang' support: " . ($isSupported ? 'YES' : 'NO'));
        }

        // Test 2.2: Invalid languages
        $invalidLanguages = ['invalid', 'xx', 'zz', 'abc', '123'];
        foreach ($invalidLanguages as $lang) {
            $isSupported = $languageModel->isSupported($lang);
            $this->recordTest("isSupported($lang) returns false", 
                !$isSupported, 
                "Language '$lang' correctly rejected: " . (!$isSupported ? 'YES' : 'NO'));
        }

        // Test 2.3: Case sensitivity
        $this->recordTest("isSupported is case sensitive", 
            !$languageModel->isSupported('EN') && $languageModel->isSupported('en'), 
            "Uppercase 'EN' rejected, lowercase 'en' accepted");

        // Test 2.4: Empty string
        $this->recordTest("isSupported handles empty string", 
            !$languageModel->isSupported(''), 
            "Empty string correctly rejected");

        // Test 2.5: Regional codes
        if (in_array('en-us', $supportedLanguages)) {
            $this->recordTest("isSupported handles regional codes", 
                $languageModel->isSupported('en-us'), 
                "Regional code 'en-us' correctly supported");
        }

        // Test 2.6: Consistency with getSupportedLanguages
        $randomLanguages = array_rand(array_flip($supportedLanguages), min(10, count($supportedLanguages)));
        $consistencyCheck = true;
        foreach ((array)$randomLanguages as $lang) {
            if (!$languageModel->isSupported($lang)) {
                $consistencyCheck = false;
                break;
            }
        }
        $this->recordTest("isSupported consistent with getSupportedLanguages", 
            $consistencyCheck, 
            "Random sample consistency check");

        echo "\n";
    }

    /**
     * Test 3: Language detection functionality
     */
    private function testLanguageDetection(): void {
        echo "3. TESTING LANGUAGE DETECTION\n";
        echo "=============================\n";

        $languageModel = new LanguageModel();

        // Test 3.1: Basic language detection
        $detectedLanguage = $languageModel->detectLanguage('en');
        $this->recordTest("detectLanguage returns valid language", 
            $languageModel->isSupported($detectedLanguage), 
            "Detected language: $detectedLanguage");

        // Test 3.2: Session priority
        $_SESSION['language'] = 'sk';
        $detectedLanguage = $languageModel->detectLanguage('en');
        $this->recordTest("detectLanguage respects session priority", 
            $detectedLanguage === 'sk', 
            "Session language detected: $detectedLanguage");

        // Test 3.3: Cookie priority (when session is empty)
        unset($_SESSION['language']);
        $_COOKIE['language'] = 'cs';
        $detectedLanguage = $languageModel->detectLanguage('en');
        $this->recordTest("detectLanguage respects cookie priority", 
            $detectedLanguage === 'cs', 
            "Cookie language detected: $detectedLanguage");

        // Test 3.4: Default fallback
        unset($_SESSION['language']);
        unset($_COOKIE['language']);
        $detectedLanguage = $languageModel->detectLanguage('de');
        $this->recordTest("detectLanguage falls back to default", 
            $detectedLanguage === 'de', 
            "Default language used: $detectedLanguage");

        // Test 3.5: Invalid session language handling
        $_SESSION['language'] = 'invalid';
        $detectedLanguage = $languageModel->detectLanguage('en');
        $this->recordTest("detectLanguage handles invalid session language", 
            $detectedLanguage !== 'invalid' && $languageModel->isSupported($detectedLanguage), 
            "Invalid session language ignored, used: $detectedLanguage");

        // Test 3.6: Invalid cookie language handling
        unset($_SESSION['language']);
        $_COOKIE['language'] = 'invalid';
        $detectedLanguage = $languageModel->detectLanguage('en');
        $this->recordTest("detectLanguage handles invalid cookie language", 
            $detectedLanguage !== 'invalid' && $languageModel->isSupported($detectedLanguage), 
            "Invalid cookie language ignored, used: $detectedLanguage");

        echo "\n";
    }

    /**
     * Test 4: Language loading functionality
     */
    private function testLanguageLoading(): void {
        echo "4. TESTING LANGUAGE LOADING\n";
        echo "===========================\n";

        $languageModel = new LanguageModel();

        // Test 4.1: Set and get current language
        $result = $languageModel->setLanguage('en');
        $currentLanguage = $languageModel->getCurrentLanguage();
        $this->recordTest("setLanguage works correctly", 
            $result && $currentLanguage === 'en', 
            "Language set to: $currentLanguage");

        // Test 4.2: Language switching
        $languageModel->setLanguage('sk');
        $currentLanguage = $languageModel->getCurrentLanguage();
        $this->recordTest("Language switching works", 
            $currentLanguage === 'sk', 
            "Language switched to: $currentLanguage");

        // Test 4.3: Invalid language rejection
        $originalLanguage = $languageModel->getCurrentLanguage();
        $result = $languageModel->setLanguage('invalid');
        $currentLanguage = $languageModel->getCurrentLanguage();
        $this->recordTest("Invalid language rejected", 
            !$result && $currentLanguage === $originalLanguage, 
            "Invalid language rejected, current: $currentLanguage");

        // Test 4.4: Text retrieval after language change
        $languageModel->setLanguage('en');
        $text = $languageModel->getText('welcome');
        $this->recordTest("Text retrieval after language change", 
            is_string($text) && !empty($text), 
            "Retrieved text: " . substr($text, 0, 50) . (strlen($text) > 50 ? '...' : ''));

        // Test 4.5: getAllTexts returns data
        $allTexts = $languageModel->getAllTexts();
        $this->recordTest("getAllTexts returns translations", 
            is_array($allTexts), 
            "Translation count: " . count($allTexts));

        echo "\n";
    }

    /**
     * Test 5: Filesystem independence
     */
    private function testFilesystemIndependence(): void {
        echo "5. TESTING FILESYSTEM INDEPENDENCE\n";
        echo "==================================\n";

        // Test 5.1: getSupportedLanguages works without files
        $languageModel = new LanguageModel('/nonexistent/path/');
        $supportedLanguages = $languageModel->getSupportedLanguages();
        $this->recordTest("getSupportedLanguages works without language files", 
            is_array($supportedLanguages) && !empty($supportedLanguages), 
            "Returns " . count($supportedLanguages) . " languages without files");

        // Test 5.2: isSupported works without files
        $isSupported = $languageModel->isSupported('en');
        $this->recordTest("isSupported works without language files", 
            $isSupported, 
            "English support correctly identified");

        // Test 5.3: Language detection works without files
        $detectedLanguage = $languageModel->detectLanguage('en');
        $this->recordTest("Language detection works without files", 
            $languageModel->isSupported($detectedLanguage), 
            "Detected: $detectedLanguage");

        // Test 5.4: Class initialization with invalid path
        try {
            $languageModel = new LanguageModel('/completely/invalid/path/');
            $this->recordTest("Initialization with invalid path succeeds", 
                true, 
                "Class initialized successfully");
        } catch (Exception $e) {
            $this->recordTest("Initialization with invalid path succeeds", 
                false, 
                "Exception: " . $e->getMessage());
        }

        // Test 5.5: Core functionality preserved
        $languageModel = new LanguageModel('/nonexistent/path/');
        $coreMethodsWork = (
            is_array($languageModel->getSupportedLanguages()) &&
            is_bool($languageModel->isSupported('en')) &&
            is_string($languageModel->getCurrentLanguage()) &&
            is_string($languageModel->detectLanguage('en'))
        );
        $this->recordTest("Core functionality preserved without files", 
            $coreMethodsWork, 
            "All core methods return expected types");

        echo "\n";
    }

    /**
     * Test 6: Sorted language array verification
     */
    private function testSortedLanguageArray(): void {
        echo "6. TESTING SORTED LANGUAGE ARRAY\n";
        echo "================================\n";

        $languageModel = new LanguageModel();
        $languages = $languageModel->getSupportedLanguages();

        // Test 6.1: Alphabetical sorting
        $sortedCheck = true;
        for ($i = 0; $i < count($languages) - 1; $i++) {
            if (strcmp($languages[$i], $languages[$i + 1]) > 0) {
                $sortedCheck = false;
                break;
            }
        }
        $this->recordTest("Languages are alphabetically sorted", 
            $sortedCheck, 
            "Array is properly sorted");

        // Test 6.2: First language is 'af' (typically first alphabetically)
        $this->recordTest("First language is 'af'", 
            $languages[0] === 'af', 
            "First language: " . $languages[0]);

        // Test 6.3: Last language is 'zu' (typically last alphabetically)
        $lastLanguage = end($languages);
        $this->recordTest("Last language is 'zu'", 
            $lastLanguage === 'zu', 
            "Last language: " . $lastLanguage);

        // Test 6.4: English variants are properly sorted
        $englishVariants = array_filter($languages, function($lang) {
            return strpos($lang, 'en') === 0;
        });
        $sortedEnglish = $englishVariants;
        sort($sortedEnglish);
        $this->recordTest("English variants are sorted", 
            array_values($englishVariants) === $sortedEnglish, 
            "English variants: " . implode(', ', $englishVariants));

        echo "\n";
    }

    /**
     * Test 7: Language validation comprehensive test
     */
    private function testLanguageValidation(): void {
        echo "7. TESTING LANGUAGE VALIDATION\n";
        echo "==============================\n";

        $languageModel = new LanguageModel();

        // Test 7.1: All supported languages validate correctly
        $supportedLanguages = $languageModel->getSupportedLanguages();
        $allValid = true;
        foreach ($supportedLanguages as $lang) {
            if (!$languageModel->isSupported($lang)) {
                $allValid = false;
                break;
            }
        }
        $this->recordTest("All supported languages validate correctly", 
            $allValid, 
            "All " . count($supportedLanguages) . " languages validate");

        // Test 7.2: Various invalid inputs
        $invalidInputs = [
            'en_US', 'EN', 'english', 'en-', '-en', 'en--us', 'en-USA', 
            'a', 'abc', '12', 'en2', '2en', 'en us', 'en.us', null, false, true, 0, 1
        ];
        
        $invalidCount = 0;
        foreach ($invalidInputs as $input) {
            try {
                $isSupported = $languageModel->isSupported((string)$input);
                if (!$isSupported) {
                    $invalidCount++;
                }
            } catch (Exception $e) {
                $invalidCount++;
            }
        }
        
        $this->recordTest("Invalid inputs are rejected", 
            $invalidCount === count($invalidInputs), 
            "$invalidCount/" . count($invalidInputs) . " invalid inputs rejected");

        // Test 7.3: Edge cases
        $edgeCases = ['', '  ', 'en-', '-en', 'en--us'];
        $edgeCount = 0;
        foreach ($edgeCases as $edge) {
            if (!$languageModel->isSupported($edge)) {
                $edgeCount++;
            }
        }
        $this->recordTest("Edge cases handled correctly", 
            $edgeCount === count($edgeCases), 
            "$edgeCount/" . count($edgeCases) . " edge cases handled");

        echo "\n";
    }

    /**
     * Test 8: Functionality without translation files
     */
    private function testFunctionalityWithoutFiles(): void {
        echo "8. TESTING FUNCTIONALITY WITHOUT FILES\n";
        echo "======================================\n";

        // Test with completely invalid path
        $languageModel = new LanguageModel('/this/path/does/not/exist/');

        // Test 8.1: Basic operations still work
        $basicOpsWork = (
            !empty($languageModel->getSupportedLanguages()) &&
            $languageModel->isSupported('en') &&
            !empty($languageModel->getCurrentLanguage()) &&
            !empty($languageModel->detectLanguage('en'))
        );
        $this->recordTest("Basic operations work without files", 
            $basicOpsWork, 
            "Core functionality maintained");

        // Test 8.2: Language setting still works
        $setResult = $languageModel->setLanguage('en');
        $currentLang = $languageModel->getCurrentLanguage();
        $this->recordTest("Language setting works without files", 
            $setResult && $currentLang === 'en', 
            "Language set to: $currentLang");

        // Test 8.3: Text retrieval gracefully handles missing files
        $text = $languageModel->getText('welcome');
        $this->recordTest("Text retrieval handles missing files", 
            is_string($text), 
            "Returns: " . (empty($text) ? 'empty string' : 'text or key'));

        // Test 8.4: Language information methods work
        $langName = $languageModel->getLanguageName('en');
        $flagCode = $languageModel->getFlagCode('en');
        $this->recordTest("Language information methods work", 
            !empty($langName) && !empty($flagCode), 
            "Name: $langName, Flag: $flagCode");

        // Test 8.5: Static methods work independently
        $nativeName = LanguageModel::getNativeLanguageName('en');
        $countryCode = LanguageModel::languageToCountryCode('en');
        $this->recordTest("Static methods work independently", 
            !empty($nativeName) && !empty($countryCode), 
            "Native: $nativeName, Country: $countryCode");

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
     * Display final verification results
     */
    private function displayResults(): void {
        echo "=== VERIFICATION RESULTS SUMMARY ===\n";
        echo "====================================\n";
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

        echo "VERIFICATION STATUS:\n";
        echo "===================\n";
        
        // Check specific requirements
        $requirements = [
            'getSupportedLanguages returns correct sorted array' => $this->checkRequirement('getSupportedLanguages'),
            'isSupported correctly validates language codes' => $this->checkRequirement('isSupported'),
            'Language detection works properly' => $this->checkRequirement('detection'),
            'Functions correctly without filesystem dependencies' => $this->checkRequirement('filesystem')
        ];

        foreach ($requirements as $req => $status) {
            $symbol = $status ? '✓' : '✗';
            echo "$symbol $req\n";
        }

        echo "\nOVERALL VERIFICATION: ";
        if ($this->failedTests === 0) {
            echo "✓ ALL REQUIREMENTS VERIFIED - Language Model is working correctly!\n";
        } elseif ($this->failedTests <= 2) {
            echo "⚠ MOSTLY VERIFIED - Minor issues detected\n";
        } else {
            echo "✗ VERIFICATION FAILED - Requirements not met\n";
        }

        echo "\nVerification completed at: " . date('Y-m-d H:i:s') . "\n";
    }

    /**
     * Check if specific requirement is met
     */
    private function checkRequirement(string $requirement): bool {
        $relevantTests = array_filter($this->testResults, function($test) use ($requirement) {
            return stripos($test['name'], $requirement) !== false;
        });

        if (empty($relevantTests)) {
            return false;
        }

        foreach ($relevantTests as $test) {
            if (!$test['passed']) {
                return false;
            }
        }

        return true;
    }
}

// Run the verification tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $verifier = new LanguageModelVerificationTest();
    $verifier->runVerificationTests();
}
