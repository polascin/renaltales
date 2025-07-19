<?php

declare(strict_types=1);

/**
 * Component Loader
 *
 * Loads all component functions to replace heavy view classes
 *
 * @package RenalTales\Components
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

// Load helper functions first
require_once __DIR__ . '/view_helpers.php';

// Load component functions
require_once __DIR__ . '/home_component.php';
require_once __DIR__ . '/error_component.php';

/**
 * Auto-load all component files
 */
function load_components(): void
{
    $componentDir = __DIR__;
    $files = glob($componentDir . '/*_component.php');

    foreach ($files as $file) {
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// Initialize components
load_components();
