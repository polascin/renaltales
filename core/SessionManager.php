<?php

/**
 * SessionManager - A comprehensive and secure session management class
 * 
 * This class provides methods for session handling, debugging, and displaying
 * session information in various formats with multilingual support and security features.
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */
class SessionManager {
  
  private $text;
  private $isDebugMode;
  private $allowedDebugIPs;
  private $maxSessionLifetime;
  private $sessionTimeout;
  private $csrfToken;
  private $sessionStarted = false;
  private $securityViolationHandled = false; // Prevent infinite loops
  private $logDirectory;
  
  /**
   * Constructor
   * 
   * @param array $text Language translations array
   * @param bool $debugMode Enable debug mode
   * @param array $allowedDebugIPs IPs allowed to see debug info
   * @param int $sessionTimeout Session timeout in seconds
   * @throws Exception if session initialization fails
   */
  public function __construct($text = [], $debugMode = false, $allowedDebugIPs = [], $sessionTimeout = 1800) {
    $this->text = is_array($text) ? $text : [];
    $this->isDebugMode = (bool)$debugMode;
    $this->allowedDebugIPs = is_array($allowedDebugIPs) ? $allowedDebugIPs : [];
    $this->sessionTimeout = max(300, min(7200, (int)$sessionTimeout)); // Between 5 minutes and 2 hours
    $this->maxSessionLifetime = (int)ini_get('session.gc_maxlifetime');
    
    // Set log directory
    $this->logDirectory = $this->getLogDirectory();
    
    try {
      // Configure secure session settings BEFORE starting session
      $this->configureSecureSession();
      
      // Start session if not already started
      $this->startSession();
      
      // Initialize security measures
      $this->initializeSecurity();
    } catch (Exception $e) {
      error_log('SessionManager initialization failed: ' . $e->getMessage());
      throw new Exception($this->getText('session_init_failed', 'Failed to initialize session manager') . ': ' . $e->getMessage());
    }
  }
  
  /**
   * Get log directory with fallback
   * 
   * @return string
   */
  private function getLogDirectory() {
    if (defined('APP_DIR')) {
      return APP_DIR . '/logs';
    }
    
    // Fallback to temporary directory
    $tempDir = sys_get_temp_dir();
    return $tempDir . '/renaltales_logs';
  }
  
