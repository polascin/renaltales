<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Monolog\Logger;
use Throwable;
use ErrorException;
use RenalTales\Views\ErrorView;

/**
 * Global Error Handler
 *
 * Handles all PHP errors, exceptions, and fatal errors in a centralized way.
 * Integrates with Monolog for comprehensive logging and provides user-friendly error pages.
 *
 * @package RenalTales\Core
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class ErrorHandler
{
    private Logger $logger;
    private bool $debug;
    private string $environment;
    private array $errorLevels;
    private bool $registered = false;

    /**
     * Constructor
     *
     * @param Logger $logger The Monolog logger instance
     * @param bool $debug Whether debug mode is enabled
     * @param string $environment The application environment
     */
    public function __construct(Logger $logger, bool $debug = false, string $environment = 'production')
    {
        $this->logger = $logger;
        $this->debug = $debug;
        $this->environment = $environment;

        // Define error level mappings
        $this->errorLevels = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'ERROR',
            E_CORE_WARNING => 'WARNING',
            E_COMPILE_ERROR => 'ERROR',
            E_COMPILE_WARNING => 'WARNING',
            E_USER_ERROR => 'ERROR',
            E_USER_WARNING => 'WARNING',
            E_USER_NOTICE => 'NOTICE',
            // E_STRICT => 'WARNING', // Deprecated in PHP 8.4+
            E_RECOVERABLE_ERROR => 'ERROR',
            E_DEPRECATED => 'WARNING',
            E_USER_DEPRECATED => 'WARNING',
        ];
    }

    /**
     * Register error handlers
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        // Register error handler
        set_error_handler([$this, 'handleError']);

        // Register exception handler
        set_exception_handler([$this, 'handleException']);

        // Register shutdown handler for fatal errors
        register_shutdown_function([$this, 'handleShutdown']);

        $this->registered = true;

        $this->logger->info('Global error handler registered', [
            'debug' => $this->debug,
            'environment' => $this->environment
        ]);
    }

    /**
     * Unregister error handlers
     *
     * @return void
     */
    public function unregister(): void
    {
        if (!$this->registered) {
            return;
        }

        restore_error_handler();
        restore_exception_handler();

        $this->registered = false;

        $this->logger->info('Global error handler unregistered');
    }

    /**
     * Handle PHP errors
     *
     * @param int $level Error level
     * @param string $message Error message
     * @param string $file File where error occurred
     * @param int $line Line where error occurred
     * @param array $context Error context
     * @return bool
     * @throws ErrorException
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0, array $context = []): bool
    {
        // Don't handle errors that have been suppressed with @
        if (!(error_reporting() & $level)) {
            return false;
        }

        $errorType = $this->errorLevels[$level] ?? 'UNKNOWN';

        // Log the error
        $this->logger->log(
            $this->getMonologLevel($level),
            sprintf('PHP %s: %s in %s on line %d', $errorType, $message, $file, $line),
            [
                'level' => $level,
                'file' => $file,
                'line' => $line,
                'context' => $context,
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
            ]
        );

        // Convert error to exception if it's a fatal error
        if ($this->isFatalError($level)) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }

        // Return true to prevent PHP's default error handler from running
        return true;
    }

    /**
     * Handle uncaught exceptions
     *
     * @param Throwable $exception The uncaught exception
     * @return void
     */
    public function handleException(Throwable $exception): void
    {
        // Log the exception
        $this->logger->critical('Uncaught exception', [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'code' => $exception->getCode(),
            'class' => get_class($exception)
        ]);

        // Clean any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Display error page
        $this->displayErrorPage($exception);
    }

    /**
     * Handle fatal errors during shutdown
     *
     * @return void
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error && $this->isFatalError($error['type'])) {
            $this->logger->critical('Fatal error during shutdown', [
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type']
            ]);

            // Clean any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Create exception from error
            $exception = new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );

            $this->displayErrorPage($exception);
        }
    }

    /**
     * Display error page to user
     *
     * @param Throwable $exception The exception to display
     * @return void
     */
    private function displayErrorPage(Throwable $exception): void
    {
        try {
            // Set appropriate HTTP status code
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=utf-8');
            }

            // Try to get language service if available
            $languageService = null;
            if (class_exists('RenalTales\Services\LanguageService')) {
                // This would need to be injected if available
                $languageService = null;
            }

            // Create and render error view
            $errorView = new ErrorView($exception, $this->debug, $languageService);
            echo $errorView->render();
        } catch (Throwable $e) {
            // Fallback error display if ErrorView fails
            $this->logger->emergency('Error displaying error page', [
                'original_exception' => $exception->getMessage(),
                'display_exception' => $e->getMessage()
            ]);

            // Simple fallback error page
            echo $this->getFallbackErrorPage($exception);
        }
    }

    /**
     * Get fallback error page when ErrorView fails
     *
     * @param Throwable $exception The exception
     * @return string HTML content
     */
    private function getFallbackErrorPage(Throwable $exception): string
    {
        $title = $this->debug ? 'Application Error' : 'Server Error';
        $message = $this->debug ?
            htmlspecialchars($exception->getMessage()) :
            'An error occurred while processing your request.';

        $details = '';
        if ($this->debug) {
            $details = '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <h1>{$title}</h1>
    <div class="error">
        <p>{$message}</p>
        <div class="debug">{$details}</div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Check if error level is fatal
     *
     * @param int $level Error level
     * @return bool
     */
    private function isFatalError(int $level): bool
    {
        return in_array($level, [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
            E_RECOVERABLE_ERROR
        ]);
    }

    /**
     * Get Monolog level from PHP error level
     *
     * @param int $level PHP error level
     * @return string Monolog level
     */
    private function getMonologLevel(int $level): string
    {
        switch ($level) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                return 'error';
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return 'warning';
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'notice';
                // Handle deprecated E_STRICT constant conditionally
            case defined('E_STRICT') ? E_STRICT : -1:
                return 'info';
            default:
                return 'debug';
        }
    }

    /**
     * Check if error handler is registered
     *
     * @return bool
     */
    public function isRegistered(): bool
    {
        return $this->registered;
    }

    /**
     * Get debug mode status
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Get environment
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }
}
