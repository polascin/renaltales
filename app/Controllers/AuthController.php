<?php
/**
 * Authentication Controller
 * Handles user registration, login, logout, and password management
 */

require_once APP_PATH . '/Core/Controller.php';

class AuthController extends Controller {
    private $authService;
    private $userRepository;
    
    public function __construct() {
        parent::__construct();
        
        // Initialize services if using the modern architecture
        if (class_exists('RenalTales\Service\AuthenticationService')) {
            require_once ROOT_PATH . '/src/Service/AuthenticationService.php';
            require_once ROOT_PATH . '/src/Repository/UserRepository.php';
            require_once ROOT_PATH . '/src/Security/AuthService.php';
            require_once ROOT_PATH . '/src/Security/SessionManager.php';
            require_once ROOT_PATH . '/src/Security/LoginThrottling.php';
            
            $this->authService = new \RenalTales\Service\AuthenticationService();
            $this->userRepository = new \RenalTales\Repository\UserRepository();
        }
    }
    
    /**
     * Show login form
     */
    public function showLogin() {
        // Redirect if already logged in
        if ($this->currentUser) {
            $this->redirect('/');
        }
        
        $this->view('auth/login', [
            'csrf_token' => $this->generateCsrf(),
            'errors' => $this->flash('errors'),
            'success' => $this->flash('success'),
            'old_input' => $this->flash('old_input')
        ]);
    }
    
    /**
     * Handle login form submission
     */
    public function login() {
        try {
            // Rate limiting check
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            try {
                $this->security->applyRateLimit('login_' . $ipAddress, 5, 900); // 5 attempts per 15 minutes
            } catch (Exception $e) {
                $this->flashError('Too many login attempts from this IP address. Please try again in 15 minutes.');
                $this->redirect('/login');
            }
            
            // Validate CSRF token
            $this->validateCsrf();
            
            // Prepare input data
            $input = [
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'remember_me' => isset($_POST['remember_me'])
            ];
            
            // Validate input using enhanced validator
            $rules = [
                'email' => 'required|email',
                'password' => 'required|min:1'
            ];
            
            $messages = [
                'email.required' => __('form.email.required'),
                'email.email' => __('form.email.invalid'),
                'password.required' => __('form.password.required')
            ];
            
            $validatedData = $this->getValidatedData($input, $rules, $messages);
            
            if (!$validatedData) {
                $this->flashValidationErrors($this->validator->getErrors());
                $this->redirect('/login');
            }
            
            // Attempt authentication with modern throttling
            $email = $validatedData['email'];
            $password = $validatedData['password'];
            
            // Use modern auth service if available
            if ($this->authService) {
                try {
                    $user = $this->authService->authenticate($email, $password, $ipAddress);
                    
                    if (!$user) {
                        $this->flashError(__('auth.login.invalid_credentials'));
                        $this->redirect('/login');
                    }
                    
                    // Create modern session
                    $sessionToken = $this->authService->createSession($user, $validatedData['remember_me']);
                    
                } catch (Exception $e) {
                    $this->flashError($e->getMessage());
                    $this->redirect('/login');
                }
            } else {
                // Fallback to legacy authentication
                $user = $this->authenticateUser($email, $password);
                
                if (!$user) {
                    $this->security->recordLoginAttempt($email, false);
                    $this->flashError(__('auth.login.invalid_credentials'));
                    $this->redirect('/login');
                }
                
                // Check if email is verified
                if (!$user['email_verified_at']) {
                    $this->flashError(__('auth.login.email_not_verified'));
                    $this->redirect('/login');
                }
                
                // Record successful login
                $this->security->recordLoginAttempt($email, true);
                
                // Create legacy session
                $this->createUserSession($user, $validatedData['remember_me']);
            }
            
            // Update last login
            $this->db->execute(
                "UPDATE users SET last_login_at = NOW() WHERE id = ?",
                [$user['id']]
            );
            
            // Log successful login
            $this->logActivity('login', 'User logged in successfully', $user['id']);
            
            $this->flashSuccess('Welcome back, ' . htmlspecialchars($user['username']) . '!');
            
            // Redirect to intended page or home
            $redirectTo = $_SESSION['intended_url'] ?? '/';
            unset($_SESSION['intended_url']);
            $this->redirect($redirectTo);
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->flashError('An error occurred during login. Please try again.');
            $this->redirect('/login');
        }
    }
    
    /**
     * Show registration form
     */
    public function showRegister() {
        // Redirect if already logged in
        if ($this->currentUser) {
            $this->redirect('/');
        }
        
        $this->view('auth/register', [
            'csrf_token' => $this->generateCsrf(),
            'errors' => $this->flash('errors'),
            'old_input' => $this->flash('old_input'),
            'supported_languages' => $GLOBALS['SUPPORTED_STORY_LANGUAGES']
        ]);
    }
    
