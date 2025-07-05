<?php
/**
 * Test script to verify translation system is working
 */

// Set up basic environment
define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/app/Core/Language.php';

// Test Slovak translations
echo "Testing Slovak translations:\n";
echo "nav.home: " . __('nav.home') . "\n";
echo "auth.login.title: " . __('auth.login.title') . "\n";
echo "home.hero.title: " . __('home.hero.title') . "\n";

// Test that Language class works properly
$language = new Language();
echo "\nCurrent language: " . $language->getCurrentLanguage() . "\n";
echo "Direct translation test: " . $language->translate('nav.home') . "\n";

// Test supported languages
$supported = $language->getSupportedLanguages();
echo "\nSupported languages: " . implode(', ', array_keys($supported)) . "\n";

echo "\nTranslation system test completed!\n";
