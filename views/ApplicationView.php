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
        // Safely get current language and ensure it's a string
        $currentLanguage = 'en';
        if ($this->languageModel && method_exists($this->languageModel, 'getCurrentLanguage')) {
            $lang = $this->languageModel->getCurrentLanguage();
            $currentLanguage = is_string($lang) ? $lang : 'en';
        }
        
        // Safely get current language name
        $currentLanguageName = 'English';
        if ($this->languageModel && method_exists($this->languageModel, 'getCurrentLanguageName')) {
            $langName = $this->languageModel->getCurrentLanguageName();
            $currentLanguageName = is_string($langName) ? $langName : 'English';
        }
        
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
        echo '<link rel="stylesheet" href="assets/css/post-navigation.css?v=' . time() . '">';
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
        echo '<div class="language-selector-container">';
        echo '<form method="POST" action="" class="language-form">';
        echo '<input type="hidden" name="_csrf_token" value="' . $this->escape($csrfToken) . '">';
        echo '<label for="lang-select" class="language-label">' . $this->escape($this->getText('select_language', 'Select Language')) . ':</label>';
        echo '<select id="lang-select" name="lang" class="language-select" onchange="this.form.submit()">';
        echo $this->renderLanguageSelector();
        echo '</select>';
        echo '<noscript><input type="submit" value="' . $this->escape($this->getText('change', 'Change')) . '" class="language-submit"></noscript>';
        echo '</form>';
        echo '</div>';
        echo '</nav>';
        echo '<script src="assets/js/addFlags.js"></script>';
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
        
        // Safely get current language for comparison
        $currentLang = 'en';
        if (method_exists($this->languageModel, 'getCurrentLanguage')) {
            $currLang = $this->languageModel->getCurrentLanguage();
            $currentLang = is_string($currLang) ? $currLang : 'en';
        }
        
        foreach ($selectableLanguages as $lang) {
            $langFile = LANGUAGE_PATH . $lang . '.php';
            if (!file_exists($langFile)) continue;
            
            $langName = $this->languageModel->getLanguageName($lang);
            
            // Get best available flag path with fallback
            $flagPath = 'assets/flags/un.webp'; // Default fallback
            if ($this->languageModel->getLanguageDetector()) {
                $detector = $this->languageModel->getLanguageDetector();
                if (method_exists($detector, 'getBestFlagPath')) {
                    $flagPath = $detector->getBestFlagPath($lang, 'assets/flags/', $_SERVER['DOCUMENT_ROOT'] ?? '');
                } else {
                    $flagPath = $this->languageModel->getFlagPath($lang);
                }
            }
            
            $selected = ($currentLang === $lang) ? ' selected' : '';
            
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
        echo '<div style="margin-left: 0.6rem; margin-right: 0.6rem; margin-bottom: 0.6rem;">';
        echo '  <div style="border-bottom: solid thin gray; padding: 0.3rem;">';
        echo '    <a href="https://en.wikipedia.org/wiki/Swatch_Internet_Time" target="_blank" style="color: gray; text-decoration: none; font-weight: bold; font-size: larger;">';
        echo '      <span>@</span><span id="beatsTime"></span>';
        echo '    </a>';
        echo '  </div>';
        echo '  <div>';
        echo '    <a href="https://time.is/" target="_blank" style="text-decoration: none; color: gray;">';
        echo '      <span>' . $this->escape($this->getText('day', 'Day')) . ':&nbsp;</span><span id="dayOfYear" style="font-weight: bold;"></span>';
        echo '      <span>&nbsp;' . $this->escape($this->getText('year', 'Year')) . ':&nbsp;</span><span id="currentYear" style="font-weight: bolder;"></span>';
        echo '      <span>&nbsp;' . $this->escape($this->getText('week', 'Week')) . ':&nbsp;</span><span id="weekNumber" style="font-weight: bold;"></span>';
        echo '      <span>&nbsp;' . $this->escape($this->getText('today_is', 'Today is')) . '&nbsp;</span><span id="dayOfWeek" style="font-weight: bold;"></span>';
        echo '      <span id="dayOfMonth" style="font-weight: bold;"></span><span id="dayPeriod"></span>';
        echo '      <span id="monthName" style="font-weight: bold;"></span>';
        echo '      <span id="dateYear" style="font-weight: bold;"></span>';
        echo '      <span id="currentTime" style="font-weight: bold;"></span>';
        echo '      <br>';
        echo '      <span>(</span><span id="timeZone" style="font-style: italic; font-variant: small-caps; font-size: small;"></span><span>)</span>';
        echo '    </a>';
        echo '  </div>';
        echo '</div>';
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
                
                // Add logout button
                $html .= '<p>' . $this->generatePostButton('logout', '', $this->getText('logout', 'Logout'), 'logout-link', '') . '</p>';
                
                $html .= '</div>';
            } else {
                $html .= '<p>' . $this->escape($this->getText('user_data_unavailable', 'User data unavailable')) . '</p>';
            }
        } else {
            $html .= '<div class="guest-user">';
            $html .= '<p>' . $this->escape($this->getText('not_logged_in', 'Not logged in')) . '</p>';
            $html .= '<p>' . $this->generatePostButton('login', '', $this->getText('login', 'Login'), 'login-link', '') . '</p>';
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
        $html .= '<li>' . $this->generatePostButton('', 'home', $this->getText('home', 'Home'), 'menu-item', 'icon-home') . '</li>';
        
        // Stories section
        $html .= '<li>' . $this->generatePostButton('', 'stories', $this->getText('stories', 'Stories'), 'menu-item', 'icon-stories') . '</li>';
        
        // Community section
        $html .= '<li>' . $this->generatePostButton('', 'community', $this->getText('community', 'Community'), 'menu-item', 'icon-community') . '</li>';
        
        // Resources section
        $html .= '<li>' . $this->generatePostButton('', 'resources', $this->getText('resources', 'Resources'), 'menu-item', 'icon-resources') . '</li>';
        
        // About section
        $html .= '<li>' . $this->generatePostButton('', 'about', $this->getText('about', 'About'), 'menu-item', 'icon-about') . '</li>';
        
        // Add authenticated user menu items
        if ($this->authenticationManager && $this->authenticationManager->isAuthenticated()) {
            $html .= '<li class="menu-separator"></li>';
            
            // My Stories
            $html .= '<li>' . $this->generatePostButton('', 'my-stories', $this->getText('my_stories', 'My Stories'), 'menu-item', 'icon-my-stories') . '</li>';
            
            // Profile
            $html .= '<li>' . $this->generatePostButton('', 'profile', $this->getText('profile', 'Profile'), 'menu-item', 'icon-profile') . '</li>';
            
            // Settings
            $html .= '<li>' . $this->generatePostButton('', 'settings', $this->getText('settings', 'Settings'), 'menu-item', 'icon-settings') . '</li>';
        } else {
            // Guest user menu items
            $html .= '<li class="menu-separator"></li>';
            
            // Login
            $html .= '<li>' . $this->generatePostButton('login', '', $this->getText('login', 'Login'), 'menu-item login-item', 'icon-login') . '</li>';
            
            // Register
            $html .= '<li>' . $this->generatePostButton('register', '', $this->getText('register', 'Register'), 'menu-item register-item', 'icon-register') . '</li>';
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
        
        // Get current section from POST or session
        $currentSection = 'home';
        if (isset($_POST['section'])) {
            $currentSection = $this->sanitizeInput($_POST['section']);
            // Store in session for persistence
            if ($this->sessionManager) {
                $this->sessionManager->setSession('current_section', $currentSection);
            }
        } elseif ($this->sessionManager) {
            $storedSection = $this->sessionManager->getSession('current_section');
            if ($storedSection) {
                $currentSection = $storedSection;
            }
        }
        
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
        $html .= $this->generatePostButton('new', 'stories', $this->getText('start_sharing', 'Start Sharing'), 'btn btn-primary');
        $html .= '</div>';
        
        $html .= '<div class="feature-card">';
        $html .= '<h3>' . $this->escape($this->getText('read_stories', 'Read Stories')) . '</h3>';
        $html .= '<p>' . $this->escape($this->getText('read_stories_desc', 'Find inspiration and comfort in the experiences of others in our community.')) . '</p>';
        $html .= $this->generatePostButton('', 'stories', $this->getText('browse_stories', 'Browse Stories'), 'btn btn-secondary');
        $html .= '</div>';
        
        $html .= '<div class="feature-card">';
        $html .= '<h3>' . $this->escape($this->getText('join_community', 'Join Community')) . '</h3>';
        $html .= '<p>' . $this->escape($this->getText('join_community_desc', 'Connect with others, participate in discussions, and build lasting friendships.')) . '</p>';
        $html .= $this->generatePostButton('', 'community', $this->getText('explore_community', 'Explore Community'), 'btn btn-secondary');
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="home-intro">';
        $html .= '<p>' . $this->escape($this->getText('home_intro2', 'This web application is designed to facilitate the sharing of personal tales and stories among individuals affected by kidney disorders, including those undergoing dialysis, those in the pre- or post-dialysis stage, and individuals living without the limitations of dialysis. This platform aims to foster a supportive community, allowing users to connect, share experiences, and provide insights that can help others navigate their journeys with kidney health.')) . '</p>';
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
     * Generate POST form button for navigation
     */
    private function generatePostButton($action, $section, $text, $class = '', $icon = '') {
        $csrfToken = $this->sessionManager ? $this->sessionManager->getCSRFToken() : 'no-token';
        
        $html = '<form method="POST" action="" style="display: inline;">';
        $html .= '<input type="hidden" name="_csrf_token" value="' . $this->escape($csrfToken) . '">';
        
        if ($action) {
            $html .= '<input type="hidden" name="action" value="' . $this->escape($action) . '">';
        }
        
        if ($section) {
            $html .= '<input type="hidden" name="section" value="' . $this->escape($section) . '">';
        }
        
        $html .= '<button type="submit" class="' . $this->escape($class) . ' post-nav-btn">';
        
        if ($icon) {
            $html .= '<i class="' . $this->escape($icon) . '"></i>';
        }
        
        $html .= $this->escape($text);
        $html .= '</button>';
        $html .= '</form>';
        
        return $html;
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
        echo '<section class="language-selection-flags">';
        echo $this->renderLanguageFlags();
        echo '</section>';
        
        // Safely get current language name
        $currentLangName = 'English';
        if ($this->languageModel && method_exists($this->languageModel, 'getCurrentLanguageName')) {
            $langName = $this->languageModel->getCurrentLanguageName();
            $currentLangName = is_string($langName) ? $langName : 'English';
        }
        
        echo '<div class="flags-language-welcome-message">';
        echo '<p>' . $this->escape($this->getText('current_language', 'Current language')) . ': <strong>' . $this->escape($currentLangName) . '</strong>. </p>';
        echo '<p>' . $this->escape($this->getText('welcome', 'Welcome')) . '</p>';
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
        $html = '<div class="language-selection-flags">';
        
        // Get current language for highlighting
        $currentLang = 'en';
        if (method_exists($this->languageModel, 'getCurrentLanguage')) {
            $currLang = $this->languageModel->getCurrentLanguage();
            $currentLang = is_string($currLang) ? $currLang : 'en';
        }
        
        foreach ($selectableLanguages as $lang) {
            $langFile = LANGUAGE_PATH . $lang . '.php';
            if (!file_exists($langFile)) continue;
            
            $langName = $this->languageModel->getLanguageName($lang);
            
            // Get best available flag path with fallback
            $flagPath = 'assets/flags/un.webp'; // Default fallback
            if ($this->languageModel->getLanguageDetector()) {
                $detector = $this->languageModel->getLanguageDetector();
                if (method_exists($detector, 'getBestFlagPath')) {
                    $flagPath = $detector->getBestFlagPath($lang, 'assets/flags/', $_SERVER['DOCUMENT_ROOT'] ?? '');
                } else {
                    $flagPath = $this->languageModel->getFlagPath($lang);
                }
            }
            
            // Add current language class for styling
            $currentClass = ($currentLang === $lang) ? ' current-language' : '';
            
            // Use secure POST form instead of GET link to avoid CSRF token exposure
            $html .= '<form method="POST" action="" class="flag-form' . $currentClass . '">';
            $html .= '<input type="hidden" name="lang" value="' . $this->escape($lang) . '">';
            
            // Add CSRF token if SecurityManager is available
            if ($this->sessionManager) {
                $csrfToken = $this->sessionManager->getCSRFToken();
                $html .= '<input type="hidden" name="_csrf_token" value="' . $this->escape($csrfToken) . '">';
            }
            
            $html .= '<button type="submit" class="flag-button" title="' . $this->escape($langName) . ' (' . $this->escape($lang) . ')">';
            $html .= '<img src="' . $this->escape($flagPath) . '" alt="' . $this->escape($langName) . '" class="flag-image" onerror="this.src=\'assets/flags/un.webp\'">';
            $html .= '<span class="flag-code">' . $this->escape($lang) . '</span>';
            $html .= '</button>';
            $html .= '</form>';
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
        echo 'if (!form.querySelector("input[name=\\"_csrf_token\\"]")) {';
        echo 'const csrfInput = document.createElement("input");';
        echo 'csrfInput.type = "hidden";';
        echo 'csrfInput.name = "_csrf_token";';
        echo 'csrfInput.value = "' . $this->escape($csrfToken) . '";';
        echo 'form.appendChild(csrfInput);';
        echo '}';
        echo '});';
        echo '});';
        
        // Get current language and convert to proper locale
        $currentLanguage = 'en';
        if ($this->languageModel && method_exists($this->languageModel, 'getCurrentLanguage')) {
            $lang = $this->languageModel->getCurrentLanguage();
            $currentLanguage = is_string($lang) ? $lang : 'en';
        }
        $localeMap = [
            'en' => 'en-US', 'sk' => 'sk-SK', 'de' => 'de-DE', 'fr' => 'fr-FR', 'es' => 'es-ES',
            'it' => 'it-IT', 'pt' => 'pt-PT', 'ru' => 'ru-RU', 'pl' => 'pl-PL', 'cs' => 'cs-CZ',
            'hu' => 'hu-HU', 'nl' => 'nl-NL', 'sv' => 'sv-SE', 'no' => 'nb-NO', 'da' => 'da-DK',
            'fi' => 'fi-FI', 'ja' => 'ja-JP', 'ko' => 'ko-KR', 'zh' => 'zh-CN', 'ar' => 'ar-SA',
            'he' => 'he-IL', 'hi' => 'hi-IN', 'th' => 'th-TH', 'tr' => 'tr-TR', 'vi' => 'vi-VN',
            'uk' => 'uk-UA', 'bg' => 'bg-BG', 'ro' => 'ro-RO', 'hr' => 'hr-HR', 'sr' => 'sr-RS',
            'sl' => 'sl-SI', 'et' => 'et-EE', 'lv' => 'lv-LV', 'lt' => 'lt-LT', 'el' => 'el-GR',
            'ca' => 'ca-ES', 'eu' => 'eu-ES', 'gl' => 'gl-ES', 'is' => 'is-IS', 'ga' => 'ga-IE',
            'cy' => 'cy-GB', 'mt' => 'mt-MT', 'sq' => 'sq-AL', 'mk' => 'mk-MK', 'bs' => 'bs-BA',
            'be' => 'be-BY', 'kk' => 'kk-KZ', 'ky' => 'ky-KG', 'uz' => 'uz-UZ', 'tg' => 'tg-TJ',
            'mn' => 'mn-MN', 'ka' => 'ka-GE', 'hy' => 'hy-AM', 'az' => 'az-AZ', 'fa' => 'fa-IR',
            'ur' => 'ur-PK', 'bn' => 'bn-BD', 'ta' => 'ta-IN', 'te' => 'te-IN', 'ml' => 'ml-IN',
            'kn' => 'kn-IN', 'gu' => 'gu-IN', 'pa' => 'pa-IN', 'ne' => 'ne-NP', 'si' => 'si-LK',
            'my' => 'my-MM', 'km' => 'km-KH', 'lo' => 'lo-LA', 'id' => 'id-ID', 'ms' => 'ms-MY',
            'tl' => 'tl-PH', 'sw' => 'sw-KE', 'am' => 'am-ET', 'om' => 'om-ET', 'so' => 'so-SO',
            'ha' => 'ha-NG', 'yo' => 'yo-NG', 'ig' => 'ig-NG', 'zu' => 'zu-ZA', 'xh' => 'xh-ZA',
            'af' => 'af-ZA', 'st' => 'st-ZA', 'tn' => 'tn-ZA', 'ss' => 'ss-ZA', 've' => 've-ZA'
        ];
        $locale = isset($localeMap[$currentLanguage]) ? $localeMap[$currentLanguage] : 'en-US';
        
        // Time and date display functionality
        echo 'const appLocale = "' . $this->escape($locale) . '";';
        echo 'function updateDateTimeDetails() {';
        echo 'try {';
        echo 'const now = new Date();';
        echo 'const dayOfYear = getDayOfYear(now);';
        echo 'const weekNumber = getWeekNumber(now);';
        echo 'let dayOfWeek, monthName, currentTime, timeZone;';
        echo 'try {';
        echo 'dayOfWeek = now.toLocaleString(appLocale, { weekday: "long" });';
        echo 'monthName = now.toLocaleString(appLocale, { month: "long" });';
        echo 'currentTime = now.toLocaleTimeString(appLocale, {timeZoneName: "short"});';
        echo 'const timeZoneFormatter = new Intl.DateTimeFormat(appLocale, {timeZoneName: "long"});';
        echo 'timeZone = timeZoneFormatter.format(now);';
        echo '} catch(localeError) {';
        echo 'console.warn("Locale error, falling back to English:", localeError);';
        echo 'dayOfWeek = now.toLocaleString("en-US", { weekday: "long" });';
        echo 'monthName = now.toLocaleString("en-US", { month: "long" });';
        echo 'currentTime = now.toLocaleTimeString("en-US", {timeZoneName: "short"});';
        echo 'const timeZoneFormatter = new Intl.DateTimeFormat("en-US", {timeZoneName: "long"});';
        echo 'timeZone = timeZoneFormatter.format(now);';
        echo '}';
        echo 'const dayOfMonth = now.getDate();';
        echo 'const currentYear = now.getFullYear();';
        echo 'const dateYear = now.getFullYear();';
        echo 'const beatsTime = calculateBeatsTime(now);';
        echo 'const dayPeriod = (appLocale.startsWith("sk") || appLocale.startsWith("cs") || appLocale.startsWith("pl")) ? "." : "";';
        echo 'if (document.getElementById("dayOfYear")) document.getElementById("dayOfYear").textContent = dayOfYear;';
        echo 'if (document.getElementById("weekNumber")) document.getElementById("weekNumber").textContent = weekNumber;';
        echo 'if (document.getElementById("dayOfWeek")) document.getElementById("dayOfWeek").textContent = dayOfWeek;';
        echo 'if (document.getElementById("dayOfMonth")) document.getElementById("dayOfMonth").textContent = dayOfMonth;';
        echo 'if (document.getElementById("dayPeriod")) document.getElementById("dayPeriod").textContent = dayPeriod;';
        echo 'if (document.getElementById("monthName")) document.getElementById("monthName").textContent = monthName;';
        echo 'if (document.getElementById("currentYear")) document.getElementById("currentYear").textContent = currentYear;';
        echo 'if (document.getElementById("dateYear")) document.getElementById("dateYear").textContent = dateYear;';
        echo 'if (document.getElementById("currentTime")) document.getElementById("currentTime").textContent = currentTime;';
        echo 'if (document.getElementById("timeZone")) document.getElementById("timeZone").textContent = timeZone;';
        echo 'if (document.getElementById("beatsTime")) document.getElementById("beatsTime").textContent = beatsTime.toFixed(2);';
        echo '} catch(error) {';
        echo 'console.error("Error updating date/time:", error);';
        echo '}';
        echo '}';
        
        echo 'function getDayOfYear(date) {';
        echo 'const start = new Date(date.getFullYear(), 0, 0);';
        echo 'const diff = (date - start) + ((start.getTimezoneOffset() - date.getTimezoneOffset()) * 60 * 1000);';
        echo 'const oneDay = 1000 * 60 * 60 * 24;';
        echo 'return Math.floor(diff / oneDay);';
        echo '}';
        
        echo 'function getWeekNumber(date) {';
        echo 'const firstDay = new Date(date.getFullYear(), 0, 1);';
        echo 'const days = Math.floor((date - firstDay) / (24 * 60 * 60 * 1000)) + ((firstDay.getDay() + 1) % 7);';
        echo 'return Math.ceil(days / 7);';
        echo '}';
        
        echo 'function calculateBeatsTime(date) {';
        echo 'const utcHours = date.getUTCHours();';
        echo 'const utcMinutes = date.getUTCMinutes();';
        echo 'const utcSeconds = date.getUTCSeconds();';
        echo 'return ((utcHours * 3600 + utcMinutes * 60 + utcSeconds) / 86.4) % 1000;';
        echo '}';
        
        echo 'setInterval(updateDateTimeDetails, 250);';
        
        echo '</script>';
    }
}
