<?php
/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */

require_once ROOT_PATH . '/src/Validation/Validator.php';
require_once ROOT_PATH . '/src/Core/FlashMessages.php';
require_once ROOT_PATH . '/src/Security/CSRFProtection.php';
require_once ROOT_PATH . '/src/Core/LanguageManager.php';
require_once ROOT_PATH . '/src/Core/Config.php';
require_once ROOT_PATH . '/src/Core/Exceptions/HttpException.php';
require_once ROOT_PATH . '/src/Core/Exceptions/NotFoundException.php';
require_once ROOT_PATH . '/src/Core/Exceptions/ForbiddenException.php';

use RenalTales\Validation\Validator;
use RenalTales\Core\FlashMessages;
use RenalTales\Security\CSRFProtection;
use RenalTales\Core\LanguageManager;
use RenalTales\Core\Config;

class Controller {
    protected $db;
    protected $currentUser;
    protected $languageManager;
    protected $validator;
    protected $security;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadCurrentUser();
        
        // Initialize language manager
        $config = new Config(ROOT_PATH . '/config/config.php');
        $this->languageManager = new LanguageManager($config);
        $this->languageManager->initialize();
        
        $this->validator = new Validator();
        $this->security = new Security();
        
        // Initialize flash message cleanup
        FlashMessages::cleanup();
    }

    protected function loadCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            $this->currentUser = $this->db->fetch(
                "SELECT * FROM users WHERE id = ? AND active = 1",
                [$_SESSION['user_id']]
            );
        }
    }

    protected function requireAuth() {
        if (!$this->currentUser) {
            $this->redirect('/login');
        }
    }

    protected function requireRole($role) {
        $this->requireAuth();
        
        if ($this->currentUser['role'] !== $role && $this->currentUser['role'] !== 'admin') {
            $this->forbidden();
        }
    }

    protected function requirePermission($permission) {
        $this->requireAuth();
        
        if (!$this->hasPermission($permission)) {
            $this->forbidden();
        }
    }

    protected function hasPermission($permission) {
        if (!$this->currentUser) {
            return false;
        }

        $userRole = $this->currentUser['role'];
        
        // Admin has all permissions
        if ($userRole === 'admin') {
            return true;
        }

        $roles = $GLOBALS['USER_ROLES'];
        if (isset($roles[$userRole])) {
            return in_array($permission, $roles[$userRole]['permissions']);
        }

        return false;
    }

    protected function view($template, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Make common variables available to all views
        $currentUser = $this->currentUser;
        $lang = $this->languageManager->getCurrentLanguage();
        $supportedLanguages = $this->languageManager->getSupportedLanguagesWithNames();
        
        // Add security and validation helpers
        $csrf_token = CSRFProtection::getToken();
        $flash_messages = FlashMessages::getAll();
        
        // Create translation array for backward compatibility with existing views
        $t = [];
        $translationKeys = [
            'nav.home', 'nav.stories', 'nav.about', 'nav.contact', 'nav.login', 'nav.register', 'nav.logout', 
            'nav.profile', 'nav.categories', 'nav.write_story', 'nav.moderation', 'nav.manage_users', 'nav.statistics',
            'btn.save', 'btn.cancel', 'btn.delete', 'btn.edit', 'btn.view', 'btn.submit', 'btn.search', 'btn.back',
            'auth.login.title', 'auth.login.subtitle', 'auth.login.email', 'auth.login.email_placeholder',
            'auth.login.password', 'auth.login.password_placeholder', 'auth.login.remember', 'auth.login.forgot',
            'auth.login.button', 'auth.login.no_account', 'auth.login.register_link',
            'auth.register.title', 'auth.register.name', 'auth.register.email', 'auth.register.password',
            'auth.register.confirm', 'auth.register.agree',
            'auth.forgot_password.title', 'auth.forgot_password.subtitle', 'auth.forgot_password.button', 
            'auth.forgot_password.remember', 'auth.forgot_password.back_to_login',
            'form.email.label', 'form.email.placeholder',
            'stories.title', 'stories.create', 'stories.read_more', 'stories.category', 'stories.author',
            'stories.published', 'stories.tags',
            'form.required', 'form.email.invalid', 'form.password.min', 'form.password.mismatch',
            'msg.success.saved', 'msg.success.deleted', 'msg.error.generic', 'msg.error.unauthorized', 'msg.error.not_found',
            'footer.copyright', 'footer.privacy', 'footer.terms', 'footer.support'
        ];
        
        foreach ($translationKeys as $key) {
            $t[$key] = $this->languageManager->translate($key);
        }
        
        // Security helper functions
        $escape = function($value) {
            return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        };
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = VIEWS_PATH . '/' . ltrim($template, '/') . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }
        
        include $viewFile;
        
        // Get the content and clean the buffer
        $content = ob_get_clean();
        
        // Auto-inject CSRF tokens into forms
        $content = CSRFProtection::injectTokensIntoForms($content);
        
        // Include layout if not an AJAX request
        if (!$this->isAjax()) {
            $this->renderLayout($content, $data);
        } else {
            echo $content;
        }
    }

    protected function renderLayout($content, $data = []) {
        extract($data);
        
        $currentUser = $this->currentUser;
        $lang = $this->languageManager->getCurrentLanguage();
        $supportedLanguages = $this->languageManager->getSupportedLanguagesWithNames();
        
        $layoutFile = VIEWS_PATH . '/layout/main.php';
        
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url, $status = 302) {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    protected function back() {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referrer);
    }

    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Enhanced CSRF validation using new system
     */
    protected function validateCsrf() {
        if (!CSRFProtection::validateRequest()) {
            $this->forbidden('Invalid or expired CSRF token');
        }
    }

    /**
     * Generate CSRF token using new system
     */
    protected function generateCsrf() {
        return CSRFProtection::getToken();
    }

    /**
     * Enhanced sanitization for output safety
     */
    protected function sanitize($input) {
        return $this->validator->sanitizeInput($input);
    }

    /**
     * Enhanced validation using new validation system
     */
    protected function validate($data, $rules, $messages = []) {
        $isValid = $this->validator->validate($data, $rules, $messages);
        
        if (!$isValid) {
            return $this->validator->getErrors();
        }
        
        return [];
    }

    /**
     * Get sanitized and validated data
     */
    protected function getValidatedData($data, $rules, $messages = []) {
        if ($this->validator->validate($data, $rules, $messages)) {
            return $this->validator->getSanitized();
        }
        
        return null;
    }

    /**
     * Enhanced flash messaging using new system
     */
    protected function flash($type, $message = null) {
        if ($message === null) {
            // Get messages of specific type
            return FlashMessages::get($type);
        }
        
        // Add message of specific type
        FlashMessages::add($type, $message);
    }

    /**
     * Add success flash message
     */
    protected function flashSuccess($message) {
        FlashMessages::success($message);
    }

    /**
     * Add error flash message
     */
    protected function flashError($message) {
        FlashMessages::error($message);
    }

    /**
     * Add warning flash message
     */
    protected function flashWarning($message) {
        FlashMessages::warning($message);
    }

    /**
     * Add info flash message
     */
    protected function flashInfo($message) {
        FlashMessages::info($message);
    }

    /**
     * Add validation errors as flash messages
     */
    protected function flashValidationErrors($errors) {
        FlashMessages::validationErrors($errors);
    }

    protected function notFound($message = 'Page not found') {
        throw new \RenalTales\Core\Exceptions\NotFoundException($message);
    }

    protected function forbidden($message = 'Access denied') {
        throw new \RenalTales\Core\Exceptions\ForbiddenException($message);
    }

    protected function error($message = 'An error occurred', $status = 500) {
        throw new \RenalTales\Core\Exceptions\HttpException($message, $status);
    }

    protected function paginate($query, $params, $page, $perPage) {
        $offset = ($page - 1) * $perPage;
        
        // Count total records
        $countQuery = preg_replace('/SELECT.*?FROM/i', 'SELECT COUNT(*) as total FROM', $query);
        $total = $this->db->fetch($countQuery, $params)['total'];
        
        // Add limit and offset to original query
        $query .= " LIMIT {$perPage} OFFSET {$offset}";
        $items = $this->db->fetchAll($query, $params);
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page,
            'per_page' => $perPage,
            'has_next' => $page < ceil($total / $perPage),
            'has_prev' => $page > 1
        ];
    }

    protected function logActivity($action, $description, $userId = null) {
        $userId = $userId ?? ($this->currentUser['id'] ?? null);
        
        $this->db->insert('activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
