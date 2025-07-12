<?php declare(strict_types=1); ob_start();

define('APP_DIR', dirname(__DIR__));
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/resources/lang/');
define('DEBUG_MODE', true);

require_once APP_DIR . '/models/BaseModel.php';
require_once APP_DIR . '/models/LanguageModel.php';
require_once APP_DIR . '/core/LanguageDetector.php';
require_once APP_DIR . '/core/Database.php';
require_once APP_DIR . '/core/SessionManager.php';
require_once APP_DIR . '/controllers/BaseController.php';
require_once APP_DIR . '/controllers/ApplicationController.php';
require_once APP_DIR . '/views/ErrorView.php';

try {
    $languageModel = new LanguageModel();
    $sessionManager = new SessionManager($languageModel->getAllTexts(), DEBUG_MODE);
    $controller = new ApplicationController($languageModel, $sessionManager);
    $output = $controller->index();
    ob_end_clean();
    echo $output;
} catch(Exception $e) 
    error_log('Exception in index_final.php: ' . $e->getMessage());
    ob_end_clean();
    $errorView = new ErrorView($e, DEBUG_MODE, null);
    echo $errorView->render();

