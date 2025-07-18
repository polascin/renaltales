<?php

// Verify the setup by loading through index.php pathway
require_once 'config/constants.php';
require_once 'src/bootstrap.php';

echo "=== Application Constants Setup Verification ===\n";
echo "APP_VERSION: " . APP_VERSION . "\n";
echo "APP_NAME: " . APP_NAME . "\n";
echo "APP_ENV: " . APP_ENV . "\n";
echo "APP_DEBUG: " . (APP_DEBUG ? 'true' : 'false') . "\n";
echo "APP_ROOT: " . APP_ROOT . "\n";
echo "APP_DIR: " . APP_DIR . "\n";
echo "Bootstrap loaded successfully!\n";
echo "=== Setup Complete ===\n";
