<?php

/**
 * SessionManager - A comprehensive and secure session management class
 * 
 * This class provides methods for session handling, debugging, and displaying
 * session information in various formats with multilingual support and security features.
 */
class SessionManager {
  
  private $text;
  private $isDebugMode;
  private $allowedDebugIPs;
  private $maxSessionLifetime;
  private $sessionTimeout;
  private $csrfToken;
  private $sessionStarted = false;
  
  /**
   * Constructor
   * 
   * @param array $text Language translations array
   * @param bool $debugMode Enable debug mode
   * @param array $allowedDebugIPs IPs allowed to see debug info
   * @param int $sessionTimeout Session timeout in seconds
   */
  public function __construct($text = [], $debugMode = false, $allowedDebugIPs = [], $sessionTimeout = 1800) {
    $this->text = $text;
    $this->isDebugMode = $debugMode;
    $this->allowedDebugIPs = $allowedDebugIPs;
    $this->sessionTimeout = $sessionTimeout;
    $this->maxSessionLifetime = ini_get('session.gc_maxlifetime');
    
    // Configure secure session settings BEFORE starting session
    $this->configureSecureSession();
    
    // Start session if not already started
    $this->startSession();
    
    // Initialize security measures
    $this->initializeSecurity();
  }
  
  /**
   * Configure secure session settings
   */
  private function configureSecureSession() {
    // Only configure if session is not active
    if (session_status() === PHP_SESSION_NONE) {
      // Set secure session configuration
      ini_set('session.cookie_httponly', 1);
      ini_set('session.cookie_secure', $this->isHttps());
      ini_set('session.cookie_samesite', 'Strict');
      ini_set('session.use_strict_mode', 1);
      ini_set('session.use_only_cookies', 1);
      ini_set('session.use_trans_sid', 0);
      ini_set('session.gc_maxlifetime', $this->sessionTimeout);
      
      // Set session name to something non-default
      session_name('SECURE_SESSION_ID');
      
      // Set session cookie parameters
      session_set_cookie_params([
        'lifetime' => 0, // Session cookie
        'path' => '/',
        'domain' => '', // Current domain
        'secure' => $this->isHttps(),
        'httponly' => true,
        'samesite' => 'Strict'
      ]);
    }
  }
  
