<?php
/**
 * Security Class
 * Handles security measures, rate limiting, CSRF protection, and other security features
 */

class Security {
    private $db;
    private $config;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->config = $GLOBALS['CONFIG'];
    }

    public function setSecurityHeaders() {
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
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src 'self' data: https:; " .
               "font-src 'self'; " .
               "connect-src 'self'; " .
               "frame-ancestors 'none';";
        header("Content-Security-Policy: {$csp}");
        
        // HSTS (only for HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    public function preventClickjacking() {
        header('X-Frame-Options: DENY');
    }

    public function applyRateLimit($identifier, $limit = null, $window = null) {
        $limit = $limit ?? $this->config['rate_limit']['requests'];
        $window = $window ?? $this->config['rate_limit']['window'];
        
        $now = time();
        $windowStart = $now - $window;
        
        // Clean old entries
        $this->db->delete(
            'rate_limits',
            'created_at < ?',
            [$windowStart]
        );
        
        // Count requests in current window
        $count = $this->db->count(
            'rate_limits',
            'identifier = ? AND created_at >= ?',
            [$identifier, $windowStart]
        );
        
        if ($count >= $limit) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Rate limit exceeded']);
            exit;
        }
        
        // Record this request
        $this->db->insert('rate_limits', [
            'identifier' => $identifier,
            'created_at' => $now
        ]);
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3          // 3 threads
        ]);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    public function encryptData($data, $key = null) {
        $key = $key ?? $this->config['security']['encryption_key'];
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public function decryptData($encryptedData, $key = null) {
        $key = $key ?? $this->config['security']['encryption_key'];
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    public function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Convert special characters to HTML entities
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $this->generateSecureToken();
        }
        return $_SESSION['csrf_token'];
    }

    public function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isSecurePassword($password) {
        $minLength = $this->config['security']['password_min_length'];
        
        if (strlen($password) < $minLength) {
            return false;
        }
        
        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Check for at least one digit
        if (!preg_match('/\d/', $password)) {
            return false;
        }
        
        // Check for at least one special character
        if (!preg_match('/[^a-zA-Z\d]/', $password)) {
            return false;
        }
        
        return true;
    }

    public function checkLoginAttempts($identifier) {
        $attempts = $this->db->count(
            'login_attempts',
            'identifier = ? AND created_at > ?',
            [$identifier, date('Y-m-d H:i:s', time() - $this->config['security']['login_lockout_time'])]
        );
        
        if ($attempts >= $this->config['security']['max_login_attempts']) {
            throw new Exception('Too many login attempts. Please try again later.');
        }
    }

    public function recordLoginAttempt($identifier, $success = false) {
        $this->db->insert('login_attempts', [
            'identifier' => $identifier,
            'success' => $success ? 1 : 0,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Clean old attempts
        $this->db->delete(
            'login_attempts',
            'created_at < ?',
            [date('Y-m-d H:i:s', time() - ($this->config['security']['login_lockout_time'] * 2))]
        );
    }

    public function detectSuspiciousActivity($userId, $action) {
        // Simple detection based on frequency of actions
        $recentActions = $this->db->count(
            'activity_logs',
            'user_id = ? AND action = ? AND created_at > ?',
            [$userId, $action, date('Y-m-d H:i:s', time() - 300)] // Last 5 minutes
        );
        
        $threshold = [
            'login' => 3,
            'password_reset' => 2,
            'story_create' => 5,
            'comment_create' => 10
        ];
        
        $limit = $threshold[$action] ?? 20;
        
        if ($recentActions >= $limit) {
            $this->logSecurityEvent('suspicious_activity', [
                'user_id' => $userId,
                'action' => $action,
                'count' => $recentActions
            ]);
            
            return true;
        }
        
        return false;
    }

    public function logSecurityEvent($event, $data) {
        $this->db->insert('security_logs', [
            'event' => $event,
            'data' => json_encode($data),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function validateFileUpload($file, $allowedTypes = null) {
        $allowedTypes = $allowedTypes ?? array_merge($this->config['uploads']['allowed_image_types'], $this->config['uploads']['allowed_document_types']);
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new Exception('No file uploaded');
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }
        
        // Check file size
        if ($file['size'] > $this->config['uploads']['max_file_size']) {
            throw new Exception('File size exceeds maximum allowed size');
        }
        
        // Check file type
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed');
        }
        
        // Additional security checks for images
        if (in_array($extension, $this->config['uploads']['allowed_image_types'])) {
            $imageInfo = getimagesize($file['tmp_name']);
            if (!$imageInfo) {
                throw new Exception('Invalid image file');
            }
        }
        
        return true;
    }

    public function sanitizeFilename($filename) {
        // Remove path information
        $filename = basename($filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Prevent directory traversal
        $filename = str_replace(['..', '/', '\\'], '', $filename);
        
        // Limit length
        if (strlen($filename) > 255) {
            $filename = substr($filename, 0, 255);
        }
        
        return $filename;
    }

    public function isBot() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $botPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i'
        ];
        
        foreach ($botPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        
        return false;
    }

    public function generateJWT($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->config['security']['jwt_secret'], true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    public function verifyJWT($jwt) {
        $parts = explode('.', $jwt);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        $signature = str_replace(['-', '_'], ['+', '/'], $parts[2]);
        
        $expectedSignature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $this->config['security']['jwt_secret'], true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));
        
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }
        
        return json_decode($payload, true);
    }
}
