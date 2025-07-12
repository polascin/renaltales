<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Exception;

/**
 * SecurityManager - Comprehensive security management
 * 
 * Handles CSRF protection, XSS prevention, security headers, and other security measures
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class SecurityManager {
    
    private $csrfToken;
    private $csrfTokenName = '_csrf_token';
    private $sessionManager;
    private $config;
    
    /**
     * Constructor
     * 
     * @param SessionManager $sessionManager
     * @param array $config Security configuration
     */
    public function __construct($sessionManager = null, $config = []) {
        $this->sessionManager = $sessionManager;
        $this->config = array_merge([
            'csrf_expire_time' => 3600, // 1 hour
            'csrf_regenerate_on_use' => true,
            'xss_protection' => true,
            'content_security_policy' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tiny.cloud",
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
    private function initializeSecurity() {
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
    public function setSecurityHeaders() {
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
    private function buildCSPHeader() {
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
    private function isHttps() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Check if current page is sensitive (requires no caching)
     * 
     * @return bool
     */
    private function isSensitivePage() {
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
    private function initializeCSRF() {
        if ($this->sessionManager) {
            $this->csrfToken = $this->sessionManager->getCSRFToken();
        } else {
            $this->generateCSRFToken();
        }
    }
    
    /**
     * Generate CSRF token
     */
    private function generateCSRFToken() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if (!isset($_SESSION[$this->csrfTokenName]) || $this->isCSRFTokenExpired()) {
            try {
                $token = bin2hex(random_bytes(32));
            } catch(Exception $e) {
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
    private function isCSRFTokenExpired() {
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
    public function getCSRFToken() {
        return $this->csrfToken;
    }
    
    /**
     * Generate CSRF token input field
     * 
     * @return string
     */
    public function getCSRFTokenField() {
        return '<input type="hidden" name="' . htmlspecialchars($this->csrfTokenName) . '" value="' . htmlspecialchars($this->getCSRFToken()) . '">';
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool
     */
    public function validateCSRFToken($token) {
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
    public function regenerateCSRFToken() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION[$this->csrfTokenName]);
        }
        $this->generateCSRFToken();
    }
    
    /**
     * Enable XSS protection
     */
    private function enableXSSProtection() {
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
    public function sanitizeInput($input, $allowHTML = false) {
        if (is_string($input)) {
            if ($allowHTML) {
                return $this->sanitizeHTML($input);
            } else {
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        
        if (is_array($input)) {
            return array_map(function($item) use ($allowHTML) {
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
    private function sanitizeHTML($html) {
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
    public function validateInput($input) {
        if (!is_string($input)) {
            return true;
        }
        
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
    public function logSecurityEvent($event, $context = []) {
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
    private function getClientIP() {
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
}
