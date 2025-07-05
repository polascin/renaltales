<?php
/**
 * Test script to verify dynamic language detection
 */

require_once __DIR__ . '/src/Core/Config.php';
require_once __DIR__ . '/src/Core/LanguageManager.php';

use RenalTales\Core\Config;
use RenalTales\Core\LanguageManager;

// Initialize configuration
$config = new Config(__DIR__ . '/config/config.php');

// Initialize LanguageManager
$languageManager = new LanguageManager($config);

// Get supported languages
$supportedLanguages = $languageManager->getSupportedLanguages();
$supportedLanguagesWithNames = $languageManager->getSupportedLanguagesWithNames();

echo "=== Language Detection Test ===\n";
echo "Number of detected languages: " . count($supportedLanguages) . "\n\n";

echo "Supported language codes:\n";
foreach ($supportedLanguages as $code) {
    echo "- {$code}\n";
}

echo "\nSupported languages with names:\n";
foreach ($supportedLanguagesWithNames as $code => $name) {
    echo "- {$code}: {$name}\n";
}

echo "\n=== Test completed ===\n";
