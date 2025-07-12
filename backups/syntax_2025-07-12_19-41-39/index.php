<?php declare(strict_types=1); ob_start();

define('APP_DIR', dirname(__DIR__));
define('DEFAULT_LANGUAGE', 'sk');
define('DEBUG_MODE', true);

// Include bootstrap for proper setup
require_once APP_DIR . '/bootstrap.php';

// Include necessary files using correct paths
require_once APP_DIR . '/src/Models/BaseModel.php';
require_once APP_DIR . '/src/Models/LanguageModel.php';
require_once APP_DIR . '/src/Core/LanguageDetector.php';
require_once APP_DIR . '/src/Core/Database.php';
require_once APP_DIR . '/src/Core/SessionManager.php';
require_once APP_DIR . '/src/Controllers/BaseController.php';
require_once APP_DIR . '/src/Controllers/ApplicationController.php';
require_once APP_DIR . '/src/Views/ErrorViewFinal.php';

// Add namespace usage
use RenalTales\Core\LanguageDetector;
use RenalTales\Core\SessionManager;
use RenalTales\Models\LanguageModel;
use RenalTales\Controllers\ApplicationController;

try {
    $languageModel = new LanguageModel();
    $sessionManager = new SessionManager($languageModel->getAllTexts(), DEBUG_MODE);
    $controller = new ApplicationController($languageModel, $sessionManager);
    $output = $controller->index();
    ob_end_clean();
    echo $output;
} catch (Exception $e) {
    ob_end_clean();
    $errorView = new ErrorView($e, DEBUG_MODE, null);
    echo $errorView->render();
}
