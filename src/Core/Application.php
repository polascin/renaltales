<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Exception;
use RenalTales\Core\ServiceProvider;
use RenalTales\Controllers\ApplicationController;
use RenalTales\Views\ErrorViewFinal;

/**
 * Application Class
 * 
 * Main application class that handles the bootstrap and request handling
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v2.0
 */
class Application
{
    private array $config;
    private ServiceProvider $serviceProvider;
    private bool $isDebugMode = false;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->isDebugMode = $this->config['app']['debug'] ?? false;
        $this->initializeApplication();
    }

    /**
     * Initialize the application
     */
    private function initializeApplication(): void
    {
        // Set error reporting based on debug mode
        if ($this->isDebugMode) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        // Initialize service provider
        $this->serviceProvider = new ServiceProvider($this->config);
    }

    /**
     * Handle the incoming request
     */
    public function handleRequest(): string
    {
        try {
            $languageModel = $this->serviceProvider->get('language_model');
            $sessionManager = $this->serviceProvider->get('session_manager');
            $controller = new ApplicationController($languageModel, $sessionManager);
            return $controller->index();
        } catch(Exception $e) 
    error_log('Exception in Application.php: ' . $e->getMessage());
            return $this->handleException($e);
        
    }

    /**
     * Handle exceptions
     */
    private function handleException(Exception $e): string
    {
        try {
            $errorView = new ErrorViewFinal($e, $this->isDebugMode, null);
            return $errorView->render();
        } catch(Exception $viewException) 
    error_log('Exception in Application.php: ' . $viewException->getMessage());
            // Fallback error handling
            $message = $this->isDebugMode ? 
                "Error: " . $e->getMessage() . "\nView Error: " . $viewException->getMessage() :
                "An unexpected error occurred.";
            
            return "<h1>Application Error</h1><p>" . htmlspecialchars($message) . "</p>";
        
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            ob_start();
            $output = $this->handleRequest();
            ob_end_clean();
            echo $output;
        } catch(Exception $e) 
    error_log('Exception in Application.php: ' . $e->getMessage());
            ob_end_clean();
            echo $this->handleException($e);
        
    }

    /**
     * Get application configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get database instance
     */
    public function getDatabase()
    {
        return $this->serviceProvider->get('database');
    }

    /**
     * Get session manager instance
     */
    public function getSessionManager()
    {
        return $this->serviceProvider->get('session_manager');
    }

    /**
     * Get language model instance
     */
    public function getLanguageModel()
    {
        return $this->serviceProvider->get('language_model');
    }

    /**
     * Get service provider instance
     */
    public function getServiceProvider(): ServiceProvider
    {
        return $this->serviceProvider;
    }
}
