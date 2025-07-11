<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to capture any unwanted output
ob_start();

// Include files silently
require_once 'views/BaseView.php';
require_once 'views/ApplicationView.php';

// Check if there's any output
$output = ob_get_contents();
ob_end_clean();

if (!empty($output)) {
    echo "Found unexpected output:\n";
    echo "Length: " . strlen($output) . "\n";
    echo "Content: ";
    var_dump($output);
    echo "Hex dump: ";
    echo bin2hex($output) . "\n";
} else {
    echo "No unexpected output found from view files.\n";
}

echo "Test completed.\n";
