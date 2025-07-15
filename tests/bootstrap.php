<?php

declare(strict_types=1);

use RenalTales\Core\Application;
use RenalTales\Tests\TestCase;

// Load the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load the application bootstrap
require_once __DIR__ . '/../bootstrap.php';

// Set up testing environment
$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_ENV['CACHE_DRIVER'] = 'array';
$_ENV['SESSION_DRIVER'] = 'array';

// Ensure test directories exist
$testDirs = [
    __DIR__ . '/logs',
    __DIR__ . '/Unit',
    __DIR__ . '/Integration',
    __DIR__ . '/Feature',
    __DIR__ . '/Context',
    __DIR__ . '/Fixtures',
    __DIR__ . '/../coverage',
    __DIR__ . '/../features/web',
    __DIR__ . '/../features/api',
    __DIR__ . '/../features/multilingual',
];

foreach ($testDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Initialize test application
TestCase::initializeTestApplication();

// Set up error handling for tests
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Set up assertion defaults
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL, 0);
assert_options(ASSERT_QUIET_EVAL, 1);

// Register cleanup function
register_shutdown_function(function () {
    TestCase::cleanupTestApplication();
});
