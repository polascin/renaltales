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
    private bool $sessionInitialized = false;
    private array $sessionConfig = [];

    /**
     * Constructor
     */
    public function __construct(array $translations = [], bool $debugMode = false)
    {
        $this->translations = $translations;
        $this->debugMode = $debugMode;
        
        // Initialize session configuration before any potential output
        $this->configureSessionSettings();
        
        // Initialize session with proper error handling
        if (!$this->initializeSession()) {
            throw new \RuntimeException('Failed to initialize session');
        }
        
        $this->initializeCSRF();
    }

    /**
     * Configure session settings before starting session
     */
    private function configureSessionSettings(): void
    {
        // Skip session configuration in CLI mode
        if (PHP_SAPI === 'cli') {
            return;
        }
        
        // Check if headers are already sent
        if (headers_sent($file, $line)) {
            throw new \RuntimeException("Headers already sent in {$file} on line {$line}. Cannot configure session settings.");
        }

        // Store default session configuration
        $this->sessionConfig = [
            'cookie_httponly' => '1',
            'use_only_cookies' => '1',
            'cookie_secure' => $this->isHttps() ? '1' : '0',
            'cookie_samesite' => 'Strict',
            'cookie_lifetime' => '0', // Session cookie expires when browser closes
            'gc_maxlifetime' => '3600', // 1 hour
            'gc_probability' => '1',
            'gc_divisor' => '100',
            'name' => 'RENALTALES_SESSION',
            'entropy_length' => '32',
            'hash_function' => 'sha256',
            'use_strict_mode' => '1',
            'use_trans_sid' => '0'
        ];

        // Apply session configuration
        foreach ($this->sessionConfig as $key => $value) {
            if (ini_set("session.{$key}", $value) === false) {
                if ($this->debugMode) {
                    error_log("Failed to set session.{$key} to {$value}");
                }
            }
        }
    }

    /**
     * Initialize session with proper error handling
     */
    private function initializeSession(): bool
    {
        try {
            // Skip session initialization in CLI mode
            if (PHP_SAPI === 'cli') {
                $this->sessionInitialized = true;
                return true;
            }
            
            // Check if session is already active
            if (session_status() === PHP_SESSION_ACTIVE) {
                $this->sessionInitialized = true;
                return true;
            }

            // Check if headers are already sent
            if (headers_sent($file, $line)) {
                throw new \RuntimeException("Headers already sent in {$file} on line {$line}. Cannot start session.");
            }

            // Start session
            if (!session_start()) {
                throw new \RuntimeException('Failed to start session');
            }

            // Validate session state
            if (session_status() !== PHP_SESSION_ACTIVE) {
                throw new \RuntimeException('Session is not active after session_start()');
            }

            $this->sessionInitialized = true;

            // Initialize session security measures
            $this->initializeSessionSecurity();

            return true;

        } catch (\Exception $e) {
            if ($this->debugMode) {
                error_log('Session initialization failed: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Initialize session security measures
     */
    private function initializeSessionSecurity(): void
    {
        // Set session start time if not exists
        if (!isset($_SESSION['session_start_time'])) {
            $_SESSION['session_start_time'] = time();
        }

        // Check for session timeout (4 hours)
        if (isset($_SESSION['session_start_time']) && (time() - $_SESSION['session_start_time']) > 14400) {
            $this->destroy();
            return;
        }

        // Set/update last activity time
        $_SESSION['last_activity'] = time();

        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            $this->regenerateSessionId();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
            $this->regenerateSessionId();
        }

        // Set fingerprint for session hijacking protection
        $fingerprint = $this->generateFingerprint();
        if (!isset($_SESSION['fingerprint'])) {
            $_SESSION['fingerprint'] = $fingerprint;
        } elseif ($_SESSION['fingerprint'] !== $fingerprint) {
            // Potential session hijacking detected
            $this->destroy();
            throw new \RuntimeException('Session hijacking detected');
        }
    }

    /**
     * Generate session fingerprint
     */
    private function generateFingerprint(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $acceptEnc = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        
        return hash('sha256', $userAgent . $acceptLang . $acceptEnc);
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
     * Check if session is properly initialized
     */
    public function isSessionInitialized(): bool
    {
        return $this->sessionInitialized && session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Validate session before operations
     */
    private function validateSession(): bool
    {
        if (!$this->isSessionInitialized()) {
            return false;
        }

        // Check for session timeout based on last activity
        if (isset($_SESSION['last_activity'])) {
            $inactivityTimeout = 3600; // 1 hour
            if ((time() - $_SESSION['last_activity']) > $inactivityTimeout) {
                $this->destroy();
                return false;
            }
        }

        // Update last activity
        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Set session value with validation
     */
    public function set(string $key, $value): void
    {
        if (!$this->validateSession()) {
            throw new \RuntimeException('Session is not valid or has expired');
        }

        // Prevent setting reserved session keys
        $reservedKeys = ['session_start_time', 'last_activity', 'last_regeneration', 'fingerprint', '_csrf_token'];
        if (in_array($key, $reservedKeys)) {
            throw new \InvalidArgumentException("Cannot set reserved session key: {$key}");
        }

        $_SESSION[$key] = $value;
    }

    /**
     * Get session value with validation
     */
    public function get(string $key, $default = null)
    {
        if (!$this->validateSession()) {
            return $default;
        }
        
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists with validation
     */
    public function has(string $key): bool
    {
        if (!$this->validateSession()) {
            return false;
        }
        
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value with validation
     */
    public function remove(string $key): void
    {
        if (!$this->validateSession()) {
            throw new \RuntimeException('Session is not valid or has expired');
        }
        
        // Prevent removing reserved session keys
        $reservedKeys = ['session_start_time', 'last_activity', 'last_regeneration', 'fingerprint', '_csrf_token'];
        if (in_array($key, $reservedKeys)) {
            throw new \InvalidArgumentException("Cannot remove reserved session key: {$key}");
        }
        
        unset($_SESSION[$key]);
    }

    /**
     * Destroy session securely
     */
    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Clear session data
            $_SESSION = [];
            
            // Clear session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 3600,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']
                );
            }
            
            // Destroy session
            session_destroy();
            $this->sessionInitialized = false;
        }
    }
    
    /**
     * Clear expired sessions (manual cleanup)
     */
    public function clearExpiredSessions(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_gc();
        }
    }
    
    /**
     * Get session configuration
     */
    public function getSessionConfig(): array
    {
        return $this->sessionConfig;
    }
    
    /**
     * Get session ID
     */
    public function getSessionId(): string
    {
        return session_id();
    }
    
    /**
     * Get session name
     */
    public function getSessionName(): string
    {
        return session_name();
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
