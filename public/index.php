<?php

declare(strict_types=1);

/**
 * Main entry point for the RenalTales application
 *
 * @package RenalTales
 * @version 2025.v4.0.dev
 * @author Ľubomír Polaščín
 **/

// File: /public/index.php

// Set error reporting level
error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', '1');

// Debugging
require_once __DIR__ . '/debugging.php';

// Set timezone
date_default_timezone_set('Europe/Bratislava');

// Directory separator constant for cross-platform compatibility
define('DS', DIRECTORY_SEPARATOR);

// Include application constants definitions
require_once dirname(__DIR__) . DS . 'config' . DS . 'constants.php';

// Include Composer autoloader = Include application classes
require_once dirname(__DIR__) . DS . 'vendor' . DS . 'autoload.php';

// Start output buffering
ob_start();

// Create an instance of the application
$app = new RenalTales\Core\Application();

try {
  // Run the application
  $output = $app->run();
  echo $output;
  // Flush the output buffer
  ob_flush();
  // Clean up the output buffer
  ob_end_clean();
} catch (Exception $e) {
  error_log('Exception in index.php: ' . $e->getMessage());
  echo 'An error occurred while processing the request.';
}
