<?php

declare(strict_types=1);

/**
 * Final Comprehensive Language Test
 *
 * This test verifies that the application works correctly after replacing
 * LanguageManager with LanguageModel in all components.
 *
 * @author Ľubomír Polaščín
 * @version 2025.v3.0dev
 */

// Define constants
define('APP_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

// Include required files
require_once APP_ROOT . '/src/Models/LanguageModel.php';
require_once APP_ROOT . '/src/Controllers/LanguageController.php';
require_once APP_ROOT . '/src/Controllers/ApplicationController.php';
require_once APP_ROOT . '/src/Core/SessionManager.php';
require_once APP_ROOT . '/src/Views/HomeView.php';

use RenalTales\Models\LanguageModel;
use RenalTales\Controllers\LanguageController;
use RenalTales\Controllers\ApplicationController;
use RenalTales\Core\SessionManager;

class FinalLanguageTest {
    private int $totalTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;
    private array $errors = [];

    public function runAllTests(): void {
        echo "=== FINAL LANGUAGE FUNCTIONALITY TEST ===\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n";
        echo "Testing complete application integration after LanguageManager → LanguageModel replacement\n\n";

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->testDirectLanguageModelUsage();
        $this->testLanguageControllerIntegration();
        $this->testApplicationControllerIntegration();
        $this->testSessionManagerIntegration();
        $this->testLanguageSwitching();
        $this->testErrorHandling();
        $this->testBackwardCompatibility();

        $this->displayFinalResults();
    }

    private function testDirectLanguageModelUsage(): void {
        echo "1. DIRECT LANGUAGE MODEL USAGE\n";
        echo "==============================\n";

        try {
            $languageModel = new LanguageModel();
            
            // Test basic functionality
            $currentLang = $languageModel->getCurrentLanguage();
            $supportedLangs = $languageModel->getSupportedLanguages();
            $text = $languageModel->getText('welcome');
            
            $this->recordTest("LanguageModel instantiation", true, "Created successfully");
            $this->recordTest("Current language retrieval", !empty($currentLang), "Current: $currentLang");
            $this->recordTest("Supported languages", count($supportedLangs) > 0, "Found " . count($supportedLangs) . " languages");
            $this->recordTest("Text retrieval", !empty($text), "Retrieved: $text");
            
            // Test language switching
            $switchResult = $languageModel->setLanguage('sk');
            $newLang = $languageModel->getCurrentLanguage();
            $this->recordTest("Language switching", $switchResult && $newLang === 'sk', "Switched to: $newLang");
            
        } catch (Exception $e) {
            $this->recordTest("Direct LanguageModel usage", false, "Exception: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testLanguageControllerIntegration(): void {
        echo "2. LANGUAGE CONTROLLER INTEGRATION\n";
        echo "==================================\n";

        try {
            $languageModel = new LanguageModel();
            $controller = new LanguageController($languageModel);
            
            $this->recordTest("LanguageController instantiation", true, "Created with LanguageModel");
            
            // Test controller methods exist
            $methods = ['switchLanguage', 'getSupportedLanguages', 'getCurrentLanguage'];
            foreach ($methods as $method) {
                $exists = method_exists($controller, $method);
                $this->recordTest("Method $method exists", $exists, $exists ? "Available" : "Missing");
            }
            
        } catch (Exception $e) {
            $this->recordTest("LanguageController integration", false, "Exception: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testApplicationControllerIntegration(): void {
        echo "3. APPLICATION CONTROLLER INTEGRATION\n";
        echo "====================================\n";

        try {
            $languageModel = new LanguageModel();
            $sessionManager = new SessionManager();
            $controller = new ApplicationController($languageModel, $sessionManager);
            
            $this->recordTest("ApplicationController instantiation", true, "Created with LanguageModel");
            
            // Test index method
            $output = $controller->index();
            $this->recordTest("Index method execution", !empty($output), "Generated HTML output");
            $this->recordTest("Output contains HTML", strpos($output, '<html') !== false, "Valid HTML structure");
            
        } catch (Exception $e) {
            $this->recordTest("ApplicationController integration", false, "Exception: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testSessionManagerIntegration(): void {
        echo "4. SESSION MANAGER INTEGRATION\n";
        echo "==============================\n";

        try {
            $languageModel = new LanguageModel();
            $sessionManager = new SessionManager($languageModel->getAllTexts());
            
            $this->recordTest("SessionManager with LanguageModel", true, "Created successfully");
            
            // Test session functionality
            $sessionManager->set('test_key', 'test_value');
            $value = $sessionManager->get('test_key');
            $this->recordTest("Session storage", $value === 'test_value', "Value stored and retrieved");
            
        } catch (Exception $e) {
            $this->recordTest("SessionManager integration", false, "Exception: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testLanguageSwitching(): void {
        echo "5. LANGUAGE SWITCHING WORKFLOW\n";
        echo "==============================\n";

        try {
            $languageModel = new LanguageModel();
            
            // Test switching between languages
            $testLanguages = ['en', 'sk', 'cs', 'de'];
            $successCount = 0;
            
            foreach ($testLanguages as $lang) {
                if ($languageModel->isSupported($lang)) {
                    $result = $languageModel->setLanguage($lang);
                    $current = $languageModel->getCurrentLanguage();
                    
                    if ($result && $current === $lang) {
                        $successCount++;
                    }
                }
            }
            
            $this->recordTest("Multi-language switching", $successCount === count($testLanguages), "Switched $successCount/" . count($testLanguages) . " languages");
            
            // Test parameter substitution
            $text = $languageModel->getText('Hello {name}!', ['name' => 'World']);
            $this->recordTest("Parameter substitution", $text === 'Hello World!', "Result: $text");
            
        } catch (Exception $e) {
            $this->recordTest("Language switching workflow", false, "Exception: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testErrorHandling(): void {
        echo "6. ERROR HANDLING\n";
        echo "=================\n";

        try {
            $languageModel = new LanguageModel();
            
            // Test invalid language
            $result = $languageModel->setLanguage('invalid_lang');
            $this->recordTest("Invalid language rejection", !$result, "Invalid language properly rejected");
            
            // Test fallback behavior
            $text = $languageModel->getText('non_existent_key', [], 'Fallback');
            $this->recordTest("Fallback behavior", $text === 'Fallback', "Fallback text used");
            
            // Test empty parameters
            $emptyText = $languageModel->getText('');
            $this->recordTest("Empty key handling", $emptyText === '', "Empty key handled gracefully");
            
        } catch (Exception $e) {
            $this->recordTest("Error handling", false, "Exception: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testBackwardCompatibility(): void {
        echo "7. BACKWARD COMPATIBILITY\n";
        echo "=========================\n";

        try {
            $languageModel = new LanguageModel();
            
            // Test that all expected methods exist
            $expectedMethods = [
                'getCurrentLanguage', 'setLanguage', 'getSupportedLanguages',
                'isSupported', 'getText', 'getAllTexts', 'getLanguageName', 'getFlagCode'
            ];
            
            $missingMethods = [];
            foreach ($expectedMethods as $method) {
                if (!method_exists($languageModel, $method)) {
                    $missingMethods[] = $method;
                }
            }
            
            $this->recordTest("All expected methods present", empty($missingMethods), 
                empty($missingMethods) ? "All methods available" : "Missing: " . implode(', ', $missingMethods));
            
            // Test method signatures are compatible
            $reflection = new ReflectionClass($languageModel);
            $getTextMethod = $reflection->getMethod('getText');
            $parameters = $getTextMethod->getParameters();
            
            $this->recordTest("getText method signature", count($parameters) >= 1, 
                "Method has " . count($parameters) . " parameters");
            
        } catch (Exception $e) {
            $this->recordTest("Backward compatibility", false, "Exception: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function recordTest(string $testName, bool $passed, string $message): void {
        $this->totalTests++;
        
        if ($passed) {
            $this->passedTests++;
            echo "✓ $testName: $message\n";
        } else {
            $this->failedTests++;
            echo "✗ $testName: $message\n";
            $this->errors[] = "$testName: $message";
        }
    }

    private function displayFinalResults(): void {
        echo "=== FINAL TEST RESULTS ===\n";
        echo "==========================\n";
        
        $successRate = $this->totalTests > 0 ? ($this->passedTests / $this->totalTests) * 100 : 0;
        
        echo "Total Tests: {$this->totalTests}\n";
        echo "Passed: {$this->passedTests}\n";
        echo "Failed: {$this->failedTests}\n";
        echo "Success Rate: " . round($successRate, 2) . "%\n\n";
        
        if (!empty($this->errors)) {
            echo "FAILED TESTS:\n";
            echo "=============\n";
            foreach ($this->errors as $error) {
                echo "- $error\n";
            }
            echo "\n";
        }
        
        echo "OVERALL ASSESSMENT: ";
        if ($this->failedTests === 0) {
            echo "🎉 EXCELLENT! All tests passed.\n";
            echo "✅ The application is working correctly after replacing LanguageManager with LanguageModel.\n";
            echo "✅ All language functions are operational.\n";
            echo "✅ Integration between components is successful.\n";
            echo "✅ Backward compatibility is maintained.\n";
        } elseif ($this->failedTests <= 2) {
            echo "⚠️  GOOD with minor issues.\n";
            echo "✅ Most functionality is working correctly.\n";
            echo "⚠️  Review the failed tests above for minor issues.\n";
        } else {
            echo "❌ NEEDS ATTENTION.\n";
            echo "❌ Multiple test failures detected.\n";
            echo "❌ Please review and fix the issues above.\n";
        }
        
        echo "\n";
        echo "=== SUMMARY ===\n";
        echo "The replacement of LanguageManager with LanguageModel has been ";
        echo ($this->failedTests === 0) ? "SUCCESSFULLY COMPLETED" : "COMPLETED WITH SOME ISSUES";
        echo ".\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n";
    }
}

// Run the final test
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new FinalLanguageTest();
    $tester->runAllTests();
}
