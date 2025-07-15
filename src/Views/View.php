<?php
// src/Views/View.php

declare(strict_types=1);

namespace RenalTales\Views;

use RenalTales\Core\LanguageManager;
use RenalTales\Core\SessionManager;
use RenalTales\Core\SecurityManager;
use RenalTales\Models\LanguageModel;
use RenalTales\Controllers\ViewController;
use RenalTales\Controllers\LanguageController;
use RenalTales\View\ErrorView;



class View {

  private LanguageModel $languageModel;
  private SessionManager $sessionManager;
  private SecurityManager $securityManager;
  private ViewController $viewController;
  private string $html;

  public function __construct(LanguageModel $languageModel) {
    $this->languageModel = $languageModel;
    $this->html = '';
  }

  /**
   * Main render method that returns a complete HTML page
   *
   * @return string Complete HTML page
   */
  public function render(): string {
    $currentLanguage = $this->languageModel->getCurrentLanguage();
    $pageTitle = $this->languageModel->getText('home.title', [], 'Renal Tales');
    $pageDescription = $this->languageModel->getText('home.description', [], 'A modern PHP application for renal health management');
    $welcomeTitle = $this->languageModel->getText('home.welcome', [], 'Welcome to Renal Tales');
    $homeIntro = $this->languageModel->getText('home.description', [], 'Welcome to our supportive community for people affected by kidney disorders.');
    $homeIntro2 = $this->languageModel->getText('home_intro2', [], 'This platform aims to foster a supportive community.');
    $currentLanguageText = $this->languageModel->getText('current_language', [], 'Current language');
    $footerCopyright = $this->languageModel->getText('footer_copyright', [], 'Ľubomír Polaščín');

    return <<<HTML
<!DOCTYPE html>
<html lang="{$currentLanguage}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{$pageDescription}">
    <meta name="author" content="Ľubomír Polaščín">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#a0c4ff">
    <meta property="og:title" content="{$pageTitle}">
    <meta property="og:description" content="{$pageDescription}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="{$currentLanguage}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{$pageTitle}">
    <meta name="twitter:description" content="{$pageDescription}">

    <title>{$pageTitle}</title>

    <!-- Favicon links -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#a0c4ff">
    <meta name="msapplication-TileColor" content="#a0c4ff">

    <!-- CSS Styles -->
    <link rel="stylesheet" href="/assets/css/basic.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/post-navigation.css">

    <!-- Google Fonts are imported in basic.css -->

    <!-- Custom inline styles for responsive design -->
    <style>
        /* Additional responsive styles */
        .main-container {
            display: grid;
            grid-template-columns: 1fr 3fr 1fr;
            grid-template-areas: "menu content notes";
            gap: 1rem;
            margin: 0;
            padding: 0;
        }

        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            .main-container {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "content"
                    "menu"
                    "notes";
                gap: 1rem;
            }

            .main-header-container {
                flex-direction: column;
                text-align: center;
            }

            .main-header-container .left-section,
            .main-header-container .central-section,
            .main-header-container .right-section {
                margin: 0.5rem 0;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .language-selection-flags {
                grid-template-columns: repeat(auto-fit, minmax(50px, 1fr));
            }
        }

        /* Tablet responsive adjustments */
        @media (min-width: 769px) and (max-width: 1024px) {
            .main-container {
                grid-template-columns: 200px 1fr 250px;
            }

            .feature-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        /* Print styles */
        @media print {
            .main-menu-container,
            .main-notes-container,
            .language-selector {
                display: none;
            }

            .main-container {
                grid-template-columns: 1fr;
                grid-template-areas: "content";
            }

            body {
                font-size: 12pt;
                line-height: 1.4;
            }
        }
    </style>
</head>
<body>
    <!-- Main header container -->
    <header class="main-header-container">
        <div class="left-section">
            <img src="/assets/images/logos/logo_shifted.gif" alt="Renal Tales Logo" class="logo" onerror="this.style.display='none'">
            <h1>{$pageTitle}</h1>
            <h2>{$this->languageModel->getText('app_subtitle', [], 'A Multilingual Web Application')}</h2>
        </div>

        <div class="central-section">
            <h3>{$this->languageModel->getText('app_version', [], 'Version 2025.v3.0dev')}</h3>
            <h4>{$this->languageModel->getText('datetime_placeholder', [], 'Date, time including detailed internet time @beat will be displayed here.')}</h4>
        </div>

        <div class="right-section">
            {$this->renderLanguageSwitcher()}
            {$this->renderUserInfo()}
        </div>
    </header>

    <!-- Main content container -->
    <div class="main-container">
        <!-- Main menu -->
        <aside class="main-menu-container">
            {$this->renderMainMenu()}
        </aside>

        <!-- Main content -->
        <main class="main-content-container">
            <div class="content-body">
                <div class="home-section">
                    <h2>{$welcomeTitle}</h2>
                    <div class="home-intro">
                        <p>{$homeIntro}</p>
                        <p>{$homeIntro2}</p>
                    </div>

                    <div class="feature-grid">
                        <div class="feature-card">
                            <h3>{$this->languageModel->getText('share_story', [], 'Share Your Story')}</h3>
                            <p>{$this->languageModel->getText('share_story_desc', [], 'Your experience matters. Share your journey to inspire and support others.')}</p>
                            <a href="#" class="btn btn-primary">{$this->languageModel->getText('start_sharing', [], 'Start Sharing')}</a>
                        </div>

                        <div class="feature-card">
                            <h3>{$this->languageModel->getText('read_stories', [], 'Read Stories')}</h3>
                            <p>{$this->languageModel->getText('read_stories_desc', [], 'Find inspiration and comfort in the experiences of others in our community.')}</p>
                            <a href="#" class="btn btn-secondary">{$this->languageModel->getText('browse_stories', [], 'Browse Stories')}</a>
                        </div>

                        <div class="feature-card">
                            <h3>{$this->languageModel->getText('join_community', [], 'Join Community')}</h3>
                            <p>{$this->languageModel->getText('join_community_desc', [], 'Connect with others, participate in discussions, and build lasting friendships.')}</p>
                            <a href="#" class="btn btn-primary">{$this->languageModel->getText('explore_community', [], 'Explore Community')}</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Notes/sidebar -->
        <aside class="main-notes-container">
            {$this->renderNotes()}
        </aside>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 {$footerCopyright}</p>
        <p><small>{$this->languageModel->getText('current_language', [], 'Current language')}: <strong>{$currentLanguage}</strong></small></p>
    </footer>

    <!-- JavaScript for enhanced functionality -->
    <script>
        // Auto-submit language selector
        document.addEventListener('DOMContentLoaded', function() {
            const languageSelect = document.getElementById('lang-select');
            if (languageSelect) {
                languageSelect.addEventListener('change', function() {
                    this.form.submit();
                });
            }

            // Add loading state for language changes
            const languageForms = document.querySelectorAll('.flag-form');
            languageForms.forEach(form => {
                form.addEventListener('submit', function() {
                    const button = this.querySelector('.flag-button');
                    if (button) {
                        button.style.opacity = '0.6';
                        button.innerHTML = button.innerHTML + ' <span style="font-size: 0.8em;">...</span>';
                    }
                });
            });
        });
    </script>
</body>
</html>
HTML;
  }


  /**
   * Render the language switcher component
   *
   * @return string HTML for language switcher
   */
  private function renderLanguageSwitcher(): string {
    $currentLanguage = $this->languageModel->getCurrentLanguage();
    $supportedLanguages = $this->languageModel->getSupportedLanguages();
    $languageLabel = $this->languageModel->getText('language_selection', [], 'Language');
    $changeLabel = $this->languageModel->getText('change', [], 'Change');

    $html = '<div class="language-selector">';
    $html .= '<div class="language-selector-container">';
    $html .= '<form method="get" action="" class="language-form">';
    $html .= '<label for="lang-select" class="language-label">' . htmlspecialchars($languageLabel) . ':</label>';
    $html .= '<select name="lang" id="lang-select" class="language-select">';

    foreach ($supportedLanguages as $code) {
      $languageName = $this->languageModel->getLanguageName($code);
      $selected = $code === $currentLanguage ? 'selected' : '';
      $html .= '<option value="' . htmlspecialchars($code) . '" ' . $selected . '>';
      $html .= htmlspecialchars($languageName);
      $html .= '</option>';
    }

    $html .= '</select>';
    $html .= '<button type="submit" class="language-submit">' . htmlspecialchars($changeLabel) . '</button>';
    $html .= '</form>';
    $html .= '</div>';

    // Add flag-based language selection
    $html .= '<div class="language-selection-flags">';
    $html .= '<div class="flags-language-welcome-message">';
    $html .= $this->languageModel->getText('language_selection', [], 'Select your language');
    $html .= '</div>';

    foreach ($supportedLanguages as $code) {
      $languageName = $this->languageModel->getLanguageName($code);
      $flagCode = $this->languageModel->getFlagCode($code);
      $isCurrentLanguage = $code === $currentLanguage;
      $formClass = $isCurrentLanguage ? 'flag-form current-language' : 'flag-form';

      $html .= '<form method="get" action="" class="' . $formClass . '">';
      $html .= '<input type="hidden" name="lang" value="' . htmlspecialchars($code) . '">';
      $html .= '<button type="submit" class="flag-button" title="' . htmlspecialchars($languageName) . '">';
      $html .= '<img src="/assets/images/flags/' . htmlspecialchars($flagCode) . '.svg" alt="' . htmlspecialchars($languageName) . '" class="flag-image" onerror="this.style.display=\'none\'">';
      $html .= '<span class="flag-code">' . htmlspecialchars(strtoupper($code)) . '</span>';
      $html .= '</button>';
      $html .= '</form>';
    }

    $html .= '</div>';
    $html .= '</div>';

    return $html;
  }

  /**
   * Render user information section
   *
   * @return string HTML for user info
   */
  private function renderUserInfo(): string {
    $html = '<div class="user-info">';

    // Check if user is logged in (placeholder logic)
    $isLoggedIn = false; // TODO: Implement actual user session check

    if ($isLoggedIn) {
      $html .= '<div class="logged-in-user">';
      $html .= '<p>' . $this->languageModel->getText('welcome_user', [], 'Welcome') . ', <strong>User</strong></p>';
      $html .= '<p><small>' . $this->languageModel->getText('role', [], 'Role') . ': Member</small></p>';
      $html .= '</div>';
      $html .= '<a href="/logout" class="logout-link">' . $this->languageModel->getText('logout', [], 'Logout') . '</a>';
    } else {
      $html .= '<div class="guest-user">';
      $html .= '<p>' . $this->languageModel->getText('not_logged_in', [], 'Not logged in') . '</p>';
      $html .= '</div>';
      $html .= '<a href="/login" class="login-link">' . $this->languageModel->getText('login', [], 'Login') . '</a>';
    }

    $html .= '</div>';
    return $html;
  }

  /**
   * Render main navigation menu
   *
   * @return string HTML for main menu
   */
  private function renderMainMenu(): string {
    $html = '<nav class="main-menu">';
    $html .= '<h3>' . $this->languageModel->getText('main_menu', [], 'Main Menu') . '</h3>';
    $html .= '<ul>';

    $menuItems = [
      ['url' => '/', 'key' => 'nav.home', 'default' => 'Home'],
      ['url' => '/stories', 'key' => 'nav.stories', 'default' => 'Stories'],
      ['url' => '/community', 'key' => 'nav.community', 'default' => 'Community'],
      ['url' => '/resources', 'key' => 'nav.resources', 'default' => 'Resources'],
      ['url' => '/about', 'key' => 'nav.about', 'default' => 'About'],
    ];

    foreach ($menuItems as $item) {
      $html .= '<li>';
      $html .= '<a href="' . htmlspecialchars($item['url']) . '" class="menu-item">';
      $html .= htmlspecialchars($this->languageModel->getText($item['key'], [], $item['default']));
      $html .= '</a>';
      $html .= '</li>';
    }

    $html .= '<li class="menu-separator"></li>';

    // Check if user is logged in for conditional menu items
    $isLoggedIn = false; // TODO: Implement actual user session check

    if ($isLoggedIn) {
      $userMenuItems = [
        ['url' => '/my-stories', 'key' => 'nav.my_stories', 'default' => 'My Stories'],
        ['url' => '/profile', 'key' => 'nav.profile', 'default' => 'Profile'],
        ['url' => '/settings', 'key' => 'nav.settings', 'default' => 'Settings'],
      ];

      foreach ($userMenuItems as $item) {
        $html .= '<li>';
        $html .= '<a href="' . htmlspecialchars($item['url']) . '" class="menu-item">';
        $html .= htmlspecialchars($this->languageModel->getText($item['key'], [], $item['default']));
        $html .= '</a>';
        $html .= '</li>';
      }
    } else {
      $html .= '<li>';
      $html .= '<a href="/login" class="menu-item login-item">';
      $html .= htmlspecialchars($this->languageModel->getText('nav.login', [], 'Login'));
      $html .= '</a>';
      $html .= '</li>';
      $html .= '<li>';
      $html .= '<a href="/register" class="menu-item register-item">';
      $html .= htmlspecialchars($this->languageModel->getText('nav.register', [], 'Register'));
      $html .= '</a>';
      $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</nav>';

    return $html;
  }

  /**
   * Render notes/sidebar content
   *
   * @return string HTML for notes section
   */
  private function renderNotes(): string {
    $html = '<div class="content-notes">';
    $html .= '<h3>' . $this->languageModel->getText('important_notes', [], 'Important Notes') . '</h3>';

    // About section
    $html .= '<div class="note-section">';
    $html .= '<h4>' . $this->languageModel->getText('about_renal_tales', [], 'About Renal Tales') . '</h4>';
    $html .= '<p>' . $this->languageModel->getText('renal_tales_description', [], 'Renal Tales is a supportive community platform where people affected by kidney disorders can share their experiences, find support, and connect with others on similar journeys.') . '</p>';
    $html .= '</div>';

    // Community guidelines
    $html .= '<div class="note-section">';
    $html .= '<h4>' . $this->languageModel->getText('community_guidelines', [], 'Community Guidelines') . '</h4>';
    $html .= '<ul>';
    $html .= '<li>' . $this->languageModel->getText('guideline_respectful', [], 'Be respectful and supportive to all community members') . '</li>';
    $html .= '<li>' . $this->languageModel->getText('guideline_privacy', [], 'Respect privacy and confidentiality') . '</li>';
    $html .= '<li>' . $this->languageModel->getText('guideline_medical', [], 'Share experiences, not medical advice') . '</li>';
    $html .= '<li>' . $this->languageModel->getText('guideline_appropriate', [], 'Keep content appropriate and relevant') . '</li>';
    $html .= '</ul>';
    $html .= '</div>';

    // Getting started
    $html .= '<div class="note-section">';
    $html .= '<h4>' . $this->languageModel->getText('getting_started', [], 'Getting Started') . '</h4>';
    $html .= '<p>' . $this->languageModel->getText('getting_started_description', [], 'New to our community? Start by reading some stories, introduce yourself, and consider sharing your own experience when you\'re ready.') . '</p>';
    $html .= '</div>';

    // Support resources
    $html .= '<div class="note-section">';
    $html .= '<h4>' . $this->languageModel->getText('support_resources', [], 'Support Resources') . '</h4>';
    $html .= '<p>' . $this->languageModel->getText('support_description', [], 'If you need immediate medical help or are in crisis, please contact your healthcare provider or emergency services.') . '</p>';
    $html .= '</div>';

    $html .= '</div>';

    return $html;
  }
}