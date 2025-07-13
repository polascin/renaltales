<?php declare(strict_types=1); ob_start();

define('APP_DIR', dirname(__DIR__));
define('DEFAULT_LANGUAGE', 'sk');
define('DEBUG_MODE', true);

// Include bootstrap for proper setup
require_once APP_DIR . '/bootstrap.php';

// Include necessary files using correct paths
// Autoload necessary classes
use RenalTales\Core\LanguageDetector;
use RenalTales\Core\SessionManager;
use RenalTales\Models\LanguageModel;
use RenalTales\Controllers\ApplicationController;
use RenalTales\Views\ErrorView;

try {
    $languageModel = new LanguageModel();
    $sessionManager = new SessionManager($languageModel->getAllTexts(), DEBUG_MODE);
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
