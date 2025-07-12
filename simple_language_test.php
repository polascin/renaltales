<?php
// Simple language change test
session_start();
require_once 'bootstrap.php';

// Set error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/language_debug.log');

echo "<h1>Simple Language Change Test</h1>";

// Display current session
echo "<h2>Current Session</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Test with different methods
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lang'])) {
    $requestedLang = $_POST['lang'];
    echo "<h2>Processing Language Change to: " . htmlspecialchars($requestedLang) . "</h2>";
    
    try {
        // Method 1: Direct session setting
        $_SESSION['language'] = $requestedLang;
        echo "<p>✅ Set language directly in session</p>";
        
        // Method 2: Using LanguageDetector
        $languageDetector = new LanguageDetector();
        $result = $languageDetector->setLanguage($requestedLang);
        echo "<p>" . ($result ? "✅" : "❌") . " LanguageDetector setLanguage: " . ($result ? "SUCCESS" : "FAILED") . "</p>";
        
        // Method 3: Create new LanguageModel to see if it picks up the change
        $languageModel = new LanguageModel();
        $currentLang = $languageModel->getCurrentLanguage();
        echo "<p>📄 LanguageModel getCurrentLanguage: " . htmlspecialchars($currentLang) . "</p>";
        
        echo "<h3>Session after changes:</h3>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        
        echo "<p><a href='?'>Refresh to see if change persists</a></p>";
        
    } catch (Exception $e) {
        echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    // Show current language info
    try {
        $languageModel = new LanguageModel();
        $currentLang = $languageModel->getCurrentLanguage();
        $supportedLanguages = $languageModel->getSupportedLanguages();
        
        echo "<h2>Current Language: " . htmlspecialchars($currentLang) . "</h2>";
        echo "<h3>Supported Languages:</h3>";
        echo "<ul>";
        foreach ($supportedLanguages as $lang) {
            echo "<li>" . htmlspecialchars($lang) . "</li>";
        }
        echo "</ul>";
        
        // Create test forms
        echo "<h2>Test Language Change Forms</h2>";
        foreach (['en', 'sk', 'de', 'fr'] as $lang) {
            echo '<form method="POST" style="display: inline-block; margin: 5px;">';
            echo '<input type="hidden" name="lang" value="' . htmlspecialchars($lang) . '">';
            echo '<button type="submit">Switch to ' . htmlspecialchars($lang) . '</button>';
            echo '</form>';
        }
        
    } catch (Exception $e) {
        echo "<p>Error loading language info: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Show debug log
echo "<h2>Debug Log</h2>";
$logFile = __DIR__ . '/language_debug.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    echo "<pre>" . htmlspecialchars($logContent) . "</pre>";
} else {
    echo "<p>No debug log found</p>";
}
?>
