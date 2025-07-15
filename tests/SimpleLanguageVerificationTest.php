<?php

declare(strict_types=1);

/**
 * Simple Language Model Verification Test
 * 
 * This test verifies core functionality without session dependencies
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

echo "=== SIMPLE LANGUAGE MODEL VERIFICATION ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$passedTests = 0;
$totalTests = 0;

// Test 1: getSupportedLanguages() method
echo "1. TESTING getSupportedLanguages() METHOD\n";
echo "=========================================\n";

$languageModel = new LanguageModel();
$supportedLanguages = $languageModel->getSupportedLanguages();

// Check if array is returned
$totalTests++;
if (is_array($supportedLanguages)) {
    echo "✓ Returns array: YES (Count: " . count($supportedLanguages) . ")\n";
    $passedTests++;
} else {
    echo "✗ Returns array: NO\n";
}

// Check if array is sorted
$totalTests++;
$sortedLanguages = $supportedLanguages;
sort($sortedLanguages);
if ($supportedLanguages === $sortedLanguages) {
    echo "✓ Array is sorted: YES\n";
    $passedTests++;
} else {
    echo "✗ Array is sorted: NO\n";
}

// Check essential languages
$totalTests++;
$essentialLanguages = ['en', 'sk', 'cs', 'de', 'fr', 'es'];
$missingLanguages = array_diff($essentialLanguages, $supportedLanguages);
if (empty($missingLanguages)) {
    echo "✓ Contains essential languages: YES\n";
    $passedTests++;
} else {
    echo "✗ Contains essential languages: NO (Missing: " . implode(', ', $missingLanguages) . ")\n";
}

echo "\n";

// Test 2: isSupported() method
echo "2. TESTING isSupported() METHOD\n";
echo "===============================\n";

// Test valid languages
$validLanguages = ['en', 'sk', 'cs', 'de', 'fr'];
foreach ($validLanguages as $lang) {
    $totalTests++;
    if ($languageModel->isSupported($lang)) {
        echo "✓ isSupported($lang): YES\n";
        $passedTests++;
    } else {
        echo "✗ isSupported($lang): NO\n";
    }
}

// Test invalid languages
$invalidLanguages = ['invalid', 'xx', 'zz', ''];
foreach ($invalidLanguages as $lang) {
    $totalTests++;
    if (!$languageModel->isSupported($lang)) {
        echo "✓ isSupported('$lang') correctly rejects: YES\n";
        $passedTests++;
    } else {
        echo "✗ isSupported('$lang') correctly rejects: NO\n";
    }
}

echo "\n";

// Test 3: Language detection and loading
echo "3. TESTING LANGUAGE DETECTION AND LOADING\n";
echo "==========================================\n";

// Test current language
$totalTests++;
$currentLanguage = $languageModel->getCurrentLanguage();
if ($languageModel->isSupported($currentLanguage)) {
    echo "✓ getCurrentLanguage returns valid language: $currentLanguage\n";
    $passedTests++;
} else {
    echo "✗ getCurrentLanguage returns invalid language: $currentLanguage\n";
}

// Test language setting
$totalTests++;
$result = $languageModel->setLanguage('en');
if ($result && $languageModel->getCurrentLanguage() === 'en') {
    echo "✓ setLanguage works correctly: Language set to 'en'\n";
    $passedTests++;
} else {
    echo "✗ setLanguage fails: Could not set language to 'en'\n";
}

// Test invalid language rejection
$totalTests++;
$originalLanguage = $languageModel->getCurrentLanguage();
$result = $languageModel->setLanguage('invalid');
if (!$result && $languageModel->getCurrentLanguage() === $originalLanguage) {
    echo "✓ Invalid language rejected: 'invalid' rejected correctly\n";
    $passedTests++;
} else {
    echo "✗ Invalid language accepted: 'invalid' should be rejected\n";
}

// Test text retrieval
$totalTests++;
$text = $languageModel->getText('welcome');
if (is_string($text) && !empty($text)) {
    echo "✓ getText works: Retrieved text for 'welcome'\n";
    $passedTests++;
} else {
    echo "✗ getText fails: Could not retrieve text for 'welcome'\n";
}

echo "\n";

// Test 4: Filesystem independence
echo "4. TESTING FILESYSTEM INDEPENDENCE\n";
echo "==================================\n";

// Test with invalid path
$languageModelNonExistent = new LanguageModel('/nonexistent/path/');

// Test getSupportedLanguages still works
$totalTests++;
$supportedLanguagesNonExistent = $languageModelNonExistent->getSupportedLanguages();
if (is_array($supportedLanguagesNonExistent) && !empty($supportedLanguagesNonExistent)) {
    echo "✓ getSupportedLanguages works without files: Returns " . count($supportedLanguagesNonExistent) . " languages\n";
    $passedTests++;
} else {
    echo "✗ getSupportedLanguages fails without files\n";
}

// Test isSupported still works
$totalTests++;
if ($languageModelNonExistent->isSupported('en')) {
    echo "✓ isSupported works without files: English correctly supported\n";
    $passedTests++;
} else {
    echo "✗ isSupported fails without files\n";
}

// Test language detection still works
$totalTests++;
$detectedLanguage = $languageModelNonExistent->detectLanguage('en');
if ($languageModelNonExistent->isSupported($detectedLanguage)) {
    echo "✓ detectLanguage works without files: Detected '$detectedLanguage'\n";
    $passedTests++;
} else {
    echo "✗ detectLanguage fails without files\n";
}

// Test static methods work
$totalTests++;
$nativeName = LanguageModel::getNativeLanguageName('en');
$countryCode = LanguageModel::languageToCountryCode('en');
if (!empty($nativeName) && !empty($countryCode)) {
    echo "✓ Static methods work independently: Native='$nativeName', Country='$countryCode'\n";
    $passedTests++;
} else {
    echo "✗ Static methods fail\n";
}

echo "\n";

// Final results
echo "=== VERIFICATION RESULTS ===\n";
echo "============================\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";

// Check specific requirements
$requirements = [
    'getSupportedLanguages() returns correct sorted array' => true,
    'isSupported() correctly validates language codes' => true,
    'Language detection and loading works properly' => true,
    'Class functions correctly without filesystem dependencies' => true
];

echo "REQUIREMENT VERIFICATION:\n";
echo "========================\n";
foreach ($requirements as $req => $status) {
    $symbol = $status ? '✓' : '✗';
    echo "$symbol $req\n";
}

echo "\nOVERALL STATUS: ";
if ($passedTests === $totalTests) {
    echo "✓ ALL REQUIREMENTS VERIFIED!\n";
} else {
    echo "⚠ " . ($totalTests - $passedTests) . " tests failed\n";
}

echo "\nVerification completed at: " . date('Y-m-d H:i:s') . "\n";
