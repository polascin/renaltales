<?php
/**
 * Simulate a language change by mimicking a request through the main entry point
 */

// Start session
session_start();

// Set application constants
define('APP_DIR', dirname(__DIR__));
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/resources/lang/');
define('APP_TITLE', 'Renal Tales');
define('DEBUG_MODE', true);

// Include required classes
require_once APP_DIR . '/core/Database.php';
require_once APP_DIR . '/core/LanguageDetector.php';
require_once APP_DIR . '/core/SessionManager.php';
require_once APP_DIR . '/models/LanguageModel.php';
require_once APP_DIR . '/controllers/ApplicationController.php';

// Mock a POST request to change language
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['lang'] = 'sk'; // Change language to Slovak

// Initialize language model and session manager
$languageModel = new LanguageModel();
$sessionManager = new SessionManager($languageModel->getAllTexts(), DEBUG_MODE);

// Create the application controller
$controller = new ApplicationController($languageModel, $sessionManager);

// Handle the request, which should include processing the language change
echo $controller->index();

