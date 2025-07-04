<?php
/**
 * RenalTales - Main Entry Point
 * A community platform for people with kidney disorders
 * Framework-less PHP application
 */

// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Set error reporting for development (change for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Define constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', __DIR__);

// Include configuration and core files
require_once CONFIG_PATH . '/config.php';
require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Core/Router.php';
require_once APP_PATH . '/Core/Controller.php';
require_once APP_PATH . '/Core/Security.php';
require_once APP_PATH . '/Core/Language.php';

// Initialize security measures
$security = new Security();
$security->preventClickjacking();
$security->setSecurityHeaders();

// Initialize language detection and setting
$language = new Language();
$currentLang = $language->detectLanguage();

// Initialize router
$router = new Router();

// Define routes
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');
$router->get('/stories', 'StoryController@index');
$router->get('/story/{id}', 'StoryController@show');
$router->get('/category/{slug}', 'StoryController@category');

// Authentication routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');

// User profile routes
$router->get('/profile', 'UserController@profile');
$router->post('/profile/update', 'UserController@updateProfile');
$router->get('/users', 'UserController@index');
$router->get('/user/{id}', 'UserController@show');

// Story management routes (authenticated users)
$router->get('/story/create', 'StoryController@create');
$router->post('/story/create', 'StoryController@store');
$router->get('/story/{id}/edit', 'StoryController@edit');
$router->post('/story/{id}/update', 'StoryController@update');
$router->post('/story/{id}/delete', 'StoryController@delete');

// Translation routes
$router->get('/story/{id}/translate', 'TranslationController@create');
$router->post('/story/{id}/translate', 'TranslationController@store');
$router->get('/translation/{id}/edit', 'TranslationController@edit');
$router->post('/translation/{id}/update', 'TranslationController@update');

// Moderation routes (moderators only)
$router->get('/admin/pending', 'ModerationController@pending');
$router->post('/admin/approve/{id}', 'ModerationController@approve');
$router->post('/admin/reject/{id}', 'ModerationController@reject');
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/statistics', 'AdminController@statistics');

// API routes for AJAX calls
$router->get('/api/languages', 'ApiController@languages');
$router->post('/api/language/switch', 'ApiController@switchLanguage');
$router->post('/api/comment', 'ApiController@addComment');
$router->get('/api/comments/{story_id}', 'ApiController@getComments');

// Static pages
$router->get('/about', 'PageController@about');
$router->get('/privacy', 'PageController@privacy');
$router->get('/terms', 'PageController@terms');
$router->get('/contact', 'PageController@contact');
$router->post('/contact', 'PageController@sendContact');

// Language switching
$router->get('/lang/{code}', 'LanguageController@switch');

try {
    // Get current URL and method
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Remove query parameters and clean URL
    $url = rtrim($url, '/') ?: '/';
    
    // Apply rate limiting
    $security->applyRateLimit($_SERVER['REMOTE_ADDR']);
    
    // Route the request
    $router->route($method, $url);
    
} catch (Exception $e) {
    // Log error
    error_log("Application Error: " . $e->getMessage());
    
    // Show error page
    http_response_code(500);
    include APP_PATH . '/Views/errors/500.php';
}
