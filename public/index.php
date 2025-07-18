<?php

declare(strict_types=1);

namespace RenalTales;

use RenalTales\Core\SecurityManager;
use RenalTales\Controllers\ViewController;
use RenalTales\Controllers\ApplicationController;
use RenalTales\Models\LanguageModel;
use RenalTales\Views\ErrorView;
use RenalTales\Core\SessionManager;
use RenalTales\Core\Logger;

/**
 * Main entry point for the RenalTales application
 *
 * @package RenalTales
 * @version 2025.v3.0dev
 * @author Ľubomír Polaščín
 **/

// File: public/index.php

// Include application constants definitions
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'constants.php';

// Load Composer autoloader immediately after constants
require_once APP_ROOT . '/vendor/autoload.php';

// Include bootstrap for proper setup
require_once APP_DIR . DS . 'bootstrap.php';

// Start output buffering
ob_start();

try {
  // Initialize and bootstrap the application
  $app = new \RenalTales\Core\Application();
  $app->bootstrap();
  $app->run();

} catch (\Throwable $e) {
  // Clean any buffered output before showing error
  if (ob_get_level()) {
    ob_end_clean();
  }
  
  error_log('Error in index.php: ' . $e->getMessage());
  
  // Try to get language service if application was initialized
  $languageService = null;
  if (isset($app) && $app->isBootstrapped()) {
    $languageService = $app->get(\RenalTales\Services\LanguageService::class);
  }
  
  // Get debug mode from environment or constant
  $debugMode = filter_var($_ENV['APP_DEBUG'] ?? APP_DEBUG ?? false, FILTER_VALIDATE_BOOLEAN);
  
  $errorView = new \RenalTales\Views\ErrorView($e, $debugMode, $languageService);
  echo $errorView->render();
  exit;
}
