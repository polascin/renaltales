<?php

/**
 * Application - Main Application Class
 * 
 * A multilingual web application for sharing kidney disorder stories
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

/**
 * Application initialization class
 */
class Application {
  
  private $languageDetector;
  private $sessionManager;
  private $currentLanguage;
  private $currentLanguageName;
  private $text;
  private $appTitle;
  private $isInitialized = false;
  private $initializationError = null;
  
  /**
   * Constructor - Initialize the application
   */
  public function __construct() {
    try {
      $this->initializeBasicTexts();
      $this->initializeLanguageDetector();
      $this->loadLanguageTexts();
      $this->initializeSessionManager();
      $this->setApplicationTitle();
      $this->isInitialized = true;
    } catch (Exception $e) {
      $this->initializationError = $e;
      error_log('Application initialization failed: ' . $e->getMessage());
      // Don't re-throw here, let the render method handle it
    }
  }
  
  /**
   * Initialize basic fallback texts before language detection
   */
  private function initializeBasicTexts() {
    $this->text = [
      'app_title' => 'Renal Tales',
      'welcome' => 'Welcome',
      'application_error' => 'Application Error',
      'service_unavailable' => 'Service Temporarily Unavailable',
      'try_again_later' => 'Please try again later.',
      'error' => 'Error',
      'file' => 'File',
      'line' => 'Line',
      'stack_trace' => 'Stack Trace',
      'initialization_failed' => 'Application initialization failed',
      'current_language' => 'Current language',
      'debug_mode_enabled' => 'Debug mode is enabled.',
    ];
  }
  
  /**
   * Initialize language detector
   * 
   * @throws Exception if LanguageDetector class is not available
   */
  private function initializeLanguageDetector() {
    if (!class_exists('LanguageDetector')) {
      throw new Exception('LanguageDetector class is not available');
    }
    
    $this->languageDetector = new LanguageDetector();
    $this->currentLanguage = $this->languageDetector->detectLanguage();
    $this->currentLanguageName = $this->languageDetector->getLanguageName($this->currentLanguage);
    
    if (empty($this->currentLanguage)) {
      throw new Exception('Failed to detect current language');
    }
  }
  
  /**
   * Load language texts with fallback
   */
  private function loadLanguageTexts() {
    $baseTexts = $this->text; // Keep fallback texts
    
    // Load English as base language
    $englishLanguageFile = LANGUAGE_PATH . 'en.php';
    if (file_exists($englishLanguageFile)) {
      $englishTexts = require $englishLanguageFile;
      if (is_array($englishTexts)) {
        $this->text = array_merge($baseTexts, $englishTexts);
      }
    }
    
    // Load current language if different from English
    if ($this->currentLanguage !== 'en') {
      $languageFile = LANGUAGE_PATH . $this->currentLanguage . '.php';
      if (file_exists($languageFile)) {
        $currentLanguageTexts = require $languageFile;
        if (is_array($currentLanguageTexts)) {
          $this->text = array_merge($this->text, $currentLanguageTexts);
        }
      }
    }
  }
  
  /**
   * Initialize session manager
   * 
   * @throws Exception if SessionManager class is not available
   */
  private function initializeSessionManager() {
    if (!class_exists('SessionManager')) {
      throw new Exception('SessionManager class is not available');
    }
    
    $allowedDebugIPs = ['127.0.0.1', '::1']; // Add your IPs here
    $this->sessionManager = new SessionManager($this->text, DEBUG_MODE, $allowedDebugIPs);
    
    // Store current language in session
    $this->sessionManager->setSession('current_language', $this->currentLanguage);
    $this->sessionManager->setSession('language_change_time', time());
  }
  
  /**
   * Set application title
   */
  private function setApplicationTitle() {
    $this->appTitle = $this->getText('app_title', defined('APP_TITLE') ? APP_TITLE : 'Renal Tales');
  }
  
  /**
   * Get translated text with fallback (safe to call even during initialization)
   * 
   * @param string $key
   * @param string $fallback
   * @return string
   */
  private function getText($key, $fallback = '') {
    if (!is_array($this->text)) {
      return $fallback;
    }
    return isset($this->text[$key]) ? $this->text[$key] : $fallback;
  }
  
  /**
   * Safely get server variable
   * 
   * @param string $key
   * @param string $default
   * @return string
   */
  private function getServerVar($key, $default = 'N/A') {
    return isset($_SERVER[$key]) ? htmlspecialchars($_SERVER[$key], ENT_QUOTES, 'UTF-8') : $default;
  }
  
