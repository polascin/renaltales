<?php

declare(strict_types=1);

namespace RenalTales\Core;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Exception;

/**
 * Dependency Injection Container
 *
 * A simple but powerful dependency injection container that handles
 * service instantiation, singleton management, and automatic dependency resolution.
 *
 * @package RenalTales\Core
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class Container
{
    /**
     * @var array<string, mixed> Container bindings
     */
    private array $bindings = [];

    /**
     * @var array<string, object> Singleton instances
     */
    private array $instances = [];

    /**
     * @var array<string, bool> Services marked as singletons
     */
    private array $singletons = [];

    /**
     * @var array<string, callable> Factory functions
     */
    private array $factories = [];

    /**
     * Bind a service to the container
     *
     * @param string $abstract The service identifier
     * @param string|callable|null $concrete The concrete implementation
     * @param bool $singleton Whether the service should be a singleton
     * @return void
     */
    public function bind(string $abstract, $concrete = null, bool $singleton = false): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
        
        if ($singleton) {
            $this->singletons[$abstract] = true;
        }
    }

    /**
     * Register a singleton service
     *
     * @param string $abstract The service identifier
     * @param string|callable|null $concrete The concrete implementation
     * @return void
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register a factory for creating instances
     *
     * @param string $abstract The service identifier
     * @param callable $factory The factory function
     * @return void
     */
    public function factory(string $abstract, callable $factory): void
    {
        $this->factories[$abstract] = $factory;
    }

    /**
     * Register an instance as a singleton
     *
     * @param string $abstract The service identifier
     * @param object $instance The instance to register
     * @return void
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
        $this->singletons[$abstract] = true;
    }

    /**
     * Resolve a service from the container
     *
     * @param string $abstract The service identifier
     * @param array<string, mixed> $parameters Constructor parameters
     * @return mixed The resolved service
     * @throws Exception When the service cannot be resolved
     */
    public function resolve(string $abstract, array $parameters = [])
    {
        // Return existing singleton instance
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Use factory if available
        if (isset($this->factories[$abstract])) {
            $instance = $this->factories[$abstract]($this, $parameters);
            
            if ($this->isSingleton($abstract)) {
                $this->instances[$abstract] = $instance;
            }
            
            return $instance;
        }

        // Get concrete implementation
        $concrete = $this->bindings[$abstract] ?? $abstract;

        // If concrete is a callable, call it
        if (is_callable($concrete)) {
            $instance = $concrete($this, $parameters);
        } else {
            // Build the service
            $instance = $this->build($concrete, $parameters);
        }

        // Store singleton instance
        if ($this->isSingleton($abstract)) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Build a service instance
     *
     * @param string $concrete The concrete class name
     * @param array<string, mixed> $parameters Constructor parameters
     * @return object The built instance
     * @throws Exception When the service cannot be built
     */
    protected function build(string $concrete, array $parameters = []): object
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new Exception("Cannot instantiate class '{$concrete}': {$e->getMessage()}");
        }

        // Check if class is instantiable
        if (!$reflector->isInstantiable()) {
            throw new Exception("Class '{$concrete}' is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        // If no constructor, just create instance
        if ($constructor === null) {
            return $reflector->newInstance();
        }

        // Resolve constructor dependencies
        $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve method dependencies
     *
     * @param array<ReflectionParameter> $parameters Method parameters
     * @param array<string, mixed> $primitives Primitive values
     * @return array<mixed> Resolved dependencies
     * @throws Exception When a dependency cannot be resolved
     */
    protected function resolveDependencies(array $parameters, array $primitives = []): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $this->resolveDependency($parameter, $primitives);
            $dependencies[] = $dependency;
        }

        return $dependencies;
    }

    /**
     * Resolve a single dependency
     *
     * @param ReflectionParameter $parameter The parameter to resolve
     * @param array<string, mixed> $primitives Primitive values
     * @return mixed The resolved dependency
     * @throws Exception When the dependency cannot be resolved
     */
    protected function resolveDependency(ReflectionParameter $parameter, array $primitives = [])
    {
        $name = $parameter->getName();

        // Check if primitive value was provided
        if (array_key_exists($name, $primitives)) {
            return $primitives[$name];
        }

        // Get parameter type
        $type = $parameter->getType();

        if ($type === null) {
            // No type hint, check if default value exists
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new Exception("Cannot resolve parameter '{$name}' without type hint or default value");
        }

        // Handle union types (PHP 8.0+)
        if ($type instanceof \ReflectionUnionType) {
            // Try to resolve the first non-builtin type in the union
            foreach ($type->getTypes() as $unionType) {
                if (!$unionType->isBuiltin()) {
                    try {
                        return $this->resolve($unionType->getName());
                    } catch (Exception $e) {
                        // Continue to next type
                        continue;
                    }
                }
            }
            
            // If no class type could be resolved, check for default value
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            
            throw new Exception("Cannot resolve union type for parameter '{$name}'");
        }

        // Handle intersection types (PHP 8.1+)
        if ($type instanceof \ReflectionIntersectionType) {
            throw new Exception("Intersection types are not supported for dependency injection");
        }

        $typeName = $type->getName();

        // Handle built-in types
        if ($type->isBuiltin()) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new Exception("Cannot resolve built-in type '{$typeName}' for parameter '{$name}'");
        }

        // Resolve class dependency
        try {
            return $this->resolve($typeName);
        } catch (Exception $e) {
            // Check if parameter has default value
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            // Check if parameter is nullable
            if ($parameter->allowsNull()) {
                return null;
            }

            throw new Exception("Cannot resolve dependency '{$typeName}' for parameter '{$name}': {$e->getMessage()}");
        }
    }

    /**
     * Check if a service is marked as singleton
     *
     * @param string $abstract The service identifier
     * @return bool True if singleton, false otherwise
     */
    protected function isSingleton(string $abstract): bool
    {
        return isset($this->singletons[$abstract]) && $this->singletons[$abstract];
    }

    /**
     * Check if a service is bound
     *
     * @param string $abstract The service identifier
     * @return bool True if bound, false otherwise
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]) || isset($this->factories[$abstract]);
    }

    /**
     * Alias for resolve method
     *
     * @param string $abstract The service identifier
     * @param array<string, mixed> $parameters Constructor parameters
     * @return mixed The resolved service
     */
    public function make(string $abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Get a service if it exists, otherwise return null
     *
     * @param string $abstract The service identifier
     * @return mixed|null The service instance or null
     */
    public function get(string $abstract)
    {
        try {
            return $this->resolve($abstract);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Forget a service binding
     *
     * @param string $abstract The service identifier
     * @return void
     */
    public function forget(string $abstract): void
    {
        unset($this->bindings[$abstract], $this->instances[$abstract], $this->singletons[$abstract], $this->factories[$abstract]);
    }

    /**
     * Clear all bindings and instances
     *
     * @return void
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->singletons = [];
        $this->factories = [];
    }

    /**
     * Get all registered services
     *
     * @return array<string> Array of service identifiers
     */
    public function getServices(): array
    {
        return array_unique(array_merge(
            array_keys($this->bindings),
            array_keys($this->instances),
            array_keys($this->factories)
        ));
    }
}
