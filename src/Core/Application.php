<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Core\Container;
use RenalTales\Core\ServiceProvider;
use RenalTales\Controllers\ApplicationController;
use Exception;

/**
 * Application Factory
 *
 * Main application class that bootstraps the entire application,
 * handles dependency injection setup, and manages the application lifecycle.
 *
 * @package RenalTales\Core
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class Application
{
    /**
     * @var Container The dependency injection container
     */
    private Container $container;

    /**
     * @var ServiceProvider The service provider
     */
    private ServiceProvider $serviceProvider;

    /**
     * @var ApplicationController The main application controller
     */
    private ApplicationController $applicationController;

    /**
     * @var bool Whether the application has been bootstrapped
     */
    private bool $bootstrapped = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->container = new Container();
        $this->serviceProvider = new ServiceProvider($this->container);
    }

    /**
     * Bootstrap the application
     *
     * @return self
     * @throws Exception If bootstrapping fails
     */
    public function bootstrap(): self
    {
        if ($this->bootstrapped) {
            return $this;
        }

        try {
            // Register all services
            $this->serviceProvider->register();

            // Boot all services
            $this->serviceProvider->boot();

            // Create the main application controller
            $this->applicationController = $this->container->resolve(ApplicationController::class);

            $this->bootstrapped = true;

            return $this;
        } catch (Exception $e) {
            throw new Exception("Failed to bootstrap application: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Run the application
     *
     * @return void
     * @throws Exception If the application is not bootstrapped
     */
    public function run(): void
    {
        if (!$this->bootstrapped) {
            throw new Exception('Application must be bootstrapped before running');
        }

        // Get the view controller and render the view
        $viewController = $this->applicationController->getViewController();
        $viewController->render();
    }

    /**
     * Get the container instance
     *
     * @return Container The container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the service provider
     *
     * @return ServiceProvider The service provider
     */
    public function getServiceProvider(): ServiceProvider
    {
        return $this->serviceProvider;
    }

    /**
     * Get the application controller
     *
     * @return ApplicationController The application controller
     * @throws Exception If the application is not bootstrapped
     */
    public function getApplicationController(): ApplicationController
    {
        if (!$this->bootstrapped) {
            throw new Exception('Application must be bootstrapped before accessing the application controller');
        }

        return $this->applicationController;
    }

    /**
     * Check if the application has been bootstrapped
     *
     * @return bool True if bootstrapped, false otherwise
     */
    public function isBootstrapped(): bool
    {
        return $this->bootstrapped;
    }

    /**
     * Get a service from the container
     *
     * @param string $abstract The service identifier
     * @return mixed The service instance
     * @throws Exception If the service cannot be resolved
     */
    public function get(string $abstract)
    {
        return $this->container->resolve($abstract);
    }

    /**
     * Check if a service exists in the container
     *
     * @param string $abstract The service identifier
     * @return bool True if the service exists, false otherwise
     */
    public function has(string $abstract): bool
    {
        return $this->container->bound($abstract);
    }

    /**
     * Handle application shutdown
     *
     * @return void
     */
    public function shutdown(): void
    {
        // Perform any cleanup operations
        // This can include logging, closing connections, etc.
        
        if ($this->bootstrapped) {
            // Log application shutdown
            if ($this->container->bound(Logger::class)) {
                $logger = $this->container->get(Logger::class);
                if ($logger) {
                    // Log shutdown if logger is available
                    error_log('Application shutdown');
                }
            }
        }
    }

    /**
     * Get application version
     *
     * @return string The application version
     */
    public function getVersion(): string
    {
        return APP_VERSION ?? '2025.3.1.dev';
    }

    /**
     * Get application name
     *
     * @return string The application name
     */
    public function getName(): string
    {
        return APP_NAME ?? 'RenalTales';
    }

    /**
     * Get application environment
     *
     * @return string The application environment
     */
    public function getEnvironment(): string
    {
        return APP_ENV ?? 'development';
    }

    /**
     * Check if application is in debug mode
     *
     * @return bool True if in debug mode, false otherwise
     */
    public function isDebug(): bool
    {
        return APP_DEBUG ?? false;
    }
}
