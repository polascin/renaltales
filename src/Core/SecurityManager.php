<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Exception;
use RenalTales\Services\RateLimiterService;
use RenalTales\Services\PasswordHashingService;

/**
 * SecurityManager - Comprehensive security management
 *
 * Handles CSRF protection, XSS prevention, security headers, rate limiting,
 * and other security measures
 *
 * @author Ľubomír Polaščín
 * @version 2025.v3.1
 */

class SecurityManager
{
    private ?string $csrfToken = null;
    private string $csrfTokenName = '_csrf_token';
    private ?SessionManager $sessionManager = null;
    private array $config = [];
    private ?RateLimiterService $rateLimiter = null;
    private ?PasswordHashingService $passwordHasher = null;

    /**
     * Constructor
     *
     * @param SessionManager|null $sessionManager
     * @param array<string, mixed> $config Security configuration
     */
    public function __construct(?SessionManager $sessionManager = null, array $config = [])
    {
        $this->sessionManager = $sessionManager;
        $this->config = array_merge([
            'csrf_expire_time' => 3600, // 1 hour
            'csrf_regenerate_on_use' => true,
            'xss_protection' => true,
            'content_security_policy' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline' https://cdn.jsdelivr.net " .
                    "https://cdnjs.cloudflare.com https://cdn.tiny.cloud",
                'style-src' => "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
                'img-src' => "'self' data: https:",
                'font-src' => "'self' https://cdnjs.cloudflare.com",
                'connect-src' => "'self'",
                'media-src' => "'self'",
                'object-src' => "'none'",
                'child-src' => "'none'",
                'worker-src' => "'none'",
                'frame-ancestors' => "'none'",
                'form-action' => "'self'",
                'base-uri' => "'self'",
                'manifest-src' => "'self'"
            ],
            'security_headers' => [
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block',
                'Referrer-Policy' => 'strict-origin-when-cross-origin',
                'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
                'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
            ]
        ], $config);

