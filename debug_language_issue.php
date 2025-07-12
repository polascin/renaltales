<?php
/**
 * Debug the exact language persistence issue
 */

session_start();

// Set application constants
define('APP_DIR', __DIR__);
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/resources/lang/');
define('APP_TITLE', 'Renal Tales');
define('DEBUG_MODE', true);

// Include required classes
require_once APP_DIR . '/core/Database.php';
require_once APP_DIR . '/core/LanguageDetector.php';
require_once APP_DIR . '/core/SessionManager.php';
require_once APP_DIR . '/models/LanguageModel.php';

echo "DEBUG: Language Persistence Issue\n";
echo "==================================\n\n";

// Step 1: Set up the session with a specific language
echo "1. MANUAL SESSION SETUP\n";
$_SESSION['language'] = 'de';
echo "   Set session language to: de\n";
echo "   Session content: " . print_r($_SESSION, true) . "\n";

// Step 2: Test LanguageDetector directly
echo "2. TEST LANGUAGEDETECTOR DIRECTLY\n";
$detector = new LanguageDetector();
echo "   LanguageDetector getCurrentLanguage(): " . $detector->getCurrentLanguage() . "\n";
echo "   LanguageDetector detectLanguage(): " . $detector->detectLanguage() . "\n";

// Step 3: Check session after LanguageDetector calls
echo "   Session after detector calls: " . ($_SESSION['language'] ?? 'not set') . "\n\n";

// Step 4: Test LanguageModel constructor
echo "3. TEST LANGUAGEMODEL CONSTRUCTOR\n";
echo "   Creating new LanguageModel...\n";
$model = new LanguageModel();
echo "   LanguageModel getCurrentLanguage(): " . $model->getCurrentLanguage() . "\n";
echo "   Session after LanguageModel creation: " . ($_SESSION['language'] ?? 'not set') . "\n\n";

// Step 5: Test with a fresh session setup
echo "4. FRESH SESSION TEST\n";
$_SESSION['language'] = 'sk';
echo "   Reset session language to: sk\n";
$freshModel = new LanguageModel();
echo "   Fresh LanguageModel getCurrentLanguage(): " . $freshModel->getCurrentLanguage() . "\n";
echo "   Session after fresh model: " . ($_SESSION['language'] ?? 'not set') . "\n\n";

// Step 6: Test setLanguage and persistence
echo "5. TEST SETLANGUAGE AND PERSISTENCE\n";
$testDetector = $model->getLanguageDetector();
if ($testDetector) {
    echo "   Setting language to 'cs' using detector...\n";
    $result = $testDetector->setLanguage('cs');
    echo "   setLanguage result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    echo "   Session after setLanguage: " . ($_SESSION['language'] ?? 'not set') . "\n";
    
    // Create new model to test persistence
    echo "   Creating new LanguageModel to test persistence...\n";
    $persistenceModel = new LanguageModel();
    echo "   Persistence LanguageModel getCurrentLanguage(): " . $persistenceModel->getCurrentLanguage() . "\n";
}

echo "\n6. CONCLUSION\n";
echo "The issue appears to be in the LanguageModel constructor.\n";
echo "It calls detectLanguage() which may override the session value.\n";
echo "Let's check which method in LanguageDetector is called in LanguageModel...\n";

// Check the actual method being called
$reflector = new ReflectionClass('LanguageModel');
$constructor = $reflector->getMethod('__construct');
echo "Constructor source is not directly accessible via reflection.\n";
echo "But based on the code, LanguageModel calls detectLanguage() in constructor.\n";

?>
