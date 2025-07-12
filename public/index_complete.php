<?php
declare(strict_types=1);

define('APP_DIR', dirname(__DIR__));
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/resources/lang/');
define('DEBUG_MODE', true);

require_once APP_DIR . '/models/LanguageModel.php';
require_once APP_DIR . '/core/SessionManager.php';
require_once APP_DIR . '/controllers/ApplicationController.php';
require_once APP_DIR . '/views/ErrorViewFinal.php';

try {
    $languageModel = new LanguageModel();
    $sessionManager = new SessionManager($languageModel->getAllTexts(), DEBUG_MODE);
    $controller = new ApplicationController($languageModel, $sessionManager);
    echo $controller->index();
} catch(Exception $e) 
    error_log('Exception in index_complete.php: ' . $e->getMessage());
    $errorView = new ErrorViewFinal($e, DEBUG_MODE, null);
    echo $errorView->render();

