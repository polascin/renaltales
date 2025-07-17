<?php

declare(strict_types=1);

namespace RenalTales\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Dependency Exception
 *
 * Thrown when a requested service or dependency cannot be found
 * in the container.
 *
 * @package RenalTales\Exceptions
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class DependencyException extends ApplicationException implements NotFoundExceptionInterface
{
    /**
     * Create exception for service not found
     *
     * @param string $service The service that was not found
     * @return static
     */
    public static function serviceNotFound(string $service): static
    {
        return new static("Service '{$service}' not found in container");
    }

    /**
     * Create exception for dependency not found
     *
     * @param string $dependency The dependency that was not found
     * @param string $context The context where it was needed
     * @return static
     */
    public static function dependencyNotFound(string $dependency, string $context): static
    {
        return new static("Dependency '{$dependency}' not found for '{$context}'");
    }

    /**
     * Create exception for missing constructor parameter
     *
     * @param string $parameter The missing parameter
     * @param string $class The class that needs the parameter
     * @return static
     */
    public static function missingParameter(string $parameter, string $class): static
    {
        return new static("Missing parameter '{$parameter}' for class '{$class}'");
    }

    /**
     * Create exception for unresolvable type
     *
     * @param string $type The unresolvable type
     * @return static
     */
    public static function unresolvableType(string $type): static
    {
        return new static("Cannot resolve type '{$type}' - no binding found");
    }
}
