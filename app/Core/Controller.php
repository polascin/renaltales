<?php
/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */

class Controller {
    protected $db;
    protected $currentUser;
    protected $language;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadCurrentUser();
        $this->language = new Language();
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
        $lang = $this->language->getCurrentLanguage();
        $supportedLanguages = $GLOBALS['SUPPORTED_STORY_LANGUAGES'];
        
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
        $lang = $this->language->getCurrentLanguage();
        $supportedLanguages = $GLOBALS['SUPPORTED_STORY_LANGUAGES'];
        
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

    protected function validateCsrf() {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        
        if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $this->forbidden('Invalid CSRF token');
        }
    }

    protected function generateCsrf() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = ucfirst($field) . ' must be a valid email address';
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = (int)$matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = ucfirst($field) . " must be at least {$min} characters";
                }
            }
            
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $max = (int)$matches[1];
                if (strlen($value) > $max) {
                    $errors[$field] = ucfirst($field) . " must not exceed {$max} characters";
                }
            }
        }
        
        return $errors;
    }

    protected function flash($key, $message = null) {
        if ($message === null) {
            $message = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        
        $_SESSION['flash'][$key] = $message;
    }

    protected function notFound() {
        http_response_code(404);
        $this->view('errors/404');
        exit;
    }

    protected function forbidden($message = 'Access denied') {
        http_response_code(403);
        $this->view('errors/403', ['message' => $message]);
        exit;
    }

    protected function error($message = 'An error occurred', $status = 500) {
        http_response_code($status);
        $this->view('errors/error', ['message' => $message]);
        exit;
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
