<?php

declare(strict_types=1);

namespace RenalTales\Services;

use Throwable;

/**
 * Error Data Service
 * 
 * Handles error data preparation and logging.
 * Extracts business logic from error view components.
 * 
 * @package RenalTales\Services
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class ErrorDataService
{
    private bool $debugMode;

    public function __construct(bool $debugMode = false)
    {
        $this->debugMode = $debugMode;
    }

    /**
     * Get error page data
     *
     * @param Throwable $exception Exception to handle
     * @return array Error page data
     */
    public function getErrorData(Throwable $exception): array
    {
        $this->logError($exception);

        return [
            'title' => $this->getErrorTitle($exception),
            'message' => $this->getErrorMessage($exception),
            'code' => $exception->getCode(),
            'debug_info' => $this->debugMode ? $this->getDebugInfo($exception) : null,
        ];
    }

    /**
     * Get user-friendly error title
     *
     * @param Throwable $exception Exception
     * @return string Error title
     */
    private function getErrorTitle(Throwable $exception): string
    {
        $code = $exception->getCode();

        return match ($code) {
            404 => 'Page Not Found',
            403 => 'Access Forbidden',
            500 => 'Internal Server Error',
            default => 'Application Error'
        };
    }

    /**
     * Get user-friendly error message
     *
     * @param Throwable $exception Exception
     * @return string Error message
     */
    private function getErrorMessage(Throwable $exception): string
    {
        $code = $exception->getCode();

        return match ($code) {
            404 => 'The page you are looking for could not be found.',
            403 => 'You do not have permission to access this resource.',
            500 => 'An internal server error occurred. Please try again later.',
            default => 'An error occurred while processing your request.'
        };
    }

    /**
     * Get debug information for development
     *
     * @param Throwable $exception Exception
     * @return array Debug information
     */
    private function getDebugInfo(Throwable $exception): array
    {
        return [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];
    }

    /**
     * Log the error
     *
     * @param Throwable $exception Exception to log
     * @return void
     */
    private function logError(Throwable $exception): void
    {
        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        error_log($logMessage);
    }
}
