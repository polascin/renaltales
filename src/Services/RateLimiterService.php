<?php

declare(strict_types=1);

namespace RenalTales\Services;

use Exception;

/**
 * Rate Limiter Service
 *
 * Provides rate limiting functionality to protect against brute force attacks
 * and other abuse scenarios.
 *
 * @package RenalTales\Services
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class RateLimiterService
{
    private array $config;
    private string $storagePath;

    /**
     * Constructor
     *
     * @param array $config Rate limiting configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->storagePath = $config['storage_path'] ?? APP_ROOT . '/storage/rate_limits';

        // Create storage directory if it doesn't exist
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Check if a request is allowed based on rate limiting rules
     *
     * @param string $key Unique identifier for the rate limit (e.g., IP address, user ID)
     * @param string $type Type of rate limit (login, api, password_reset, etc.)
     * @return bool True if request is allowed, false if rate limited
     */
    public function isAllowed(string $key, string $type): bool
    {
        if (!$this->config['enabled'] ?? true) {
            return true;
        }

        $limit = $this->config['limits'][$type] ?? null;
        if (!$limit) {
            return true;
        }

        $identifier = $this->generateIdentifier($key, $type);
        $attempts = $this->getAttempts($identifier);
        $now = time();

        // Clean old attempts
        $attempts = array_filter($attempts, function ($timestamp) use ($now, $limit) {
            return ($now - $timestamp) < $limit['window'];
        });

        // Check if we're in lockout period
        if ($this->isInLockout($identifier, $type)) {
            return false;
        }

        // Check if we've exceeded the rate limit
        if (count($attempts) >= $limit['requests']) {
            $this->setLockout($identifier, $type);
            return false;
        }

        return true;
    }

    /**
     * Record an attempt for rate limiting
     *
     * @param string $key Unique identifier for the rate limit
     * @param string $type Type of rate limit
     * @return void
     */
    public function recordAttempt(string $key, string $type): void
    {
        if (!$this->config['enabled'] ?? true) {
            return;
        }

        $identifier = $this->generateIdentifier($key, $type);
        $attempts = $this->getAttempts($identifier);
        $attempts[] = time();

        $this->saveAttempts($identifier, $attempts);
    }

    /**
     * Reset attempts for a specific key and type
     *
     * @param string $key Unique identifier for the rate limit
     * @param string $type Type of rate limit
     * @return void
     */
    public function resetAttempts(string $key, string $type): void
    {
        $identifier = $this->generateIdentifier($key, $type);
        $this->removeAttempts($identifier);
        $this->removeLockout($identifier);
    }

    /**
     * Get remaining attempts for a specific key and type
     *
     * @param string $key Unique identifier for the rate limit
     * @param string $type Type of rate limit
     * @return int Number of remaining attempts
     */
    public function getRemainingAttempts(string $key, string $type): int
    {
        if (!$this->config['enabled'] ?? true) {
            return PHP_INT_MAX;
        }

        $limit = $this->config['limits'][$type] ?? null;
        if (!$limit) {
            return PHP_INT_MAX;
        }

        $identifier = $this->generateIdentifier($key, $type);
        $attempts = $this->getAttempts($identifier);
        $now = time();

        // Clean old attempts
        $attempts = array_filter($attempts, function ($timestamp) use ($now, $limit) {
            return ($now - $timestamp) < $limit['window'];
        });

        return max(0, $limit['requests'] - count($attempts));
    }

    /**
     * Get time until reset for a specific key and type
     *
     * @param string $key Unique identifier for the rate limit
     * @param string $type Type of rate limit
     * @return int Time in seconds until reset
     */
    public function getTimeUntilReset(string $key, string $type): int
    {
        if (!$this->config['enabled'] ?? true) {
            return 0;
        }

        $limit = $this->config['limits'][$type] ?? null;
        if (!$limit) {
            return 0;
        }

        $identifier = $this->generateIdentifier($key, $type);

        // Check if in lockout
        if ($this->isInLockout($identifier, $type)) {
            $lockoutTime = $this->getLockoutTime($identifier);
            if ($lockoutTime) {
                return max(0, $lockoutTime + $limit['lockout_duration'] - time());
            }
        }

        $attempts = $this->getAttempts($identifier);
        if (empty($attempts)) {
            return 0;
        }

        $oldestAttempt = min($attempts);
        return max(0, $oldestAttempt + $limit['window'] - time());
    }

    /**
     * Generate a unique identifier for rate limiting
     *
     * @param string $key
     * @param string $type
     * @return string
     */
    private function generateIdentifier(string $key, string $type): string
    {
        return hash('sha256', $key . ':' . $type);
    }

    /**
     * Get attempts from storage
     *
     * @param string $identifier
     * @return array
     */
    private function getAttempts(string $identifier): array
    {
        $file = $this->storagePath . '/' . $identifier . '.attempts';
        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return [];
        }

        $attempts = json_decode($content, true);
        return is_array($attempts) ? $attempts : [];
    }

    /**
     * Save attempts to storage
     *
     * @param string $identifier
     * @param array $attempts
     * @return void
     */
    private function saveAttempts(string $identifier, array $attempts): void
    {
        $file = $this->storagePath . '/' . $identifier . '.attempts';
        file_put_contents($file, json_encode($attempts), LOCK_EX);
    }

    /**
     * Remove attempts from storage
     *
     * @param string $identifier
     * @return void
     */
    private function removeAttempts(string $identifier): void
    {
        $file = $this->storagePath . '/' . $identifier . '.attempts';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Check if identifier is in lockout period
     *
     * @param string $identifier
     * @param string $type
     * @return bool
     */
    private function isInLockout(string $identifier, string $type): bool
    {
        $limit = $this->config['limits'][$type] ?? null;
        if (!$limit) {
            return false;
        }

        $lockoutTime = $this->getLockoutTime($identifier);
        if (!$lockoutTime) {
            return false;
        }

        return (time() - $lockoutTime) < $limit['lockout_duration'];
    }

    /**
     * Set lockout for identifier
     *
     * @param string $identifier
     * @param string $type
     * @return void
     */
    private function setLockout(string $identifier, string $type): void
    {
        $file = $this->storagePath . '/' . $identifier . '.lockout';
        file_put_contents($file, time(), LOCK_EX);

        // Log the lockout
        $this->logRateLimitViolation($identifier, $type);
    }

    /**
     * Get lockout time for identifier
     *
     * @param string $identifier
     * @return int|null
     */
    private function getLockoutTime(string $identifier): ?int
    {
        $file = $this->storagePath . '/' . $identifier . '.lockout';
        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        return $content !== false ? (int) $content : null;
    }

    /**
     * Remove lockout for identifier
     *
     * @param string $identifier
     * @return void
     */
    private function removeLockout(string $identifier): void
    {
        $file = $this->storagePath . '/' . $identifier . '.lockout';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Log rate limit violation
     *
     * @param string $identifier
     * @param string $type
     * @return void
     */
    private function logRateLimitViolation(string $identifier, string $type): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => 'rate_limit_violation',
            'identifier' => $identifier,
            'type' => $type,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'request_uri' => substr($_SERVER['REQUEST_URI'] ?? '', 0, 255),
        ];

        $logDir = APP_ROOT . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/rate_limits.log';
        $logData = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";

        file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);
    }

    /**
     * Cleanup old rate limit files
     *
     * @return void
     */
    public function cleanup(): void
    {
        if (!is_dir($this->storagePath)) {
            return;
        }

        $files = glob($this->storagePath . '/*');
        $now = time();
        $maxAge = 86400; // 24 hours

        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $maxAge) {
                unlink($file);
            }
        }
    }
}
