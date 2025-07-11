<?php
/**
 * Final test to check for unwanted output in main application
 */

// Start output buffering to capture any unwanted output
ob_start();

try {
    // Include the main bootstrap file
    require_once 'bootstrap.php';
    
    // Capture any output from the includes
    $unwantedOutput = ob_get_contents();
    
    // Clean the buffer
    ob_clean();
    
    if (!empty($unwantedOutput)) {
        echo "PROBLEM FOUND: Unwanted output detected:\n";
        echo "Length: " . strlen($unwantedOutput) . " bytes\n";
        echo "Content: '" . addslashes($unwantedOutput) . "'\n";
        echo "Hex dump: " . bin2hex($unwantedOutput) . "\n";
    } else {
        echo "SUCCESS: No unwanted output detected from bootstrap.php\n";
    }
    
    // Test creating an ApplicationController instance
    ob_start();
    
    // Create required dependencies
    $database = new Database();
    $sessionManager = new SessionManager($database);
    $controller = new ApplicationController($database, $sessionManager);
    
    $unwantedOutput2 = ob_get_contents();
    ob_clean();
    
    if (!empty($unwantedOutput2)) {
        echo "PROBLEM FOUND: Unwanted output from ApplicationController:\n";
        echo "Length: " . strlen($unwantedOutput2) . " bytes\n";
        echo "Content: '" . addslashes($unwantedOutput2) . "'\n";
        echo "Hex dump: " . bin2hex($unwantedOutput2) . "\n";
    } else {
        echo "SUCCESS: No unwanted output from ApplicationController\n";
    }
    
} catch (Exception $e) {
    ob_clean();
    echo "ERROR during test: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

// End output buffering
ob_end_clean();

echo "Test completed.\n";
