<?php

/**
 * Renal Tales - Main Application Entry Point
 * 
 * A multilingual web application for sharing kidney disorder stories
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

// Set application constants
define('APP_DIR', dirname(__DIR__));
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/resources/lang/');
define('APP_TITLE', 'Renal Tales');
define('DEBUG_MODE', true); // True in testing. Set to false in production

// Include required classes
require_once APP_DIR . '/core/Database.php';
require_once APP_DIR . '/core/LanguageDetector.php';
require_once APP_DIR . '/core/SessionManager.php';
require_once APP_DIR . '/core/SecurityManager.php';
require_once APP_DIR . '/core/RateLimitManager.php';
require_once APP_DIR . '/core/InputValidator.php';
require_once APP_DIR . '/core/FileUploadManager.php';
require_once APP_DIR . '/models/LanguageModel.php';
require_once APP_DIR . '/views/ApplicationView.php';
require_once APP_DIR . '/views/ErrorView.php';
require_once APP_DIR . '/controllers/ApplicationController.php';

// Initialize MVC components
try {
  $languageModel = new LanguageModel();
  $sessionManager = new SessionManager($languageModel->getAllTexts(), DEBUG_MODE);
  $securityManager = new SecurityManager($sessionManager);
  $rateLimitManager = new RateLimitManager();
  $inputValidator = new InputValidator();
  $fileUploadManager = new FileUploadManager();
  
  // Make security components available globally
  $GLOBALS['securityManager'] = $securityManager;
  $GLOBALS['rateLimitManager'] = $rateLimitManager;
  $GLOBALS['inputValidator'] = $inputValidator;
  $GLOBALS['fileUploadManager'] = $fileUploadManager;
  
  $controller = new ApplicationController($languageModel, $sessionManager);
  
  // Handle the request
  echo $controller->index();
  
} catch (Exception $e) {
  // Handle errors gracefully
  try {
    // Try to use the controller's error method if available
    if (isset($controller)) {
      echo $controller->error($e, DEBUG_MODE);
    } else {
      // Fallback to basic error view
      $errorView = new ErrorView($e, DEBUG_MODE);
      echo $errorView->render();
    }
  } catch (Exception $errorException) {
    // Ultimate fallback
    echo 'Critical Application Error: ' . $e->getMessage();
  }
  
  // Log the error
  error_log('Application Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
}

?>