    /**
     * Handle registration form submission
     */
    public function register() {
        try {
            // Rate limiting for registration attempts
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            try {
                $this->security->applyRateLimit('register_' . $ipAddress, 3, 3600); // 3 attempts per hour
            } catch (Exception $e) {
                $this->flashError('Too many registration attempts from this IP address. Please try again in an hour.');
                $this->redirect('/register');
            }
            
            // Validate CSRF token
            $this->validateCsrf();
            
            // Prepare input data
            $input = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'password_confirmation' => $_POST['password_confirmation'] ?? '',
                'full_name' => $_POST['full_name'] ?? '',
                'language_preference' => $_POST['language_preference'] ?? 'en',
                'agree_terms' => isset($_POST['agree_terms'])
            ];
            
            // Enhanced validation rules
            $rules = [
                'username' => 'required|username|unique:users,username',
                'email' => 'required|email|max:254|unique:users,email',
                'password' => 'required|password|confirmed',
                'password_confirmation' => 'required',
                'full_name' => 'max:100',
                'language_preference' => 'required|in:' . implode(',', array_keys($GLOBALS['SUPPORTED_STORY_LANGUAGES'])),
                'agree_terms' => 'required'
            ];
            
            $messages = [
                'username.required' => 'Username is required.',
                'username.unique' => 'This username is already taken.',
                'email.required' => 'Email address is required.',
                'email.unique' => 'This email address is already registered.',
                'password.required' => 'Password is required.',
                'password_confirmation.required' => 'Password confirmation is required.',
                'agree_terms.required' => 'You must agree to the terms and conditions.'
            ];
            
            $validatedData = $this->getValidatedData($input, $rules, $messages);
            
            if (!$validatedData) {
                $this->flashValidationErrors($this->validator->getErrors());
                $this->redirect('/register');
            }
            
            // Create user with validated data
            $userId = $this->createUser($validatedData);
            
            if (!$userId) {
                $this->flashError('Failed to create account. Please try again.');
                $this->redirect('/register');
            }
            
            // Send verification email
            $this->sendVerificationEmail($userId, $validatedData['email']);
            
            // Log registration
            $this->logActivity('register', 'User registered successfully', $userId);
            
            $this->flashSuccess('Registration successful! Please check your email to verify your account.');
            $this->redirect('/login');
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->flashError('An error occurred during registration. Please try again.');
            $this->redirect('/register');
        }
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        if ($this->currentUser) {
            // Log logout activity
            $this->logActivity('logout', 'User logged out', $this->currentUser['id']);
            
            // Destroy session data
            $this->destroyUserSession();
        }
        
