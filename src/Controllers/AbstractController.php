<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Contracts\ControllerInterface;
use RenalTales\Services\LanguageService;
use RenalTales\Core\SecurityManager;
use RenalTales\Core\SessionManager;
use RenalTales\Contracts\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract Base Controller
 *
 * Provides common functionality for all controllers including:
 * - Request handling utilities
 * - Response creation
 * - Security validation
 * - Language management
 * - Logging
 *
 * @package RenalTales\Controllers
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
abstract class AbstractController implements ControllerInterface
{
    protected LanguageService $languageService;
    protected SecurityManager $securityManager;
    protected SessionManager $sessionManager;
    protected LoggerInterface $logger;

    /**
     * @var array<string, mixed> Common data available to all controllers
     */
    protected array $sharedData = [];

    /**
     * Constructor
     *
     * @param LanguageService $languageService Language service for translations
     * @param SecurityManager $securityManager Security manager for validation
     * @param SessionManager $sessionManager Session manager for user sessions
     * @param LoggerInterface $logger Logger for error tracking
     */
    public function __construct(
        LanguageService $languageService,
        SecurityManager $securityManager,
        SessionManager $sessionManager,
        LoggerInterface $logger
    ) {
        $this->languageService = $languageService;
        $this->securityManager = $securityManager;
        $this->sessionManager = $sessionManager;
        $this->logger = $logger;

        $this->initializeSharedData();
    }

    /**
     * Initialize shared data available to all controllers
     */
    protected function initializeSharedData(): void
    {
        $this->sharedData = [
            'current_language' => $this->languageService->getCurrentLanguage(),
            'supported_languages' => $this->languageService->getSupportedLanguagesWithNames(),
            'app_name' => 'RenalTales',
            'app_version' => '2025.3.1.dev',
            'is_authenticated' => $this->sessionManager->has('user_id'),
            'user_id' => $this->sessionManager->get('user_id'),
            'csrf_token' => $this->securityManager->getCSRFToken() ?? '',
        ];
    }

    /**
     * Get translated text
     *
     * @param string $key Translation key
     * @param array<string, string|int|float> $parameters Parameters for replacement
     * @param string $fallback Fallback text if translation not found
     * @return string Translated text
     */
    protected function trans(string $key, array $parameters = [], string $fallback = ''): string
    {
        return $this->languageService->getText($key, $parameters, $fallback);
    }

    /**
     * Create a JSON response
     *
     * @param array<string, mixed> $data Response data
     * @param int $status HTTP status code
     * @param array<string, string> $headers Additional headers
     * @return ResponseInterface JSON response
     */
    protected function json(array $data, int $status = 200, array $headers = []): ResponseInterface
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $response = $this->createResponse($json, $status);
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    /**
     * Create an HTML response
     *
     * @param string $html HTML content
     * @param int $status HTTP status code
     * @param array<string, string> $headers Additional headers
     * @return ResponseInterface HTML response
     */
    protected function html(string $html, int $status = 200, array $headers = []): ResponseInterface
    {
        $response = $this->createResponse($html, $status);
        $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    /**
     * Create a redirect response
     *
     * @param string $url Redirect URL
     * @param int $status HTTP status code (301, 302, 303, 307, 308)
     * @return ResponseInterface Redirect response
     */
    protected function redirect(string $url, int $status = 302): ResponseInterface
    {
        $response = $this->createResponse('', $status);
        return $response->withHeader('Location', $url);
    }

    /**
     * Render a view
     *
     * @param ViewInterface $view View to render
     * @param array<string, mixed> $data Data to pass to view
     * @return ResponseInterface HTML response with rendered view
     */
    protected function view(ViewInterface $view, array $data = []): ResponseInterface
    {
        $viewData = array_merge($this->sharedData, $data);
        $html = $view->render($viewData);

        return $this->html($html);
    }

    /**
     * Validate CSRF token
     *
     * @param ServerRequestInterface $request HTTP request
     * @return bool True if valid, false otherwise
     */
    protected function validateCSRF(ServerRequestInterface $request): bool
    {
        $parsedBody = $request->getParsedBody();
        $token = is_array($parsedBody) && isset($parsedBody['csrf_token'])
            ? (string) $parsedBody['csrf_token']
            : '';

        return $this->securityManager->validateCSRFToken($token);
    }

    /**
     * Check if request is AJAX
     *
     * @param ServerRequestInterface $request HTTP request
     * @return bool True if AJAX request, false otherwise
     */
    protected function isAjax(ServerRequestInterface $request): bool
    {
        $header = $request->getHeaderLine('X-Requested-With');
        return strtolower($header) === 'xmlhttprequest';
    }

    /**
     * Get request parameter
     *
     * @param ServerRequestInterface $request HTTP request
     * @param string $key Parameter key
     * @param mixed $default Default value if parameter not found
     * @return mixed Parameter value
     */
    protected function getParameter(ServerRequestInterface $request, string $key, $default = null)
    {
        $queryParams = $request->getQueryParams();
        if (isset($queryParams[$key])) {
            return $queryParams[$key];
        }

        $parsedBody = $request->getParsedBody();
        if (is_array($parsedBody) && isset($parsedBody[$key])) {
            return $parsedBody[$key];
        }

        return $default;
    }

    /**
     * Log controller action
     *
     * @param string $action Action name
     * @param array<string, mixed> $context Additional context
     */
    protected function logAction(string $action, array $context = []): void
    {
        $this->logger->info("Controller action: {$action}", array_merge([
            'controller' => static::class,
            'user_id' => $this->sharedData['user_id'] ?? null,
            'language' => $this->sharedData['current_language'],
        ], $context));
    }

    /**
     * Log error
     *
     * @param string $message Error message
     * @param \Throwable|null $exception Exception if available
     * @param array<string, mixed> $context Additional context
     */
    protected function logError(string $message, ?\Throwable $exception = null, array $context = []): void
    {
        $this->logger->error($message, array_merge([
            'controller' => static::class,
            'exception' => $exception ? [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ] : null,
        ], $context));
    }

    /**
     * Create a basic response
     *
     * @param string $body Response body
     * @param int $status HTTP status code
     * @return ResponseInterface Response
     */
    abstract protected function createResponse(string $body, int $status = 200): ResponseInterface;

    /**
     * Get default supported HTTP methods
     *
     * @return array<string> Array of supported HTTP methods
     */
    public function getSupportedMethods(): array
    {
        return ['GET', 'POST'];
    }
}
