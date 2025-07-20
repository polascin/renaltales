<?php

// Set error reporting level
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('Europe/Bratislava');

// Include Composer autoloader
require_once __DIR__ . DS . '..' . DS . 'vendor' . DS . 'autoload.php';

// Include application constants definitions
require_once __DIR__ . DS . '..' . DS . 'config' . DS . 'constants.php';

// Create an instance of the Application
$app = new RenalTales\Application();

// Run the application
$app->run();
