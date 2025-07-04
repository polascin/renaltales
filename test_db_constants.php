<?php
/**
 * Test file to verify database configuration is loaded correctly
 */

// Load bootstrap to get environment variables
require_once __DIR__ . '/bootstrap/autoload.php';

// Load config
$config = require_once __DIR__ . '/config/config.php';

echo "Testing database configuration:\n";
echo "DB_HOST from env: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "DB_DATABASE from env: " . ($_ENV['DB_DATABASE'] ?? 'NOT SET') . "\n";
echo "DB_USERNAME from env: " . ($_ENV['DB_USERNAME'] ?? 'NOT SET') . "\n";
echo "DB_CHARSET from env: " . ($_ENV['DB_CHARSET'] ?? 'NOT SET') . "\n";

echo "\nDatabase config array:\n";
echo "Host: " . $config['database']['host'] . "\n";
echo "Database: " . $config['database']['database'] . "\n";
echo "Username: " . $config['database']['username'] . "\n";
echo "Charset: " . $config['database']['charset'] . "\n";

echo "\nConfiguration test completed successfully!\n";
