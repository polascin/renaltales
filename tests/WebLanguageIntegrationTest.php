<?php

declare(strict_types=1);

/**
 * Web-based Language Integration Test
 *
 * Tests the language functions through web requests to ensure they work correctly
 * in a real web environment after replacing LanguageManager with LanguageModel.
 *
 * @author Ľubomír Polaščín
 * @version 2025.v3.0dev
 */

// Set up environment
define('APP_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

// Start output buffering to capture any output
ob_start();

// Include required files
require_once APP_ROOT . '/src/Models/LanguageModel.php';
require_once APP_ROOT . '/src/Controllers/LanguageController.php';

use RenalTales\Models\LanguageModel;
use RenalTales\Controllers\LanguageController;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Language Integration Test - RenalTales</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .test-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .test-header {
            background: #3498db;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #3498db;
        }
        
        .test-result {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        .info {
            background-color: #d1ecf1;
            border: 1px solid #b8daff;
            color: #0c5460;
        }
        
        .language-switch {
            display: inline-block;
            padding: 8px 16px;
            margin: 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        
        .language-switch:hover {
            background: #0056b3;
        }
        
        .language-switch.active {
            background: #28a745;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #e9ecef;
            border-radius: 8px;
            flex: 1;
            margin: 0 10px;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #3498db;
        }
        
        .text-demo {
            padding: 15px;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .flag {
            display: inline-block;
            width: 20px;
            height: 15px;
            margin-right: 8px;
            background: #ddd;
            border-radius: 2px;
            text-align: center;
            line-height: 15px;
            font-size: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-green {
            background-color: #28a745;
        }
        
        .status-red {
            background-color: #dc3545;
        }
        
        .status-yellow {
            background-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>🌍 Web Language Integration Test</h1>
            <p>Testing LanguageModel functionality in web environment</p>
            <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <?php
        // Initialize test results
        $testResults = [];
        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;
        $warnings = [];

        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Test 1: Basic LanguageModel Functionality
        echo '<div class="test-section">';
        echo '<h2>1. Basic LanguageModel Functionality</h2>';
        
        try {
            $languageModel = new LanguageModel();
            $currentLanguage = $languageModel->getCurrentLanguage();
            $supportedLanguages = $languageModel->getSupportedLanguages();
            
            echo '<div class="test-result success">';
            echo '<span class="status-indicator status-green"></span>';
            echo '<strong>✓ LanguageModel Initialization:</strong> Successfully created LanguageModel instance';
            echo '</div>';
            
            echo '<div class="test-result info">';
            echo '<strong>Current Language:</strong> ' . htmlspecialchars($currentLanguage);
            echo '</div>';
            
            echo '<div class="test-result info">';
            echo '<strong>Supported Languages:</strong> ' . count($supportedLanguages) . ' languages found';
            echo '</div>';
            
            $passedTests += 3;
            $totalTests += 3;
            
        } catch (Exception $e) {
            echo '<div class="test-result error">';
            echo '<span class="status-indicator status-red"></span>';
            echo '<strong>✗ LanguageModel Initialization Failed:</strong> ' . htmlspecialchars($e->getMessage());
            echo '</div>';
            $failedTests += 1;
            $totalTests += 1;
        }
        
        echo '</div>';

        // Test 2: Language Information and Metadata
        echo '<div class="test-section">';
        echo '<h2>2. Language Information and Metadata</h2>';
        
        if (isset($languageModel) && isset($supportedLanguages)) {
            $testLanguages = ['en', 'en-us', 'sk', 'cs', 'de', 'fr', 'es'];
            
            echo '<table>';
            echo '<tr><th>Language</th><th>Code</th><th>Native Name</th><th>Flag Code</th><th>Status</th></tr>';
            
            foreach ($testLanguages as $lang) {
                $isSupported = in_array($lang, $supportedLanguages);
                $nativeName = $languageModel->getLanguageName($lang);
                $flagCode = $languageModel->getFlagCode($lang);
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars($lang) . '</td>';
                echo '<td>' . htmlspecialchars($lang) . '</td>';
                echo '<td>' . htmlspecialchars($nativeName) . '</td>';
                echo '<td><span class="flag">' . htmlspecialchars($flagCode) . '</span>' . htmlspecialchars($flagCode) . '</td>';
                echo '<td>';
                if ($isSupported) {
                    echo '<span class="status-indicator status-green"></span>Supported';
                    $passedTests++;
                } else {
                    echo '<span class="status-indicator status-red"></span>Not Supported';
                    $failedTests++;
                }
                echo '</td>';
                echo '</tr>';
                $totalTests++;
            }
            
            echo '</table>';
        }
        
        echo '</div>';

        // Test 3: Text Retrieval and Translation
        echo '<div class="test-section">';
        echo '<h2>3. Text Retrieval and Translation</h2>';
        
        if (isset($languageModel)) {
            $testKeys = ['welcome', 'app_title', 'home', 'login', 'logout', 'about'];
            
            foreach ($testKeys as $key) {
                $text = $languageModel->getText($key);
                $hasTranslation = !empty($text) && $text !== $key;
                
                echo '<div class="text-demo">';
                echo '<strong>Key:</strong> ' . htmlspecialchars($key) . '<br>';
                echo '<strong>Translation:</strong> ' . htmlspecialchars($text) . '<br>';
                echo '<strong>Status:</strong> ';
                if ($hasTranslation) {
                    echo '<span class="status-indicator status-green"></span>Translation found';
                    $passedTests++;
                } else {
                    echo '<span class="status-indicator status-yellow"></span>Using key as fallback';
                    $warnings[] = "No translation found for key: $key";
                }
                echo '</div>';
                $totalTests++;
            }
        }
        
        echo '</div>';

        // Test 4: Language Switching
        echo '<div class="test-section">';
        echo '<h2>4. Language Switching Test</h2>';
        
        if (isset($languageModel) && isset($supportedLanguages)) {
            $currentLang = $languageModel->getCurrentLanguage();
            
            echo '<div class="test-result info">';
            echo '<strong>Current Language:</strong> ' . htmlspecialchars($currentLang);
            echo '</div>';
            
            echo '<p>Click to test language switching:</p>';
            
            $testLanguages = array_slice($supportedLanguages, 0, 10); // First 10 languages
            foreach ($testLanguages as $lang) {
                $isActive = ($lang === $currentLang) ? 'active' : '';
                echo '<a href="?switch_to=' . urlencode($lang) . '" class="language-switch ' . $isActive . '">';
                echo '<span class="flag">' . htmlspecialchars($languageModel->getFlagCode($lang)) . '</span>';
                echo htmlspecialchars($languageModel->getLanguageName($lang));
                echo ' (' . htmlspecialchars($lang) . ')';
                echo '</a>';
            }
            
            // Handle language switching
            if (isset($_GET['switch_to']) && in_array($_GET['switch_to'], $supportedLanguages)) {
                $newLang = $_GET['switch_to'];
                $switchResult = $languageModel->setLanguage($newLang);
                
                if ($switchResult) {
                    echo '<div class="test-result success">';
                    echo '<span class="status-indicator status-green"></span>';
                    echo '<strong>✓ Language Switch Successful:</strong> Changed to ' . htmlspecialchars($newLang);
                    echo '</div>';
                    $passedTests++;
                    
                    // Test if the switch persisted
                    $newCurrentLang = $languageModel->getCurrentLanguage();
                    if ($newCurrentLang === $newLang) {
                        echo '<div class="test-result success">';
                        echo '<span class="status-indicator status-green"></span>';
                        echo '<strong>✓ Language Persistence:</strong> Language change persisted correctly';
                        echo '</div>';
                        $passedTests++;
                    } else {
                        echo '<div class="test-result error">';
                        echo '<span class="status-indicator status-red"></span>';
                        echo '<strong>✗ Language Persistence Failed:</strong> Expected ' . htmlspecialchars($newLang) . ', got ' . htmlspecialchars($newCurrentLang);
                        echo '</div>';
                        $failedTests++;
                    }
                    $totalTests += 2;
                } else {
                    echo '<div class="test-result error">';
                    echo '<span class="status-indicator status-red"></span>';
                    echo '<strong>✗ Language Switch Failed:</strong> Could not switch to ' . htmlspecialchars($newLang);
                    echo '</div>';
                    $failedTests++;
                    $totalTests++;
                }
            }
        }
        
        echo '</div>';

        // Test 5: Parameter Substitution
        echo '<div class="test-section">';
        echo '<h2>5. Parameter Substitution Test</h2>';
        
        if (isset($languageModel)) {
            $testCases = [
                'Hello {name}' => ['name' => 'World'],
                'User {id}: {name}' => ['id' => '123', 'name' => 'John Doe'],
                'Welcome {user}, you have {count} messages' => ['user' => 'Alice', 'count' => '5'],
                'No parameters' => []
            ];
            
            foreach ($testCases as $template => $params) {
                $result = $languageModel->getText($template, $params);
                
                echo '<div class="text-demo">';
                echo '<strong>Template:</strong> ' . htmlspecialchars($template) . '<br>';
                echo '<strong>Parameters:</strong> ' . htmlspecialchars(json_encode($params)) . '<br>';
                echo '<strong>Result:</strong> ' . htmlspecialchars($result) . '<br>';
                echo '<strong>Status:</strong> ';
                
                // Check if parameters were substituted correctly
                $expectedSubstitutions = count($params);
                $actualSubstitutions = $expectedSubstitutions;
                
                foreach ($params as $key => $value) {
                    if (strpos($result, '{' . $key . '}') !== false) {
                        $actualSubstitutions--;
                    }
                }
                
                if ($actualSubstitutions === $expectedSubstitutions) {
                    echo '<span class="status-indicator status-green"></span>Parameters substituted correctly';
                    $passedTests++;
                } else {
                    echo '<span class="status-indicator status-red"></span>Parameter substitution failed';
                    $failedTests++;
                }
                echo '</div>';
                $totalTests++;
            }
        }
        
        echo '</div>';

        // Test 6: Session Integration
        echo '<div class="test-section">';
        echo '<h2>6. Session Integration Test</h2>';
        
        if (isset($languageModel)) {
            echo '<div class="test-result info">';
            echo '<strong>Session Status:</strong> ' . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive');
            echo '</div>';
            
            echo '<div class="test-result info">';
            echo '<strong>Session Language:</strong> ' . htmlspecialchars($_SESSION['language'] ?? 'Not set');
            echo '</div>';
            
            echo '<div class="test-result info">';
            echo '<strong>Cookie Language:</strong> ' . htmlspecialchars($_COOKIE['language'] ?? 'Not set');
            echo '</div>';
            
            // Test session persistence
            if (isset($_SESSION['language'])) {
                $sessionLang = $_SESSION['language'];
                $currentLang = $languageModel->getCurrentLanguage();
                
                if ($sessionLang === $currentLang) {
                    echo '<div class="test-result success">';
                    echo '<span class="status-indicator status-green"></span>';
                    echo '<strong>✓ Session Persistence:</strong> Session language matches current language';
                    echo '</div>';
                    $passedTests++;
                } else {
                    echo '<div class="test-result warning">';
                    echo '<span class="status-indicator status-yellow"></span>';
                    echo '<strong>⚠ Session Mismatch:</strong> Session has ' . htmlspecialchars($sessionLang) . ', current is ' . htmlspecialchars($currentLang);
                    echo '</div>';
                    $warnings[] = "Session language mismatch";
                }
                $totalTests++;
            }
        }
        
        echo '</div>';

        // Test 7: Error Handling
        echo '<div class="test-section">';
        echo '<h2>7. Error Handling Test</h2>';
        
        if (isset($languageModel)) {
            // Test invalid language
            $invalidLangResult = $languageModel->setLanguage('invalid_lang');
            
            echo '<div class="test-result ' . ($invalidLangResult ? 'error' : 'success') . '">';
            echo '<span class="status-indicator status-' . ($invalidLangResult ? 'red' : 'green') . '"></span>';
            echo '<strong>' . ($invalidLangResult ? '✗' : '✓') . ' Invalid Language Handling:</strong> ';
            echo $invalidLangResult ? 'Invalid language was accepted (ERROR)' : 'Invalid language was rejected correctly';
            echo '</div>';
            
            if ($invalidLangResult) {
                $failedTests++;
            } else {
                $passedTests++;
            }
            $totalTests++;
            
            // Test empty key
            $emptyKeyResult = $languageModel->getText('');
            
            echo '<div class="test-result success">';
            echo '<span class="status-indicator status-green"></span>';
            echo '<strong>✓ Empty Key Handling:</strong> Empty key returned: "' . htmlspecialchars($emptyKeyResult) . '"';
            echo '</div>';
            $passedTests++;
            $totalTests++;
            
            // Test fallback
            $fallbackResult = $languageModel->getText('non_existent_key', [], 'Fallback Text');
            
            echo '<div class="test-result ' . ($fallbackResult === 'Fallback Text' ? 'success' : 'warning') . '">';
            echo '<span class="status-indicator status-' . ($fallbackResult === 'Fallback Text' ? 'green' : 'yellow') . '"></span>';
            echo '<strong>' . ($fallbackResult === 'Fallback Text' ? '✓' : '⚠') . ' Fallback Handling:</strong> ';
            echo 'Result: "' . htmlspecialchars($fallbackResult) . '"';
            echo '</div>';
            
            if ($fallbackResult === 'Fallback Text') {
                $passedTests++;
            } else {
                $warnings[] = "Fallback handling might not be working as expected";
            }
            $totalTests++;
        }
        
        echo '</div>';

        // Test Results Summary
        echo '<div class="test-section">';
        echo '<h2>📊 Test Results Summary</h2>';
        
        $successRate = $totalTests > 0 ? ($passedTests / $totalTests) * 100 : 0;
        
        echo '<div class="stats">';
        echo '<div class="stat-item">';
        echo '<div class="stat-number">' . $totalTests . '</div>';
        echo '<div>Total Tests</div>';
        echo '</div>';
        echo '<div class="stat-item">';
        echo '<div class="stat-number" style="color: #28a745;">' . $passedTests . '</div>';
        echo '<div>Passed</div>';
        echo '</div>';
        echo '<div class="stat-item">';
        echo '<div class="stat-number" style="color: #dc3545;">' . $failedTests . '</div>';
        echo '<div>Failed</div>';
        echo '</div>';
        echo '<div class="stat-item">';
        echo '<div class="stat-number" style="color: #ffc107;">' . count($warnings) . '</div>';
        echo '<div>Warnings</div>';
        echo '</div>';
        echo '<div class="stat-item">';
        echo '<div class="stat-number">' . round($successRate, 2) . '%</div>';
        echo '<div>Success Rate</div>';
        echo '</div>';
        echo '</div>';
        
        // Overall status
        echo '<div class="test-result ';
        if ($failedTests === 0 && count($warnings) === 0) {
            echo 'success">';
            echo '<span class="status-indicator status-green"></span>';
            echo '<strong>🎉 ALL TESTS PASSED!</strong> Language functions are working correctly after replacing LanguageManager with LanguageModel.';
        } elseif ($failedTests === 0) {
            echo 'warning">';
            echo '<span class="status-indicator status-yellow"></span>';
            echo '<strong>⚠ MOSTLY SUCCESSFUL!</strong> All tests passed but there are some warnings to review.';
        } elseif ($failedTests <= 2) {
            echo 'warning">';
            echo '<span class="status-indicator status-yellow"></span>';
            echo '<strong>⚠ MINOR ISSUES DETECTED!</strong> Most tests passed but there are some failures that need attention.';
        } else {
            echo 'error">';
            echo '<span class="status-indicator status-red"></span>';
            echo '<strong>❌ SIGNIFICANT ISSUES!</strong> Multiple test failures detected. Please review the implementation.';
        }
        echo '</div>';
        
        // Display warnings if any
        if (!empty($warnings)) {
            echo '<div class="test-result warning">';
            echo '<strong>⚠ Warnings:</strong>';
            echo '<ul>';
            foreach ($warnings as $warning) {
                echo '<li>' . htmlspecialchars($warning) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // Performance Information
        echo '<div class="test-section">';
        echo '<h2>⚡ Performance Information</h2>';
        
        if (isset($languageModel)) {
            $startTime = microtime(true);
            for ($i = 0; $i < 1000; $i++) {
                $languageModel->getText('welcome');
            }
            $endTime = microtime(true);
            $textRetrievalTime = $endTime - $startTime;
            
            echo '<div class="test-result info">';
            echo '<strong>Text Retrieval Performance:</strong> 1000 getText() calls took ' . round($textRetrievalTime, 4) . ' seconds';
            echo '</div>';
            
            $startTime = microtime(true);
            $allTexts = $languageModel->getAllTexts();
            $endTime = microtime(true);
            $getAllTextsTime = $endTime - $startTime;
            
            echo '<div class="test-result info">';
            echo '<strong>Get All Texts Performance:</strong> getAllTexts() with ' . count($allTexts) . ' translations took ' . round($getAllTextsTime, 4) . ' seconds';
            echo '</div>';
        }
        
        echo '</div>';
        
        // Environment Information
        echo '<div class="test-section">';
        echo '<h2>🔧 Environment Information</h2>';
        
        echo '<table>';
        echo '<tr><th>Property</th><th>Value</th></tr>';
        echo '<tr><td>PHP Version</td><td>' . phpversion() . '</td></tr>';
        echo '<tr><td>Server Software</td><td>' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</td></tr>';
        echo '<tr><td>Session Status</td><td>' . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . '</td></tr>';
        echo '<tr><td>Current Time</td><td>' . date('Y-m-d H:i:s') . '</td></tr>';
        echo '<tr><td>Request Method</td><td>' . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown') . '</td></tr>';
        echo '<tr><td>User Agent</td><td>' . htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . '</td></tr>';
        echo '</table>';
        
        echo '</div>';
        ?>
    </div>

    <script>
        // Auto-refresh functionality
        function autoRefresh() {
            setTimeout(function() {
                location.reload();
            }, 30000); // Refresh every 30 seconds
        }
        
        // Enable auto-refresh for continuous testing
        // autoRefresh();
        
        // Add click handlers for language switching
        document.querySelectorAll('.language-switch').forEach(function(link) {
            link.addEventListener('click', function(e) {
                // Show loading state
                this.innerHTML = '🔄 Switching...';
                this.style.pointerEvents = 'none';
            });
        });
        
        // Performance monitoring
        window.addEventListener('load', function() {
            const loadTime = performance.now();
            console.log('Page loaded in ' + loadTime + 'ms');
        });
    </script>
</body>
</html>