        $this->initializeSecurity();
    }

    /**
     * Initialize security measures
     */
    private function initializeSecurity(): void
    {
        // Set security headers
        $this->setSecurityHeaders();

        // Initialize CSRF protection
        $this->initializeCSRF();

        // Enable XSS protection
        if ($this->config['xss_protection']) {
            $this->enableXSSProtection();
        }
    }

    /**
     * Set security headers
     */
    public function setSecurityHeaders(): void
    {
        // Don't set headers if they've already been sent
        if (headers_sent()) {
            return;
        }

        // Set Content Security Policy
        $csp = $this->buildCSPHeader();
        header("Content-Security-Policy: $csp");

        // Set other security headers
        foreach ($this->config['security_headers'] as $header => $value) {
            // Only set HSTS header if HTTPS is enabled
            if ($header === 'Strict-Transport-Security' && !$this->isHttps()) {
                continue;
            }
            header("$header: $value");
        }

        // Set cache control headers for sensitive pages
        if ($this->isSensitivePage()) {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
    }

    /**
     * Build Content Security Policy header
     *
     * @return string
     */
    private function buildCSPHeader(): string
    {
        $csp = [];
        foreach ($this->config['content_security_policy'] as $directive => $value) {
            $csp[] = "$directive $value";
        }
        return implode('; ', $csp);
    }

    /**
     * Check if connection is HTTPS
     *
     * @return bool
     */
    private function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * Check if current page is sensitive (requires no caching)
     *
     * @return bool
     */
    private function isSensitivePage(): bool
    {
        $sensitivePaths = ['/admin', '/dashboard', '/profile', '/settings', '/login'];
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';

        foreach ($sensitivePaths as $path) {
            if (strpos($currentPath, $path) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Initialize CSRF protection
     */
    private function initializeCSRF(): void
    {
        if ($this->sessionManager) {
            $this->csrfToken = $this->sessionManager->getCSRFToken();
        } else {
            $this->generateCSRFToken();
        }
    }

    /**
     * Generate CSRF token
     */
    private function generateCSRFToken(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION[$this->csrfTokenName]) || $this->isCSRFTokenExpired()) {
            try {
                $token = bin2hex(random_bytes(32));
            } catch (Exception $e) {
                error_log('Exception in SecurityManager.php: ' . $e->getMessage());
                $token = hash('sha256', uniqid(mt_rand(), true));
            }

            $_SESSION[$this->csrfTokenName] = [
                'token' => $token,
                'time' => time()
            ];
        }

        $this->csrfToken = $_SESSION[$this->csrfTokenName]['token'];
    }

    /**
     * Check if CSRF token is expired
     *
     * @return bool
     */
    private function isCSRFTokenExpired(): bool
    {
        if (!isset($_SESSION[$this->csrfTokenName]['time'])) {
            return true;
        }

        return (time() - $_SESSION[$this->csrfTokenName]['time']) > $this->config['csrf_expire_time'];
    }

    /**
     * Get CSRF token
     *
     * @return string
     */
    public function getCSRFToken(): string
    {
        if ($this->csrfToken === null) {
            $this->initializeCSRF();
        }
        return $this->csrfToken ?? '';
    }

    /**
     * Generate CSRF token input field
     *
     * @return string
     */
    public function getCSRFTokenField(): string
    {
        return '<input type="hidden" name="' . htmlspecialchars($this->csrfTokenName) .
            '" value="' . htmlspecialchars($this->getCSRFToken()) . '">';
    }

    /**
     * Validate CSRF token
     *
     * @param string $token Token to validate
     * @return bool
     */
    public function validateCSRFToken(string $token): bool
    {
        if ($this->sessionManager) {
            return $this->sessionManager->validateCSRFToken($token);
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        if (!isset($_SESSION[$this->csrfTokenName]['token']) || empty($token)) {
            return false;
        }

        $isValid = hash_equals($_SESSION[$this->csrfTokenName]['token'], $token);

        // Regenerate token on use if configured
        if ($isValid && $this->config['csrf_regenerate_on_use']) {
            $this->regenerateCSRFToken();
        }

        return $isValid;
    }

    /**
     * Regenerate CSRF token
     */
    public function regenerateCSRFToken(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION[$this->csrfTokenName]);
        }
        $this->generateCSRFToken();
    }

    /**
     * Enable XSS protection
     */
    private function enableXSSProtection(): void
    {
        // Set appropriate headers for XSS protection
        if (!headers_sent()) {
            header('X-XSS-Protection: 1; mode=block');
            header('X-Content-Type-Options: nosniff');
        }
    }

    /**
     * Sanitize input to prevent XSS
     *
     * @param mixed $input
     * @param bool $allowHTML
     * @return mixed
     */
    public function sanitizeInput(mixed $input, bool $allowHTML = false): mixed
    {
        if (is_string($input)) {
            if ($allowHTML) {
                return $this->sanitizeHTML($input);
            } else {
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        if (is_array($input)) {
            return array_map(function ($item) use ($allowHTML) {
                return $this->sanitizeInput($item, $allowHTML);
            }, $input);
        }

        return $input;
    }

    /**
     * Sanitize HTML content
     *
     * @param string $html
     * @return string
     */
    private function sanitizeHTML(string $html): string
    {
        // Define allowed tags and attributes
        $allowedTags = [
            'p', 'br', 'strong', 'em', 'u', 'ol', 'ul', 'li', 'a', 'img',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'code', 'pre'
        ];

        $allowedAttributes = [
            'a' => ['href', 'title'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'blockquote' => ['cite']
        ];

        // Remove dangerous tags and attributes
        $html = strip_tags($html, '<' . implode('><', $allowedTags) . '>');

        // Remove dangerous attributes
        $html = preg_replace('/(<[^>]+)\s+(on\w+|javascript:|vbscript:|data:)/i', '$1', $html);

        // Remove script and style tags completely
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);

        return $html;
    }

    /**
     * Validate input against common injection patterns
     *
     * @param string $input
     * @return bool
     */
    public function validateInput(string $input): bool
    {

        // Check for common injection patterns
        $dangerousPatterns = [
            '/(<script[^>]*>.*?<\/script>)/is',
            '/(<iframe[^>]*>.*?<\/iframe>)/is',
            '/(<object[^>]*>.*?<\/object>)/is',
            '/(<embed[^>]*>.*?<\/embed>)/is',
            '/(<form[^>]*>.*?<\/form>)/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onclick=/i',
            '/onerror=/i',
            '/onmouseover=/i',
            '/onfocus=/i',
            '/onblur=/i',
            '/onchange=/i',
            '/onsubmit=/i',
            '/document\.cookie/i',
            '/document\.write/i',
            '/eval\s*\(/i',
            '/expression\s*\(/i'
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Log security event
     *
     * @param string $event
     * @param array $context
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip_address' => $this->getClientIP(),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'request_uri' => substr($_SERVER['REQUEST_URI'] ?? '', 0, 255),
            'session_id' => session_id(),
            'context' => $context
        ];

        $logDir = APP_DIR . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/security_events.log';
        $logData = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";

        file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private function getClientIP(): string
    {
        $ipHeaders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Set rate limiter service
     *
     * @param RateLimiterService $rateLimiter
     * @return void
     */
    public function setRateLimiter(RateLimiterService $rateLimiter): void
    {
        $this->rateLimiter = $rateLimiter;
    }

    /**
     * Set password hasher service
     *
     * @param PasswordHashingService $passwordHasher
     * @return void
     */
    public function setPasswordHasher(PasswordHashingService $passwordHasher): void
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Check if request is rate limited
     *
     * @param string $type Type of rate limit (login, api, etc.)
     * @param string|null $identifier Custom identifier, uses IP if null
     * @return bool True if request is allowed, false if rate limited
     */
    public function isRateLimited(string $type, ?string $identifier = null): bool
    {
        if (!$this->rateLimiter) {
            return false;
        }

        $key = $identifier ?? $this->getClientIP();
        return !$this->rateLimiter->isAllowed($key, $type);
    }

    /**
     * Record rate limit attempt
     *
     * @param string $type Type of rate limit
     * @param string|null $identifier Custom identifier, uses IP if null
     * @return void
     */
    public function recordRateLimitAttempt(string $type, ?string $identifier = null): void
    {
        if (!$this->rateLimiter) {
            return;
        }

        $key = $identifier ?? $this->getClientIP();
        $this->rateLimiter->recordAttempt($key, $type);
    }

    /**
     * Reset rate limit attempts
     *
     * @param string $type Type of rate limit
     * @param string|null $identifier Custom identifier, uses IP if null
     * @return void
     */
    public function resetRateLimitAttempts(string $type, ?string $identifier = null): void
    {
        if (!$this->rateLimiter) {
            return;
        }

        $key = $identifier ?? $this->getClientIP();
        $this->rateLimiter->resetAttempts($key, $type);
    }

    /**
     * Get remaining rate limit attempts
     *
     * @param string $type Type of rate limit
     * @param string|null $identifier Custom identifier, uses IP if null
     * @return int Number of remaining attempts
     */
    public function getRemainingAttempts(string $type, ?string $identifier = null): int
    {
        if (!$this->rateLimiter) {
            return PHP_INT_MAX;
        }

        $key = $identifier ?? $this->getClientIP();
        return $this->rateLimiter->getRemainingAttempts($key, $type);
    }

    /**
     * Get time until rate limit reset
     *
     * @param string $type Type of rate limit
     * @param string|null $identifier Custom identifier, uses IP if null
     * @return int Time in seconds until reset
     */
    public function getTimeUntilReset(string $type, ?string $identifier = null): int
    {
        if (!$this->rateLimiter) {
            return 0;
        }

        $key = $identifier ?? $this->getClientIP();
        return $this->rateLimiter->getTimeUntilReset($key, $type);
    }

    /**
     * Hash password using secure algorithm
     *
     * @param string $password Password to hash
     * @return string Hashed password
     * @throws Exception If hashing fails
     */
    public function hashPassword(string $password): string
    {
        if (!$this->passwordHasher) {
            throw new Exception('Password hasher not configured');
        }

        return $this->passwordHasher->hashPassword($password);
    }

    /**
     * Verify password against hash
     *
     * @param string $password Password to verify
     * @param string $hash Hash to verify against
     * @return bool True if password matches hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        if (!$this->passwordHasher) {
            return false;
        }

        return $this->passwordHasher->verifyPassword($password, $hash);
    }

    /**
     * Validate password against security requirements
     *
     * @param string $password Password to validate
     * @return bool True if password meets requirements
     */
    public function validatePassword(string $password): bool
    {
        if (!$this->passwordHasher) {
            return strlen($password) >= 8;
        }

        return $this->passwordHasher->validatePassword($password);
    }

    /**
     * Check if password hash needs rehashing
     *
     * @param string $hash Hash to check
     * @return bool True if rehashing is needed
     */
    public function needsPasswordRehash(string $hash): bool
    {
        if (!$this->passwordHasher) {
            return false;
        }

        return $this->passwordHasher->needsRehash($hash);
    }

    /**
     * Get password requirements
     *
     * @return array Array of password requirements
     */
    public function getPasswordRequirements(): array
    {
        if (!$this->passwordHasher) {
            return ['Must be at least 8 characters long'];
        }

        return $this->passwordHasher->getPasswordRequirements();
    }

    /**
     * Calculate password strength
     *
     * @param string $password Password to evaluate
     * @return int Strength score (0-100)
     */
    public function calculatePasswordStrength(string $password): int
    {
        if (!$this->passwordHasher) {
            return strlen($password) >= 8 ? 50 : 20;
        }

        return $this->passwordHasher->calculatePasswordStrength($password);
    }

    /**
     * Enhanced Content Security Policy with nonce support
     *
     * @return string Generated nonce for CSP
     */
    public function generateCSPNonce(): string
    {
        try {
            return base64_encode(random_bytes(16));
        } catch (Exception $e) {
            return base64_encode(hash('sha256', uniqid(mt_rand(), true), true));
        }
    }

    /**
     * Validate file upload security
     *
     * @param array $file $_FILES array element
     * @return bool True if file is safe to upload
     */
    public function validateFileUpload(array $file): bool
    {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        // Check file size
        $maxSize = $this->config['input_validation']['max_file_size'] ?? 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return false;
        }

        // Check file extension
        $allowedTypes = $this->config['input_validation']['allowed_file_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            return false;
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx']
        ];

        $validMime = false;
        foreach ($allowedMimeTypes as $mime => $extensions) {
            if ($mimeType === $mime && in_array($extension, $extensions)) {
                $validMime = true;
                break;
            }
        }

        return $validMime;
    }

    /**
     * Generate secure random token
     *
     * @param int $length Token length
     * @return string Generated token
     */
    public function generateSecureToken(int $length = 32): string
    {
        try {
            return bin2hex(random_bytes($length));
        } catch (Exception $e) {
            return hash('sha256', uniqid(mt_rand(), true) . microtime());
        }
    }

    /**
     * Validate request origin
     *
     * @param array $allowedOrigins List of allowed origins
     * @return bool True if origin is allowed
     */
    public function validateOrigin(array $allowedOrigins = []): bool
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';

        if (empty($origin)) {
            return false;
        }

        if (empty($allowedOrigins)) {
            $allowedOrigins = [$this->config['app']['url'] ?? 'http://localhost'];
        }

        foreach ($allowedOrigins as $allowedOrigin) {
            if (strpos($origin, $allowedOrigin) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the rate limiter service
     *
     * @param RateLimiterService $rateLimiter
     * @return void
     */
    public function setRateLimiterService(RateLimiterService $rateLimiter): void
    {
        $this->rateLimiter = $rateLimiter;
    }

    /**
     * Set the password hashing service
     *
     * @param PasswordHashingService $passwordHasher
     * @return void
     */
    public function setPasswordHashingService(PasswordHashingService $passwordHasher): void
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Get the rate limiter service
     *
     * @return RateLimiterService|null
     */
    public function getRateLimiterService(): ?RateLimiterService
    {
        return $this->rateLimiter;
    }

    /**
     * Get the password hashing service
     *
     * @return PasswordHashingService|null
     */
    public function getPasswordHashingService(): ?PasswordHashingService
    {
        return $this->passwordHasher;
    }
}
