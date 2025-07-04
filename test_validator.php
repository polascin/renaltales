<?php
/**
 * Test Validator Fix
 */

use RenalTales\Validation\Validator;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

try {
    // Load bootstrap
    require_once ROOT_PATH . '/bootstrap/autoload.php';
    
    // Load config
    $config = require_once ROOT_PATH . '/config/config.php';
    
    // Set globals
    $GLOBALS['CONFIG'] = $config;
    
    echo "Testing Validator class...\n";
    
    // Test 1: Try to instantiate Validator
    require_once ROOT_PATH . '/src/Validation/Validator.php';
    
    $validator = new Validator();
    echo "✅ Validator instantiated successfully\n";
    
    // Test 2: Try to use validation
    $testData = [
        'email' => 'test@example.com',
        'username' => 'testuser'
    ];
    
    $rules = [
        'email' => 'required|email',
        'username' => 'required|min:3'
    ];
    
    $isValid = $validator->validate($testData, $rules);
    echo "✅ Validator->validate() worked: " . ($isValid ? 'Valid' : 'Invalid') . "\n";
    
    echo "\n🎉 All Validator tests passed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
