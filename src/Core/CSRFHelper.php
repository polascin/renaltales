<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Exception;

/**
 * CSRFHelper - Cross-Site Request Forgery protection helper
 * 
 * Provides easy-to-use CSRF token management
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class CSRFHelper {
    
    private static $tokenName = '_csrf_token';
    private static $headerName = 'X-CSRF-Token';
    private static $expireTime = 3600; // 1 hour
    
    /**
     * Generate a new CSRF token
     * 
     * @return string CSRF token
     */
    public static function generateToken(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        try {
            $token = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            // Fallback for systems without random_bytes
            $token = hash('sha256', uniqid(mt_rand(), true));
        }
        
        $_SESSION[self::$tokenName] = [
            'token' => $token,
            'time' => time()
        ];
        
        return $token;
    }
    
    /**
     * Get current CSRF token
     * 
     * @return string CSRF token
     */
    public static function getToken(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::$tokenName]) || self::isTokenExpired()) {
            return self::generateToken();
        }
        
        return $_SESSION[self::$tokenName]['token'];
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool True if valid
     */
    public static function validateToken(string $token): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        if (!isset($_SESSION[self::$tokenName]['token']) || empty($token)) {
            return false;
        }
        
        if (self::isTokenExpired()) {
            return false;
        }
        
        return hash_equals($_SESSION[self::$tokenName]['token'], $token);
    }
    
    /**
     * Validate CSRF token from request
     * 
     * @return bool True if valid
     */
    public static function validateRequest(): bool {
        // Check POST data first
        $token = $_POST[self::$tokenName] ?? null;
        
        // If not found in POST, check headers
        if (!$token) {
            $token = $_SERVER['HTTP_' . str_replace('-', '_', strtoupper(self::$headerName))] ?? null;
        }
        
        // If still not found, check GET (not recommended but sometimes needed)
        if (!$token) {
            $token = $_GET[self::$tokenName] ?? null;
        }
        
        return $token ? self::validateToken($token) : false;
    }
    
    /**
     * Generate HTML input field for CSRF token
     * 
     * @return string HTML input field
     */
    public static function getTokenField(): string {
        $token = self::getToken();
        $name = htmlspecialchars(self::$tokenName, ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
        
        return "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\">";
    }
    
    /**
     * Generate meta tag for CSRF token (for AJAX requests)
     * 
     * @return string HTML meta tag
     */
    public static function getTokenMeta(): string {
        $token = self::getToken();
        $name = htmlspecialchars(self::$tokenName, ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
        
        return "<meta name=\"{$name}\" content=\"{$value}\">";
    }
    
    /**
     * Get token for JavaScript
     * 
     * @return string JavaScript code to set token
     */
    public static function getTokenScript(): string {
        $token = self::getToken();
        $name = json_encode(self::$tokenName);
        $value = json_encode($token);
        
        return "window.csrfToken = {name: {$name}, value: {$value}};";
    }
    
    /**
     * Check if current token is expired
     * 
     * @return bool True if expired
     */
    private static function isTokenExpired(): bool {
        if (!isset($_SESSION[self::$tokenName]['time'])) {
            return true;
        }
        
        return (time() - $_SESSION[self::$tokenName]['time']) > self::$expireTime;
    }
    
    /**
     * Regenerate CSRF token
     * 
     * @return string New CSRF token
     */
    public static function regenerateToken(): string {
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION[self::$tokenName]);
        }
        
        return self::generateToken();
    }
    
    /**
     * Set custom token name
     * 
     * @param string $name Token name
     */
    public static function setTokenName(string $name): void {
        self::$tokenName = $name;
    }
    
    /**
     * Set custom header name
     * 
     * @param string $name Header name
     */
    public static function setHeaderName(string $name): void {
        self::$headerName = $name;
    }
    
    /**
     * Set token expiration time
     * 
     * @param int $seconds Expiration time in seconds
     */
    public static function setExpireTime(int $seconds): void {
        self::$expireTime = $seconds;
    }
    
    /**
     * Middleware function to check CSRF for POST requests
     * 
     * @return bool True if request is safe
     */
    public static function checkRequest(): bool {
        // Only check POST, PUT, PATCH, DELETE requests
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }
        
        // Skip CSRF check for AJAX requests from same origin
        if (self::isSameOriginRequest()) {
            return self::validateRequest();
        }
        
        return self::validateRequest();
    }
    
    /**
     * Check if request is from same origin
     * 
     * @return bool True if same origin
     */
    private static function isSameOriginRequest(): bool {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        if (empty($origin) && empty($referer)) {
            return false;
        }
        
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $expectedOrigin = $scheme . '://' . $host;
        
        if (!empty($origin)) {
            return $origin === $expectedOrigin;
        }
        
        if (!empty($referer)) {
            return strpos($referer, $expectedOrigin) === 0;
        }
        
        return false;
    }
    
    /**
     * Handle CSRF attack (log and respond)
     * 
     * @param string $reason Reason for CSRF failure
     */
    public static function handleCSRFAttack(string $reason = 'Invalid CSRF token'): void {
        // Log the CSRF attack attempt
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'csrf_attack',
            'reason' => $reason,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'origin' => $_SERVER['HTTP_ORIGIN'] ?? '',
            'session_id' => session_id()
        ];
        
        self::logCSRFAttack($logEntry);
        
        // Send security response
        http_response_code(403);
        header('Content-Type: application/json');
        
        $response = [
            'error' => 'CSRF token validation failed',
            'message' => 'This request appears to be forged. Please refresh the page and try again.',
            'code' => 'CSRF_TOKEN_INVALID'
        ];
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Log CSRF attack attempt
     * 
     * @param array $logEntry Log entry data
     */
    private static function logCSRFAttack(array $logEntry): void {
        try {
            $logDir = defined('APP_DIR') ? APP_DIR . '/logs' : sys_get_temp_dir() . '/logs';
            
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $logFile = $logDir . '/csrf_attacks.log';
            $logData = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
            
            file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log('CSRF attack logging failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Create CSRF middleware for easy integration
     * 
     * @return callable Middleware function
     */
    public static function middleware(): callable {
        return function() {
            if (!self::checkRequest()) {
                self::handleCSRFAttack();
            }
        };
    }
    
    /**
     * Get CSRF configuration for forms
     * 
     * @return array Configuration array
     */
    public static function getFormConfig(): array {
        return [
            'token' => self::getToken(),
            'name' => self::$tokenName,
            'header' => self::$headerName,
            'field' => self::getTokenField(),
            'meta' => self::getTokenMeta(),
            'script' => self::getTokenScript()
        ];
    }
    
    /**
     * Validate and regenerate token in one call
     * 
     * @param string $token Token to validate
     * @param bool $regenerate Whether to regenerate after validation
     * @return bool True if valid
     */
    public static function validateAndRegenerate(string $token, bool $regenerate = true): bool {
        $isValid = self::validateToken($token);
        
        if ($isValid && $regenerate) {
            self::regenerateToken();
        }
        
        return $isValid;
    }
}
