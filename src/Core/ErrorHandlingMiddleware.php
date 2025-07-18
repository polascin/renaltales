<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Contracts\MiddlewareInterface;
use Closure;
use Throwable;
use RenalTales\Core\ErrorHandler;
use Psr\Log\LoggerInterface;

/**
 * Error Handling Middleware
 *
 * Global error handling middleware that captures exceptions,
 * logs them using the logger & provides a response to the client.
 *
 * @package RenalTales\Core
 * @version 2025.3.1.dev
 */
class ErrorHandlingMiddleware implements MiddlewareInterface
{
    private ErrorHandler $errorHandler;
    private LoggerInterface $logger;

    /**
     * Constructor
     */
    public function __construct(ErrorHandler $errorHandler, LoggerInterface $logger)
    {
        $this->errorHandler = $errorHandler;
        $this->logger = $logger;
    }

    /**
     * Handle middleware
     *
     * @param mixed $request The request object
     * @param Closure(mixed): mixed $next The next middleware in the chain
     * @return mixed
     */
    public function handle($request, Closure $next): mixed
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            $this->errorHandler->handleException($e);

            // Log processed exception
            $this->logger->error('Exception handled in middleware', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return a generic error response
            return 'An error occurred while processing the request.';
        }
    }
}
