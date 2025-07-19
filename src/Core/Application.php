<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Core\Container;
use RenalTales\Core\ServiceProvider;
use RenalTales\Controllers\ApplicationController;
use RenalTales\Http\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use RenalTales\Core\Router;

/**
 * Application Factory
 *
 * Main application class that bootstraps the entire application,
 * handles dependency injection setup, and manages the application lifecycle.
 *
 * @package RenalTales\Core
 * @version 2025.v3.1.dev
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
     * @var ApplicationController|null The main application controller
     */
    private ?ApplicationController $applicationController = null;

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
            // Simplified bootstrap - just mark as bootstrapped
            // We're no longer using complex service providers
            $this->bootstrapped = true;

            return $this;
        } catch (\Throwable $e) {
            error_log("Failed to bootstrap application: {$e->getMessage()}");
            throw new \Exception("Failed to bootstrap application: {$e->getMessage()}", 0, $e);
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
        try {
            // Create a PSR-7 request from globals
            $request = $this->createRequestFromGlobals();

            // Use the new Router to handle the request
            $router = new Router();
            $response = $router->handle($request);

            // Send the response
            $this->sendResponse($response);
        } catch (\Throwable $e) {
            // Handle any errors that occur during request processing
            error_log("Application error: " . $e->getMessage());
            
            // Send a simple error response
            $this->sendErrorResponse($e->getMessage(), 500);
        }
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
        if (!$this->bootstrapped || $this->applicationController === null) {
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
        return APP_VERSION ?? '2025.v3.1.dev';
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

    /**
     * Create a PSR-7 request from PHP globals
     *
     * @return ServerRequestInterface
     */
    private function createRequestFromGlobals(): ServerRequestInterface
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $headers = [];
        $body = '';

        // Extract headers from $_SERVER
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerName = str_replace('HTTP_', '', $key);
                $headerName = str_replace('_', '-', $headerName);
                $headers[ucwords(strtolower($headerName), '-')] = $value;
            }
        }

        // Get request body for POST/PUT/PATCH requests
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $body = file_get_contents('php://input') ?: '';
        }

        return new ServerRequest(
            $method,
            $uri,
            $headers,
            $body,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
    }

    /**
     * Send a PSR-7 response to the client
     *
     * @param ResponseInterface $response
     * @return void
     */
    private function sendResponse(ResponseInterface $response): void
    {
        // Set the response status code
        if (!headers_sent()) {
            http_response_code($response->getStatusCode());

            // Set response headers
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }

        // Output the response body
        echo $response->getBody();
    }

    /**
     * Send a simple error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @return void
     */
    private function sendErrorResponse(string $message, int $statusCode = 500): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: text/html; charset=utf-8');
        }

        $html = "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Error {$statusCode} - RenalTales</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .error-container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; margin-bottom: 20px; }
        p { color: #666; line-height: 1.6; margin-bottom: 15px; }
        .error-code { font-size: 2em; font-weight: bold; color: #d32f2f; margin-bottom: 10px; }
        a { color: #1976d2; text-decoration: none; padding: 10px 20px; background: #e3f2fd; border-radius: 4px; display: inline-block; margin-top: 20px; }
        a:hover { background: #bbdefb; }
    </style>
</head>
<body>
    <div class=\"error-container\">
        <div class=\"error-code\">Error {$statusCode}</div>
        <h1>Application Error</h1>
        <p>An error occurred while processing your request.</p>
        <p><strong>Message:</strong> {$message}</p>
        <a href=\"/\">Back to Home</a>
    </div>
</body>
</html>";

        echo $html;
    }
}
