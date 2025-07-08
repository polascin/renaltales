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
define('LANGUAGE_PATH', APP_DIR . '/languages/');
define('APP_TITLE', 'Renal Tales');
define('DEBUG_MODE', true); // True in testing. Set to false in production

// Include required classes
require_once APP_DIR . '/core/LanguageDetector.php';
require_once APP_DIR . '/core/SessionManager.php';
require_once APP_DIR . '/core/Application.php';

// Initialize and run the application
try {
  $app = new Application();
  echo $app->render();
} catch (Exception $e) {
  // Handle application errors
  $app = new Application();
  $app->handleError($e);
}

?>
