<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use RenalTales\Core\Application;
use RenalTales\Core\Config;
use RenalTales\Core\Router;
use RenalTales\Core\Session;
use RenalTales\Security\SecurityService;

// Load configuration
$config = new Config(__DIR__ . '/../config/config.php');

// Initialize session with secure settings
Session::start([
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

// Initialize security service
$security = new SecurityService($config);

// Initialize router
$router = new Router();

// Initialize application
$app = new Application($config, $router, $security);

// Run the application
$app->run();
