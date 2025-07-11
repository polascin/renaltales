<?php

ob_start();

// Set required constants
define('APP_DIR', '.');
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', './resources/lang/');
define('APP_TITLE', 'Renal Tales');
define('DEBUG_MODE', true);

// Include all the files that index.php includes
require_once 'core/Database.php';
require_once 'core/LanguageDetector.php';
require_once 'core/SessionManager.php';
require_once 'core/SecurityManager.php';
require_once 'core/RateLimitManager.php';
require_once 'core/InputValidator.php';
require_once 'core/FileUploadManager.php';
require_once 'models/LanguageModel.php';
require_once 'views/ApplicationView.php';
require_once 'views/ErrorView.php';
require_once 'controllers/ApplicationController.php';

$output = ob_get_contents();
ob_end_clean();

if (!empty($output)) {
    echo "Unwanted output found:\n";
    echo "Length: " . strlen($output) . "\n";
    echo "Content: ";
    var_dump($output);
    echo "Hex: " . bin2hex($output) . "\n";
} else {
    echo "All files clean - no unwanted output\n";
}

echo "Test completed.\n";
