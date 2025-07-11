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
        
        echo '<!DOCTYPE html>';
        echo '<html lang="' . $this->escape($currentLanguage) . '">';
        echo '<head>';
        echo '<meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<meta name="csrf-token" content="' . $this->escape($csrfToken) . '">';
        echo '<title>' . $this->escape($appTitle) . '</title>';
        echo '<link rel="stylesheet" href="assets/css/basic.css?v=' . time() . '">';
        echo '<link rel="stylesheet" href="assets/css/style.css?v=' . time() . '">';
        echo '</head>';
        echo '<body>';
        $this->renderLanguageSelection();
        $this->renderHeader();
        $this->renderMainContent();
        $this->renderFooter();
        $this->renderJavaScript();
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Render language selection section
     */
    private function renderLanguageSelection() {
        if (!$this->sessionManager) {
            echo '<nav class="language-selector"><p>' . $this->escape($this->getText('language_selection_unavailable', 'Language selection not available')) . '</p></nav>';
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
        echo '<img src="assets/images/logos/logo_shifted.gif" alt="' . $this->escape($appTitle) . ' logo" class="logo">';
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
        echo $this->renderUserInformation();
        echo '</div>';
        echo '</header>';
    }
    
    /**
     * Render user information section
     */
    private function renderUserInformation() {
        if (!$this->authenticationManager) {
            return '<p>' . $this->escape($this->getText('user_information', 'User information:')) . '</p>';
        }
        
        $html = '<div class="user-info">';
        $html .= '<p>' . $this->escape($this->getText('user_information', 'User information:')) . '</p>';
        
        if ($this->authenticationManager->isAuthenticated()) {
            $currentUser = $this->authenticationManager->getCurrentUser();
            
            if ($currentUser) {
                $html .= '<div class="logged-in-user">';
                $html .= '<p><strong>' . $this->escape($this->getText('welcome_user', 'Welcome')) . ':</strong> ';
                
                // Display user's display name or username
                if (isset($currentUser['full_name']) && !empty($currentUser['full_name'])) {
                    $html .= $this->escape($currentUser['full_name']);
                } elseif (isset($currentUser['username']) && !empty($currentUser['username'])) {
                    $html .= $this->escape($currentUser['username']);
                } elseif (isset($currentUser['email']) && !empty($currentUser['email'])) {
                    $html .= $this->escape($currentUser['email']);
                } else {
                    $html .= $this->escape($this->getText('user', 'User'));
                }
                
                $html .= '</p>';
                
                // Display user role if available
                if (isset($currentUser['role']) && !empty($currentUser['role'])) {
                    $html .= '<p><small>' . $this->escape($this->getText('role', 'Role')) . ': ' . $this->escape($currentUser['role']) . '</small></p>';
                }
                
                // Add logout link/button
                $html .= '<p><a href="?action=logout" class="logout-link">' . $this->escape($this->getText('logout', 'Logout')) . '</a></p>';
                
                $html .= '</div>';
            } else {
                $html .= '<p>' . $this->escape($this->getText('user_data_unavailable', 'User data unavailable')) . '</p>';
            }
        } else {
            $html .= '<div class="guest-user">';
            $html .= '<p>' . $this->escape($this->getText('not_logged_in', 'Not logged in')) . '</p>';
            $html .= '<p><a href="?action=login" class="login-link">' . $this->escape($this->getText('login', 'Login')) . '</a></p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render main menu section
     */
    private function renderMainMenu() {
        $html = '<nav class="main-menu">';
        $html .= '<h3>' . $this->escape($this->getText('main_menu', 'Main Menu')) . '</h3>';
        $html .= '<ul>';
        
        // Home/Dashboard
        $html .= '<li><a href="/" class="menu-item">';
        $html .= '<i class="icon-home"></i>';
        $html .= $this->escape($this->getText('home', 'Home'));
        $html .= '</a></li>';
        
        // Stories section
        $html .= '<li><a href="?section=stories" class="menu-item">';
        $html .= '<i class="icon-stories"></i>';
        $html .= $this->escape($this->getText('stories', 'Stories'));
        $html .= '</a></li>';
        
        // Community section
        $html .= '<li><a href="?section=community" class="menu-item">';
        $html .= '<i class="icon-community"></i>';
        $html .= $this->escape($this->getText('community', 'Community'));
        $html .= '</a></li>';
        
        // Resources section
        $html .= '<li><a href="?section=resources" class="menu-item">';
        $html .= '<i class="icon-resources"></i>';
        $html .= $this->escape($this->getText('resources', 'Resources'));
        $html .= '</a></li>';
        
        // About section
        $html .= '<li><a href="?section=about" class="menu-item">';
        $html .= '<i class="icon-about"></i>';
        $html .= $this->escape($this->getText('about', 'About'));
        $html .= '</a></li>';
        
        // Add authenticated user menu items
        if ($this->authenticationManager && $this->authenticationManager->isAuthenticated()) {
            $html .= '<li class="menu-separator"></li>';
            
            // My Stories
            $html .= '<li><a href="?section=my-stories" class="menu-item">';
            $html .= '<i class="icon-my-stories"></i>';
            $html .= $this->escape($this->getText('my_stories', 'My Stories'));
            $html .= '</a></li>';
            
            // Profile
            $html .= '<li><a href="?section=profile" class="menu-item">';
            $html .= '<i class="icon-profile"></i>';
            $html .= $this->escape($this->getText('profile', 'Profile'));
            $html .= '</a></li>';
            
            // Settings
            $html .= '<li><a href="?section=settings" class="menu-item">';
            $html .= '<i class="icon-settings"></i>';
            $html .= $this->escape($this->getText('settings', 'Settings'));
            $html .= '</a></li>';
        } else {
            // Guest user menu items
            $html .= '<li class="menu-separator"></li>';
            
            // Login
            $html .= '<li><a href="?action=login" class="menu-item login-item">';
            $html .= '<i class="icon-login"></i>';
            $html .= $this->escape($this->getText('login', 'Login'));
            $html .= '</a></li>';
            
            // Register
            $html .= '<li><a href="?action=register" class="menu-item register-item">';
            $html .= '<i class="icon-register"></i>';
            $html .= $this->escape($this->getText('register', 'Register'));
            $html .= '</a></li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Render content notes section
     */
    private function renderContentNotes() {
        $html = '<aside class="content-notes">';
        $html .= '<h3>' . $this->escape($this->getText('important_notes', 'Important Notes')) . '</h3>';
        
        $html .= '<div class="note-section">';
        $html .= '<h4>' . $this->escape($this->getText('about_renal_tales', 'About Renal Tales')) . '</h4>';
        $html .= '<p>' . $this->escape($this->getText('renal_tales_description', 'Renal Tales is a supportive community platform where people affected by kidney disorders can share their experiences, find support, and connect with others on similar journeys.')) . '</p>';
        $html .= '</div>';
        
        $html .= '<div class="note-section">';
        $html .= '<h4>' . $this->escape($this->getText('community_guidelines', 'Community Guidelines')) . '</h4>';
        $html .= '<ul>';
        $html .= '<li>' . $this->escape($this->getText('guideline_respectful', 'Be respectful and supportive to all community members')) . '</li>';
        $html .= '<li>' . $this->escape($this->getText('guideline_privacy', 'Respect privacy and confidentiality')) . '</li>';
        $html .= '<li>' . $this->escape($this->getText('guideline_medical', 'Share experiences, not medical advice')) . '</li>';
        $html .= '<li>' . $this->escape($this->getText('guideline_appropriate', 'Keep content appropriate and relevant')) . '</li>';
        $html .= '</ul>';
        $html .= '</div>';
        
        $html .= '<div class="note-section">';
        $html .= '<h4>' . $this->escape($this->getText('getting_started', 'Getting Started')) . '</h4>';
        $html .= '<p>' . $this->escape($this->getText('getting_started_description', 'New to our community? Start by reading some stories, introduce yourself, and consider sharing your own experience when you\'re ready.')) . '</p>';
        $html .= '</div>';
        
        $html .= '<div class="note-section">';
        $html .= '<h4>' . $this->escape($this->getText('support_resources', 'Support Resources')) . '</h4>';
        $html .= '<p>' . $this->escape($this->getText('support_description', 'If you need immediate medical help or are in crisis, please contact your healthcare provider or emergency services.')) . '</p>';
        $html .= '</div>';
        
        $html .= '</aside>';
        
        return $html;
    }
    
    /**
     * Render main content body
     */
    private function renderMainContentBody() {
        $html = '<div class="content-body">';
        
        // Get current section from URL parameters
        $currentSection = isset($_GET['section']) ? $this->sanitizeInput($_GET['section']) : 'home';
        
        switch ($currentSection) {
            case 'stories':
                $html .= $this->renderStoriesSection();
                break;
            case 'community':
                $html .= $this->renderCommunitySection();
                break;
            case 'resources':
                $html .= $this->renderResourcesSection();
                break;
            case 'about':
                $html .= $this->renderAboutSection();
                break;
            case 'my-stories':
                $html .= $this->renderMyStoriesSection();
                break;
            case 'profile':
                $html .= $this->renderProfileSection();
                break;
            case 'settings':
                $html .= $this->renderSettingsSection();
                break;
            case 'home':
            default:
                $html .= $this->renderHomeSection();
                break;
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render home section
     */
    private function renderHomeSection() {
        $html = '<section class="home-section">';
        $html .= '<h2>' . $this->escape($this->getText('welcome_home', 'Welcome to Renal Tales')) . '</h2>';
        
        $html .= '<div class="home-intro">';
        $html .= '<p>' . $this->escape($this->getText('home_intro', 'Welcome to our supportive community for people affected by kidney disorders. Here you can share your story, read others\' experiences, and find support from people who understand your journey.')) . '</p>';
        $html .= '</div>';
        
        $html .= '<div class="home-features">';
        $html .= '<div class="feature-grid">';
        
        $html .= '<div class="feature-card">';
        $html .= '<h3>' . $this->escape($this->getText('share_story', 'Share Your Story')) . '</h3>';
        $html .= '<p>' . $this->escape($this->getText('share_story_desc', 'Your experience matters. Share your journey to inspire and support others.')) . '</p>';
        $html .= '<a href="?section=stories&action=new" class="btn btn-primary">' . $this->escape($this->getText('start_sharing', 'Start Sharing')) . '</a>';
        $html .= '</div>';
        
        $html .= '<div class="feature-card">';
        $html .= '<h3>' . $this->escape($this->getText('read_stories', 'Read Stories')) . '</h3>';
        $html .= '<p>' . $this->escape($this->getText('read_stories_desc', 'Find inspiration and comfort in the experiences of others in our community.')) . '</p>';
        $html .= '<a href="?section=stories" class="btn btn-secondary">' . $this->escape($this->getText('browse_stories', 'Browse Stories')) . '</a>';
        $html .= '</div>';
        
        $html .= '<div class="feature-card">';
        $html .= '<h3>' . $this->escape($this->getText('join_community', 'Join Community')) . '</h3>';
        $html .= '<p>' . $this->escape($this->getText('join_community_desc', 'Connect with others, participate in discussions, and build lasting friendships.')) . '</p>';
        $html .= '<a href="?section=community" class="btn btn-secondary">' . $this->escape($this->getText('explore_community', 'Explore Community')) . '</a>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</section>';
        
        return $html;
    }
    
    /**
     * Placeholder method for stories section
     */
    private function renderStoriesSection() {
        return '<section class="stories-section"><h2>' . $this->escape($this->getText('stories', 'Stories')) . '</h2><p>' . $this->escape($this->getText('stories_coming_soon', 'Stories section coming soon...')) . '</p></section>';
    }
    
    /**
     * Placeholder method for community section
     */
    private function renderCommunitySection() {
        return '<section class="community-section"><h2>' . $this->escape($this->getText('community', 'Community')) . '</h2><p>' . $this->escape($this->getText('community_coming_soon', 'Community section coming soon...')) . '</p></section>';
    }
    
    /**
     * Placeholder method for resources section
     */
    private function renderResourcesSection() {
        return '<section class="resources-section"><h2>' . $this->escape($this->getText('resources', 'Resources')) . '</h2><p>' . $this->escape($this->getText('resources_coming_soon', 'Resources section coming soon...')) . '</p></section>';
    }
    
    /**
     * Placeholder method for about section
     */
    private function renderAboutSection() {
        return '<section class="about-section"><h2>' . $this->escape($this->getText('about', 'About')) . '</h2><p>' . $this->escape($this->getText('about_coming_soon', 'About section coming soon...')) . '</p></section>';
    }
    
    /**
     * Placeholder method for my stories section
     */
    private function renderMyStoriesSection() {
        return '<section class="my-stories-section"><h2>' . $this->escape($this->getText('my_stories', 'My Stories')) . '</h2><p>' . $this->escape($this->getText('my_stories_coming_soon', 'My Stories section coming soon...')) . '</p></section>';
    }
    
    /**
     * Placeholder method for profile section
     */
    private function renderProfileSection() {
        return '<section class="profile-section"><h2>' . $this->escape($this->getText('profile', 'Profile')) . '</h2><p>' . $this->escape($this->getText('profile_coming_soon', 'Profile section coming soon...')) . '</p></section>';
    }
    
    /**
     * Placeholder method for settings section
     */
    private function renderSettingsSection() {
        return '<section class="settings-section"><h2>' . $this->escape($this->getText('settings', 'Settings')) . '</h2><p>' . $this->escape($this->getText('settings_coming_soon', 'Settings section coming soon...')) . '</p></section>';
    }
    
    /**
     * Sanitize user input
     */
    private function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Render main content section
     */
    private function renderMainContent() {
        echo '<main>';
        echo '<div class="main-container">';

        // Main content goes here
        echo '<div class="main-menu-container">';
        echo $this->renderMainMenu();
        echo '</div>';

        echo '<div class="main-content-container">';
        echo $this->renderMainContentBody();
        echo '</div>';
        
        echo '<div class="main-notes-container">';
        echo $this->renderContentNotes();
        echo '</div>';

        echo '</div>';

        // Language Selection Flags
        echo '<div>';
        echo '<section class="language-selection-flags">';
        echo $this->renderLanguageFlags();
        echo '<p>' . $this->escape($this->getText('current_language', 'Current language')) . ': <strong>' . $this->escape($this->languageModel ? $this->languageModel->getCurrentLanguageName() : 'English') . '</strong>. </p>';
        echo '<p>' . $this->escape($this->getText('welcome', 'Welcome')) . '! </p>';
        echo '</section>';
        echo '</div>'; 
               
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
