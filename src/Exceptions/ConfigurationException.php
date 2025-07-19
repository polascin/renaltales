<?php

declare(strict_types=1);

namespace RenalTales\Exceptions;

use Exception;

/**
 * Configuration Exception
 *
 * Thrown when configuration-related errors occur, such as missing
 * configuration files, invalid configuration values, or validation failures.
 *
 * @package RenalTales\Exceptions
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class ConfigurationException extends Exception
{
    /**
     * Create a new configuration exception instance
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for missing configuration file
     *
     * @param string $configFile The missing configuration file
     * @return static
     */
    public static function missingConfigFile(string $configFile): static
    {
        return new static("Configuration file '{$configFile}' not found or not readable.");
    }

    /**
     * Create exception for invalid configuration value
     *
     * @param string $key The configuration key
     * @param mixed $value The invalid value
     * @param string $expectedType The expected type
     * @return static
     */
    public static function invalidValue(string $key, mixed $value, string $expectedType): static
    {
        $actualType = gettype($value);
        return new static("Configuration key '{$key}' expected {$expectedType}, got {$actualType}.");
    }

    /**
     * Create exception for missing required configuration
     *
     * @param string $key The missing configuration key
     * @return static
     */
    public static function missingRequired(string $key): static
    {
        return new static("Required configuration key '{$key}' is missing.");
    }

    /**
     * Create exception for invalid environment
     *
     * @param string $environment The invalid environment
     * @param array $validEnvironments List of valid environments
     * @return static
     */
    public static function invalidEnvironment(string $environment, array $validEnvironments): static
    {
        $valid = implode(', ', $validEnvironments);
        return new static("Invalid environment '{$environment}'. Valid environments: {$valid}.");
    }
}
