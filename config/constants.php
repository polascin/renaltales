<?php

declare(strict_types=1);

// -*- coding: utf-8 -*-

/**
 * Constants for the RenalTales application
 * This file defines various constants used throughout the application
 * such as application name, version, and default language.
 *
 * @package RenalTales
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */

// File: config/constants.php

// Define application constants
// Name, version, and default language of the application; change as needed
define('APP_NAME', 'RenalTales');
define('APP_VERSION', '2025.3.1.dev');
define('DEFAULT_LANGUAGE', 'sk');
// Base URL of the application; change as needed; https://ladvina.eu/ in production
define('APP_URL', 'https://renaltales.test/');
// Website URL of the application; change as needed; https://ladvina.eu/ in production
define('APP_WEBSITE', 'ladvina.eu');
// Full website URL; change as needed; https://ladvina.eu/ in production
define('APP_WEBSITE_URL', 'https://ladvina.eu/');
// Application root directory; change as needed; G:\Môj disk\www\renaltales in development
define('APP_ROOT', dirname(__DIR__));
// Application environment; can be 'development', 'testing', or 'production'
define('APP_ENV', 'development');
// Debug mode; set to true for development, false for production
define('APP_DEBUG', true);
// Directory where the application is located
define('APP_DIR', dirname(__DIR__));
// Define paths for assets and logs
define('LOGS_DIR', APP_DIR . DS . 'logs');
// Define paths for configuration and resources
define('CONFIG_DIR', APP_DIR . DS . 'config');
define('RESOURCES_DIR', APP_DIR . DS . 'resources');
// Define paths for language files
define('LANG_DIR', RESOURCES_DIR . DS . 'lang');
define('LANGUAGE_PATH', RESOURCES_DIR . DS . 'lang');
// Directory where the public files are located
define('PUBLIC_DIR', __DIR__);
// Define paths for public assets
define('ASSETS_DIR', PUBLIC_DIR . DS . 'assets');
// Define paths for public images
define('IMAGES_DIR', ASSETS_DIR . DS . 'images');
// Define paths for illustrations
define('ILLUSTRATIONS_DIR', ASSETS_DIR . DS . 'images' . DS . 'illustrations');
// Define paths for logos
define('LOGOS_DIR', ASSETS_DIR . DS . 'images' . DS . 'logos');
// Define paths for flags
define('FLAGS_DIR', ASSETS_DIR . DS . 'flags');
// Define paths for public css stylesheets
define('CSS_DIR', ASSETS_DIR . DS . 'css');
// Define paths for public favicon
define('FAVICON_DIR', ASSETS_DIR . DS . 'favicon');
// Define paths for public templates
define('TEMPLATE_DIR', ASSETS_DIR . DS . 'template');
// Define paths for public scripts
define('SCRIPTS_DIR', ASSETS_DIR . DS . 'js');
// Define paths for public fonts
define('FONTS_DIR', ASSETS_DIR . DS . 'fonts');
// Define paths for segments of HTML code
define('SEGMENTS_DIR', APP_DIR . DS . 'resources' . DS . 'segments');
// Define paths for public uploads
define('UPLOADS_DIR', APP_DIR . DS . 'storage' . DS . 'uploads');
// Define paths for application source directories
define('CORE_DIR', APP_DIR . DS . 'src' . DS . 'Core');
define('CONTROLLERS_DIR', APP_DIR . DS . 'src' . DS . 'Controllers');
define('MODELS_DIR', APP_DIR . DS . 'src' . DS . 'Models');
define('VIEWS_DIR', APP_DIR . DS . 'src' . DS . 'Views');

// End of file
