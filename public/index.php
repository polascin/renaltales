<?php

/**
 * Main entry point for the RenalTales application
 *
 * @package RenalTales
 * @version 2025.v3.0dev
 * @author Ľubomír Polaščín
**/

// File: public/index.php

// Directory separator constant for cross-platform compatibility
define('DS', DIRECTORY_SEPARATOR);

// Include application constants definitions
require_once dirname(__DIR__) . DS . 'config' . DS . 'constants.php';

// Include bootstrap for proper setup
require_once APP_DIR . DS . 'bootstrap.php';

// Set up the output buffer
ob_start();
// Include the necessary classes
require_once APP_DIR . DS . 'loader.php';
// Set the default timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');
// Check if the application is in debug mode
define('DEBUG_MODE', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
// Initialize the application components
use RenalTales\Controllers\ApplicationController;
// Start the session manager with the language model texts
use RenalTales\Core\SessionManager;
use RenalTales\Models\LanguageModel;
use RenalTales\Views\ErrorView;

try {
    $languageModel = new LanguageModel();
    $sessionManager = new SessionManager($languageModel->getAllTexts());
    $controller = new ApplicationController($languageModel, $sessionManager);
    $output = $controller->index();
    ob_end_clean();
    echo $output;
} catch(Exception $e) {
    error_log('Exception in index.php: ' . $e->getMessage());
    ob_end_clean();
    $errorView = new ErrorView($e, DEBUG_MODE, $languageModel ?? null);
    echo $errorView->render();
}