        $this->flash('success', 'You have been logged out successfully.');
        $this->redirect('/');
    }
    
    /**
     * Show forgot password form
     */
    public function showForgotPassword() {
        if ($this->currentUser) {
            $this->redirect('/');
        }
        
        $this->view('auth/forgot-password', [
            'csrf_token' => $this->generateCsrf(),
            'errors' => $this->flash('errors'),
            'success' => $this->flash('success')
        ]);
    }
    
    /**
     * Handle forgot password form submission
     */
    public function forgotPassword() {
        try {
            $this->validateCsrf();
            
            $email = $this->sanitize($_POST['email'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('errors', ['email' => 'Please enter a valid email address.']);
                $this->redirect('/forgot-password');
            }
            
            $user = $this->db->fetch("SELECT id, email, username FROM users WHERE email = ?", [$email]);
            
            if ($user) {
                $this->sendPasswordResetEmail($user);
            }
            
            // Always show success message for security
            $this->flash('success', 'If an account with that email exists, a password reset link has been sent.');
            $this->redirect('/forgot-password');
            
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $this->flash('errors', ['general' => 'An error occurred. Please try again.']);
            $this->redirect('/forgot-password');
        }
    }
    
    /**
     * Show reset password form
     */
    public function showResetPassword($token) {
        if ($this->currentUser) {
            $this->redirect('/');
        }
        
        // Validate token
        $tokenData = $this->db->fetch(
            "SELECT * FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()",
            [$token]
        );
        
        if (!$tokenData) {
            $this->flash('errors', ['general' => 'Invalid or expired password reset token.']);
            $this->redirect('/forgot-password');
        }
        
        $this->view('auth/reset-password', [
            'csrf_token' => $this->generateCsrf(),
            'token' => $token,
            'errors' => $this->flash('errors')
        ]);
    }
    
    /**
     * Handle reset password form submission
     */
    public function resetPassword() {
        try {
            $this->validateCsrf();
            
            $input = [
                'token' => $this->sanitize($_POST['token'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'password_confirmation' => $_POST['password_confirmation'] ?? ''
            ];
            
            $errors = $this->validate($input, [
                'token' => 'required',
                'password' => 'required|min:8',
                'password_confirmation' => 'required'
            ]);
            
            if ($input['password'] !== $input['password_confirmation']) {
                $errors['password_confirmation'] = 'Password confirmation does not match.';
            }
            
            if (!empty($errors)) {
                $this->flash('errors', $errors);
                $this->redirect('/reset-password/' . $input['token']);
            }
            
            // Validate token
            $tokenData = $this->db->fetch(
                "SELECT * FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()",
                [$input['token']]
            );
            
            if (!$tokenData) {
                $this->flash('errors', ['general' => 'Invalid or expired password reset token.']);
                $this->redirect('/forgot-password');
            }
            
            // Update password
            $hashedPassword = password_hash($input['password'], PASSWORD_ARGON2ID);
            $this->db->execute(
                "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?",
                [$hashedPassword, $tokenData['user_id']]
            );
            
            // Delete used token
            $this->db->execute("DELETE FROM password_reset_tokens WHERE token = ?", [$input['token']]);
            
            // Log password reset
            $this->logActivity('password_reset', 'Password reset successfully', $tokenData['user_id']);
            
            $this->flash('success', 'Your password has been reset successfully. You can now log in.');
            $this->redirect('/login');
            
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            $this->flash('errors', ['general' => 'An error occurred. Please try again.']);
            $this->redirect('/forgot-password');
        }
    }
    
    /**
     * Validate registration input
     */
    private function validateRegistration($input) {
        $errors = [];
        
        // Username validation
        if (empty($input['username'])) {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($input['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters long.';
        } elseif (strlen($input['username']) > 50) {
            $errors['username'] = 'Username must not exceed 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
            $errors['username'] = 'Username can only contain letters, numbers, and underscores.';
        }
        
        // Email validation
        if (empty($input['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        
        // Password validation
        $minLength = $GLOBALS['CONFIG']['security']['password_min_length'];
        if (empty($input['password'])) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($input['password']) < $minLength) {
            $errors['password'] = "Password must be at least {$minLength} characters long.";
        } elseif ($input['password'] !== $input['password_confirmation']) {
            $errors['password_confirmation'] = 'Password confirmation does not match.';
        }
        
        // Full name validation
        if (!empty($input['full_name']) && strlen($input['full_name']) > 100) {
            $errors['full_name'] = 'Full name must not exceed 100 characters.';
        }
        
        // Language preference validation
        if (!array_key_exists($input['language_preference'], $GLOBALS['SUPPORTED_STORY_LANGUAGES'])) {
            $errors['language_preference'] = 'Invalid language preference.';
        }
        
        // Terms agreement validation
        if (!$input['agree_terms']) {
            $errors['agree_terms'] = 'You must agree to the terms and conditions.';
        }
        
        return $errors;
    }
    
    /**
     * Authenticate user with email and password
     */
    private function authenticateUser($email, $password) {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ? AND role != 'banned'",
            [$email]
        );
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return null;
    }
    
    /**
     * Create user session
     */
    private function createUserSession($user, $rememberMe = false) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in_at'] = time();
        
        // Set remember me cookie if requested
        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            
            // Store token in database
            $this->db->execute(
                "UPDATE users SET remember_token = ? WHERE id = ?",
                [hash('sha256', $token), $user['id']]
            );
        }
    }
    
    /**
     * Destroy user session
     */
    private function destroyUserSession() {
        // Clear remember me cookie and token
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            
            if ($this->currentUser) {
                $this->db->execute(
                    "UPDATE users SET remember_token = NULL WHERE id = ?",
                    [$this->currentUser['id']]
                );
            }
        }
        
        // Clear session data
        session_unset();
        session_destroy();
        
        // Start new session
        session_start();
        session_regenerate_id(true);
    }
    
    /**
     * Create new user
     */
    private function createUser($input) {
        $hashedPassword = password_hash($input['password'], PASSWORD_ARGON2ID);
        
        return $this->db->insert('users', [
            'username' => $input['username'],
            'email' => $input['email'],
            'password_hash' => $hashedPassword,
            'full_name' => $input['full_name'],
            'role' => 'user',
            'language_preference' => $input['language_preference'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Send email verification email
     */
    private function sendVerificationEmail($userId, $email) {
        $token = bin2hex(random_bytes(32));
        
        // Store verification token
        $this->db->insert('email_verification_tokens', [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600) // 1 hour
        ]);
        
        // In a real application, you would send an actual email here
        // For now, we'll just log it
        error_log("Verification email would be sent to {$email} with token: {$token}");
    }
    
    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail($user) {
        $token = bin2hex(random_bytes(32));
        
        // Clean up old tokens for this user
        $this->db->execute("DELETE FROM password_reset_tokens WHERE user_id = ?", [$user['id']]);
        
        // Store reset token
        $this->db->insert('password_reset_tokens', [
            'user_id' => $user['id'],
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600) // 1 hour
        ]);
        
        // In a real application, you would send an actual email here
        // For now, we'll just log it
        error_log("Password reset email would be sent to {$user['email']} with token: {$token}");
    }
}
