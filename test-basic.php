<?php
/**
 * Basic test script to verify the development environment setup
 * This script tests basic functionality without requiring all Composer dependencies
 */

echo "=== RenalTales Development Environment Basic Test ===\n\n";

// Test 1: PHP Version
echo "1. Testing PHP Version...\n";
$phpVersion = phpversion();
echo "   ✓ PHP Version: $phpVersion\n";

if (version_compare($phpVersion, '8.1.0', '>=')) {
    echo "   ✓ PHP 8.1+ requirement met\n";
} else {
    echo "   ✗ PHP 8.1+ required, got $phpVersion\n";
    exit(1);
}

// Test 2: Directory Structure
echo "\n2. Testing Directory Structure...\n";
$directories = [
    'src' => 'src',
    'tests' => 'tests',
    'storage' => 'storage',
    'storage/logs' => 'storage/logs',
    'storage/cache' => 'storage/cache',
    'storage/sessions' => 'storage/sessions',
    'storage/files' => 'storage/files',
];

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        echo "   ✓ $name directory exists\n";
    } else {
        echo "   ✗ $name directory missing\n";
    }
}

// Test 3: PSR-4 Autoloading Test (without Composer)
echo "\n3. Testing PSR-4 File Structure...\n";
$autoloadTest = [
    'composer.json' => 'composer.json',
    'bootstrap.php' => 'bootstrap.php',
    'Application class' => 'src/Application.php',
];

foreach ($autoloadTest as $name => $path) {
    if (file_exists($path)) {
        echo "   ✓ $name exists\n";
    } else {
        echo "   ✗ $name missing\n";
    }
}

// Test 4: Environment file
echo "\n4. Testing Environment Configuration...\n";
if (file_exists('.env')) {
    echo "   ✓ .env file exists\n";
    
    // Simple .env parsing
    $envContent = file_get_contents('.env');
    $required = ['APP_NAME', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
    
    foreach ($required as $key) {
        if (strpos($envContent, $key) !== false) {
            echo "   ✓ $key configured\n";
        } else {
            echo "   ✗ $key not found in .env\n";
        }
    }
} else {
    echo "   ✗ .env file missing\n";
}

// Test 5: GitIgnore
echo "\n5. Testing Git Configuration...\n";
if (file_exists('.gitignore')) {
    echo "   ✓ .gitignore exists\n";
    
    $gitignoreContent = file_get_contents('.gitignore');
    $shouldIgnore = ['/vendor/', '.env', 'storage/logs/', 'storage/cache/'];
    
    foreach ($shouldIgnore as $pattern) {
        if (strpos($gitignoreContent, $pattern) !== false) {
            echo "   ✓ $pattern ignored\n";
        } else {
            echo "   ✗ $pattern not ignored\n";
        }
    }
} else {
    echo "   ✗ .gitignore missing\n";
}

// Test 6: PHP Extensions
echo "\n6. Testing PHP Extensions...\n";
$extensions = [
    'pdo' => 'PDO Database',
    'pdo_mysql' => 'PDO MySQL',
    'json' => 'JSON',
    'mbstring' => 'Multibyte String',
    'openssl' => 'OpenSSL',
    'curl' => 'cURL',
    'xml' => 'XML',
    'zip' => 'ZIP',
];

foreach ($extensions as $ext => $name) {
    if (extension_loaded($ext)) {
        echo "   ✓ $name extension loaded\n";
    } else {
        echo "   ⚠ $name extension not loaded\n";
    }
}

// Test 7: File permissions
echo "\n7. Testing File Permissions...\n";
$testFile = 'storage/logs/test.log';
$testDir = dirname($testFile);

if (is_writable($testDir)) {
    echo "   ✓ Logs directory is writable\n";
    
    // Try to create a test file
    if (file_put_contents($testFile, "Test log entry\n")) {
        echo "   ✓ Can create log files\n";
        unlink($testFile); // Clean up
    } else {
        echo "   ✗ Cannot create log files\n";
    }
} else {
    echo "   ✗ Logs directory is not writable\n";
}

echo "\n=== Basic Test Complete ===\n";
echo "Your basic development environment structure is ready!\n\n";

// Show composer.json content
echo "Composer configuration:\n";
if (file_exists('composer.json')) {
    $composer = json_decode(file_get_contents('composer.json'), true);
    echo "- Project: " . $composer['name'] . "\n";
    echo "- Description: " . $composer['description'] . "\n";
    echo "- PSR-4 Autoloading: " . (isset($composer['autoload']['psr-4']) ? 'Configured' : 'Not configured') . "\n";
    echo "- Dependencies: " . count($composer['require']) . " required packages\n";
    echo "- Dev Dependencies: " . count($composer['require-dev']) . " dev packages\n";
}

echo "\nNext steps:\n";
echo "1. Run 'composer install' to install dependencies\n";
echo "2. Configure database settings in .env file\n";
echo "3. Create your first entity classes in src/\n";
echo "4. Set up database migrations\n";
echo "5. Start developing!\n";
