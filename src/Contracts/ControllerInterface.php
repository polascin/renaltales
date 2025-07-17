<?php

declare(strict_types=1);

namespace RenalTales\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller Interface
 *
 * Defines the contract for all application controllers.
 * Controllers should handle HTTP requests and return responses.
 *
 * @package RenalTales\Contracts
 * @version 2025.3.1.dev
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

    /**
     * Get supported HTTP methods
     *
     * @return array<string> Array of supported HTTP methods
     */
    public function getSupportedMethods(): array;
}
