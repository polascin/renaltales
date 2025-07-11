<?php

declare(strict_types=1);

require_once 'BaseView.php';

/**
 * LoginView - Login page view
 * 
 * Handles rendering of the login page
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

class LoginView extends BaseView {
    
    private array $errors = [];
    
    /**
     * Set validation errors
     * 
     * @param array $errors
     */
    public function setErrors(array $errors): void {
        $this->errors = $errors;
    }
    
    /**
     * Render the login page content
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
            <title><?php echo $this->escape($this->getText('login', 'Login')); ?> - <?php echo $this->escape($appTitle); ?></title>
            <link rel="stylesheet" href="assets/css/basic.css?v=<?php echo time(); ?>">
            <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
        </head>
        <body class="login-page">
            <?php $this->renderLanguageSelection(); ?>
            <?php $this->renderLoginHeader(); ?>
            <?php $this->renderLoginForm(); ?>
            <?php $this->renderLoginFooter(); ?>
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
        echo '<input type="hidden" name="action" value="login">';
        echo '<input type="hidden" name="csrf_token" value="' . $this->escape($csrfToken) . '">';
        echo '<select name="lang" onchange="this.form.submit()">';
        echo $this->renderLanguageSelector();
        echo '</select>';
        echo '<noscript><input type="submit" value="' . $this->escape($this->getText('change', 'Change')) . '"></noscript>';
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
            $selected = ($this->languageModel->getCurrentLanguage() === $lang) ? ' selected' : '';
            
            $html .= '<option value="' . $this->escape($lang) . '"' . $selected . '>';
            $html .= $this->escape($langName) . ' (' . $this->escape($lang) . ')';
            $html .= '</option>';
        }
        
        return $html;
    }
    
    /**
     * Render login header
     */
    private function renderLoginHeader() {
        $appTitle = $this->getText('app_title', defined('APP_TITLE') ? APP_TITLE : 'Renal Tales');
        
        echo '<header class="login-header">';
        echo '<div class="login-header-content">';
        echo '<a href="/" class="logo-link">';
        echo '<img src="assets/images/logos/logo.gif" alt="' . $this->escape($appTitle) . ' logo" class="logo">';
        echo '<h1>' . $this->escape($appTitle) . '</h1>';
        echo '</a>';
        echo '<h2>' . $this->escape($this->getText('login_welcome', 'Welcome Back')) . '</h2>';
        echo '<p>' . $this->escape($this->getText('login_subtitle', 'Sign in to access your account')) . '</p>';
        echo '</div>';
        echo '</header>';
    }
    
    /**
     * Render login form
     */
    private function renderLoginForm() {
        $csrfToken = $this->sessionManager ? $this->sessionManager->getCSRFToken() : 'no-token';
        
        echo '<main class="login-main">';
        echo '<div class="login-container">';
        echo '<div class="login-form-wrapper">';
        
        echo '<form method="post" action="?action=login" class="login-form" novalidate>';
        echo '<input type="hidden" name="csrf_token" value="' . $this->escape($csrfToken) . '">';
        
        // Display general errors
        if (!empty($this->errors['general'])) {
            echo '<div class="alert alert-error">';
            echo '<p>' . $this->escape($this->errors['general']) . '</p>';
            echo '</div>';
        }
        
        // Email/Username field
        echo '<div class="form-group">';
        echo '<label for="identifier">' . $this->escape($this->getText('email_or_username', 'Email or Username')) . ' <span class="required">*</span></label>';
        echo '<input type="text" id="identifier" name="identifier" required ';
        echo 'value="' . $this->escape($this->get('identifier', '')) . '" ';
        echo 'placeholder="' . $this->escape($this->getText('enter_email_username', 'Enter your email or username')) . '" ';
        echo 'class="form-control' . (!empty($this->errors['identifier']) ? ' error' : '') . '">';
        if (!empty($this->errors['identifier'])) {
            echo '<span class="error-message">' . $this->escape($this->errors['identifier']) . '</span>';
        }
        echo '</div>';
        
        // Password field
        echo '<div class="form-group">';
        echo '<label for="password">' . $this->escape($this->getText('password', 'Password')) . ' <span class="required">*</span></label>';
        echo '<input type="password" id="password" name="password" required ';
        echo 'placeholder="' . $this->escape($this->getText('enter_password', 'Enter your password')) . '" ';
        echo 'class="form-control' . (!empty($this->errors['password']) ? ' error' : '') . '">';
        if (!empty($this->errors['password'])) {
            echo '<span class="error-message">' . $this->escape($this->errors['password']) . '</span>';
        }
        echo '</div>';
        
        // Remember me checkbox
        echo '<div class="form-group checkbox-group">';
        echo '<label class="checkbox-label">';
        echo '<input type="checkbox" name="remember_me" value="1"' . ($this->get('remember_me') ? ' checked' : '') . '>';
        echo '<span class="checkmark"></span>';
        echo $this->escape($this->getText('remember_me', 'Remember me'));
        echo '</label>';
        echo '</div>';
        
        // Submit button
        echo '<div class="form-group">';
        echo '<button type="submit" class="btn btn-primary btn-full">';
        echo $this->escape($this->getText('sign_in', 'Sign In'));
        echo '</button>';
        echo '</div>';
        
        echo '</form>';
        
        // Additional links
        echo '<div class="login-links">';
        echo '<p>';
        echo '<a href="?action=forgot-password" class="link">';
        echo $this->escape($this->getText('forgot_password', 'Forgot your password?'));
        echo '</a>';
        echo '</p>';
        echo '<p>';
        echo $this->escape($this->getText('no_account', 'Don\'t have an account?')) . ' ';
        echo '<a href="?action=register" class="link">';
        echo $this->escape($this->getText('sign_up_here', 'Sign up here'));
        echo '</a>';
        echo '</p>';
        echo '<p>';
        echo '<a href="/" class="link">';
        echo $this->escape($this->getText('back_to_home', 'Back to Home'));
        echo '</a>';
        echo '</p>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        echo '</main>';
    }
    
    /**
     * Render login footer
     */
    private function renderLoginFooter() {
        echo '<footer class="login-footer">';
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
        
        echo '<script>';
        echo 'document.addEventListener("DOMContentLoaded", function() {';
        
        // Auto-focus on first empty field
        echo 'const firstEmptyField = document.querySelector(".form-control:not([value])");';
        echo 'if (firstEmptyField) firstEmptyField.focus();';
        
        // Form validation
        echo 'const form = document.querySelector(".login-form");';
        echo 'if (form) {';
        echo 'form.addEventListener("submit", function(e) {';
        echo 'const identifier = document.getElementById("identifier").value.trim();';
        echo 'const password = document.getElementById("password").value;';
        echo 'if (!identifier || !password) {';
        echo 'e.preventDefault();';
        echo 'alert("' . $this->escape($this->getText('please_fill_required_fields', 'Please fill in all required fields.')) . '");';
        echo '}';
        echo '});';
        echo '}';
        
        echo '});';
        echo '</script>';
    }
}
