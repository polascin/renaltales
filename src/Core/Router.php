<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Controllers\HomeController;
use RenalTales\Controllers\LanguageController;
use RenalTales\Controllers\ErrorController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Simple Router
 *
 * Direct route mapping to controller methods without complex routing logic.
 * Implements simple request flow: Route → Controller method → Prepare data → Render template
 *
 * @package RenalTales\Core
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class Router
{
    /**
     * @var array Simple route mappings
     */
    private array $routes = [
        '/' => [HomeController::class, 'index'],
        '/home' => [HomeController::class, 'index'],
        '/language/switch' => [LanguageController::class, 'switch'],
        '/language/current' => [LanguageController::class, 'current'],
        '/error' => [ErrorController::class, 'index'],
    ];

    /**
     * Handle incoming request
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            // Get path from URI safely
            $uri = $request->getUri();
            if (is_object($uri)) {
                $path = $uri->getPath();
            } else {
                $path = parse_url($request->getRequestTarget(), PHP_URL_PATH) ?: '/';
            }
        } catch (\Throwable $e) {
            // Fallback to request target if URI fails
            $path = parse_url($request->getRequestTarget(), PHP_URL_PATH) ?: '/';
        }

        // Clean the path
        $path = rtrim($path, '/') ?: '/';

        // Check if route exists
        if (!isset($this->routes[$path])) {
            // Try to handle query parameter routing for backward compatibility
            $queryParams = $request->getQueryParams();
            if (isset($queryParams['page'])) {
                return $this->handlePageRequest($request, $queryParams['page']);
            }

            // Route not found - return 404
            return $this->createErrorResponse('Page not found', 404);
        }

        [$controllerClass, $method] = $this->routes[$path];

        try {
            $controller = new $controllerClass();
            return $controller->$method($request);
        } catch (\Throwable $e) {
            error_log("Router error: " . $e->getMessage());
            error_log("Router error file: " . $e->getFile() . ":" . $e->getLine());
            error_log("Router error trace: " . $e->getTraceAsString());
            return $this->createErrorResponse('Internal server error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle legacy page parameter routing
     *
     * @param ServerRequestInterface $request
     * @param string $page
     * @return ResponseInterface
     */
    private function handlePageRequest(ServerRequestInterface $request, string $page): ResponseInterface
    {
        switch ($page) {
            case 'home':
                $controller = new HomeController();
                return $controller->index($request);

            case 'language':
                $controller = new LanguageController();
                return $controller->current($request);

            default:
                return $this->createErrorResponse('Page not found', 404);
        }
    }

    /**
     * Create simple error response
     *
     * @param string $message
     * @param int $statusCode
     * @return ResponseInterface
     */
    private function createErrorResponse(string $message, int $statusCode = 404): ResponseInterface
    {
        $controller = new ErrorController();
        return $controller->error($message, $statusCode);
    }

    /**
     * Add a route
     *
     * @param string $path
     * @param string $controllerClass
     * @param string $method
     * @return void
     */
    public function addRoute(string $path, string $controllerClass, string $method): void
    {
        $this->routes[$path] = [$controllerClass, $method];
    }

    /**
     * Get all registered routes
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
