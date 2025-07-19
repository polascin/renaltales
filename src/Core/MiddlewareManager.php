<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Contracts\MiddlewareInterface;
use Closure;

/**
 * Middleware Manager
 *
 * Manages the middleware pipeline and handles execution of middleware chain.
 *
 * @package RenalTales\Core
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class MiddlewareManager
{
    /**
     * @var array List of registered middleware
     */
    private array $middleware = [];

    /**
     * Add middleware to the pipeline
     *
     * @param MiddlewareInterface $middleware
     * @return self
     */
    public function add(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Execute the middleware pipeline
     *
     * @param mixed $request The request object
     * @param Closure(mixed): mixed $finalHandler The final handler to execute
     * @return mixed The response
     */
    public function handle($request, Closure $finalHandler): mixed
    {
        $middleware = array_reverse($this->middleware);

        $pipeline = array_reduce($middleware, function ($next, $middleware) {
            return function ($request) use ($middleware, $next) {
                return $middleware->handle($request, $next);
            };
        }, $finalHandler);

        return $pipeline($request);
    }

    /**
     * Get count of registered middleware
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->middleware);
    }

    /**
     * Clear all middleware
     *
     * @return self
     */
    public function clear(): self
    {
        $this->middleware = [];
        return $this;
    }
}
