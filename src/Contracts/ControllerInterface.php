<?php

declare(strict_types=1);

namespace RenalTales\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Simplified Controller Interface
 *
 * Minimal contract for streamlined controllers.
 *
 * @package RenalTales\Contracts
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
interface ControllerInterface
{
    /**
     * Handle an HTTP request and return a response
     *
     * @param ServerRequestInterface $request The HTTP request
     * @return ResponseInterface The HTTP response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;

    /**
     * Get the controller name
     *
     * @return string The controller name
     */
    public function getName(): string;
}
