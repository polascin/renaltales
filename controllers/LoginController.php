<?php

declare(strict_types=1);

require_once 'BaseController.php';
require_once __DIR__ . '/../views/LoginView.php';
require_once __DIR__ . '/../core/AuthenticationManager.php';

/**
 * LoginController - Handles user authentication
 * 
 * Manages login, logout, and authentication processes
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

class LoginController extends BaseController {
    
    private mixed $languageModel;
    private mixed $sessionManager;
    private mixed $authenticationManager;
    
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
    }
    
    /**
     * Display login form
     */
    public function showLoginForm(): string {
        // If user is already logged in, redirect to main page
        if ($this->sessionManager && $this->sessionManager->getSession('user_id')) {
            $this->redirect('/');
            return '';
        }
        
        $view = new LoginView($this->languageModel, $this->sessionManager, $this->authenticationManager);
        return $view->render();
    }
    
    /**
     * Process login form submission
     */
    public function processLogin(): string {
        $errors = [];
        
        // Validate CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->sessionManager->validateCSRFToken($csrfToken)) {
            $errors[] = $this->getText('invalid_csrf_token', 'Invalid security token. Please try again.');
        }
        
        // Get form data
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';
        
        // Validate input
        if (empty($email)) {
            $errors[] = $this->getText('email_required', 'Email is required.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = $this->getText('email_invalid', 'Please enter a valid email address.');
        }
        
        if (empty($password)) {
            $errors[] = $this->getText('password_required', 'Password is required.');
        }
        
        // If validation fails, show login form with errors
        if (!empty($errors)) {
            $view = new LoginView($this->languageModel, $this->sessionManager, $this->authenticationManager);
            $view->setErrors($errors);
            return $view->render();
        }
        
        // Attempt authentication
        $result = $this->authenticationManager->authenticate($email, $password, $this->getClientIP());
        
        if ($result && $result['success']) {
            // Login successful
            $user = $result['user'];
            
            // Set session variables
            $this->sessionManager->setSession('user_id', $user['id']);
            $this->sessionManager->setSession('user_email', $user['email']);
            $this->sessionManager->setSession('user_name', $user['full_name']);
            $this->sessionManager->setSession('user_role', $user['role']);
            $this->sessionManager->setSession('login_time', time());
            
            // Handle remember me
            if ($rememberMe) {
                $this->setRememberMeCookie($user['id']);
            }
            
            // Check if 2FA is required
            if ($result['requires_2fa']) {
                $this->sessionManager->setSession('2fa_user_id', $user['id']);
                $this->sessionManager->setSession('2fa_required', true);
                $this->redirect('/?action=2fa');
                return '';
            }
            
            // Redirect to intended page or dashboard
            $redirectTo = $this->sessionManager->getSession('login_redirect') ?? '/';
            $this->sessionManager->removeSession('login_redirect');
            $this->redirect($redirectTo);
            return '';
        } else {
            // Login failed
            $message = $result['message'] ?? $this->getText('login_failed', 'Invalid email or password.');
            $errors[] = $message;
            
            $view = new LoginView($this->languageModel, $this->sessionManager, $this->authenticationManager);
            $view->setErrors($errors);
            return $view->render();
        }
    }
    
    /**
     * Handle logout
     */
    public function logout(): string {
        $userId = $this->sessionManager->getSession('user_id');
        
        if ($userId) {
            // Log the logout event
            $this->authenticationManager->logSecurityEvent(
                $userId,
                'logout',
                $this->getClientIP(),
                'User logged out'
            );
        }
        
        // Clear session
        $this->sessionManager->destroySession();
        
        // Clear remember me cookie
        $this->clearRememberMeCookie();
        
        // Redirect to login page
        $this->redirect('/?action=login');
        return '';
    }
    
    /**
     * Set remember me cookie
     */
    private function setRememberMeCookie(int $userId): void {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store token in database
        $this->authenticationManager->storeRememberToken($userId, $token, $expires);
        
        // Set cookie
        setcookie('remember_token', $token, $expires, '/', '', true, true);
    }
    
    /**
     * Clear remember me cookie
     */
    private function clearRememberMeCookie(): void {
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP(): string {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Get text from language model
     */
    private function getText(string $key, string $default = ''): string {
        return $this->languageModel ? $this->languageModel->getText($key, $default) : $default;
    }
    
    /**
     * Sanitize input string
     */
    private function sanitizeInput(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Redirect to URL
     */
    private function redirect(string $url): void {
        header("Location: $url");
        exit;
    }
}

?>
