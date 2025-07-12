<?php

declare(strict_types=1);

// Enable output buffering
ob_start();

// Load Composer autoloader and bootstrap
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap.php';

use RenalTales\Core\Application;

try {
    // Get global configuration
    $config = $GLOBALS['config'] ?? [];
    
    // Create and run the application
    $app = new Application($config);
    $app->run();
    
} catch(Throwable $e) 
    error_log('Exception in index_refactored.php: ' . $e->getMessage());
    // Clean the output buffer
    ob_end_clean();
    
    // Handle critical errors
    $isDebugMode = ($config['app']['debug'] ?? false);
    
    if ($isDebugMode) {
        echo "<h1>Application Error</h1>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
     else {
        echo "<h1>Application Error</h1>";
        echo "<p>An unexpected error occurred. Please try again later.</p>";
        
        // Log the error
        error_log("Critical application error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    }
}