  /**
   * Check if application is properly initialized
   * 
   * @return bool
   */
  public function isInitialized() {
    return $this->isInitialized;
  }
  
  /**
   * Get initialization error if any
   * 
   * @return Exception|null
   */
  public function getInitializationError() {
    return $this->initializationError;
  }
  
  /**
   * Get current language
   * 
   * @return string
   */
  public function getCurrentLanguage() {
    return $this->currentLanguage ?? 'en';
  }
  
  /**
   * Get current language name
   * 
   * @return string
   */
  public function getCurrentLanguageName() {
    return $this->currentLanguageName ?? 'English';
  }
  
  /**
   * Get application title
   * 
   * @return string
   */
  public function getAppTitle() {
    return $this->appTitle ?? 'Renal Tales';
  }
  
  /**
   * Get session manager instance
   * 
   * @return SessionManager|null
   */
  public function getSessionManager() {
    return $this->sessionManager;
  }
  
  /**
   * Get language detector instance
   * 
   * @return LanguageDetector|null
   */
  public function getLanguageDetector() {
    return $this->languageDetector;
  }
  
  /**
   * Get all text translations
   * 
   * @return array
   */
  public function getTexts() {
    return $this->text ?? [];
  }
  
  /**
   * Render language selector dropdown
   * 
   * @return string
   */
  private function renderLanguageSelector() {
    if (!$this->languageDetector) {
      return '<option value="en">English (en)</option>';
    }
    
    $selectableLanguages = $this->languageDetector->getSupportedLanguages();
    $html = '';
    
    foreach ($selectableLanguages as $lang) {
      $langFile = LANGUAGE_PATH . $lang . '.php';
      if (!file_exists($langFile)) continue;
      
      $langName = $this->languageDetector->getLanguageName($lang);
      $flagPath = $this->languageDetector->getFlagPath($lang);
      $selected = ($this->currentLanguage === $lang) ? ' selected' : '';
      
      $html .= '<option value="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '"' . $selected . ' data-flag="' . htmlspecialchars($flagPath, ENT_QUOTES, 'UTF-8') . '">';
      $html .= htmlspecialchars($langName, ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . ')';
      $html .= '</option>';
    }
    
    return $html;
  }
  
  /**
   * Render language flags
   * 
   * @return string
   */
  private function renderLanguageFlags() {
    if (!$this->languageDetector) {
      return '<p>Language selection not available</p>';
    }
    
    $selectableLanguages = $this->languageDetector->getSupportedLanguages();
    $html = '';
    
    foreach ($selectableLanguages as $lang) {
      $langFile = LANGUAGE_PATH . $lang . '.php';
      if (!file_exists($langFile)) continue;
      
      $langName = $this->languageDetector->getLanguageName($lang);
      $flagPath = $this->languageDetector->getFlagPath($lang);
      
      $html .= '<a href="?lang=' . urlencode($lang) . '" title="' . htmlspecialchars($langName, ENT_QUOTES, 'UTF-8') . '">';
      $html .= '<img src="' . htmlspecialchars($flagPath, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($langName, ENT_QUOTES, 'UTF-8') . '">';
      $html .= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8');
      $html .= '</a>';
    }
    
    return $html;
  }
  
  /**
   * Render service information
   * 
   * @return string
   */
  private function renderServiceInformation() {
    $html = '<div class="server-info">';
    $html .= '<h2>' . htmlspecialchars($this->getText('server_information', 'Server Information'), ENT_QUOTES, 'UTF-8') . '</h2>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('user_agent', 'User Agent'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $this->getServerVar('HTTP_USER_AGENT') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('ip_address', 'IP Address'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $this->getServerVar('REMOTE_ADDR') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('server_software', 'Server Software'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $this->getServerVar('SERVER_SOFTWARE') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('server_name', 'Server Name'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $this->getServerVar('SERVER_NAME') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('server_protocol', 'Server Protocol'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $this->getServerVar('SERVER_PROTOCOL') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('request_method', 'Request Method'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $this->getServerVar('REQUEST_METHOD') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('request_uri', 'Request URI'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $this->getServerVar('REQUEST_URI') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('query_string', 'Query String'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $this->getServerVar('QUERY_STRING') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('document_root', 'Document Root'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $this->getServerVar('DOCUMENT_ROOT') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('script_name', 'Script Name'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $this->getServerVar('SCRIPT_NAME') . '</p>';
    $html .= '</div>';
    
    return $html;
  }
  
  /**
   * Render application information
   * 
   * @return string
   */
  private function renderApplicationInformation() {
    $html = '<div class="app-info">';
    $html .= '<h2>' . htmlspecialchars($this->getText('application_information', 'Application Information'), ENT_QUOTES, 'UTF-8') . '</h2>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('app_title_item', 'Application Title'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($this->getAppTitle(), ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('current_language', 'Current Language'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($this->getCurrentLanguageName(), ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '<p><strong>' . htmlspecialchars($this->getText('current_language_code', 'Current Language Code'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($this->getCurrentLanguage(), ENT_QUOTES, 'UTF-8') . '</p>';
    
    if (defined('LANGUAGE_PATH')) {
      $html .= '<p><strong>' . htmlspecialchars($this->getText('current_language_file', 'Current Language File'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars(LANGUAGE_PATH . $this->getCurrentLanguage() . '.php', ENT_QUOTES, 'UTF-8') . '</p>';
    }
    
    if ($this->languageDetector) {
      $html .= '<p><strong>' . htmlspecialchars($this->getText('current_language_flag', 'Current Language Flag'), ENT_QUOTES, 'UTF-8') . ':</strong> ';
      $html .= '<img src="' . htmlspecialchars($this->languageDetector->getFlagPath($this->getCurrentLanguage()), ENT_QUOTES, 'UTF-8') . '" ';
      $html .= 'alt="' . htmlspecialchars($this->getText('current_language_flag_alt', 'Flag of the current language'), ENT_QUOTES, 'UTF-8') . '" class="flag"></p>';
    }
    
    $html .= '</div>';
    
    return $html;
  }
  
  /**
   * Render language selection section
   * 
   * @return string
   */
  private function renderLanguageSelection() {
    if (!$this->sessionManager) {
      return '<nav class="language-selector"><p>Language selection not available</p></nav>';
    }
    
    $csrfToken = $this->sessionManager->getCSRFToken();
    
    $html = '<nav class="language-selector">';
    $html .= '<ul>';
    $html .= '<li>';
    $html .= '<form method="get" action="">';
    $html .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '">';
    $html .= '<select name="lang" onchange="this.form.submit()">';
    $html .= $this->renderLanguageSelector();
    $html .= '</select>';
    $html .= '<noscript><input type="submit" value="' . htmlspecialchars($this->getText('change', 'Change'), ENT_QUOTES, 'UTF-8') . '"></noscript>';
    $html .= '<script src="assets/js/addFlags.js"></script>';
    $html .= '</form>';
    $html .= '</li>';
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
  }
  
  /**
   * Render header section
   * 
   * @return string
   */
  private function renderHeader() {
    $html = '<header class="main-header-container">';
    $html .= '<div class="left-section">';
    $html .= '<img src="assets/images/logos/logo.gif" alt="' . htmlspecialchars($this->getAppTitle(), ENT_QUOTES, 'UTF-8') . ' logo" class="logo">';
    $html .= '<h1>' . htmlspecialchars($this->getAppTitle(), ENT_QUOTES, 'UTF-8') . '</h1>';
    
    if (defined('APP_TITLE')) {
      $html .= '<h2>' . htmlspecialchars(APP_TITLE, ENT_QUOTES, 'UTF-8') . '</h2>';
    }
    
    $html .= '<h3>' . htmlspecialchars($this->getText('app_subtitle', 'A Multilingual WebApplication'), ENT_QUOTES, 'UTF-8') . '</h3>';
    $html .= '<h4>' . htmlspecialchars($this->getText('app_description', 'A web application for sharing tales and stories from the community of people with kidney disorders, including those on dialysis, and those who have had or are waiting for a renal transplant.'), ENT_QUOTES, 'UTF-8') . '</h4>';
    $html .= '<h5>' . htmlspecialchars($this->getText('app_version', 'Version 2025.v1.0test'), ENT_QUOTES, 'UTF-8') . '</h5>';
    $html .= '<h6>' . htmlspecialchars($this->getText('app_author', 'Lumpe Paskuden von Lumpenen aka Walter Kyo aka Walter Csoelle Kyo aka Lubomir Polascin'), ENT_QUOTES, 'UTF-8') . '</h6>';
    $html .= '</div>';
    $html .= '<div class="central-section">';
    $html .= '<p>' . htmlspecialchars($this->getText('datetime_placeholder', 'Tu bude zobrazený dátum, čas, vrátane podrobného internetového času @beat.'), ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '</div>';
    $html .= '<div class="right-section">';
    $html .= '<p>' . htmlspecialchars($this->getText('user_information', 'User information:'), ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '</div>';
    $html .= '</header>';
    
    return $html;
  }
  
  /**
   * Render main content section
   * 
   * @return string
   */
  private function renderMainContent() {
    $html = '<main>';
    
    // Language Selection Flags
    $html .= '<section class="language-selection-flags">';
    $html .= $this->renderLanguageFlags();
    $html .= '<p>' . htmlspecialchars($this->getText('current_language', 'Current language'), ENT_QUOTES, 'UTF-8') . ': <strong>' . htmlspecialchars($this->getCurrentLanguageName(), ENT_QUOTES, 'UTF-8') . '</strong>. </p>';
    $html .= '<p>' . htmlspecialchars($this->getText('welcome', 'Welcome'), ENT_QUOTES, 'UTF-8') . '! </p>';
    $html .= '</section>';
    
    $html .= '</main>';
    
    return $html;
  }

  /**
   * Render service information section (for debug mode)
   * 
   * @return string
   */
  private function renderServiceInformationSection() {
    $html = '<section class="service-information-section">';
    $html .= '<hr>';
    $html .= '<div class="debug-info">';
    $html .= '<p>' . htmlspecialchars($this->getText('debug_mode_enabled', 'Debug mode is enabled.'), ENT_QUOTES, 'UTF-8') . ' ';
    $html .= htmlspecialchars($this->getText('current_language', 'Current language'), ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars($this->getCurrentLanguage(), ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '</div>';
    
    $html .= '<h1>' . htmlspecialchars($this->getText('service_information', 'Service Information'), ENT_QUOTES, 'UTF-8') . '</h1>';
    
    // Session Information
    if ($this->sessionManager) {
      ob_start();
      $this->sessionManager->displaySessionComprehensive();
      $html .= ob_get_clean();
    } else {
      $html .= '<p>Session manager not available</p>';
    }
    $html .= '<hr>';
    
    // Server Information
    $html .= $this->renderServiceInformation();
    $html .= '<hr>';
    
    // Application Information
    $html .= $this->renderApplicationInformation();
    $html .= '<hr>';
    
    $html .= '</section>';
    
    return $html;
  }
  
  /**
   * Render footer section
   * 
   * @return string
   */
  private function renderFooter() {
    $html = '<footer>';
    $html .= '<p>&copy; ' . date('Y') . ' ' . htmlspecialchars($this->getText('footer_copyright', 'Ľubomír Polaščín'), ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '</footer>';
    
    return $html;
  }
  
  /**
   * Render JavaScript section
   * 
   * @return string
   */
  private function renderJavaScript() {
    if (!$this->sessionManager) {
      return '<script>console.log("Session manager not available");</script>';
    }
    
    $csrfToken = $this->sessionManager->getCSRFToken();
    
    $html = '<script>';
    $html .= 'document.addEventListener("DOMContentLoaded", function() {';
    $html .= 'const forms = document.querySelectorAll("form");';
    $html .= 'forms.forEach(function(form) {';
    $html .= 'if (!form.querySelector("input[name=\"csrf_token\"]")) {';
    $html .= 'const csrfInput = document.createElement("input");';
    $html .= 'csrfInput.type = "hidden";';
    $html .= 'csrfInput.name = "csrf_token";';
    $html .= 'csrfInput.value = "' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '";';
    $html .= 'form.appendChild(csrfInput);';
    $html .= '}';
    $html .= '});';
    $html .= '});';
    $html .= '</script>';
    
    return $html;
  }
  
  /**
   * Render the complete HTML page
   * 
   * @return string
   */
  public function render() {
    // Handle initialization errors
    if (!$this->isInitialized && $this->initializationError) {
      return $this->handleError($this->initializationError);
    }
    
    if (!$this->isInitialized) {
      return $this->renderErrorPage('Application not properly initialized');
    }
    
    $csrfToken = $this->sessionManager ? $this->sessionManager->getCSRFToken() : 'no-token';
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo htmlspecialchars($this->getCurrentLanguage(), ENT_QUOTES, 'UTF-8'); ?>">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
      <title><?php echo htmlspecialchars($this->getAppTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
      <link rel="stylesheet" href="assets/css/basic.css?v=<?php echo time(); ?>">
      <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    </head>
    <body>
      <?php echo $this->renderLanguageSelection(); ?>
      <?php echo $this->renderHeader(); ?>
      <?php echo $this->renderMainContent(); ?>
      <?php
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
          echo $this->renderServiceInformationSection();
        }
      ?>
      <?php echo $this->renderFooter(); ?>
      <?php echo $this->renderJavaScript(); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
  }
  
  /**
   * Render error page
   * 
   * @param string $message
   * @return string
   */
  private function renderErrorPage($message) {
    $html = '<!DOCTYPE html>';
    $html .= '<html lang="en">';
    $html .= '<head>';
    $html .= '<meta charset="utf-8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
    $html .= '<title>' . htmlspecialchars($this->getText('application_error', 'Application Error'), ENT_QUOTES, 'UTF-8') . '</title>';
    $html .= '<style>body{font-family:Arial,sans-serif;margin:50px;}.error{color:#d9534f;border:1px solid #d9534f;padding:20px;border-radius:5px;}</style>';
    $html .= '</head>';
    $html .= '<body>';
    $html .= '<div class="error">';
    $html .= '<h1>' . htmlspecialchars($this->getText('application_error', 'Application Error'), ENT_QUOTES, 'UTF-8') . '</h1>';
    $html .= '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '</div>';
    $html .= '</body>';
    $html .= '</html>';
    
    return $html;
  }
  
  /**
   * Handle application errors
   * 
   * @param Exception $e
   * @return string
   */
  public function handleError(Exception $e) {
    $isDebugMode = defined('DEBUG_MODE') && DEBUG_MODE;
    
    if ($isDebugMode) {
      $html = '<!DOCTYPE html>';
      $html .= '<html lang="en">';
      $html .= '<head>';
      $html .= '<meta charset="utf-8">';
      $html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
      $html .= '<title>' . htmlspecialchars($this->getText('application_error', 'Application Error'), ENT_QUOTES, 'UTF-8') . '</title>';
      $html .= '<style>body{font-family:Arial,sans-serif;margin:20px;}.error{color:#d9534f;border:1px solid #d9534f;padding:20px;border-radius:5px;margin-bottom:20px;}pre{background:#f5f5f5;padding:15px;border-radius:5px;overflow:auto;}</style>';
      $html .= '</head>';
      $html .= '<body>';
      $html .= '<div class="error">';
      $html .= '<h1>' . htmlspecialchars($this->getText('application_error', 'Application Error'), ENT_QUOTES, 'UTF-8') . '</h1>';
      $html .= '<p><strong>' . htmlspecialchars($this->getText('error', 'Error'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
      $html .= '<p><strong>' . htmlspecialchars($this->getText('file', 'File'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . '</p>';
      $html .= '<p><strong>' . htmlspecialchars($this->getText('line', 'Line'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . $e->getLine() . '</p>';
      $html .= '<h3>' . htmlspecialchars($this->getText('stack_trace', 'Stack Trace'), ENT_QUOTES, 'UTF-8') . '</h3>';
      $html .= '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
      $html .= '</div>';
      $html .= '</body>';
      $html .= '</html>';
    } else {
      $html = '<!DOCTYPE html>';
      $html .= '<html lang="en">';
      $html .= '<head>';
      $html .= '<meta charset="utf-8">';
      $html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
      $html .= '<title>' . htmlspecialchars($this->getText('service_unavailable', 'Service Temporarily Unavailable'), ENT_QUOTES, 'UTF-8') . '</title>';
      $html .= '<style>body{font-family:Arial,sans-serif;margin:50px;text-align:center;}.error{color:#d9534f;}</style>';
      $html .= '</head>';
      $html .= '<body>';
      $html .= '<div class="error">';
      $html .= '<h1>' . htmlspecialchars($this->getText('service_unavailable', 'Service Temporarily Unavailable'), ENT_QUOTES, 'UTF-8') . '</h1>';
      $html .= '<p>' . htmlspecialchars($this->getText('try_again_later', 'Please try again later.'), ENT_QUOTES, 'UTF-8') . '</p>';
      $html .= '</div>';
      $html .= '</body>';
      $html .= '</html>';
      
      error_log('Application Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    }
    
    return $html;
  }
}

?>