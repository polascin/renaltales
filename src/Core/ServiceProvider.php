<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Exception;
use RenalTales\Core\Database;
use RenalTales\Core\SessionManager;
use RenalTales\Models\LanguageModel;

/**
 * Service Provider for Dependency Injection
 * 
 * Manages the creation and configuration of application services
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v2.0
 */
class ServiceProvider
{
    private array $config;
    private array $services = [];
    private array $singletons = [];
    private array $instances = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->registerServices();
    }

    /**
     * Register all application services
     */
    private function registerServices(): void
    {
        // Register Database as singleton
        $this->singleton('database', function () {
            return new Database($this->config['database'] ?? []);
        });

        // Register LanguageModel
        $this->bind('language_model', function () {
            return new LanguageModel();
        });

        // Register SessionManager
        $this->bind('session_manager', function () {
            $languageModel = $this->get('language_model');
            $texts = $languageModel ? $languageModel->getAllTexts() : [];
            $debugMode = $this->config['app']['debug'] ?? false;
            
            return new SessionManager($texts, $debugMode);
        });
    }

    /**
     * Bind a service to the container
     */
    public function bind(string $name, callable $factory): void
    {
        $this->services[$name] = $factory;
    }

    /**
     * Bind a singleton service to the container
     */
    public function singleton(string $name, callable $factory): void
    {
        $this->services[$name] = $factory;
        $this->singletons[] = $name;
    }

    /**
     * Get a service from the container
     */
    public function get(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new Exception("Service '{$name}' not found in container");
        }

        // If it's a singleton and already created, return existing instance
        if (in_array($name, $this->singletons) && isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Create new instance
        $service = $this->services[$name]();

        // Store singleton instances
        if (in_array($name, $this->singletons)) {
            $this->instances[$name] = $service;
        }

        return $service;
    }

    /**
     * Check if a service exists
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * Get all registered service names
     */
    public function getServices(): array
    {
        return array_keys($this->services);
    }
}