  /**
   * Start session safely
   */
  private function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
      if (session_start()) {
        $this->sessionStarted = true;
      } else {
        throw new Exception('Failed to start session');
      }
    } else {
      $this->sessionStarted = true;
    }
  }
  
  /**
   * Initialize security measures
   */
  private function initializeSecurity() {
    if (!$this->sessionStarted) {
      return;
    }
    
    // Check for session hijacking
    $this->checkSessionHijacking();
    
    // Check session timeout
    $this->checkSessionTimeout();
    
    // Generate CSRF token
    $this->generateCSRFToken();
    
    // Regenerate session ID periodically
    $this->periodicSessionRegeneration();
  }
  
  /**
   * Check if connection is HTTPS
   * 
   * @return bool
   */
  private function isHttps() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
  }
  
  /**
   * Check for session hijacking attempts
   */
  private function checkSessionHijacking() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ipAddress = $this->getClientIP();
    
    if (isset($_SESSION['_security'])) {
      $storedUserAgent = $_SESSION['_security']['user_agent'] ?? '';
      $storedIP = $_SESSION['_security']['ip_address'] ?? '';
      
      // Check for user agent mismatch
      if ($storedUserAgent !== $userAgent) {
        $this->handleSecurityViolation('User agent mismatch');
        return;
      }
      
      // Check for IP address change (optional - can be disabled for mobile users)
      if ($this->shouldCheckIP() && $storedIP !== $ipAddress) {
        $this->handleSecurityViolation('IP address mismatch');
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
    if (isset($_SESSION['_security']['last_activity'])) {
      $timeSinceLastActivity = time() - $_SESSION['_security']['last_activity'];
      
      if ($timeSinceLastActivity > $this->sessionTimeout) {
        $this->handleSecurityViolation('Session timeout');
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
      $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    $this->csrfToken = $_SESSION['_csrf_token'];
  }
  
  /**
   * Get CSRF token
   * 
   * @return string
   */
  public function getCSRFToken() {
    return $this->csrfToken;
  }
  
  /**
   * Validate CSRF token
   * 
   * @param string $token
   * @return bool
   */
  public function validateCSRFToken($token) {
    return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
  }
  
  /**
   * Periodic session regeneration
   */
  private function periodicSessionRegeneration() {
    $regenerationInterval = 300; // 5 minutes
    
    if (!isset($_SESSION['_security']['last_regeneration'])) {
      $_SESSION['_security']['last_regeneration'] = time();
    }
    
    $timeSinceRegeneration = time() - $_SESSION['_security']['last_regeneration'];
    
    if ($timeSinceRegeneration > $regenerationInterval) {
      session_regenerate_id(true);
      $_SESSION['_security']['last_regeneration'] = time();
    }
  }
  
  /**
   * Handle security violations
   * 
   * @param string $reason
   */
  private function handleSecurityViolation($reason) {
    // Log security violation
    $this->logSecurityViolation($reason);
    
    // Destroy session
    $this->destroySession();
    
    // Redirect to login or show error
    $this->handleSecurityResponse();
  }
  
  /**
   * Log security violations
   * 
   * @param string $reason
   */
  private function logSecurityViolation($reason) {
    $logEntry = [
      'timestamp' => date('Y-m-d H:i:s'),
      'ip_address' => $this->getClientIP(),
      'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
      'session_id' => session_id(),
      'reason' => $reason,
      'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
    ];
    
    // Create logs directory if it doesn't exist
    $logDir = APP_DIR . '/logs';
    if (!is_dir($logDir)) {
      mkdir($logDir, 0755, true);
    }
    
    // Log to file
    $logFile = $logDir . '/security_violations.log';
    error_log(json_encode($logEntry) . "\n", 3, $logFile);
  }
  
  /**
   * Handle security response
   */
  private function handleSecurityResponse() {
    // In production, redirect to login page
    // For now, just show error message
    http_response_code(403);
    die('Security violation detected. Session terminated.');
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
    
    // If no specific IPs are configured, allow debug for localhost only
    if (empty($this->allowedDebugIPs)) {
      $clientIP = $this->getClientIP();
      return in_array($clientIP, ['127.0.0.1', '::1', 'localhost']);
    }
    
    // Check if client IP is in allowed list
    return in_array($this->getClientIP(), $this->allowedDebugIPs);
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
    $text = isset($this->text[$key]) ? $this->text[$key] : $fallback;
    return $this->sanitizeOutput($text);
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
    // Filter sensitive information
    return [
      'lifetime' => $params['lifetime'],
      'path' => $params['path'],
      'domain' => $params['domain'],
      'secure' => $params['secure'],
      'httponly' => $params['httponly'],
      'samesite' => $params['samesite'] ?? 'not set'
    ];
  }
  
  /**
   * Get session data (filtered)
   * 
   * @return array
   */
  public function getSessionData() {
    $sessionData = $_SESSION;
    
    // Remove sensitive data from display
    $sensitiveKeys = ['_security', '_csrf_token', 'password', 'token', 'secret'];
    foreach ($sensitiveKeys as $key) {
      if (isset($sessionData[$key])) {
        $sessionData[$key] = '[FILTERED]';
      }
    }
    
    return $sessionData;
  }
  
  /**
   * Check if session is empty
   * 
   * @return bool
   */
  public function isSessionEmpty() {
    $userData = $_SESSION;
    // Remove system keys for empty check
    unset($userData['_security'], $userData['_csrf_token']);
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
    // Validate key
    if (!is_string($key) || empty($key)) {
      return false;
    }
    
    // Prevent overwriting system keys
    if (strpos($key, '_') === 0) {
      return false;
    }
    
    // Check if key exists and overwrite is not allowed
    if (!$allowOverwrite && isset($_SESSION[$key])) {
      return false;
    }
    
    // Sanitize value if it's a string
    if (is_string($value)) {
      $value = trim($value);
    }
    
    $_SESSION[$key] = $value;
    return true;
  }
  
  /**
   * Get session variable
   * 
   * @param string $key
   * @param mixed $default Default value if key not found
   * @return mixed
   */
  public function getSession($key, $default = null) {
    // Prevent access to system keys
    if (strpos($key, '_') === 0) {
      return $default;
    }
    
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
  }
  
  /**
   * Remove session variable
   * 
   * @param string $key
   * @return bool
   */
  public function removeSession($key) {
    // Prevent removal of system keys
    if (strpos($key, '_') === 0) {
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
  }
  
  /**
   * Destroy session safely
   */
  public function destroySession() {
    // Clear session data
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
      );
    }
    
    // Destroy session
    session_destroy();
  }
  
  /**
   * Regenerate session ID securely
   * 
   * @param bool $deleteOldSession Delete old session data
   */
  public function regenerateSessionId($deleteOldSession = true) {
    session_regenerate_id($deleteOldSession);
    $_SESSION['_security']['last_regeneration'] = time();
  }
  
  /**
   * Display session information as simple var_dump (debug only)
   */
  public function displaySessionVarDump() {
    if (!$this->isDebugAllowed()) {
      echo '<p>Debug information not available.</p>';
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
      echo '<p>Debug information not available.</p>';
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
      echo '<p>Debug information not available.</p>';
      return;
    }
    
    echo '<div class="session-json">';
    echo '<h3>' . $this->getText('session_json', 'Session JSON') . '</h3>';
    echo '<p><strong>' . $this->getText('session_id', 'Session ID') . ':</strong> ' . $this->getSessionId() . '</p>';
    echo '<p><strong>' . $this->getText('session_data', 'Session Data') . ':</strong></p>';
    echo '<pre>' . $this->sanitizeOutput(json_encode($this->getSessionData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
    echo '</div>';
  }
  
  /**
   * Display complete session debug information
   */
  public function displaySessionDebug() {
    if (!$this->isDebugAllowed()) {
      echo '<p>Debug information not available.</p>';
      return;
    }
    
    echo '<div class="session-debug">';
    echo '<h3>' . $this->getText('session_debug', 'Session Debug') . '</h3>';
    
    echo '<p><strong>' . $this->getText('session_status', 'Session Status') . ':</strong> ' . $this->getSessionStatus() . '</p>';
    echo '<p><strong>' . $this->getText('session_id', 'Session ID') . ':</strong> ' . $this->getSessionId() . '</p>';
    echo '<p><strong>' . $this->getText('session_name', 'Session Name') . ':</strong> ' . $this->getSessionName() . '</p>';
    
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
      echo '<p>Debug information not available.</p>';
      return;
    }
    
    echo '<div class="session-table">';
    echo '<h3>' . $this->getText('session_table', 'Session Table') . '</h3>';
    
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr><th>' . $this->getText('session_key', 'Key') . '</th><th>' . $this->getText('session_value', 'Value') . '</th><th>' . $this->getText('session_type', 'Type') . '</th></tr>';
    
    $sessionData = $this->getSessionData();
    
    if (!empty($sessionData)) {
      foreach ($sessionData as $key => $value) {
        echo '<tr>';
        echo '<td>' . $this->sanitizeOutput($key) . '</td>';
        echo '<td>' . $this->sanitizeOutput($value) . '</td>';
        echo '<td>' . $this->sanitizeOutput(gettype($value)) . '</td>';
        echo '</tr>';
      }
    } else {
      echo '<tr><td colspan="3"><em>' . $this->getText('session_empty', 'Session is empty') . '</em></td></tr>';
    }
    
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
    echo '<li><strong>' . $this->getText('session_data_size', 'Data Size') . ':</strong> ' . $sessionSize . ' bytes</li>';
    echo '<li><strong>' . $this->getText('session_max_lifetime', 'Max Lifetime') . ':</strong> ' . $this->maxSessionLifetime . ' seconds</li>';
    echo '<li><strong>' . $this->getText('session_cookie_lifetime', 'Cookie Lifetime') . ':</strong> ' . ini_get('session.cookie_lifetime') . ' seconds</li>';
    echo '<li><strong>' . $this->getText('session_security_level', 'Security Level') . ':</strong> ' . ($this->isHttps() ? 'HTTPS' : 'HTTP') . '</li>';
    echo '</ul>';
    
    echo '</div>';
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
      'security_level' => $this->isHttps() ? 'HTTPS' : 'HTTP'
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
    
    return file_put_contents($filename, json_encode($sessionInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
  }
}

?>