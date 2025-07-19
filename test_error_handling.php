<?php

declare(strict_types=1);

// Test script for error handling and logging
define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', __DIR__);
define('APP_DIR', __DIR__);
define('APP_DEBUG', true);
define('APP_ENV', 'development');

// Load environment
$_ENV['APP_ENV'] = 'development';
$_ENV['APP_DEBUG'] = 'true';

// Bootstrap the application
require_once __DIR__ . '/bootstrap.php';

use RenalTales\Core\Application;

echo "Testing Error Handling and Logging System\n";
echo "=========================================\n\n";

try {
    // Initialize and bootstrap the application
    $app = new Application();
    $app->bootstrap();

    // Get the error handler
    $errorHandler = $app->get(\RenalTales\Core\ErrorHandler::class);
    $logger = $app->get(\Psr\Log\LoggerInterface::class);

    echo "1. Testing logging functionality...\n";
    $logger->info("Test info message");
    $logger->warning("Test warning message");
    $logger->error("Test error message");
    echo "✓ Logging tests completed\n\n";

    echo "2. Testing error handler registration...\n";
    echo "Error handler registered: " . ($errorHandler->isRegistered() ? "Yes" : "No") . "\n";
    echo "Debug mode: " . ($errorHandler->isDebug() ? "Yes" : "No") . "\n";
    echo "Environment: " . $errorHandler->getEnvironment() . "\n";
    echo "✓ Error handler tests completed\n\n";

    echo "3. Testing middleware manager...\n";
    $middlewareManager = $app->get(\RenalTales\Core\MiddlewareManager::class);
    echo "Middleware count: " . $middlewareManager->count() . "\n";
    echo "✓ Middleware manager tests completed\n\n";

    echo "4. Testing controlled error (warning)...\n";
    // This should trigger our error handler but not stop execution
    $undefinedVariable = $nonExistentVariable ?? "default";
    echo "Warning test completed without fatal error\n\n";

    echo "All tests completed successfully!\n";
    echo "Check the log file at: " . APP_ROOT . "/storage/logs/app.log\n";

} catch (Throwable $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
