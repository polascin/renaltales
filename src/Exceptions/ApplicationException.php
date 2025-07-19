<?php

declare(strict_types=1);

namespace RenalTales\Exceptions;

use Exception;
use Throwable;

/**
 * Application Exception
 *
 * Base exception class for all application-specific exceptions.
 * Provides enhanced error handling with context information and logging support.
 *
 * @package RenalTales\Exceptions
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class ApplicationException extends Exception
{
    protected array $context = [];
    protected ?string $userMessage = null;
    protected int $severity = 0;

    /**
     * Create a new application exception instance
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Throwable|null $previous The previous exception
     * @param array $context Additional context information
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the context information
     *
     * @return array Context information
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set context information
     *
     * @param array $context Context information
     * @return self
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Add context information
     *
     * @param string $key Context key
     * @param mixed $value Context value
     * @return self
     */
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Get user-friendly message
     *
     * @return string|null User-friendly message or null if not set
     */
    public function getUserMessage(): ?string
    {
        return $this->userMessage;
    }

    /**
     * Set user-friendly message
     *
     * @param string $message User-friendly message
     * @return self
     */
    public function setUserMessage(string $message): self
    {
        $this->userMessage = $message;
        return $this;
    }

    /**
     * Get exception severity
     *
     * @return int Severity level (0 = low, 1 = medium, 2 = high, 3 = critical)
     */
    public function getSeverity(): int
    {
        return $this->severity;
    }

    /**
     * Set exception severity
     *
     * @param int $severity Severity level (0 = low, 1 = medium, 2 = high, 3 = critical)
     * @return self
     */
    public function setSeverity(int $severity): self
    {
        $this->severity = max(0, min(3, $severity));
        return $this;
    }

    /**
     * Get severity level as string
     *
     * @return string Severity level string
     */
    public function getSeverityString(): string
    {
        return match ($this->severity) {
            0 => 'low',
            1 => 'medium',
            2 => 'high',
            3 => 'critical',
            default => 'unknown'
        };
    }

    /**
     * Check if exception is critical
     *
     * @return bool True if critical, false otherwise
     */
    public function isCritical(): bool
    {
        return $this->severity === 3;
    }

    /**
     * Convert exception to array for logging
     *
     * @return array Exception data as array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
            'context' => $this->getContext(),
            'user_message' => $this->getUserMessage(),
            'severity' => $this->getSeverityString(),
            'previous' => $this->getPrevious() ? $this->getPrevious()->getMessage() : null,
        ];
    }

    /**
     * Convert exception to JSON string
     *
     * @return string JSON representation of exception
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
