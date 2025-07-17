<?php

declare(strict_types=1);

namespace RenalTales\Exceptions;

use Psr\Container\ContainerExceptionInterface;

/**
 * Container Exception
 *
 * Thrown when the container cannot resolve a dependency or encounters
 * an error during dependency resolution.
 *
 * @package RenalTales\Exceptions
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class ContainerException extends ApplicationException implements ContainerExceptionInterface
{
    /**
     * Create exception for circular dependency
     *
     * @param string $service The service with circular dependency
     * @param array $stack The dependency stack
     * @return static
     */
    public static function circularDependency(string $service, array $stack): static
    {
        $stackStr = implode(' -> ', $stack);
        return new static("Circular dependency detected for '{$service}': {$stackStr}");
    }

    /**
     * Create exception for unresolvable dependency
     *
     * @param string $service The unresolvable service
     * @param string $parameter The parameter that couldn't be resolved
     * @return static
     */
    public static function unresolvableParameter(string $service, string $parameter): static
    {
        return new static("Cannot resolve parameter '{$parameter}' for service '{$service}'");
    }

    /**
     * Create exception for invalid binding
     *
     * @param string $service The service with invalid binding
     * @param string $reason The reason for invalidity
     * @return static
     */
    public static function invalidBinding(string $service, string $reason): static
    {
        return new static("Invalid binding for service '{$service}': {$reason}");
    }

    /**
     * Create exception for non-instantiable class
     *
     * @param string $class The non-instantiable class
     * @return static
     */
    public static function nonInstantiable(string $class): static
    {
        return new static("Class '{$class}' is not instantiable (abstract or interface)");
    }
}
