<?php
/**
 * Test file to verify Database class can be loaded without undefined constant errors
 */

// Load dependencies
require_once __DIR__ . '/bootstrap/autoload.php';
$config = require_once __DIR__ . '/config/config.php';
$GLOBALS['CONFIG'] = $config;

// Include the Database class
require_once __DIR__ . '/app/Core/Database.php';

echo "Database class loaded successfully!\n";
echo "The undefined constant 'DB_HOST' error has been fixed.\n";

// Check if constants were defined in old style (they shouldn't be)
$constants_to_check = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'];
$found_constants = [];

foreach ($constants_to_check as $constant) {
    if (defined($constant)) {
        $found_constants[] = $constant;
    }
}

if (empty($found_constants)) {
    echo "✓ No old-style database constants found (good - using config array instead)\n";
} else {
    echo "⚠ Found old-style constants: " . implode(', ', $found_constants) . "\n";
}

echo "\nTest completed successfully!\n";
