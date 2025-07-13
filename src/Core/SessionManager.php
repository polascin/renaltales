<?php

declare(strict_types=1);

namespace RenalTales\Core;

/**
 * Session Manager
 *
 * Handles session management and security
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class SessionManager
{
    private array $translations;
    private bool $debugMode;
    private ?SecurityManager $securityManager = null;
    private string $csrfToken = '';

    /**
     * Constructor
     */
    public function __construct(array $translations = [], bool $debugMode = false)
    {
        $this->translations = $translations;
        $this->debugMode = $debugMode;
        $this->initializeSession();
        $this->initializeCSRF();
    }

    /**
     * Initialize session
     */
    private function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session configuration
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_secure', $this->isHttps() ? '1' : '0');
            ini_set('session.cookie_samesite', 'Strict');

            session_start();
        }

        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            $this->regenerateSessionId();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
            $this->regenerateSessionId();
        }
    }

    /**
     * Regenerate session ID
     */
    public function regenerateSessionId(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    /**
     * Initialize CSRF protection
     */
    private function initializeCSRF(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $tokenName = '_csrf_token';
        if (!isset($_SESSION[$tokenName]) || $this->isCSRFTokenExpired()) {
            try {
                $token = bin2hex(random_bytes(32));
            } catch (\Exception $e) {
                $token = hash('sha256', uniqid(mt_rand(), true));
            }

            $_SESSION[$tokenName] = [
                'token' => $token,
                'time' => time()
            ];
        }

        $this->csrfToken = $_SESSION[$tokenName]['token'];
    }

    /**
     * Check if CSRF token is expired
     */
    private function isCSRFTokenExpired(): bool
    {
        $tokenName = '_csrf_token';
        if (!isset($_SESSION[$tokenName]['time'])) {
            return true;
        }

        return (time() - $_SESSION[$tokenName]['time']) > 3600; // 1 hour
    }

    /**
     * Get CSRF token
     */
    public function getCSRFToken(): string
    {
        return $this->csrfToken;
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRFToken(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $tokenName = '_csrf_token';
        if (!isset($_SESSION[$tokenName]['token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION[$tokenName]['token'], $token);
    }

    /**
     * Set session value
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destroy session
     */
    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            session_start();
        }
    }

    /**
     * Check if HTTPS is enabled
     */
    private function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * Get debug mode status
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * Get translations
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
