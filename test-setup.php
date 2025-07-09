<?php
/**
 * Test script to verify the development environment setup
 * This script tests PSR-4 autoloading, environment configuration, and basic functionality
 */

// Bootstrap the application
require_once __DIR__ . '/bootstrap.php';

// Test PSR-4 autoloading
use RenalTales\Application;

echo "=== RenalTales Development Environment Test ===\n\n";

// Test 1: Autoloading
echo "1. Testing PSR-4 Autoloading...\n";
try {
    $app = Application::getInstance();
    echo "   ✓ PSR-4 autoloading working correctly\n";
} catch (Exception $e) {
    echo "   ✗ PSR-4 autoloading failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Environment configuration
echo "\n2. Testing Environment Configuration...\n";
try {
    $app->initialize($GLOBALS['config']);
    echo "   ✓ Environment configuration loaded\n";
    echo "   - App Name: " . $app->getName() . "\n";
    echo "   - Version: " . $app->getVersion() . "\n";
    echo "   - Environment: " . $app->getEnvironment() . "\n";
    echo "   - Debug Mode: " . ($app->isDebug() ? 'ON' : 'OFF') . "\n";
} catch (Exception $e) {
    echo "   ✗ Environment configuration failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Database configuration
echo "\n3. Testing Database Configuration...\n";
try {
    $dbConfig = $app->getDatabaseConfig();
    echo "   ✓ Database configuration loaded\n";
    echo "   - Host: " . $dbConfig['host'] . "\n";
    echo "   - Database: " . $dbConfig['name'] . "\n";
    echo "   - User: " . $dbConfig['user'] . "\n";
    echo "   - Charset: " . $dbConfig['charset'] . "\n";
} catch (Exception $e) {
    echo "   ✗ Database configuration failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Storage directories
echo "\n4. Testing Storage Directories...\n";
$directories = [
    'Storage' => $app->getStoragePath(),
    'Cache' => $app->getCachePath(),
    'Logs' => $app->getLogsPath(),
];

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        echo "   ✓ $name directory exists: $path\n";
    } else {
        echo "   ✗ $name directory missing: $path\n";
    }
}

// Test 5: Composer dependencies
echo "\n5. Testing Composer Dependencies...\n";
$dependencies = [
    'Doctrine ORM' => 'Doctrine\\ORM\\EntityManager',
    'Monolog' => 'Monolog\\Logger',
    'Symfony Dotenv' => 'Symfony\\Component\\Dotenv\\Dotenv',
    'Symfony Console' => 'Symfony\\Component\\Console\\Application',
    'Symfony Validator' => 'Symfony\\Component\\Validator\\Validator\\ValidatorInterface',
    'Guzzle HTTP' => 'GuzzleHttp\\Client',
    'Respect Validation' => 'Respect\\Validation\\Validator',
];

foreach ($dependencies as $name => $class) {
    if (class_exists($class)) {
        echo "   ✓ $name loaded\n";
    } else {
        echo "   ✗ $name not available\n";
    }
}

// Test 6: Logging functionality
echo "\n6. Testing Logging Functionality...\n";
try {
    $app->logInfo('Test log message from setup script');
    echo "   ✓ Logging functionality working\n";
} catch (Exception $e) {
    echo "   ✗ Logging failed: " . $e->getMessage() . "\n";
}

// Test 7: PHP Extensions
echo "\n7. Testing PHP Extensions...\n";
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

echo "\n=== Setup Test Complete ===\n";
echo "Your development environment is ready!\n\n";

// Display next steps
echo "Next steps:\n";
echo "1. Configure your database connection in .env file\n";
echo "2. Set up Doctrine ORM entity mappings\n";
echo "3. Create database migrations\n";
echo "4. Start developing your application\n";
echo "\nUseful commands:\n";
echo "- composer test: Run PHPUnit tests\n";
echo "- composer phpstan: Run static analysis\n";
echo "- composer phpcs: Check code style\n";
echo "- composer migrate: Run database migrations\n";
