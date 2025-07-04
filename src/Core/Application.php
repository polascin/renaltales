<?php
declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Security\SecurityService;

class Application
{
    private Config $config;
    private Router $router;
    private SecurityService $security;
    private LanguageManager $languageManager;

    public function __construct(
        Config $config,
        Router $router,
        SecurityService $security
    ) {
        $this->config = $config;
        $this->router = $router;
        $this->security = $security;
        $this->languageManager = new LanguageManager($config);
        
        $this->initialize();
    }

    private function initialize(): void
    {
        // Set error reporting based on environment
        if ($this->config->get('app.debug')) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        // Set timezone
        date_default_timezone_set($this->config->get('app.timezone'));

        // Initialize language detection and settings
        $this->languageManager->initialize();

        // Register routes
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        // Home page
        $this->router->get('/', 'HomeController@index');

        // Authentication routes
        $this->router->get('/login', 'AuthController@loginForm');
        $this->router->post('/login', 'AuthController@login');
        $this->router->get('/register', 'AuthController@registerForm');
        $this->router->post('/register', 'AuthController@register');
        $this->router->post('/logout', 'AuthController@logout');

        // Story routes
        $this->router->get('/stories', 'StoryController@index');
        $this->router->get('/stories/create', 'StoryController@create');
        $this->router->post('/stories', 'StoryController@store');
        $this->router->get('/stories/{id}', 'StoryController@show');
        $this->router->get('/stories/{id}/edit', 'StoryController@edit');
        $this->router->put('/stories/{id}', 'StoryController@update');
        $this->router->delete('/stories/{id}', 'StoryController@delete');

        // Translation routes
        $this->router->get('/translations', 'TranslationController@index');
        $this->router->get('/translations/{storyId}', 'TranslationController@edit');
        $this->router->post('/translations/{storyId}', 'TranslationController@store');

        // User management routes
        $this->router->get('/users', 'UserController@index');
        $this->router->get('/users/{id}', 'UserController@show');
        $this->router->put('/users/{id}', 'UserController@update');
        $this->router->delete('/users/{id}', 'UserController@delete');

        // Language switch route
        $this->router->get('/language/{code}', 'LanguageController@switch');
    }

    public function run(): void
    {
        try {
            // Process the current request
            $response = $this->router->dispatch();
            
            // Send the response
            $response->send();
        } catch (\Exception $e) {
            // Log the error
            error_log($e->getMessage());
            
            // Show appropriate error page based on environment
            if ($this->config->get('app.debug')) {
                throw $e;
            } else {
                // Show user-friendly error page
                $this->showErrorPage($e);
            }
        }
    }

    private function showErrorPage(\Exception $e): void
    {
        $statusCode = $e instanceof HttpException ? $e->getCode() : 500;
        http_response_code($statusCode);
        
        // Load and display appropriate error template
        include __DIR__ . '/../../templates/errors/' . $statusCode . '.php';
    }
}
