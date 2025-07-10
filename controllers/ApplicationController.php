<?php

declare(strict_types=1);

require_once 'BaseController.php';
require_once 'LoginController.php';
require_once __DIR__ . '/../views/ApplicationView.php';
require_once __DIR__ . '/../views/ErrorView.php';
require_once __DIR__ . '/../core/AuthenticationManager.php';

/**
 * ApplicationController - Main application controller
 * 
 * Handles user requests and coordinates between models and views
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

class ApplicationController extends BaseController {
    
    private mixed $languageModel;
    private mixed $sessionManager;
    private mixed $authenticationManager;
    private mixed $loginController;
    
    /**
     * Constructor
     * 
     * @param mixed $languageModel
     * @param mixed $sessionManager
     */
    public function __construct(mixed $languageModel, mixed $sessionManager) {
        $this->languageModel = $languageModel;
        $this->sessionManager = $sessionManager;
        $this->authenticationManager = new AuthenticationManager($sessionManager);
        $this->loginController = new LoginController($languageModel, $sessionManager);
    }
    
    /**
     * Handle the main index action
     */
    public function index(): string {
        try {
            // Handle language change if requested
            $this->handleLanguageChange();
            
            // Check for login/logout actions
            $action = $_GET['action'] ?? '';
            
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
        if (!isset($_GET['lang']) || empty($_GET['lang'])) {
            return;
        }
        
        $requestedLanguage = $this->sanitizeInput($_GET['lang']);
        
        // Validate language code format (should be 2-3 letter code)
        if (!$this->isValidInput($requestedLanguage, '_-') || strlen($requestedLanguage) > 10) {
            return;
        }
        
        if (!$this->sessionManager || !$this->languageModel) {
            return;
        }
        
        // Validate CSRF token
        $csrfToken = isset($_GET['csrf_token']) ? $this->sanitizeInput($_GET['csrf_token']) : '';
        if (!$csrfToken || !$this->sessionManager->validateCSRFToken($csrfToken)) {
            return;
        }
        
        // Check if language is supported
        $supportedLanguages = $this->languageModel->getSupportedLanguages();
        
        if (in_array($requestedLanguage, $supportedLanguages, true)) {
            // Set language in session
            $this->sessionManager->setSession('user_language', $requestedLanguage);
            
            // Redirect to avoid resubmission
            $this->redirect($_SERVER['PHP_SELF'] ?? '/');
        }
    }
    
    /**
     * Handle error display
     * 
     * @param \Exception $exception
     * @param bool $isDebugMode
     * @return string
     */
    public function error(\Exception $exception, bool $isDebugMode = false): string {
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
     * @param string $input
     * @return string
     */
    private function sanitizeInput(string $input): string {
        // Trim whitespace
        $input = trim($input);
        
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
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
     * @return mixed
     */
    public function getLanguageModel(): mixed {
        return $this->languageModel;
    }
    
    /**
     * Get session manager
     * 
     * @return mixed
     */
    public function getSessionManager(): mixed {
        return $this->sessionManager;
    }
}

?>
