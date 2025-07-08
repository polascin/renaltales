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
  
  /**
   * Constructor - Initialize the application
   */
  public function __construct() {
    $this->initializeLanguageDetector();
    $this->loadLanguageTexts();
    $this->initializeSessionManager();
    $this->setApplicationTitle();
  }
  
  /**
   * Initialize language detector
   */
  private function initializeLanguageDetector() {
    $this->languageDetector = new LanguageDetector();
    $this->currentLanguage = $this->languageDetector->detectLanguage();
    $this->currentLanguageName = $this->languageDetector->getLanguageName($this->currentLanguage);
  }
  
  /**
   * Load language texts with fallback
   */
  private function loadLanguageTexts() {
    // Load English as base language
    $englishLanguageFile = LANGUAGE_PATH . 'en.php';
    if (file_exists($englishLanguageFile)) {
      $this->text = require_once $englishLanguageFile;
    } else {
      $this->text = [];
    }
    
    // Load current language if different from English
    if ($this->currentLanguage !== 'en') {
      $languageFile = LANGUAGE_PATH . $this->currentLanguage . '.php';
      if (file_exists($languageFile)) {
        $currentLanguageTexts = require_once $languageFile;
        $this->text = array_merge($this->text, $currentLanguageTexts);
      }
    }
  }
  
  /**
   * Initialize session manager
   */
  private function initializeSessionManager() {
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
    $this->appTitle = isset($this->text['app_title']) ? $this->text['app_title'] : APP_TITLE;
  }
  
  /**
   * Get translated text with fallback
   */
  private function getText($key, $fallback = '') {
    return isset($this->text[$key]) ? $this->text[$key] : $fallback;
  }
  
  /**
   * Safely get server variable
   */
  private function getServerVar($key, $default = 'N/A') {
    return isset($_SERVER[$key]) ? htmlspecialchars($_SERVER[$key]) : $default;
  }
  
  /**
   * Get current language
   * 
   * @return string
   */
  public function getCurrentLanguage() {
    return $this->currentLanguage;
  }
  
  /**
   * Get current language name
   * 
   * @return string
   */
  public function getCurrentLanguageName() {
    return $this->currentLanguageName;
  }
  
  /**
   * Get application title
   * 
   * @return string
   */
  public function getAppTitle() {
    return $this->appTitle;
  }
  
  /**
   * Get session manager instance
   * 
   * @return SessionManager
   */
  public function getSessionManager() {
    return $this->sessionManager;
  }
  
  /**
   * Get language detector instance
   * 
   * @return LanguageDetector
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
    return $this->text;
  }
  
  /**
   * Render language selector dropdown
   */
  private function renderLanguageSelector() {
    $selectableLanguages = $this->languageDetector->getSupportedLanguages();
    $html = '';
    
    foreach ($selectableLanguages as $lang) {
      $langFile = LANGUAGE_PATH . $lang . '.php';
      if (!file_exists($langFile)) continue;
      
      $langName = $this->languageDetector->getLanguageName($lang);
      $flagPath = $this->languageDetector->getFlagPath($lang);
      $selected = ($this->currentLanguage === $lang) ? ' selected' : '';
      
      $html .= '<option value="' . htmlspecialchars($lang) . '"' . $selected . ' data-flag="' . htmlspecialchars($flagPath) . '">';
      $html .= htmlspecialchars($langName) . ' (' . htmlspecialchars($lang) . ')';
      $html .= '</option>';
    }
    
    return $html;
  }
  
  /**
   * Render language flags
   */
  private function renderLanguageFlags() {
    $selectableLanguages = $this->languageDetector->getSupportedLanguages();
    $html = '';
    
    foreach ($selectableLanguages as $lang) {
      $langFile = LANGUAGE_PATH . $lang . '.php';
      if (!file_exists($langFile)) continue;
      
      $langName = $this->languageDetector->getLanguageName($lang);
      $flagPath = $this->languageDetector->getFlagPath($lang);
      
      $html .= '<a href="?lang=' . urlencode($lang) . '" title="' . htmlspecialchars($langName) . '">';
      $html .= '<img src="' . htmlspecialchars($flagPath) . '" alt="' . htmlspecialchars($langName) . '">';
      $html .= htmlspecialchars($lang);
      $html .= '</a>';
    }
    
    return $html;
  }
  
  /**
   * Render service information
   */
  private function renderServiceInformation() {
    $html = '<div class="server-info">';
    $html .= '<h2>' . $this->getText('server_information', 'Server Information') . '</h2>';
    $html .= '<p><strong>' . $this->getText('user_agent', 'User Agent') . ':</strong> ' . $this->getServerVar('HTTP_USER_AGENT') . '</p>';
    $html .= '<p><strong>' . $this->getText('ip_address', 'IP Address') . ':</strong> ' . $this->getServerVar('REMOTE_ADDR') . '</p>';
    $html .= '<p><strong>' . $this->getText('server_software', 'Server Software') . ':</strong> ' . $this->getServerVar('SERVER_SOFTWARE') . '</p>';
    $html .= '<p><strong>' . $this->getText('server_name', 'Server Name') . ':</strong> ' . $this->getServerVar('SERVER_NAME') . '</p>';
    $html .= '<p><strong>' . $this->getText('server_protocol', 'Server Protocol') . ':</strong> ' . $this->getServerVar('SERVER_PROTOCOL') . '</p>';
    $html .= '<p><strong>' . $this->getText('request_method', 'Request Method') . ':</strong> ' . $this->getServerVar('REQUEST_METHOD') . '</p>';
    $html .= '<p><strong>' . $this->getText('request_uri', 'Request URI') . ':</strong> ' . $this->getServerVar('REQUEST_URI') . '</p>';
    $html .= '<p><strong>' . $this->getText('query_string', 'Query String') . ':</strong> ' . $this->getServerVar('QUERY_STRING') . '</p>';
    $html .= '<p><strong>' . $this->getText('document_root', 'Document Root') . ':</strong> ' . $this->getServerVar('DOCUMENT_ROOT') . '</p>';
    $html .= '<p><strong>' . $this->getText('script_name', 'Script Name') . ':</strong> ' . $this->getServerVar('SCRIPT_NAME') . '</p>';
    $html .= '</div>';
    
    return $html;
  }
  
  /**
   * Render application information
   */
  private function renderApplicationInformation() {
    $html = '<div class="app-info">';
    $html .= '<h2>' . $this->getText('application_information', 'Application Information') . '</h2>';
    $html .= '<p><strong>' . $this->getText('app_title_item', 'Application Title') . ':</strong> ' . htmlspecialchars($this->appTitle) . '</p>';
    $html .= '<p><strong>' . $this->getText('current_language', 'Current Language') . ':</strong> ' . htmlspecialchars($this->currentLanguageName) . '</p>';
    $html .= '<p><strong>' . $this->getText('current_language_code', 'Current Language Code') . ':</strong> ' . htmlspecialchars($this->currentLanguage) . '</p>';
    $html .= '<p><strong>' . $this->getText('current_language_file', 'Current Language File') . ':</strong> ' . htmlspecialchars(LANGUAGE_PATH . $this->currentLanguage . '.php') . '</p>';
    $html .= '<p><strong>' . $this->getText('current_language_flag', 'Current Language Flag') . ':</strong> ';
    $html .= '<img src="' . htmlspecialchars($this->languageDetector->getFlagPath($this->currentLanguage)) . '" ';
    $html .= 'alt="' . htmlspecialchars($this->getText('current_language_flag_alt', 'Flag of the current language')) . '" class="flag"></p>';
    $html .= '</div>';
    
    return $html;
  }
  
  /**
   * Render navigation section
   */
  private function renderNavigation() {
    $csrfToken = $this->sessionManager->getCSRFToken();
    
    $html = '<nav class="language-selector">';
    $html .= '<ul>';
    $html .= '<li>';
    $html .= '<form method="get" action="">';
    $html .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken) . '">';
    $html .= '<select name="lang" onchange="this.form.submit()">';
    $html .= $this->renderLanguageSelector();
    $html .= '</select>';
    $html .= '<noscript><input type="submit" value="' . $this->getText('change', 'Change') . '"></noscript>';
    $html .= '<script src="assets/js/addFlags.js"></script>';
    $html .= '</form>';
    $html .= '</li>';
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
  }
  
  /**
   * Render header section
   */
  private function renderHeader() {
    $html = '<header class="main-header-container">';
    $html .= '<div class="left-section">';
    $html .= '<img src="assets/images/logos/logo.gif" alt="' . htmlspecialchars($this->appTitle) . ' logo" class="logo">';
    $html .= '<h1>' . htmlspecialchars($this->appTitle) . '</h1>';
    $html .= '<h2>' . htmlspecialchars(APP_TITLE) . '</h2>';
    $html .= '<h3>' . htmlspecialchars($this->getText('app_subtitle', 'A Multilingual WebApplication')) . '</h3>';
    $html .= '<h4>' . htmlspecialchars($this->getText('app_description', 'A web application for sharing tales and stories from the community of people with kidney disorders, including those on dialysis, and those who have had or are waiting for a renal transplant.')) . '</h4>';
    $html .= '<h5>' . htmlspecialchars($this->getText('app_version', 'Version 2025.v1.0test')) . '</h5>';
    $html .= '<h6>' . htmlspecialchars($this->getText('app_author', 'Lumpe Paskuden von Lumpenen aka Walter Kyo aka Walter Csoelle Kyo aka Lubomir Polascin')) . '</h6>';
    $html .= '</div>';
    $html .= '<div class="central-section">';
    $html .= '<p>' . htmlspecialchars($this->getText('datetime_placeholder', 'Tu bude zobrazený dátum, čas, vrátane podrobného internetového času @beat.')) . '</p>';
    $html .= '</div>';
    $html .= '<div class="right-section">';
    $html .= '<p>' . htmlspecialchars($this->getText('user_information', 'User information:')) . '</p>';
    $html .= '</div>';
    $html .= '</header>';
    
    return $html;
  }
  
  /**
   * Render main content section
   */
  private function renderMainContent() {
    $html = '<main>';
    $html .= '<hr>';
    
    // Language Selection Flags
    $html .= '<section class="language-selection-flags">';
    $html .= $this->renderLanguageFlags();
    $html .= '<p>' . $this->getText('current_language', 'Current language') . ': <strong>' . htmlspecialchars($this->currentLanguageName) . '</strong></p>';
    $html .= '<p>' . $this->getText('welcome', 'Welcome!') . '</p>';
    $html .= '</section>';
    
    $html .= '<hr>';
    
    // Service Information
    $html .= '<section class="service-information">';
    $html .= '<h1>' . $this->getText('service_information', 'Service Information') . '</h1>';
    
    // Session Information
    ob_start();
    $this->sessionManager->displaySessionComprehensive();
    $html .= ob_get_clean();
    
    $html .= '<hr>';
    
    // Server Information
    $html .= $this->renderServiceInformation();
    
    $html .= '<hr>';
    
    // Application Information
    $html .= $this->renderApplicationInformation();
    
    $html .= '</section>';
    
    $html .= '<hr>';
    $html .= '</main>';
    
    return $html;
  }
  
  /**
   * Render footer section
   */
  private function renderFooter() {
    $html = '<footer>';
    $html .= '<p>&copy; ' . date('Y') . ' ' . htmlspecialchars($this->getText('footer_copyright', 'Ľubomír Polaščín')) . '</p>';
    $html .= '</footer>';
    
    return $html;
  }
  
  /**
   * Render JavaScript section
   */
  private function renderJavaScript() {
    $csrfToken = $this->sessionManager->getCSRFToken();
    
    $html = '<script>';
    $html .= 'document.addEventListener("DOMContentLoaded", function() {';
    $html .= 'const forms = document.querySelectorAll("form");';
    $html .= 'forms.forEach(function(form) {';
    $html .= 'if (!form.querySelector("input[name=\"csrf_token\"]")) {';
    $html .= 'const csrfInput = document.createElement("input");';
    $html .= 'csrfInput.type = "hidden";';
    $html .= 'csrfInput.name = "csrf_token";';
    $html .= 'csrfInput.value = "' . htmlspecialchars($csrfToken) . '";';
    $html .= 'form.appendChild(csrfInput);';
    $html .= '}';
    $html .= '});';
    $html .= '});';
    $html .= '</script>';
    
    return $html;
  }
  
  /**
   * Render the complete HTML page
   */
  public function render() {
    $csrfToken = $this->sessionManager->getCSRFToken();
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo htmlspecialchars($this->currentLanguage); ?>">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
      <title><?php echo htmlspecialchars($this->appTitle); ?></title>
      <link rel="stylesheet" href="assets/css/basic.css?v=<?php echo time(); ?>">
      <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    </head>
    <body>
      <?php echo $this->renderNavigation(); ?>
      <?php echo $this->renderHeader(); ?>
      <?php echo $this->renderMainContent(); ?>
      <?php echo $this->renderFooter(); ?>
      <?php echo $this->renderJavaScript(); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
  }
  
  /**
   * Handle application errors
   * 
   * @param Exception $e
   */
  public function handleError(Exception $e) {
    if (DEBUG_MODE) {
      echo '<h1>Application Error</h1>';
      echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
      echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
      echo '<h1>Service Temporarily Unavailable</h1>';
      echo '<p>Please try again later.</p>';
      error_log('Application Error: ' . $e->getMessage());
    }
  }
}

?>