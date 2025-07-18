<?php

declare(strict_types=1);

// PHPStan bootstrap file
// This file is loaded before PHPStan starts analyzing

// Load composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load constants if they haven't been loaded yet
if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/config/constants.php';
}
