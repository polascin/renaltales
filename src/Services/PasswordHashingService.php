<?php

declare(strict_types=1);

namespace RenalTales\Services;

use Exception;

/**
 * Password Hashing Service
 *
 * Provides secure password hashing using Argon2 algorithm with fallback
 * to bcrypt for legacy support.
 *
 * @package RenalTales\Services
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class PasswordHashingService
{
    private array $config;

    /**
     * Constructor
     *
     * @param array $config Password hashing configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'algorithm' => PASSWORD_ARGON2ID,
            'options' => [
                'memory_cost' => 65536, // 64 MB
                'time_cost' => 4,       // 4 iterations
                'threads' => 3,         // 3 parallel threads
            ],
            'legacy_support' => true,
            'min_length' => 8,
            'max_length' => 128,
            'require_special_chars' => true,
            'require_numbers' => true,
            'require_uppercase' => true,
            'require_lowercase' => true,
        ], $config);
    }

    /**
     * Hash a password using Argon2
     *
     * @param string $password The password to hash
     * @return string The hashed password
     * @throws Exception If hashing fails
     */
    public function hashPassword(string $password): string
    {
        // Validate password requirements
        if (!$this->validatePassword($password)) {
            throw new Exception('Password does not meet security requirements');
        }

        $hash = password_hash($password, $this->config['algorithm'], $this->config['options']);

        if ($hash === false) {
            throw new Exception('Password hashing failed');
        }

        return $hash;
    }

    /**
     * Verify a password against a hash
     *
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     * @return bool True if password matches hash, false otherwise
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if a password hash needs to be rehashed
     *
     * @param string $hash The hash to check
     * @return bool True if rehashing is needed, false otherwise
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, $this->config['algorithm'], $this->config['options']);
    }

    /**
     * Validate password against security requirements
     *
     * @param string $password The password to validate
     * @return bool True if password meets requirements, false otherwise
     */
    public function validatePassword(string $password): bool
    {
        $length = strlen($password);

        // Check length requirements
        if ($length < $this->config['min_length'] || $length > $this->config['max_length']) {
            return false;
        }

        // Check for required character types
        if ($this->config['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            return false;
        }

        if ($this->config['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if ($this->config['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            return false;
        }

        if ($this->config['require_special_chars'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Get password requirements for display to users
     *
     * @return array Array of password requirements
     */
    public function getPasswordRequirements(): array
    {
        $requirements = [];

        $requirements[] = "Must be between {$this->config['min_length']} and {$this->config['max_length']} characters";

        if ($this->config['require_lowercase']) {
            $requirements[] = "Must contain at least one lowercase letter";
        }

        if ($this->config['require_uppercase']) {
            $requirements[] = "Must contain at least one uppercase letter";
        }

        if ($this->config['require_numbers']) {
            $requirements[] = "Must contain at least one number";
        }

        if ($this->config['require_special_chars']) {
            $requirements[] = "Must contain at least one special character";
        }

        return $requirements;
    }

    /**
     * Generate a secure random password
     *
     * @param int $length The length of the password to generate
     * @return string The generated password
     * @throws Exception If password generation fails
     */
    public function generatePassword(int $length = 12): string
    {
        if ($length < $this->config['min_length']) {
            $length = $this->config['min_length'];
        }

        if ($length > $this->config['max_length']) {
            $length = $this->config['max_length'];
        }

        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $chars = '';
        $password = '';

        // Ensure at least one character from each required set
        if ($this->config['require_lowercase']) {
            $chars .= $lowercase;
            $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        }

        if ($this->config['require_uppercase']) {
            $chars .= $uppercase;
            $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        }

        if ($this->config['require_numbers']) {
            $chars .= $numbers;
            $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        }

        if ($this->config['require_special_chars']) {
            $chars .= $special;
            $password .= $special[random_int(0, strlen($special) - 1)];
        }

        // Fill the rest of the password length
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        // Shuffle the password to randomize character positions
        $password = str_shuffle($password);

        return $password;
    }

    /**
     * Calculate password strength score
     *
     * @param string $password The password to evaluate
     * @return int Password strength score (0-100)
     */
    public function calculatePasswordStrength(string $password): int
    {
        $score = 0;
        $length = strlen($password);

        // Length scoring
        if ($length >= 8) {
            $score += 20;
        }
        if ($length >= 12) {
            $score += 10;
        }
        if ($length >= 16) {
            $score += 10;
        }

        // Character variety scoring
        if (preg_match('/[a-z]/', $password)) {
            $score += 15;
        }
        if (preg_match('/[A-Z]/', $password)) {
            $score += 15;
        }
        if (preg_match('/[0-9]/', $password)) {
            $score += 15;
        }
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 15;
        }

        // Bonus for good practices
        if (preg_match('/[a-z].*[A-Z]|[A-Z].*[a-z]/', $password)) {
            $score += 5;
        }
        if (preg_match('/[0-9].*[^A-Za-z0-9]|[^A-Za-z0-9].*[0-9]/', $password)) {
            $score += 5;
        }

        // Penalties for common patterns
        if (preg_match('/(.)\1{2,}/', $password)) {
            $score -= 10;
        } // Repeated characters
        if (preg_match('/123|abc|qwe|password|admin/i', $password)) {
            $score -= 20;
        } // Common patterns

        return max(0, min(100, $score));
    }

    /**
     * Get password strength description
     *
     * @param int $score The password strength score
     * @return string Description of password strength
     */
    public function getPasswordStrengthDescription(int $score): string
    {
        if ($score >= 80) {
            return 'Very Strong';
        }
        if ($score >= 60) {
            return 'Strong';
        }
        if ($score >= 40) {
            return 'Medium';
        }
        if ($score >= 20) {
            return 'Weak';
        }
        return 'Very Weak';
    }

    /**
     * Migrate legacy bcrypt password to Argon2
     *
     * @param string $password The plain text password
     * @param string $oldHash The old bcrypt hash
     * @return string|null New Argon2 hash if migration successful, null otherwise
     */
    public function migrateLegacyPassword(string $password, string $oldHash): ?string
    {
        if (!$this->config['legacy_support']) {
            return null;
        }

        // Verify the old password first
        if (!password_verify($password, $oldHash)) {
            return null;
        }

        // Check if it's actually a bcrypt hash
        if (!$this->isBcryptHash($oldHash)) {
            return null;
        }

        try {
            return $this->hashPassword($password);
        } catch (Exception $e) {
            error_log('Password migration failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a hash is a bcrypt hash
     *
     * @param string $hash The hash to check
     * @return bool True if it's a bcrypt hash, false otherwise
     */
    private function isBcryptHash(string $hash): bool
    {
        return strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0 || strpos($hash, '$2b$') === 0;
    }

    /**
     * Get algorithm information
     *
     * @return array Algorithm information
     */
    public function getAlgorithmInfo(): array
    {
        return [
            'algorithm' => $this->config['algorithm'],
            'algorithm_name' => $this->getAlgorithmName($this->config['algorithm']),
            'options' => $this->config['options'],
            'legacy_support' => $this->config['legacy_support'],
        ];
    }

    /**
     * Get algorithm name from constant
     *
     * @param mixed $algorithm The algorithm constant
     * @return string Algorithm name
     */
    private function getAlgorithmName($algorithm): string
    {
        switch ($algorithm) {
            case PASSWORD_ARGON2I:
                return 'Argon2i';
            case PASSWORD_ARGON2ID:
                return 'Argon2id';
            case PASSWORD_BCRYPT:
                return 'bcrypt';
            default:
                return 'Unknown';
        }
    }

    /**
     * Benchmark password hashing performance
     *
     * @return array Performance benchmarks
     */
    public function benchmarkPerformance(): array
    {
        $testPassword = 'TestPassword123!';
        $iterations = 5;

        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->hashPassword($testPassword);
        }
        $endTime = microtime(true);

        $averageTime = ($endTime - $startTime) / $iterations;

        return [
            'iterations' => $iterations,
            'total_time' => $endTime - $startTime,
            'average_time' => $averageTime,
            'hashes_per_second' => 1 / $averageTime,
            'algorithm' => $this->getAlgorithmName($this->config['algorithm']),
            'options' => $this->config['options'],
        ];
    }
}
