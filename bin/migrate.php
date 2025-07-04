<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use RenalTales\Core\Config;
use RenalTales\Database\MigrationManager;

try {
    $config = new Config(__DIR__ . '/../config/config.php');
    $migrationManager = new MigrationManager($config);
    
    echo "Running database migrations...\n";
    $migrationManager->migrate();
    echo "Database migrations completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
