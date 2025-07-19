<?php

require_once 'bootstrap.php';

// Include required classes
require_once 'core/LanguageDetector.php';
require_once 'core/SessionManager.php';
require_once 'models/LanguageModel.php';
require_once 'views/ApplicationView.php';

echo "=== TRANSLATION KEY INTEGRITY CHECK ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Load reference English translations
$enFile = 'resources/lang/en.php';
if (file_exists($enFile)) {
    $enTranslations = include $enFile;
    $enKeys = array_keys($enTranslations);
    echo "English reference keys: " . count($enKeys) . "\n";

    // Check a few important languages
    $testLanguages = ['sk', 'de', 'fr', 'es', 'cs'];

    foreach ($testLanguages as $lang) {
        $langFile = "resources/lang/{$lang}.php";
        if (file_exists($langFile)) {
            $translations = include $langFile;
            $langKeys = array_keys($translations);
            $missingKeys = array_diff($enKeys, $langKeys);
            $extraKeys = array_diff($langKeys, $enKeys);

            echo "{$lang}: " . count($langKeys) . " keys";
            if (count($missingKeys) > 0) {
                echo " (missing " . count($missingKeys) . ")";
            }
            if (count($extraKeys) > 0) {
                echo " (extra " . count($extraKeys) . ")";
            }
            echo "\n";

            if (count($missingKeys) > 0 && count($missingKeys) <= 5) {
                echo "  Missing: " . implode(', ', $missingKeys) . "\n";
            }
        } else {
            echo "{$lang}: FILE NOT FOUND\n";
        }
    }
} else {
    echo "ERROR: English reference file not found\n";
}

echo "\n=== LANGUAGE DETECTOR INTEGRATION ===\n";
try {
    $detector = new LanguageDetector();
    $currentLang = $detector->getCurrentLanguage();
    $supportedCount = count($detector->getSupportedLanguages());

    echo "Current detected language: {$currentLang}\n";
    echo "Total supported languages: {$supportedCount}\n";

    // Test language setting
    echo "\nTesting language setting:\n";
    $testLangs = ['sk', 'en', 'de'];
    foreach ($testLangs as $testLang) {
        $result = $detector->setLanguage($testLang);
        $afterSet = $detector->getCurrentLanguage();
        echo "Set {$testLang}: " . ($result ? "SUCCESS" : "FAILED") . " -> Current: {$afterSet}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== SESSION INTEGRATION ===\n";
session_start();
echo "Session status: " . session_status() . "\n";
echo "Session language: " . ($_SESSION['language'] ?? 'not set') . "\n";
echo "Session user_language: " . ($_SESSION['user_language'] ?? 'not set') . "\n";

echo "\n=== VIEW INTEGRATION ===\n";
try {
    $languageModel = new LanguageModel();
    $sessionManager = new SessionManager();
    $view = new ApplicationView($languageModel, $sessionManager);

    echo "ApplicationView instantiated successfully\n";
    echo "Language model current language: " . $languageModel->getCurrentLanguage() . "\n";

} catch (Exception $e) {
    echo "ERROR creating view: " . $e->getMessage() . "\n";
}
