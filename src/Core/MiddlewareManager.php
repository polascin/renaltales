<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Closure;

/**
 * Simplified Middleware Manager
 *
 * Minimal middleware pipeline - only essential middleware.
 *
 * @package RenalTales\Core
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class MiddlewareManager
{
    private array $middleware = [];

    /**
     * Add essential middleware only
     */
    public function addEssential(callable $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Execute minimal middleware pipeline
     */
    public function handle($request, Closure $finalHandler): mixed
    {
        // Skip complex pipeline if no essential middleware
        if (empty($this->middleware)) {
            return $finalHandler($request);
        }

        // Simple execution for essential middleware only
        $result = $request;
        foreach ($this->middleware as $middleware) {
            $result = $middleware($result);
        }
        
        return $finalHandler($result);
    }

    public function clear(): self
    {
        $this->middleware = [];
        return $this;
    }
}
