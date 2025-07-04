<?php
declare(strict_types=1);

namespace RenalTales\Security;

/**
 * CSRF Protection System
 * 
 * Provides comprehensive CSRF protection with token generation,
 * validation, and automatic form injection.
 */
class CSRFProtection
{
    private const TOKEN_LENGTH = 32;
    private const TOKEN_LIFETIME = 3600; // 1 hour
    private const SESSION_KEY = '_csrf_tokens';
    private const HEADER_NAME = 'X-CSRF-Token';
    
    /**
     * Generate a new CSRF token
     */
    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $timestamp = time();
        
        // Initialize session storage
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
        
        // Store token with timestamp
        $_SESSION[self::SESSION_KEY][$token] = $timestamp;
        
        // Clean up old tokens
        self::cleanupExpiredTokens();
        
        return $token;
    }
    
    /**
     * Get or generate CSRF token
     */
    public static function getToken(): string
    {
        // Try to get existing valid token
        if (isset($_SESSION[self::SESSION_KEY])) {
            foreach ($_SESSION[self::SESSION_KEY] as $token => $timestamp) {
                if ($timestamp > time() - self::TOKEN_LIFETIME) {
                    return $token;
                }
            }
        }
        
        // Generate new token if none exist or all expired
        return self::generateToken();
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken(string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        
        // Check if token exists in session
        if (!isset($_SESSION[self::SESSION_KEY][$token])) {
            return false;
        }
        
        $timestamp = $_SESSION[self::SESSION_KEY][$token];
        
        // Check if token is still valid (not expired)
        if ($timestamp <= time() - self::TOKEN_LIFETIME) {
            unset($_SESSION[self::SESSION_KEY][$token]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate CSRF token from request
     */
    public static function validateRequest(): bool
    {
        $token = self::getTokenFromRequest();
        
        if (!$token) {
            return false;
        }
        
        return self::validateToken($token);
    }
    
    /**
     * Get CSRF token from request (POST, GET, or Header)
     */
    public static function getTokenFromRequest(): ?string
    {
        // Check POST data first
        if (isset($_POST['csrf_token']) && !empty($_POST['csrf_token'])) {
            return $_POST['csrf_token'];
        }
        
        // Check GET data
        if (isset($_GET['csrf_token']) && !empty($_GET['csrf_token'])) {
            return $_GET['csrf_token'];
        }
        
        // Check headers (for AJAX requests)
        $headers = getallheaders();
        if (isset($headers[self::HEADER_NAME])) {
            return $headers[self::HEADER_NAME];
        }
        
        // Check alternative header format
        $headerKey = 'HTTP_' . str_replace('-', '_', strtoupper(self::HEADER_NAME));
        if (isset($_SERVER[$headerKey])) {
            return $_SERVER[$headerKey];
        }
        
        return null;
    }
    
    /**
     * Generate CSRF hidden input field
     */
    public static function generateHiddenField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Generate CSRF meta tag for AJAX requests
     */
    public static function generateMetaTag(): string
    {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Invalidate a specific token
     */
    public static function invalidateToken(string $token): void
    {
        unset($_SESSION[self::SESSION_KEY][$token]);
    }
    
    /**
     * Invalidate all tokens (useful on login/logout)
     */
    public static function invalidateAllTokens(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
    
    /**
     * Clean up expired tokens
     */
    public static function cleanupExpiredTokens(): void
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return;
        }
        
        $cutoff = time() - self::TOKEN_LIFETIME;
        
        $_SESSION[self::SESSION_KEY] = array_filter(
            $_SESSION[self::SESSION_KEY],
            fn($timestamp) => $timestamp > $cutoff
        );
        
        // Remove session key if no tokens left
        if (empty($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY]);
        }
    }
    
    /**
     * Get token count
     */
    public static function getTokenCount(): int
    {
        return count($_SESSION[self::SESSION_KEY] ?? []);
    }
    
    /**
     * Check if CSRF protection should be skipped for this route
     */
    public static function shouldSkipProtection(string $route): bool
    {
        $skipRoutes = [
            '/api/webhooks/',
            '/api/public/',
            '/cron/',
            '/health-check'
        ];
        
        foreach ($skipRoutes as $skipRoute) {
            if (str_starts_with($route, $skipRoute)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Middleware function to validate CSRF tokens
     */
    public static function middleware(string $route, string $method): bool
    {
        // Skip CSRF protection for GET, HEAD, OPTIONS requests
        if (in_array(strtoupper($method), ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }
        
        // Skip for certain routes
        if (self::shouldSkipProtection($route)) {
            return true;
        }
        
        // Validate token for POST, PUT, PATCH, DELETE requests
        return self::validateRequest();
    }
    
    /**
     * Generate JavaScript code for AJAX CSRF protection
     */
    public static function generateAjaxScript(): string
    {
        $token = self::getToken();
        
        return "
        <script>
        // CSRF Protection for AJAX requests
        (function() {
            const token = '" . addslashes($token) . "';
            
            // Set up jQuery AJAX defaults
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    beforeSend: function(xhr, settings) {
                        if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                            xhr.setRequestHeader('" . self::HEADER_NAME . "', token);
                        }
                    }
                });
            }
            
            // Set up Fetch API defaults
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                if (options.method && !/^(GET|HEAD|OPTIONS|TRACE)$/i.test(options.method)) {
                    options.headers = options.headers || {};
                    options.headers['" . self::HEADER_NAME . "'] = token;
                }
                return originalFetch(url, options);
            };
            
            // Set up XMLHttpRequest defaults
            const originalOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
                this._method = method;
                return originalOpen.call(this, method, url, async, user, password);
            };
            
            const originalSend = XMLHttpRequest.prototype.send;
            XMLHttpRequest.prototype.send = function(data) {
                if (this._method && !/^(GET|HEAD|OPTIONS|TRACE)$/i.test(this._method)) {
                    this.setRequestHeader('" . self::HEADER_NAME . "', token);
                }
                return originalSend.call(this, data);
            };
        })();
        </script>";
    }
    
    /**
     * Auto-inject CSRF tokens into forms
     */
    public static function injectTokensIntoForms(string $html): string
    {
        // Find all forms that don't already have CSRF tokens
        $pattern = '/<form\s[^>]*method\s*=\s*["\']?post["\']?[^>]*>(.*?)<\/form>/is';
        
        return preg_replace_callback($pattern, function($matches) {
            $formContent = $matches[1];
            
            // Check if CSRF token already exists
            if (strpos($formContent, 'name="csrf_token"') !== false) {
                return $matches[0];
            }
            
            // Inject CSRF token
            $csrfField = self::generateHiddenField();
            $newFormContent = $csrfField . $formContent;
            
            return str_replace($formContent, $newFormContent, $matches[0]);
        }, $html);
    }
    
    /**
     * Create CSRF-protected URL
     */
    public static function protectedUrl(string $url): string
    {
        $token = self::getToken();
        $separator = strpos($url, '?') !== false ? '&' : '?';
        
        return $url . $separator . 'csrf_token=' . urlencode($token);
    }
    
    /**
     * Rotate token (generate new one and invalidate current)
     */
    public static function rotateToken(): string
    {
        // Get current token to invalidate
        $currentToken = self::getTokenFromRequest();
        
        // Generate new token
        $newToken = self::generateToken();
        
        // Invalidate current token if it exists
        if ($currentToken) {
            self::invalidateToken($currentToken);
        }
        
        return $newToken;
    }
    
    /**
     * Get CSRF configuration for client-side use
     */
    public static function getClientConfig(): array
    {
        return [
            'token' => self::getToken(),
            'header_name' => self::HEADER_NAME,
            'field_name' => 'csrf_token'
        ];
    }
}