  /**
   * Configure secure session settings
   * 
   * @throws Exception if session configuration fails
   */
  private function configureSecureSession() {
    // Only configure if session is not active
    if (session_status() === PHP_SESSION_NONE) {
      try {
        // Set secure session configuration
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $this->isHttps() ? '1' : '0');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.gc_maxlifetime', (string)$this->sessionTimeout);
        
        // Set session name to something non-default
        if (!session_name('SECURE_SESSION_ID')) {
          throw new Exception($this->getText('session_name_failed', 'Failed to set session name'));
        }
        
        // Set session cookie parameters
        $cookieParams = [
          'lifetime' => 0, // Session cookie
          'path' => '/',
          'domain' => '', // Current domain
          'secure' => $this->isHttps(),
          'httponly' => true,
          'samesite' => 'Strict'
        ];
        
        if (!session_set_cookie_params($cookieParams)) {
          throw new Exception($this->getText('session_cookie_params_failed', 'Failed to set cookie parameters'));
        }
      } catch (Exception $e) {
        throw new Exception($this->getText('session_config_failed', 'Session configuration failed') . ': ' . $e->getMessage());
      }
    }
  }
  
  /**
   * Start session safely
   * 
   * @throws Exception if session start fails
   */
  private function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
      if (!session_start()) {
        throw new Exception($this->getText('session_start_failed', 'Failed to start session'));
      }
      $this->sessionStarted = true;
    } elseif (session_status() === PHP_SESSION_ACTIVE) {
      $this->sessionStarted = true;
    } else {
      throw new Exception($this->getText('sessions_disabled', 'Sessions are disabled'));
    }
  }
  
  /**
   * Initialize security measures
   */
  private function initializeSecurity() {
    if (!$this->sessionStarted || $this->securityViolationHandled) {
      return;
    }
    
    try {
      // Check for session hijacking
      $this->checkSessionHijacking();
      
      // Check session timeout
      $this->checkSessionTimeout();
      
      // Generate CSRF token
      $this->generateCSRFToken();
      
      // Regenerate session ID periodically
      $this->periodicSessionRegeneration();
    } catch (Exception $e) {
      error_log('Security initialization failed: ' . $e->getMessage());
      // Don't re-throw here to prevent breaking the application
    }
  }
  
  /**
   * Check if connection is HTTPS
   * 
   * @return bool
   */
  private function isHttps() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
  }
  
  /**
   * Check for session hijacking attempts
   */
  private function checkSessionHijacking() {
    if ($this->securityViolationHandled) {
      return;
    }
    
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ipAddress = $this->getClientIP();
    
    if (isset($_SESSION['_security'])) {
      $storedUserAgent = $_SESSION['_security']['user_agent'] ?? '';
      $storedIP = $_SESSION['_security']['ip_address'] ?? '';
      
      // Check for user agent mismatch
      if ($storedUserAgent !== $userAgent) {
        $this->handleSecurityViolation($this->getText('security_user_agent_mismatch', 'User agent mismatch'));
        return;
      }
      
      // Check for IP address change (optional - can be disabled for mobile users)
      if ($this->shouldCheckIP() && $storedIP !== $ipAddress) {
        $this->handleSecurityViolation($this->getText('security_ip_mismatch', 'IP address mismatch'));
        return;
      }
    } else {
      // First time - store security info
      $_SESSION['_security'] = [
        'user_agent' => $userAgent,
        'ip_address' => $ipAddress,
        'created_at' => time(),
        'last_activity' => time()
      ];
    }
  }
  
  /**
   * Get client IP address safely
   * 
   * @return string
   */
  private function getClientIP() {
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
        // Handle comma-separated IPs
        if (strpos($ip, ',') !== false) {
          $ip = trim(explode(',', $ip)[0]);
        }
        // Validate IP
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
          return $ip;
        }
      }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  }
  
  /**
   * Check if IP checking should be enabled
   * 
   * @return bool
   */
  private function shouldCheckIP() {
    // Disable IP checking for mobile networks or specific use cases
    return !isset($_SESSION['_security']['mobile_user']);
  }
  
  /**
   * Check session timeout
   */
  private function checkSessionTimeout() {
    if ($this->securityViolationHandled) {
      return;
    }
    
    if (isset($_SESSION['_security']['last_activity'])) {
      $timeSinceLastActivity = time() - $_SESSION['_security']['last_activity'];
      
      if ($timeSinceLastActivity > $this->sessionTimeout) {
        $this->handleSecurityViolation($this->getText('security_session_timeout', 'Session timeout'));
        return;
      }
    }
    
    // Update last activity
    $_SESSION['_security']['last_activity'] = time();
  }
  
  /**
   * Generate CSRF token
   */
  private function generateCSRFToken() {
    if (!isset($_SESSION['_csrf_token'])) {
      try {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
      } catch (Exception $e) {
        // Fallback for systems without random_bytes
        $_SESSION['_csrf_token'] = hash('sha256', uniqid(mt_rand(), true));
      }
    }
    $this->csrfToken = $_SESSION['_csrf_token'];
  }
  
  /**
   * Get CSRF token
   * 
   * @return string
   */
  public function getCSRFToken() {
    return $this->csrfToken ?? '';
  }
  
  /**
   * Validate CSRF token
   * 
   * @param string $token
   * @return bool
   */
  public function validateCSRFToken($token) {
    if (!isset($_SESSION['_csrf_token']) || empty($token)) {
      return false;
    }
    
    return hash_equals($_SESSION['_csrf_token'], $token);
  }
  
  /**
   * Periodic session regeneration
   */
  private function periodicSessionRegeneration() {
    $regenerationInterval = 300; // 5 minutes
    
    if (!isset($_SESSION['_security']['last_regeneration'])) {
      $_SESSION['_security']['last_regeneration'] = time();
      return;
    }
    
    $timeSinceRegeneration = time() - $_SESSION['_security']['last_regeneration'];
    
    if ($timeSinceRegeneration > $regenerationInterval) {
      try {
        session_regenerate_id(true);
        $_SESSION['_security']['last_regeneration'] = time();
      } catch (Exception $e) {
        error_log('Session regeneration failed: ' . $e->getMessage());
      }
    }
  }
  
  /**
   * Handle security response with multilingual support
   */
  private function handleSecurityResponse() {
    // Set appropriate headers
    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    
    // Get multilingual error message
    $errorMessage = $this->getText('security_violation_detected', 'Security violation detected. Session terminated.');
    $accessDenied = $this->getText('access_denied', 'Access Denied');
    
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . htmlspecialchars($accessDenied, ENT_QUOTES, 'UTF-8') . '</title>';
    echo '<style>body{font-family:Arial,sans-serif;margin:50px;text-align:center;}.error{color:#d9534f;border:1px solid #d9534f;padding:20px;border-radius:5px;display:inline-block;}</style>';
    echo '</head>';
    echo '<body>';
    echo '<div class="error">';
    echo '<h1>' . htmlspecialchars($accessDenied, ENT_QUOTES, 'UTF-8') . '</h1>';
    echo '<p>' . htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
    exit;
  }
  
  /**
   * Handle security violations with multilingual logging
   * 
   * @param string $reason
   */
  private function handleSecurityViolation($reason) {
    // Prevent infinite loops
    if ($this->securityViolationHandled) {
      return;
    }
    
    $this->securityViolationHandled = true;
    
    // Get translated reason
    $translatedReason = $this->getSecurityReasonTranslation($reason);
    
    // Log security violation
    $this->logSecurityViolation($translatedReason);
    
    // Destroy session
    $this->destroySession();
    
    // Handle security response
    $this->handleSecurityResponse();
  }
  
  /**
   * Get translated security reason
   * 
   * @param string $reason
   * @return string
   */
  private function getSecurityReasonTranslation($reason) {
    $translations = [
        'User agent mismatch' => $this->getText('security_user_agent_mismatch', 'User agent mismatch'),
        'IP address mismatch' => $this->getText('security_ip_mismatch', 'IP address mismatch'),
        'Session timeout' => $this->getText('security_session_timeout', 'Session timeout'),
    ];
    
    return $translations[$reason] ?? $reason;
  }
  
  /**
   * Log security violations safely
   * 
   * @param string $reason
   */
  private function logSecurityViolation($reason) {
    try {
      // Sanitize reason to prevent log injection
      $sanitizedReason = preg_replace('/[\r\n\t]/', ' ', $reason);
      $sanitizedReason = substr($sanitizedReason, 0, 255); // Limit length
      
      $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_address' => $this->getClientIP(),
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        'session_id' => session_id(),
        'reason' => $sanitizedReason,
        'request_uri' => substr($_SERVER['REQUEST_URI'] ?? '', 0, 255)
      ];
      
      // Create logs directory if it doesn't exist
      if (!is_dir($this->logDirectory)) {
        if (!mkdir($this->logDirectory, 0755, true)) {
          error_log('Failed to create log directory: ' . $this->logDirectory);
          return;
        }
      }
      
      // Log to file with proper error handling
      $logFile = $this->logDirectory . '/security_violations.log';
      $logData = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
      
      if (file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX) === false) {
        error_log('Failed to write security violation log');
      }
    } catch (Exception $e) {
      error_log('Security logging failed: ' . $e->getMessage());
    }
  }
  
  /**
   * Check if debug mode is allowed for current user
   * 
   * @return bool
   */
  private function isDebugAllowed() {
    if (!$this->isDebugMode) {
      return false;
    }
    
    $clientIP = $this->getClientIP();
    
    // If no specific IPs are configured, allow debug for localhost only
    if (empty($this->allowedDebugIPs)) {
      return in_array($clientIP, ['127.0.0.1', '::1']) || $clientIP === 'localhost';
    }
    
    // Check if client IP is in allowed list
    return in_array($clientIP, $this->allowedDebugIPs);
  }
  
  /**
   * Sanitize output for display
   * 
   * @param mixed $data
   * @return string
   */
  private function sanitizeOutput($data) {
    if (is_string($data)) {
      return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    if (is_array($data) || is_object($data)) {
      return htmlspecialchars(print_r($data, true), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    return htmlspecialchars((string)$data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
  }
  
  /**
   * Get translated text with fallback
   * 
   * @param string $key Translation key
   * @param string $fallback Fallback text if translation not found
   * @return string
   */
  private function getText($key, $fallback = '') {
    return isset($this->text[$key]) ? $this->text[$key] : $fallback;
  }
  
  /**
   * Check if session manager is properly initialized
   * 
   * @return bool
   */
  public function isInitialized() {
    return $this->sessionStarted && !empty($this->csrfToken);
  }
  
  /**
   * Get session status as string
   * 
   * @return string
   */
  public function getSessionStatus() {
    switch (session_status()) {
      case PHP_SESSION_DISABLED:
        return $this->getText('session_disabled', 'Sessions are disabled');
      case PHP_SESSION_NONE:
        return $this->getText('session_none', 'No session started');
      case PHP_SESSION_ACTIVE:
        return $this->getText('session_active', 'Session is active');
      default:
        return $this->getText('session_unknown', 'Unknown session status');
    }
  }
  
  /**
   * Get session ID (masked for security)
   * 
   * @return string
   */
  public function getSessionId() {
    $sessionId = session_id();
    
    if (empty($sessionId)) {
      return $this->getText('session_id_none', 'No session ID');
    }
    
    // Mask session ID for security (show only first 8 and last 4 characters)
    if (strlen($sessionId) > 12) {
      return substr($sessionId, 0, 8) . '****' . substr($sessionId, -4);
    }
    
    return $sessionId;
  }
  
  /**
   * Get session name
   * 
   * @return string
   */
  public function getSessionName() {
    return session_name();
  }
  
  /**
   * Get session cookie parameters (filtered)
   * 
   * @return array
   */
  public function getSessionCookieParams() {
    $params = session_get_cookie_params();
    
    return [
      'lifetime' => $params['lifetime'] ?? 0,
      'path' => $params['path'] ?? '/',
      'domain' => $params['domain'] ?? '',
      'secure' => $params['secure'] ?? false,
      'httponly' => $params['httponly'] ?? false,
      'samesite' => $params['samesite'] ?? $this->getText('not_set', 'not set')
    ];
  }
  
  /**
   * Get session data (filtered for security)
   * 
   * @return array
   */
  public function getSessionData() {
    if (!$this->sessionStarted) {
      return [];
    }
    
    $sessionData = $_SESSION;
    
    // Remove sensitive data from display
    $sensitiveKeys = ['_security', '_csrf_token', 'password', 'token', 'secret', 'api_key'];
    foreach ($sensitiveKeys as $key) {
      if (isset($sessionData[$key])) {
        $sessionData[$key] = $this->getText('filtered', '[FILTERED]');
      }
    }
    
    return $sessionData;
  }
  
  /**
   * Check if session is empty (excluding system data)
   * 
   * @return bool
   */
  public function isSessionEmpty() {
    if (!$this->sessionStarted) {
      return true;
    }
    
    $userData = $_SESSION;
    // Remove system keys for empty check
    $systemKeys = ['_security', '_csrf_token'];
    foreach ($systemKeys as $key) {
      unset($userData[$key]);
    }
    
    return empty($userData);
  }
  
  /**
   * Set session variable with validation
   * 
   * @param string $key
   * @param mixed $value
   * @param bool $allowOverwrite
   * @return bool
   */
  public function setSession($key, $value, $allowOverwrite = true) {
    if (!$this->sessionStarted) {
      return false;
    }
    
    // Validate key
    if (!is_string($key) || empty($key)) {
      return false;
    }
    
    // Prevent setting system keys
    if (strpos($key, '_') === 0) {
      return false;
    }
    
    // Check key length and characters
    if (strlen($key) > 64 || !preg_match('/^[a-zA-Z0-9_.-]+$/', $key)) {
      return false;
    }
    
    // Check if key exists and overwrite is not allowed
    if (!$allowOverwrite && isset($_SESSION[$key])) {
      return false;
    }
    
    // Sanitize value if it's a string
    if (is_string($value)) {
      $value = trim($value);
      // Limit string length
      if (strlen($value) > 10000) {
        return false;
      }
    }
    
    $_SESSION[$key] = $value;
    return true;
  }
  
  /**
   * Get session variable safely
   * 
   * @param string $key
   * @param mixed $default Default value if key not found
   * @return mixed
   */
  public function getSession($key, $default = null) {
    if (!$this->sessionStarted) {
      return $default;
    }
    
    // Prevent access to system keys
    if (!is_string($key) || strpos($key, '_') === 0) {
      return $default;
    }
    
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
  }
  
  /**
   * Remove session variable safely
   * 
   * @param string $key
   * @return bool
   */
  public function removeSession($key) {
    if (!$this->sessionStarted) {
      return false;
    }
    
    // Prevent removal of system keys
    if (!is_string($key) || strpos($key, '_') === 0) {
      return false;
    }
    
    if (isset($_SESSION[$key])) {
      unset($_SESSION[$key]);
      return true;
    }
    
    return false;
  }
  
  /**
   * Clear all session data (except system data)
   */
  public function clearSession() {
    if (!$this->sessionStarted) {
      return false;
    }
    
    $systemKeys = ['_security', '_csrf_token'];
    $systemData = [];
    
    // Preserve system data
    foreach ($systemKeys as $key) {
      if (isset($_SESSION[$key])) {
        $systemData[$key] = $_SESSION[$key];
      }
    }
    
    // Clear all session data
    $_SESSION = [];
    
    // Restore system data
    foreach ($systemData as $key => $value) {
      $_SESSION[$key] = $value;
    }
    
    return true;
  }
  
  /**
   * Destroy session safely
   */
  public function destroySession() {
    if (!$this->sessionStarted) {
      return;
    }
    
    // Clear session data
    $_SESSION = [];
    
    // Delete session cookie if cookies are used
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params['path'], 
        $params['domain'],
        $params['secure'], 
        $params['httponly']
      );
    }
    
    // Destroy session
    try {
      session_destroy();
      $this->sessionStarted = false;
    } catch (Exception $e) {
      error_log('Session destruction failed: ' . $e->getMessage());
    }
  }
  
  /**
   * Regenerate session ID securely
   * 
   * @param bool $deleteOldSession Delete old session data
   * @return bool
   */
  public function regenerateSessionId($deleteOldSession = true) {
    if (!$this->sessionStarted) {
      return false;
    }
    
    try {
      if (session_regenerate_id($deleteOldSession)) {
        $_SESSION['_security']['last_regeneration'] = time();
        return true;
      }
    } catch (Exception $e) {
      error_log('Session ID regeneration failed: ' . $e->getMessage());
    }
    
    return false;
  }
  
  /**
   * Display session information as simple var_dump (debug only)
   */
  public function displaySessionVarDump() {
    if (!$this->isDebugAllowed()) {
      echo '<p>' . $this->getText('debug_not_allowed', 'Debug information is not available for security reasons.') . '</p>';
      return;
    }
    
    echo '<div class="session-vardump">';
    echo '<h3>' . $this->getText('session_vardump', 'Session Var Dump') . '</h3>';
    echo '<pre>';
    var_dump($this->getSessionData());
    echo '</pre>';
    echo '</div>';
  }
  
  /**
   * Display session information as formatted HTML
   */
  public function displaySessionFormatted() {
    if (!$this->isDebugAllowed()) {
      echo '<p>' . $this->getText('debug_not_allowed', 'Debug information is not available for security reasons.') . '</p>';
      return;
    }
    
    echo '<div class="session-formatted">';
    echo '<h3>' . $this->getText('session_information', 'Session Information') . '</h3>';
    
    $sessionData = $this->getSessionData();
    
    if (!empty($sessionData)) {
      echo '<ul>';
      foreach ($sessionData as $key => $value) {
        echo '<li>';
        echo '<strong>' . $this->sanitizeOutput($key) . ':</strong> ';
        echo $this->sanitizeOutput($value);
        echo '</li>';
      }
      echo '</ul>';
    } else {
      echo '<p><em>' . $this->getText('session_empty', 'Session is empty') . '</em></p>';
    }
    echo '</div>';
  }
  
  /**
   * Display session information as JSON
   */
  public function displaySessionJson() {
    if (!$this->isDebugAllowed()) {
      echo '<p>' . $this->getText('debug_not_allowed', 'Debug information is not available for security reasons.') . '</p>';
      return;
    }
    
    echo '<div class="session-json">';
    echo '<h3>' . $this->getText('session_json', 'Session JSON') . '</h3>';
    echo '<p><strong>' . $this->getText('session_id', 'Session ID') . ':</strong> ' . $this->getSessionId() . '</p>';
    echo '<p><strong>' . $this->getText('session_data', 'Session Data') . ':</strong></p>';
    
    $jsonData = json_encode($this->getSessionData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo '<pre>' . $this->sanitizeOutput($jsonData) . '</pre>';
    echo '</div>';
  }
  
  /**
   * Display complete session debug information
   */
  public function displaySessionDebug() {
    if (!$this->isDebugAllowed()) {
      echo '<p>' . $this->getText('debug_not_allowed', 'Debug information is not available for security reasons.') . '</p>';
      return;
    }
    
    echo '<div class="session-debug">';
    echo '<h3>' . $this->getText('session_debug', 'Session Debug') . '</h3>';
    
    echo '<p><strong>' . $this->getText('session_status', 'Session Status') . ':</strong> ' . $this->sanitizeOutput($this->getSessionStatus()) . '</p>';
    echo '<p><strong>' . $this->getText('session_id', 'Session ID') . ':</strong> ' . $this->sanitizeOutput($this->getSessionId()) . '</p>';
    echo '<p><strong>' . $this->getText('session_name', 'Session Name') . ':</strong> ' . $this->sanitizeOutput($this->getSessionName()) . '</p>';
    
    echo '<p><strong>' . $this->getText('session_cookie_params', 'Cookie Parameters') . ':</strong></p>';
    echo '<pre>' . $this->sanitizeOutput(print_r($this->getSessionCookieParams(), true)) . '</pre>';
    
    echo '<p><strong>' . $this->getText('session_data', 'Session Data') . ':</strong></p>';
    $sessionData = $this->getSessionData();
    if (!empty($sessionData)) {
      echo '<pre>' . $this->sanitizeOutput(print_r($sessionData, true)) . '</pre>';
    } else {
      echo '<p><em>' . $this->getText('session_empty', 'Session is empty') . '</em></p>';
    }
    
    echo '</div>';
  }
  
  /**
   * Display session information in table format
   */
  public function displaySessionTable() {
    if (!$this->isDebugAllowed()) {
      echo '<p>' . $this->getText('debug_not_allowed', 'Debug information is not available for security reasons.') . '</p>';
      return;
    }
    
    echo '<div class="session-table">';
    echo '<h3>' . $this->getText('session_table', 'Session Table') . '</h3>';
    
    echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
    echo '<thead>';
    echo '<tr style="background-color: #f5f5f5;">';
    echo '<th>' . $this->getText('session_key', 'Key') . '</th>';
    echo '<th>' . $this->getText('session_value', 'Value') . '</th>';
    echo '<th>' . $this->getText('session_type', 'Type') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    $sessionData = $this->getSessionData();
    
    if (!empty($sessionData)) {
      foreach ($sessionData as $key => $value) {
        echo '<tr>';
        echo '<td>' . $this->sanitizeOutput($key) . '</td>';
        echo '<td style="max-width: 300px; word-wrap: break-word;">' . $this->sanitizeOutput($value) . '</td>';
        echo '<td>' . $this->sanitizeOutput(gettype($value)) . '</td>';
        echo '</tr>';
      }
    } else {
      echo '<tr><td colspan="3" style="text-align: center;"><em>' . $this->getText('session_empty', 'Session is empty') . '</em></td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
  }
  
  /**
   * Display session statistics
   */
  public function displaySessionStats() {
    echo '<div class="session-stats">';
    echo '<h3>' . $this->getText('session_statistics', 'Session Statistics') . '</h3>';
    
    $sessionData = $this->getSessionData();
    $sessionSize = strlen(serialize($sessionData));
    $sessionCount = count($sessionData);
    
    echo '<ul>';
    echo '<li><strong>' . $this->getText('session_variables_count', 'Variables Count') . ':</strong> ' . $sessionCount . '</li>';
    echo '<li><strong>' . $this->getText('session_data_size', 'Data Size') . ':</strong> ' . $this->formatBytes($sessionSize) . '</li>';
    echo '<li><strong>' . $this->getText('session_max_lifetime', 'Max Lifetime') . ':</strong> ' . $this->maxSessionLifetime . ' ' . $this->getText('seconds', 'seconds') . '</li>';
    echo '<li><strong>' . $this->getText('session_cookie_lifetime', 'Cookie Lifetime') . ':</strong> ' . ini_get('session.cookie_lifetime') . ' ' . $this->getText('seconds', 'seconds') . '</li>';
    echo '<li><strong>' . $this->getText('session_security_level', 'Security Level') . ':</strong> ' . ($this->isHttps() ? 'HTTPS' : 'HTTP') . '</li>';
    echo '<li><strong>' . $this->getText('session_save_path', 'Save Path') . ':</strong> ' . $this->sanitizeOutput(session_save_path()) . '</li>';
    echo '</ul>';
    
    echo '</div>';
  }
  
  /**
   * Format bytes to human readable format
   * 
   * @param int $bytes
   * @return string
   */
  private function formatBytes($bytes) {
    if ($bytes >= 1024 * 1024) {
      return round($bytes / (1024 * 1024), 2) . ' ' . $this->getText('mb', 'MB');
    } elseif ($bytes >= 1024) {
      return round($bytes / 1024, 2) . ' ' . $this->getText('kb', 'KB');
    } else {
      return $bytes . ' ' . $this->getText('bytes', 'bytes');
    }
  }
  
  /**
   * Display comprehensive session information
   */
  public function displaySessionComprehensive() {
    echo '<div class="session-comprehensive">';
    echo '<h2>' . $this->getText('session_comprehensive', 'Comprehensive Session Information') . '</h2>';
    
    // Basic info (always shown)
    $this->displaySessionStats();
    echo '<hr>';
    
    // Debug info (only if allowed)
    if ($this->isDebugAllowed()) {
      $this->displaySessionDebug();
      echo '<hr>';
      $this->displaySessionTable();
      echo '<hr>';
      $this->displaySessionJson();
    } else {
      echo '<p>' . $this->getText('debug_not_allowed', 'Debug information is not available for security reasons.') . '</p>';
    }
    
    echo '</div>';
  }
  
  /**
   * Get session information as array (filtered)
   * 
   * @return array
   */
  public function getSessionInfo() {
    $sessionData = $this->getSessionData();
    
    return [
      'status' => $this->getSessionStatus(),
      'id' => $this->getSessionId(),
      'name' => $this->getSessionName(),
      'data' => $sessionData,
      'cookie_params' => $this->getSessionCookieParams(),
      'is_empty' => empty($sessionData),
      'data_size' => strlen(serialize($sessionData)),
      'variables_count' => count($sessionData),
      'max_lifetime' => $this->maxSessionLifetime,
      'cookie_lifetime' => ini_get('session.cookie_lifetime'),
      'security_level' => $this->isHttps() ? 'HTTPS' : 'HTTP',
      'save_path' => session_save_path(),
      'is_initialized' => $this->isInitialized()
    ];
  }
  
  /**
   * Export session data to JSON file (with security checks)
   * 
   * @param string $filename
   * @return bool
   */
  public function exportSessionToJson($filename = 'session_export.json') {
    if (!$this->isDebugAllowed()) {
      return false;
    }
    
    $sessionInfo = $this->getSessionInfo();
    $sessionInfo['export_timestamp'] = date('Y-m-d H:i:s');
    $sessionInfo['export_ip'] = $this->getClientIP();
    
    // Ensure filename is safe
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    if (empty($filename)) {
      $filename = 'session_export.json';
    }
    
    try {
      return file_put_contents($filename, json_encode($sessionInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    } catch (Exception $e) {
      error_log('Session export failed: ' . $e->getMessage());
      return false;
    }
  }
}

?>