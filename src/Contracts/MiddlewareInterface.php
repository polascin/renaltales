<?php

declare(strict_types=1);

namespace RenalTales\Contracts;

use Closure;

/**
 * Middleware Interface
 *
 * Contract for all middleware components in the application.
 * Follows the PSR-15 middleware pattern.
 *
 * @package RenalTales\Contracts
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
interface MiddlewareInterface
{
    /**
     * Process the middleware
     *
     * @param mixed $request The request object
     * @param Closure(mixed): mixed $next The next middleware in the pipeline
     * @return mixed The response
     */
    public function handle($request, Closure $next): mixed;
}
