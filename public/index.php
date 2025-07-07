<?php

/**
 * Renal Tales - Main Application Entry Point
 * 
 * A multilingual web application for sharing kidney disorder stories
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

// Set application constants
define('APP_DIR', dirname(__DIR__));
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/languages/');
define('APP_TITLE', 'Renal Tales');
define('DEBUG_MODE', true); // Set to false in production

// Include required classes
require_once APP_DIR . '/core/LanguageDetector.php';
require_once APP_DIR . '/core/SessionManager.php';

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
      <!-- Navigation -->
      <nav class="language-selector">
        <ul>
          <li>
            <form method="get" action="">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
              <select name="lang" onchange="this.form.submit()">
                <?php echo $this->renderLanguageSelector(); ?>
              </select>
              <noscript><input type="submit" value="<?php echo $this->getText('change', 'Change'); ?>"></noscript>
              <script src="assets/js/addFlags.js"></script>
            </form>
          </li>
        </ul>
      </nav>
      
      <!-- Header -->
      <header class="main-header-container">
        <div class="left-section">
          <img src="assets/images/logos/logo.gif" alt="<?php echo htmlspecialchars($this->appTitle) . ' logo'; ?>" class="logo">
          <h1><?php echo htmlspecialchars($this->appTitle); ?></h1>
          <h2><?php echo htmlspecialchars(APP_TITLE); ?></h2>
          <h3><?php echo htmlspecialchars($this->getText('app_subtitle', 'A Multilingual WebApplication')); ?></h3>
          <h4><?php echo htmlspecialchars($this->getText('app_description', 'A web application for sharing tales and stories from the community of people with kidney disorders, including those on dialysis, and those who have had or are waiting for a renal transplant.')); ?></h4>
          <h5><?php echo htmlspecialchars($this->getText('app_version', 'Version 2025.v1.0test')); ?></h5>
          <h6><?php echo htmlspecialchars($this->getText('app_author', 'Lumpe Paskuden von Lumpenen aka Walter Kyo aka Walter Csoelle Kyo aka Lubomir Polascin')); ?></h6>
        </div>
        <div class="central-section">
          <p><?php echo htmlspecialchars($this->getText('datetime_placeholder', 'Tu bude zobrazený dátum, čas, vrátane podrobného internetového času @beat.')); ?></p>
        </div>
        <div class="right-section">
          <p><?php echo htmlspecialchars($this->getText('user_information', 'User information:')); ?></p>
        </div>
      </header>
      
      <!-- Main Content -->
      <main>
        <hr>
        
        <!-- Language Selection Flags -->
        <section class="language-selection-flags">
          <?php echo $this->renderLanguageFlags(); ?>
          <p><?php echo $this->getText('current_language', 'Current language'); ?>: <strong><?php echo htmlspecialchars($this->currentLanguageName); ?></strong></p>
          <p><?php echo $this->getText('welcome', 'Welcome!'); ?></p>
        </section>
        
        <hr>
        
        <!-- Service Information -->
        <section class="service-information">
          <h1><?php echo $this->getText('service_information', 'Service Information'); ?></h1>
          
          <!-- Session Information -->
          <?php $this->sessionManager->displaySessionComprehensive(); ?>
          
          <hr>
          
          <!-- Server Information -->
          <?php echo $this->renderServiceInformation(); ?>
          
          <hr>
          
          <!-- Application Information -->
          <?php echo $this->renderApplicationInformation(); ?>
        </section>
        
        <hr>
      </main>
      
      <!-- Footer -->
      <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($this->getText('footer_copyright', 'Ľubomír Polaščín')); ?></p>
      </footer>
      
      <!-- Scripts -->
      <script>
        // Add CSRF token to all forms
        document.addEventListener('DOMContentLoaded', function() {
          const forms = document.querySelectorAll('form');
          forms.forEach(function(form) {
            if (!form.querySelector('input[name="csrf_token"]')) {
              const csrfInput = document.createElement('input');
              csrfInput.type = 'hidden';
              csrfInput.name = 'csrf_token';
              csrfInput.value = '<?php echo htmlspecialchars($csrfToken); ?>';
              form.appendChild(csrfInput);
            }
          });
        });
      </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
  }
}

// Initialize and run the application
try {
  $app = new Application();
  echo $app->render();
} catch (Exception $e) {
  // In production, log error and show generic error page
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

?>
