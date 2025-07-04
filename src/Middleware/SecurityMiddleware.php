<?php
declare(strict_types=1);

namespace RenalTales\Middleware;

use RenalTales\Security\CSRFProtection;
use RenalTales\Core\FlashMessages;

/**
 * Security Middleware
 * 
 * Handles automatic CSRF protection, rate limiting, and security headers
 * for all requests to the application.
 */
class SecurityMiddleware
{
    /**
     * Process the request through security checks
     */
    public static function handle(string $route, string $method): bool
    {
        // Set security headers
        self::setSecurityHeaders();
        
        // Check CSRF protection for state-changing requests
        if (!CSRFProtection::middleware($route, $method)) {
            http_response_code(403);
            FlashMessages::error('Invalid or expired security token. Please try again.');
            
            // If it's an AJAX request, return JSON
            if (self::isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'CSRF token validation failed',
                    'redirect' => $_SERVER['HTTP_REFERER'] ?? '/'
                ]);
                exit;
            }
            
            // Redirect back or to home
            $redirectTo = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$redirectTo}");
            exit;
        }
        
        // Apply general rate limiting
        if (!self::checkGeneralRateLimit()) {
            http_response_code(429);
            FlashMessages::error('Too many requests. Please slow down and try again.');
            
            if (self::isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'Rate limit exceeded',
                    'retry_after' => 60
                ]);
                exit;
            }
            
            $redirectTo = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$redirectTo}");
            exit;
        }
        
        return true;
    }
    
    /**
     * Set security headers
     */
    private static function setSecurityHeaders(): void
    {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
               "img-src 'self' data: https: blob:; " .
               "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
               "connect-src 'self'; " .
               "frame-ancestors 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self';";
        header("Content-Security-Policy: {$csp}");
        
        // HSTS (only for HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Remove server information
        header_remove('X-Powered-By');
        header_remove('Server');
    }
    
    /**
     * Check general rate limiting
     */
    private static function checkGeneralRateLimit(): bool
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Create a unique identifier combining IP and partial user agent
        $identifier = hash('sha256', $ipAddress . substr($userAgent, 0, 50));
        
        // Check if we have a database connection for rate limiting
        try {
            require_once dirname(__DIR__, 2) . '/app/Core/Database.php';
            $db = \Database::getInstance();
            
            // Clean up old rate limit records
            $db->query("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            
            // Check current rate limit
            $query = "SELECT COUNT(*) as count FROM rate_limits WHERE action_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            $result = $db->query($query, [$identifier])->fetch();
            
            $maxRequests = 300; // 300 requests per hour
            
            if ($result['count'] >= $maxRequests) {
                return false;
            }
            
            // Record this request
            $db->query("INSERT INTO rate_limits (action_key, created_at) VALUES (?, NOW())", [$identifier]);
            
            return true;
            
        } catch (\Exception $e) {
            // If database is not available, allow the request
            error_log("Rate limiting database error: " . $e->getMessage());
            return true;
        }
    }
    
    /**
     * Check if request is AJAX
     */
    private static function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Sanitize all input data
     */
    public static function sanitizeInput(): void
    {
        // Sanitize GET parameters
        $_GET = self::deepSanitize($_GET);
        
        // Sanitize POST parameters (except for password fields)
        $_POST = self::deepSanitize($_POST, ['password', 'password_confirmation']);
        
        // Sanitize COOKIE parameters
        $_COOKIE = self::deepSanitize($_COOKIE);
    }
    
    /**
     * Deep sanitize array data
     */
    private static function deepSanitize(array $data, array $skip = []): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            // Skip password fields and other sensitive data
            if (in_array($key, $skip)) {
                $sanitized[$key] = $value;
                continue;
            }
            
            if (is_array($value)) {
                $sanitized[$key] = self::deepSanitize($value, $skip);
            } else {
                // Remove null bytes and trim
                $value = str_replace(chr(0), '', (string)$value);
                $value = trim($value);
                
                // Only sanitize for output if it's not a password field
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate file uploads in request
     */
    public static function validateFileUploads(): bool
    {
        if (empty($_FILES)) {
            return true;
        }
        
        $validator = new \RenalTales\Validation\Validator();
        
        foreach ($_FILES as $fieldName => $file) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue; // No file uploaded for this field
            }
            
            $options = [
                'max_size' => 10485760, // 10MB
                'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
                'allowed_mimes' => [
                    'image/jpeg', 'image/png', 'image/gif',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ]
            ];
            
            if (!$validator->validateFileUpload($file, $options)) {
                FlashMessages::validationErrors($validator->getErrors());
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check for suspicious request patterns
     */
    public static function detectSuspiciousActivity(): bool
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Check for common attack patterns
        $suspiciousPatterns = [
            '/\.\./i',                          // Directory traversal
            '/union.*select/i',                 // SQL injection
            '/<script/i',                       // XSS attempts
            '/javascript:/i',                   // JavaScript injection
            '/vbscript:/i',                     // VBScript injection
            '/on\w+\s*=/i',                    // Event handler injection
            '/\x00/',                          // Null byte injection
            '/%00/',                           // URL encoded null byte
            '/\bor\s+1\s*=\s*1/i',            // SQL injection
            '/\bselect\s+.*\bfrom\s+/i',      // SQL injection
            '/\binsert\s+into\s+/i',          // SQL injection
            '/\bdelete\s+from\s+/i',          // SQL injection
            '/\bdrop\s+table\s+/i',           // SQL injection
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $requestUri) || preg_match($pattern, http_build_query($_GET)) || preg_match($pattern, http_build_query($_POST))) {
                self::logSuspiciousActivity('suspicious_pattern', [
                    'pattern' => $pattern,
                    'ip' => $ipAddress,
                    'user_agent' => $userAgent,
                    'request_uri' => $requestUri
                ]);
                return false;
            }
        }
        
        // Check for rapid requests from same IP
        if (self::isRapidRequests($ipAddress)) {
            self::logSuspiciousActivity('rapid_requests', [
                'ip' => $ipAddress,
                'user_agent' => $userAgent
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Check for rapid requests from same IP
     */
    private static function isRapidRequests(string $ipAddress): bool
    {
        $cacheKey = "rapid_requests_{$ipAddress}";
        
        // Use simple file-based caching if no other cache available
        $cacheFile = sys_get_temp_dir() . '/' . md5($cacheKey) . '.cache';
        
        $now = time();
        $requests = [];
        
        if (file_exists($cacheFile)) {
            $data = file_get_contents($cacheFile);
            $requests = json_decode($data, true) ?: [];
        }
        
        // Remove requests older than 1 minute
        $requests = array_filter($requests, fn($timestamp) => $timestamp > $now - 60);
        
        // Add current request
        $requests[] = $now;
        
        // Save back to cache
        file_put_contents($cacheFile, json_encode($requests));
        
        // Check if too many requests in the last minute
        return count($requests) > 30; // More than 30 requests per minute
    }
    
    /**
     * Log suspicious activity
     */
    private static function logSuspiciousActivity(string $event, array $data): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'data' => $data
        ];
        
        error_log("Suspicious activity: " . json_encode($logEntry));
        
        // Try to log to database if available
        try {
            require_once dirname(__DIR__, 2) . '/app/Core/Database.php';
            $db = \Database::getInstance();
            $db->query(
                "INSERT INTO security_logs (event, data, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$event, json_encode($data), $data['ip'] ?? 'unknown', $data['user_agent'] ?? 'unknown']
            );
        } catch (\Exception $e) {
            // Database not available, continue with file logging only
        }
    }
}
