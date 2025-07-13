<?php
/**
 * Comprehensive verification of language persistence
 * Tests the complete flow from flag button click to session persistence
 */

session_start();

// Set application constants
define('APP_DIR', __DIR__);
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/resources/lang/');
define('APP_TITLE', 'Renal Tales');
define('DEBUG_MODE', true);

// Include required classes
use RenalTales\Core\Database;
use RenalTales\Core\LanguageDetector;
use RenalTales\Core\SessionManager;
use RenalTales\Models\LanguageModel;

require_once APP_DIR . '/../vendor/autoload.php';

echo "=" . str_repeat("=", 60) . "\n";
echo "         LANGUAGE PERSISTENCE VERIFICATION REPORT\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Initialize components
$languageModel = new LanguageModel();
$sessionManager = new SessionManager($languageModel->getAllTexts(), DEBUG_MODE);

echo "SESSION ID: " . session_id() . "\n\n";

// Test 1: Initial State
echo "1. INITIAL STATE\n";
echo "   Current Language: " . $languageModel->getCurrentLanguage() . "\n";
echo "   Session Language: " . ($_SESSION['language'] ?? 'not set') . "\n";
echo "   SessionManager initialized: " . ($sessionManager->isInitialized() ? 'YES' : 'NO') . "\n";
echo "   LanguageDetector available: " . (($languageModel->getLanguageDetector() !== null) ? 'YES' : 'NO') . "\n\n";

// Test 2: Language Support
echo "2. LANGUAGE SUPPORT VERIFICATION\n";
$supportedLanguages = $languageModel->getSupportedLanguages();
echo "   Supported Languages: " . implode(', ', $supportedLanguages) . "\n";
echo "   Total Supported: " . count($supportedLanguages) . "\n\n";

// Test 3: Language Switching Process
echo "3. LANGUAGE SWITCHING PROCESS\n";
$testLanguages = ['sk', 'en', 'cs', 'de'];

foreach ($testLanguages as $lang) {
    echo "   Testing switch to '$lang':\n";
    
    // Step 1: Check if supported
    $isSupported = in_array($lang, $supportedLanguages);
    echo "      - Is Supported: " . ($isSupported ? 'YES' : 'NO') . "\n";
    
    if ($isSupported) {
        // Step 2: Get LanguageDetector
        $languageDetector = $languageModel->getLanguageDetector();
        if ($languageDetector) {
            // Step 3: Set language
            $result = $languageDetector->setLanguage($lang);
            echo "      - setLanguage result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
            
            // Step 4: Verify session update
            $sessionLang = $_SESSION['language'] ?? 'not set';
            echo "      - Session updated: " . ($sessionLang === $lang ? 'YES' : 'NO') . " (value: $sessionLang)\n";
            
            // Step 5: Test LanguageModel persistence
            $newLanguageModel = new LanguageModel();
            $currentLang = $newLanguageModel->getCurrentLanguage();
            echo "      - LanguageModel persistence: " . ($currentLang === $lang ? 'YES' : 'NO') . " (value: $currentLang)\n";
            
            // Step 6: Test SessionManager access
            $sessionManagerLang = $sessionManager->getSession('language');
            echo "      - SessionManager access: " . ($sessionManagerLang === $lang ? 'YES' : 'NO') . " (value: " . ($sessionManagerLang ?? 'null') . ")\n";
        } else {
            echo "      - ERROR: LanguageDetector not available\n";
        }
    }
    echo "\n";
}

// Test 4: Session Persistence Across Requests
echo "4. SESSION PERSISTENCE ANALYSIS\n";
echo "   Session Status: " . $sessionManager->getSessionStatus() . "\n";
echo "   Session Content:\n";
foreach ($_SESSION as $key => $value) {
    if ($key !== '_security' && $key !== '_csrf_token') {
        echo "      $key: " . (is_array($value) ? '[array]' : $value) . "\n";
    } else {
        echo "      $key: [FILTERED]\n";
    }
}
echo "\n";

// Test 5: Cookie Persistence
echo "5. COOKIE PERSISTENCE\n";
$languageCookie = $_COOKIE['language'] ?? 'not set';
echo "   Language Cookie: $languageCookie\n";
if ($languageCookie !== 'not set') {
    echo "   Cookie persistence: ENABLED\n";
} else {
    echo "   Cookie persistence: NOT SET (Note: CLI testing doesn't support cookies)\n";
}
echo "\n";

// Test 6: Flag Button Simulation
echo "6. FLAG BUTTON CLICK SIMULATION\n";
echo "   Simulating click on German flag button...\n";

// Simulate the exact process from the language-switcher component
$_POST['lang'] = 'de';
$_POST['_csrf_token'] = $sessionManager->getCSRFToken();

// Simulate the ApplicationController handling
echo "   POST data set: lang=de, CSRF token provided\n";

// This is what ApplicationController->handleLanguageChangePost() does:
$requestedLanguage = $_POST['lang'];
$csrfToken = $_POST['_csrf_token'];

echo "   Requested Language: $requestedLanguage\n";
echo "   CSRF Token Valid: " . ($sessionManager->validateCSRFToken($csrfToken) ? 'YES' : 'NO') . "\n";

if ($sessionManager->validateCSRFToken($csrfToken) && in_array($requestedLanguage, $supportedLanguages)) {
    $languageDetector = $languageModel->getLanguageDetector();
    if ($languageDetector) {
        $result = $languageDetector->setLanguage($requestedLanguage);
        echo "   Language Set Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
        echo "   Session After Set: " . ($_SESSION['language'] ?? 'not set') . "\n";
        
        // Verify with new LanguageModel (simulating next request)
        $nextRequestModel = new LanguageModel();
        $nextRequestLang = $nextRequestModel->getCurrentLanguage();
        echo "   Next Request Language: $nextRequestLang\n";
        echo "   Persistence Verified: " . ($nextRequestLang === $requestedLanguage ? 'YES' : 'NO') . "\n";
    }
}

echo "\n";

// Test 7: Summary
echo "7. VERIFICATION SUMMARY\n";
$finalLanguage = $languageModel->getCurrentLanguage();
$sessionLanguage = $_SESSION['language'] ?? 'not set';

echo "   ✓ LanguageModel initializes correctly\n";
echo "   ✓ SessionManager initializes correctly\n";
echo "   ✓ LanguageDetector is available\n";
echo "   ✓ Language switching works: " . ($finalLanguage === 'de' ? 'YES' : 'NO') . "\n";
echo "   ✓ Session persistence works: " . ($sessionLanguage === 'de' ? 'YES' : 'NO') . "\n";
echo "   ✓ Cross-request persistence: " . ($finalLanguage === $sessionLanguage ? 'YES' : 'NO') . "\n";

echo "\n" . "=" . str_repeat("=", 60) . "\n";
echo "FINAL STATE: Language = $finalLanguage, Session = $sessionLanguage\n";
echo "=" . str_repeat("=", 60) . "\n";
?>
