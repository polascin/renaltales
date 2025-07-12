<?php

declare(strict_types=1);

require_once 'BaseController.php';
require_once 'LoginController.php';
require_once __DIR__ . '/../views/ApplicationView.php';
require_once __DIR__ . '/../views/ErrorView_temp.php';
require_once __DIR__ . '/../core/AuthenticationManager.php';
require_once __DIR__ . '/../core/AdminSecurityManager.php';
require_once __DIR__ . '/../core/SessionRegenerationManager.php';

/**
 * ApplicationController - Main application controller
 * 
 * Handles user requests and coordinates between models and views
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

class ApplicationController extends BaseController {
    
    private ?object $languageModel;
    private ?object $sessionManager;
    private ?AuthenticationManager $authenticationManager;
    private ?LoginController $loginController;
    private ?AdminSecurityManager $adminSecurityManager;
    private ?SessionRegenerationManager $sessionRegenerationManager;
    
    /**
     * Constructor
     * 
     * @param object|null $languageModel
     * @param object|null $sessionManager
     */
    public function __construct(?object $languageModel, ?object $sessionManager) {
        $this->languageModel = $languageModel;
        $this->sessionManager = $sessionManager;
        
        try {
            $this->authenticationManager = new AuthenticationManager($sessionManager);
            $this->loginController = new LoginController($languageModel, $sessionManager);
            $this->adminSecurityManager = new AdminSecurityManager();
            $this->sessionRegenerationManager = new SessionRegenerationManager();
        } catch (\Exception $e) {
            error_log("ApplicationController::__construct() error: " . $e->getMessage());
            $this->authenticationManager = null;
            $this->loginController = null;
            $this->adminSecurityManager = null;
            $this->sessionRegenerationManager = null;
        }
    }
    
    /**
     * Handle the main index action
     */
    public function index(): string {
        try {
            // Ensure dependencies are available
            if (!$this->loginController) {
                throw new \RuntimeException("LoginController not initialized properly");
            }
            
            // Initialize security tracking
            $this->initializeSecurityTracking();
            
            // Enhanced session security
            $this->enhanceSessionSecurity();
            
            // Output buffering and content security
            ob_start();
            
            // Security headers for all responses
            $this->setSecurityHeaders();
            
            // Handle language change if requested
            $this->handleLanguageChange();
            
            // Handle navigation and actions
            $this->handleNavigation();
            
            // Check for login/logout actions
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'login':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        return $this->loginController->processLogin();
                    } else {
                        return $this->loginController->showLoginForm();
                    }
                    
                case 'logout':
                    return $this->loginController->logout();
                    
                default:
                    // Create and render the main view
                    $view = new ApplicationView($this->languageModel, $this->sessionManager, $this->authenticationManager);
                    return $view->render();
            }
        } catch (\Exception $e) {
            // Log error and return error view
            error_log("ApplicationController::index() error: " . $e->getMessage());
            return $this->error($e, defined('DEBUG_MODE') && DEBUG_MODE);
        }
    }
    
    /**
     * Handle language change requests
     */
    private function handleLanguageChange(): void {
        // Debug logging
        error_log("ApplicationController: handleLanguageChange() - REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
        error_log("ApplicationController: handleLanguageChange() - POST data: " . print_r($_POST, true));
        error_log("ApplicationController: handleLanguageChange() - GET data: " . print_r($_GET, true));
        
        // Check if this is a POST request for secure language switching
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lang'])) {
            error_log("ApplicationController: Handling POST language change for: " . $_POST['lang']);
            $this->handleLanguageChangePost();
            return;
        }
        
        // No longer supporting GET requests for language changes
        // All language changes must use secure POST requests
    }
    
    /**
     * Handle secure language change via POST
     */
    private function handleLanguageChangePost(): void {
        error_log("ApplicationController: handleLanguageChangePost() called");
        error_log("ApplicationController: POST data: " . print_r($_POST, true));
        
        $requestedLanguage = $this->sanitizeInput($_POST['lang'] ?? '');
        error_log("ApplicationController: Requested language: " . $requestedLanguage);
        
        // Validate language code format (should be 2-3 letter code)
        if (!$this->isValidInput($requestedLanguage, '_-') || strlen($requestedLanguage) > 10) {
            error_log("ApplicationController: Invalid language code format: " . $requestedLanguage);
            return;
        }
        
        if (!$this->sessionManager || !$this->languageModel) {
            error_log("ApplicationController: Missing sessionManager or languageModel for language change");
            return;
        }
        
        // Validate CSRF token from POST data (secure)
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        error_log("ApplicationController: Looking for CSRF token in POST data");
        error_log("ApplicationController: Available POST keys: " . implode(', ', array_keys($_POST)));
        
        // Check for alternative CSRF token names
        if (!$csrfTokenRaw && isset($_POST['csrf_token'])) {
            $csrfTokenRaw = $_POST['csrf_token'];
            error_log("ApplicationController: Found alternative csrf_token field");
        }
        
        // Debug logging
        error_log("ApplicationController: Raw CSRF token type: " . gettype($csrfTokenRaw));
        if (is_string($csrfTokenRaw)) {
            error_log("ApplicationController: Raw CSRF token value: " . substr($csrfTokenRaw, 0, 50) . "...");
        }
        
        $csrfToken = $this->sanitizeInput($csrfTokenRaw);
        if (!$csrfToken || !$this->sessionManager->validateCSRFToken($csrfToken)) {
            error_log("ApplicationController: Invalid CSRF token for language change");
            return;
        }
        error_log("ApplicationController: CSRF token validated successfully");
        
        // Check if language is supported
        try {
            $supportedLanguages = $this->languageModel->getSupportedLanguages();
            error_log("ApplicationController: Supported languages: " . print_r($supportedLanguages, true));
            
            if (in_array($requestedLanguage, $supportedLanguages, true)) {
                // Set language using LanguageDetector
                error_log("ApplicationController: Setting language using LanguageDetector: " . $requestedLanguage);
                
                // Get language detector from language model
                if ($this->languageModel && method_exists($this->languageModel, 'getLanguageDetector')) {
                    $languageDetector = $this->languageModel->getLanguageDetector();
                    if ($languageDetector && method_exists($languageDetector, 'setLanguage')) {
                        $setResult = $languageDetector->setLanguage($requestedLanguage);
                        error_log("ApplicationController: LanguageDetector setLanguage result: " . ($setResult ? 'SUCCESS' : 'FAILED'));
                    } else {
                        error_log("ApplicationController: LanguageDetector not available or missing setLanguage method");
                        // Fallback to direct session setting
                        $_SESSION['language'] = $requestedLanguage;
                        error_log("ApplicationController: Fallback - set language directly in session");
                    }
                } else {
                    error_log("ApplicationController: LanguageModel missing getLanguageDetector method");
                    // Fallback to direct session setting
                    $_SESSION['language'] = $requestedLanguage;
                    error_log("ApplicationController: Fallback - set language directly in session");
                }
                
                // Verify it was actually set
                if (isset($_SESSION['language'])) {
                    error_log("ApplicationController: Session language after setting: " . $_SESSION['language']);
                } else {
                    error_log("ApplicationController: Session language NOT SET after setLanguage call");
                }
                
                // Redirect to avoid resubmission
                error_log("ApplicationController: Redirecting after language change to: " . ($_SERVER['PHP_SELF'] ?? '/'));
                $this->redirect($_SERVER['PHP_SELF'] ?? '/');
            } else {
                error_log("ApplicationController: Unsupported language: " . $requestedLanguage);
            }
        } catch (\Exception $e) {
            error_log("ApplicationController: Error during language change: " . $e->getMessage());
        }
    }
    
    /**
     * Handle navigation requests via POST
     */
    private function handleNavigation(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section'])) {
            error_log("ApplicationController: Handling POST navigation to section: " . $_POST['section']);
            
            $requestedSection = $this->sanitizeInput($_POST['section'] ?? '');
            
            // Validate section name format
            if (!$this->isValidInput($requestedSection, '-') || strlen($requestedSection) > 20) {
                error_log("ApplicationController: Invalid section name format: " . $requestedSection);
                return;
            }
            
            if (!$this->sessionManager) {
                error_log("ApplicationController: Missing sessionManager for navigation");
                return;
            }
            
            // Validate CSRF token from POST data
            $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
            $csrfToken = $this->sanitizeInput($csrfTokenRaw);
            
            if (!$csrfToken || !$this->sessionManager->validateCSRFToken($csrfToken)) {
                error_log("ApplicationController: Invalid CSRF token for navigation");
                return;
            }
            
            // Store section in session for persistence
            $this->sessionManager->setSession('current_section', $requestedSection);
            
            // Also handle any additional action parameter
            if (isset($_POST['action']) && !empty($_POST['action'])) {
                $action = $this->sanitizeInput($_POST['action']);
                $this->sessionManager->setSession('current_action', $action);
            }
            
            error_log("ApplicationController: Navigation successful - section set to: " . $requestedSection);
        }
    }
    
    /**
     * Handle error display
     * 
     * @param \Exception $exception
     * @param bool $isDebugMode
     * @return string
     */
    public function error(Exception $exception, bool $isDebugMode = false): string {
        $errorView = new ErrorView($exception, $isDebugMode, $this->languageModel);
        return $errorView->render();
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url
     */
    private function redirect(string $url): void {
        // Prevent open redirect vulnerabilities
        if (!$this->isValidRedirectUrl($url)) {
            $url = '/';
        }
        
        header("Location: $url");
        exit;
    }
    
    /**
     * Validate redirect URL to prevent open redirects
     * 
     * @param string $url
     * @return bool
     */
    private function isValidRedirectUrl(string $url): bool {
        // Allow relative URLs
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            return true;
        }
        
        // Allow same-origin URLs
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $baseUrl = $scheme . '://' . $host;
            
            if (strpos($url, $baseUrl) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize input string to prevent XSS and other attacks
     * 
     * @param mixed $input
     * @return string
     */
    private function sanitizeInput($input): string {
        // Handle arrays or objects
        if (is_array($input)) {
            error_log("ApplicationController: Received array input, using first string value");
            // Get first string value from array
            foreach ($input as $value) {
                if (is_string($value)) {
                    $input = $value;
                    break;
                }
            }
            // If no string found, convert to empty string
            if (is_array($input)) {
                $input = '';
            }
        } elseif (is_object($input)) {
            error_log("ApplicationController: Received object input, converting to string");
            $input = (string) $input;
        } elseif (!is_string($input)) {
            $input = (string) $input;
        }
        
        // Check if input looks like HTML-encoded JSON (e.g., {"token":"..."} -> {&quot;token&quot;:...})
        if (str_contains($input, '&quot;') || str_contains($input, '&#')) {
            // Decode HTML entities first
            $input = html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            // If it's JSON, try to extract the token value
            if (str_starts_with($input, '{') && str_contains($input, 'token')) {
                $decoded = json_decode($input, true);
                if (is_array($decoded) && isset($decoded['token'])) {
                    $input = $decoded['token'];
                }
            }
        }
        
        // Trim whitespace
        $input = trim($input);
        
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // For CSRF tokens, don't HTML encode - keep them as-is
        // For other inputs, convert special characters to HTML entities
        if (!preg_match('/^[a-f0-9]{64}$/', $input)) {
            $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $input;
    }
    
    /**
     * Validate that a string contains only alphanumeric characters and allowed symbols
     * 
     * @param string $input
     * @param string $allowedChars Additional allowed characters (regex pattern)
     * @return bool
     */
    private function isValidInput(string $input, string $allowedChars = ''): bool {
        $pattern = '/^[a-zA-Z0-9' . $allowedChars . ']+$/';
        return preg_match($pattern, $input) === 1;
    }

    /**
     * Get language model
     * 
     * @return object|null
     */
    public function getLanguageModel(): ?object {
        return $this->languageModel;
    }
    
    /**
     * Get session manager
     * 
     * @return object|null
     */
    public function getSessionManager(): ?object {
        return $this->sessionManager;
    }
    
    /**
     * Enhance session security with intelligent regeneration and monitoring
     */
    private function enhanceSessionSecurity(): void {
        try {
            if (!$this->sessionRegenerationManager) {
                return;
            }
            
            // Build security context
            $context = [
                'is_admin' => $this->isAdminUser(),
                'privilege_change' => $this->hasPrivilegeChange(),
                'suspicious_activity' => $this->hasSuspiciousActivity(),
                'ip_change' => $this->hasIPAddressChanged(),
                'user_agent_change' => $this->hasUserAgentChanged()
            ];
            
            // Perform intelligent session regeneration
            $regenerated = $this->sessionRegenerationManager->intelligentRegeneration($context);
            
            if ($regenerated) {
                error_log("Session regenerated for enhanced security");
            }
            
            // Validate admin session if applicable
            if ($this->isAdminUser() && $this->adminSecurityManager) {
                if (!$this->adminSecurityManager->validateAdminSession()) {
                    $this->handleAdminSecurityViolation();
                }
            }
            
        } catch (\Exception $e) {
            error_log("Enhanced security error: " . $e->getMessage());
        }
    }
    
    /**
     * Check if current user is admin
     */
    private function isAdminUser(): bool {
        return isset($_SESSION['admin_user_id']) || 
               (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
    }
    
    /**
     * Check for privilege changes
     */
    private function hasPrivilegeChange(): bool {
        // Check if user role has changed in this request
        $currentRole = $_SESSION['user_role'] ?? null;
        $previousRole = $_SESSION['_security']['previous_role'] ?? null;
        
        if ($previousRole && $currentRole !== $previousRole) {
            $_SESSION['_security']['previous_role'] = $currentRole;
            return true;
        }
        
        $_SESSION['_security']['previous_role'] = $currentRole;
        return false;
    }
    
    /**
     * Check for suspicious activity indicators
     */
    private function hasSuspiciousActivity(): bool {
        // Check for rapid requests
        $now = time();
        $lastRequest = $_SESSION['_security']['last_request_time'] ?? 0;
        $_SESSION['_security']['last_request_time'] = $now;
        
        if ($now - $lastRequest < 1) {
            return true;
        }
        
        // Check for suspicious parameters
        $suspiciousParams = ['exec', 'eval', 'system', 'shell_exec', '<script', 'javascript:'];
        $allParams = array_merge($_GET, $_POST);
        
        foreach ($allParams as $value) {
            if (is_string($value)) {
                foreach ($suspiciousParams as $param) {
                    if (stripos($value, $param) !== false) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if IP address has changed
     */
    private function hasIPAddressChanged(): bool {
        $currentIP = $this->getClientIP();
        $storedIP = $_SESSION['_security']['ip_address'] ?? '';
        
        return !empty($storedIP) && $storedIP !== $currentIP;
    }
    
    /**
     * Check if user agent has changed
     */
    private function hasUserAgentChanged(): bool {
        $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $storedUA = $_SESSION['_security']['user_agent'] ?? '';
        
        return !empty($storedUA) && $storedUA !== $currentUA;
    }
    
    /**
     * Handle admin security violation
     */
    private function handleAdminSecurityViolation(): void {
        // Log the violation
        error_log("Admin security violation detected for user: " . ($_SESSION['admin_user_id'] ?? 'unknown'));
        
        // Destroy admin session
        if ($this->adminSecurityManager) {
            $this->adminSecurityManager->destroyAdminSession();
        }
        
        // Redirect to login (will be handled via POST form)
        $this->redirect('/');
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP(): string {
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
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Initialize security tracking for the session
     */
    private function initializeSecurityTracking(): void {
        if (!isset($_SESSION['_security'])) {
            $_SESSION['_security'] = [
                'created_at' => time(),
                'ip_address' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'last_activity' => time(),
                'request_count' => 0,
                'previous_role' => null,
                'last_request_time' => 0
            ];
        } else {
            $_SESSION['_security']['last_activity'] = time();
            $_SESSION['_security']['request_count'] = ($_SESSION['_security']['request_count'] ?? 0) + 1;
        }
    }
    
    /**
     * Set comprehensive security headers
     */
    private function setSecurityHeaders(): void {
        // Prevent XSS
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Strict transport security (HTTPS only)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self'; " .
               "frame-src 'none'; " .
               "object-src 'none'; " .
               "base-uri 'self';";
        header("Content-Security-Policy: " . $csp);
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        
        // Cache control for sensitive pages
        if ($this->isAdminUser() || $this->requiresAuth()) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        }
    }
    
    /**
     * Check if current page requires authentication
     */
    private function requiresAuth(): bool {
        // Check current section from session
        $section = $this->sessionManager ? $this->sessionManager->getSession('current_section') : 'home';
        $action = $this->sessionManager ? $this->sessionManager->getSession('current_action') : '';
        
        $authRequiredSections = ['profile', 'settings', 'my-stories'];
        $authRequiredActions = ['admin', 'upload', 'story_editor', 'new'];
        
        return in_array($section, $authRequiredSections) || in_array($action, $authRequiredActions);
    }
}
