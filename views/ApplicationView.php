<?php

declare(strict_types=1);

require_once 'BaseView.php';

/**
 * ApplicationView - Main application view
 * 
 * Handles rendering of the main application page
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

class ApplicationView extends BaseView {
    
    /**
     * Render the main application content
     */
    protected function renderContent(): void {
        $currentLanguage = $this->languageModel ? $this->languageModel->getCurrentLanguage() : 'en';
        $currentLanguageName = $this->languageModel ? $this->languageModel->getCurrentLanguageName() : 'English';
        $appTitle = $this->getText('app_title', defined('APP_TITLE') ? APP_TITLE : 'Renal Tales');
        $csrfToken = $this->sessionManager ? $this->sessionManager->getCSRFToken() : 'no-token';
        
        ?>
        <!DOCTYPE html>
        <html lang="<?php echo $this->escape($currentLanguage); ?>">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="csrf-token" content="<?php echo $this->escape($csrfToken); ?>">
            <title><?php echo $this->escape($appTitle); ?></title>
            <link rel="stylesheet" href="assets/css/basic.css?v=<?php echo time(); ?>">
            <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
        </head>
        <body>
            <?php $this->renderLanguageSelection(); ?>
            <?php $this->renderHeader(); ?>
            <?php $this->renderMainContent(); ?>
            <?php
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $this->renderServiceInformationSection();
            }
            ?>
            <?php $this->renderFooter(); ?>
            <?php $this->renderJavaScript(); ?>
        </body>
        </html>
        <?php
    }
    
    /**
     * Render language selection section
     */
    private function renderLanguageSelection() {
        if (!$this->sessionManager) {
            echo '<nav class="language-selector"><p>Language selection not available</p></nav>';
            return;
        }
        
        $csrfToken = $this->sessionManager->getCSRFToken();
        
        echo '<nav class="language-selector">';
        echo '<ul>';
        echo '<li>';
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="csrf_token" value="' . $this->escape($csrfToken) . '">';
        echo '<select name="lang" onchange="this.form.submit()">';
        echo $this->renderLanguageSelector();
        echo '</select>';
        echo '<noscript><input type="submit" value="' . $this->escape($this->getText('change', 'Change')) . '"></noscript>';
        echo '<script src="assets/js/addFlags.js"></script>';
        echo '</form>';
        echo '</li>';
        echo '</ul>';
        echo '</nav>';
    }
    
    /**
     * Render language selector dropdown
     */
    private function renderLanguageSelector() {
        if (!$this->languageModel || !$this->languageModel->getLanguageDetector()) {
            return '<option value="en">English (en)</option>';
        }
        
        $selectableLanguages = $this->languageModel->getSupportedLanguages();
        $html = '';
        
        foreach ($selectableLanguages as $lang) {
            $langFile = LANGUAGE_PATH . $lang . '.php';
            if (!file_exists($langFile)) continue;
            
            $langName = $this->languageModel->getLanguageName($lang);
            $flagPath = $this->languageModel->getFlagPath($lang);
            $selected = ($this->languageModel->getCurrentLanguage() === $lang) ? ' selected' : '';
            
            $html .= '<option value="' . $this->escape($lang) . '"' . $selected . ' data-flag="' . $this->escape($flagPath) . '">';
            $html .= $this->escape($langName) . ' (' . $this->escape($lang) . ')';
            $html .= '</option>';
        }
        
        return $html;
    }
    
    /**
     * Render header section
     */
    private function renderHeader() {
        $appTitle = $this->getText('app_title', defined('APP_TITLE') ? APP_TITLE : 'Renal Tales');
        
        echo '<header class="main-header-container">';
        echo '<div class="left-section">';
        echo '<img src="assets/images/logos/logo.gif" alt="' . $this->escape($appTitle) . ' logo" class="logo">';
        echo '<h1>' . $this->escape($appTitle) . '</h1>';
        
        if (defined('APP_TITLE')) {
            echo '<h2>' . $this->escape(APP_TITLE) . '</h2>';
        }
        
        echo '<h3>' . $this->escape($this->getText('app_subtitle', 'A Multilingual WebApplication')) . '</h3>';
        echo '<h4>' . $this->escape($this->getText('app_description', 'A web application for sharing tales and stories from the community of people with kidney disorders, including those on dialysis, and those who have had or are waiting for a renal transplant.')) . '</h4>';
        echo '<h5>' . $this->escape($this->getText('app_version', 'Version 2025.v1.0test')) . '</h5>';
        echo '<h6>' . $this->escape($this->getText('app_author', 'Lumpe Paskuden von Lumpenen aka Walter Kyo aka Walter Csoelle Kyo aka Lubomir Polascin')) . '</h6>';
        echo '</div>';
        echo '<div class="central-section">';
        echo '<p>' . $this->escape($this->getText('datetime_placeholder', 'Tu bude zobrazený dátum, čas, vrátane podrobného internetového času @beat.')) . '</p>';
        echo '</div>';
        echo '<div class="right-section">';
        echo '<p>' . $this->escape($this->getText('user_information', 'User information:')) . '</p>';
        echo '</div>';
        echo '</header>';
    }
    
    /**
     * Render main content section
     */
    private function renderMainContent() {
        echo '<main>';
        
        // Language Selection Flags
        echo '<section class="language-selection-flags">';
        echo $this->renderLanguageFlags();
        echo '<p>' . $this->escape($this->getText('current_language', 'Current language')) . ': <strong>' . $this->escape($this->languageModel ? $this->languageModel->getCurrentLanguageName() : 'English') . '</strong>. </p>';
        echo '<p>' . $this->escape($this->getText('welcome', 'Welcome')) . '! </p>';
        echo '</section>';
        
        echo '</main>';
    }
    
    /**
     * Render language flags
     */
    private function renderLanguageFlags() {
        if (!$this->languageModel || !$this->languageModel->getLanguageDetector()) {
            return '<p>Language selection not available</p>';
        }
        
        $selectableLanguages = $this->languageModel->getSupportedLanguages();
        $html = '';
        
        foreach ($selectableLanguages as $lang) {
            $langFile = LANGUAGE_PATH . $lang . '.php';
            if (!file_exists($langFile)) continue;
            
            $langName = $this->languageModel->getLanguageName($lang);
            $flagPath = $this->languageModel->getFlagPath($lang);
            
            $html .= '<a href="?lang=' . urlencode($lang) . '" title="' . $this->escape($langName) . '">';
            $html .= '<img src="' . $this->escape($flagPath) . '" alt="' . $this->escape($langName) . '">';
            $html .= $this->escape($lang);
            $html .= '</a>';
        }
        
        return $html;
    }
    
    /**
     * Render service information section (for debug mode)
     */
    private function renderServiceInformationSection() {
        echo '<section class="service-information-section">';
        echo '<hr>';
        echo '<div class="debug-info">';
        echo '<p>' . $this->escape($this->getText('debug_mode_enabled', 'Debug mode is enabled.')) . ' ';
        echo $this->escape($this->getText('current_language', 'Current language')) . ': ' . $this->escape($this->languageModel ? $this->languageModel->getCurrentLanguage() : 'en') . '</p>';
        echo '</div>';
        
        echo '<h1>' . $this->escape($this->getText('service_information', 'Service Information')) . '</h1>';
        
        // Session Information
        if ($this->sessionManager) {
            ob_start();
            $this->sessionManager->displaySessionComprehensive();
            echo ob_get_clean();
        } else {
            echo '<p>Session manager not available</p>';
        }
        echo '<hr>';
        
        // Server Information
        echo $this->renderServiceInformation();
        echo '<hr>';
        
        // Application Information
        echo $this->renderApplicationInformation();
        echo '<hr>';
        
        echo '</section>';
    }
    
    /**
     * Render service information
     */
    private function renderServiceInformation() {
        $html = '<div class="server-info">';
        $html .= '<h2>' . $this->escape($this->getText('server_information', 'Server Information')) . '</h2>';
        $html .= '<p><strong>' . $this->escape($this->getText('user_agent', 'User Agent')) . ':</strong> ' . $this->getServerVar('HTTP_USER_AGENT') . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('ip_address', 'IP Address')) . ':</strong> ' . $this->getServerVar('REMOTE_ADDR') . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('server_software', 'Server Software')) . ':</strong> ' . $this->getServerVar('SERVER_SOFTWARE') . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('server_name', 'Server Name')) . ':</strong> ' . $this->getServerVar('SERVER_NAME') . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('server_protocol', 'Server Protocol')) . ':</strong> ' . $this->getServerVar('SERVER_PROTOCOL') . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('request_method', 'Request Method')) . ':</strong> ' . $this->getServerVar('REQUEST_METHOD') . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('request_uri', 'Request URI')) . ':</strong> ' . $this->getServerVar('REQUEST_URI') . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('query_string', 'Query String')) . ':</strong> ' . $this->getServerVar('QUERY_STRING') . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('document_root', 'Document Root')) . ':</strong> ' . $this->getServerVar('DOCUMENT_ROOT') . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('script_name', 'Script Name')) . ':</strong> ' . $this->getServerVar('SCRIPT_NAME') . '</p>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render application information
     */
    private function renderApplicationInformation() {
        $appTitle = $this->getText('app_title', defined('APP_TITLE') ? APP_TITLE : 'Renal Tales');
        
        $html = '<div class="app-info">';
        $html .= '<h2>' . $this->escape($this->getText('application_information', 'Application Information')) . '</h2>';
        $html .= '<p><strong>' . $this->escape($this->getText('app_title_item', 'Application Title')) . ':</strong> ' . $this->escape($appTitle) . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('current_language', 'Current Language')) . ':</strong> ' . $this->escape($this->languageModel ? $this->languageModel->getCurrentLanguageName() : 'English') . '</p>';
        $html .= '<p><strong>' . $this->escape($this->getText('current_language_code', 'Current Language Code')) . ':</strong> ' . $this->escape($this->languageModel ? $this->languageModel->getCurrentLanguage() : 'en') . '</p>';
        
        if (defined('LANGUAGE_PATH') && $this->languageModel) {
            $html .= '<p><strong>' . $this->escape($this->getText('current_language_file', 'Current Language File')) . ':</strong> ' . $this->escape(LANGUAGE_PATH . $this->languageModel->getCurrentLanguage() . '.php') . '</p>';
        }
        
        if ($this->languageModel && $this->languageModel->getLanguageDetector()) {
            $html .= '<p><strong>' . $this->escape($this->getText('current_language_flag', 'Current Language Flag')) . ':</strong> ';
            $html .= '<img src="' . $this->escape($this->languageModel->getFlagPath($this->languageModel->getCurrentLanguage())) . '" ';
            $html .= 'alt="' . $this->escape($this->getText('current_language_flag_alt', 'Flag of the current language')) . '" class="flag"></p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render footer section
     */
    private function renderFooter() {
        echo '<footer>';
        echo '<p>&copy; ' . date('Y') . ' ' . $this->escape($this->getText('footer_copyright', 'Ľubomír Polaščín')) . '</p>';
        echo '</footer>';
    }
    
    /**
     * Render JavaScript section
     */
    private function renderJavaScript() {
        if (!$this->sessionManager) {
            return;
        }
        
        $csrfToken = $this->sessionManager->getCSRFToken();
        
        echo '<script>';
        echo 'document.addEventListener("DOMContentLoaded", function() {';
        echo 'const forms = document.querySelectorAll("form");';
        echo 'forms.forEach(function(form) {';
        echo 'if (!form.querySelector("input[name=\\"csrf_token\\"]")) {';
        echo 'const csrfInput = document.createElement("input");';
        echo 'csrfInput.type = "hidden";';
        echo 'csrfInput.name = "csrf_token";';
        echo 'csrfInput.value = "' . $this->escape($csrfToken) . '";';
        echo 'form.appendChild(csrfInput);';
        echo '}';
        echo '});';
        echo '});';
        echo '</script>';
    }
}

?>
