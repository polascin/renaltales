<?php

declare(strict_types=1);

namespace RenalTales\Views;

use RenalTales\Models\LanguageModel;
use Exception;

/**
 * Home View Class
 *
 * Handles the display and rendering of the home page for the RenalTales application
 * Features comprehensive layout with header, hero section, main content, language switcher, and footer
 *
 * @author Ľubomír Polaščín
 * @package RenalTales
 * @version 2025.3.1.dev
 */
class HomeView extends AbstractView
{
    private array $supportedLanguages;

    /**
     * HomeView constructor
     *
     * @param string|LanguageModel|null $language The language string or LanguageModel instance
     * @param string $appName The application name
     * @param array $supportedLanguages Array of supported languages (code => name)
     */
    public function __construct($language = null, string $appName = 'RenalTales', array $supportedLanguages = [])
    {
        parent::__construct($language, $appName);

        $this->supportedLanguages = $supportedLanguages ?: [
            'en' => 'English',
            'sk' => 'Slovak',
            'la' => 'Latin',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $data = []): string
    {
        try {
            $this->data = array_merge($this->data, $data);
            $currentLanguage = $this->getCurrentLanguage();

            // Get all text content using translation keys with fallback values
            $pageTitle = $this->getText('home.title', 'Renal Tales - Home');
            $welcomeTitle = $this->getText('home.welcome', "Welcome to {$this->appName}!");
            $homeIntro = $this->getText('home.description', 'Welcome to our supportive community for people affected by kidney disorders.');
            $homeDescription = $this->getText('home_intro2', 'This web application is designed to facilitate the sharing of personal experiences among individuals affected by kidney disorders.');

            // Feature cards content
            $shareStoryTitle = $this->getText('share_story', 'Share Your Story');
            $shareStoryDesc = $this->getText('share_story_desc', 'Your experience matters. Share your journey to inspire and support others.');
            $startSharing = $this->getText('start_sharing', 'Start Sharing');

            $readStoriesTitle = $this->getText('read_stories', 'Read Stories');
            $readStoriesDesc = $this->getText('read_stories_desc', 'Find inspiration and comfort in the experiences of others in our community.');
            $browseStories = $this->getText('browse_stories', 'Browse Stories');

            $joinCommunityTitle = $this->getText('join_community', 'Join Community');
            $joinCommunityDesc = $this->getText('join_community_desc', 'Connect with others, participate in discussions, and build lasting friendships.');
            $exploreCommunity = $this->getText('explore_community', 'Explore Community');

            // Navigation items using navigation keys with fallback values
            $home = $this->getText('nav.home', 'Home');
            $stories = $this->getText('nav.stories', 'Stories');
            $community = $this->getText('nav.community', 'Community');
            $about = $this->getText('nav.about', 'About');
            $login = $this->getText('nav.login', 'Login');
            $register = $this->getText('nav.register', 'Register');

            // Footer
            $footerCopyright = $this->getText('footer_copyright', 'Ľubomír Polaščín');
            $currentYear = date('Y');

            return $this->getHomePageTemplate(
                $currentLanguage,
                $pageTitle,
                $welcomeTitle,
                $homeIntro,
                $homeDescription,
                $shareStoryTitle,
                $shareStoryDesc,
                $startSharing,
                $readStoriesTitle,
                $readStoriesDesc,
                $browseStories,
                $joinCommunityTitle,
                $joinCommunityDesc,
                $exploreCommunity,
                $home,
                $stories,
                $community,
                $about,
                $login,
                $register,
                $footerCopyright,
                $currentYear
            );
        } catch (Exception $e) {
            // Log error and return safe error message
            error_log("HomeView: Error rendering page - " . $e->getMessage());
            return $this->getErrorTemplate('Home Page Error', 'An error occurred while loading the home page. Please try again later.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'home';
    }

  /**
   * Get the home page template
   *
   * @param string $currentLanguage Current language code
   * @param string $pageTitle Page title
   * @param string $welcomeTitle Welcome title
   * @param string $homeIntro Home introduction text
   * @param string $homeDescription Home description text
   * @param string $shareStoryTitle Share story title
   * @param string $shareStoryDesc Share story description
   * @param string $startSharing Start sharing button text
   * @param string $readStoriesTitle Read stories title
   * @param string $readStoriesDesc Read stories description
   * @param string $browseStories Browse stories button text
   * @param string $joinCommunityTitle Join community title
   * @param string $joinCommunityDesc Join community description
   * @param string $exploreCommunity Explore community button text
   * @param string $home Home navigation text
   * @param string $stories Stories navigation text
   * @param string $community Community navigation text
   * @param string $about About navigation text
   * @param string $login Login navigation text
   * @param string $register Register navigation text
   * @param string $footerCopyright Footer copyright text
   * @param string $currentYear Current year
   * @return string The HTML template
   */
  private function getHomePageTemplate(
    string $currentLanguage,
    string $pageTitle,
    string $welcomeTitle,
    string $homeIntro,
    string $homeDescription,
    string $shareStoryTitle,
    string $shareStoryDesc,
    string $startSharing,
    string $readStoriesTitle,
    string $readStoriesDesc,
    string $browseStories,
    string $joinCommunityTitle,
    string $joinCommunityDesc,
    string $exploreCommunity,
    string $home,
    string $stories,
    string $community,
    string $about,
    string $login,
    string $register,
    string $footerCopyright,
    string $currentYear
  ): string {
    $languageSwitcher = $this->renderLanguageSwitcher();

    return <<<HTML
<!DOCTYPE html>
<html lang="{$currentLanguage}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RenalTales - A supportive community platform for people affected by kidney disorders">
    <meta name="author" content="Ľubomír Polaščín">
    <title>{$pageTitle}</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="stylesheet" href="/assets/css/basic.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/navigation.css">
    <link rel="stylesheet" href="/assets/css/language-switcher.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body>
    <!-- Header Section -->
<header class="main-header" role="banner">
    <div class="container">
        <div class="main-header-container">
            <div class="left-section">
                <a href="/" class="navbar-brand">
                    <img src="/assets/images/logos/logo.gif" alt="{$pageTitle} Logo" class="logo" role="img" onerror="this.style.display='none'">
                </a>
            </div>
            <div class="central-section">
                <h1>{$this->getAppName()}</h1>
                <h2>{$this->trans('app.subtitle', 'Supporting Kidney Health Stories')}</h2>
            </div>
            <div class="right-section">
                {$languageSwitcher}
            </div>
        </div>
        <nav class="navbar navbar-expand-md">
            <button class="navbar-toggler" type="button" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active"><a href="/" class="nav-link">{$home}</a></li>
                    <li class="nav-item"><a href="/stories" class="nav-link">{$stories}</a></li>
                    <li class="nav-item"><a href="/community" class="nav-link">{$community}</a></li>
                    <li class="nav-item"><a href="/about" class="nav-link">{$about}</a></li>
                    <li class="nav-item"><a href="/login" class="nav-link btn btn-primary">{$login}</a></li>
                    <li class="nav-item"><a href="/register" class="nav-link btn btn-secondary">{$register}</a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>

    <!-- Hero/Welcome Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h2 class="hero-title">{$welcomeTitle}</h2>
            <p class="hero-intro">{$homeIntro}</p>
        </div>
    </section>

    <!-- Main Content Area -->
    <main class="main-container">
        <div class="main-content-container">
            <div class="content-body">
                <section class="home-section">
                    <div class="home-intro">
                        <p>{$homeDescription}</p>
                    </div>

                    <div class="feature-grid">
                        <div class="feature-card">
                            <h3>{$shareStoryTitle}</h3>
                            <p>{$shareStoryDesc}</p>
                            <a href="/stories/create" class="btn btn-primary">{$startSharing}</a>
                        </div>

                        <div class="feature-card">
                            <h3>{$readStoriesTitle}</h3>
                            <p>{$readStoriesDesc}</p>
                            <a href="/stories" class="btn btn-secondary">{$browseStories}</a>
                        </div>

                        <div class="feature-card">
                            <h3>{$joinCommunityTitle}</h3>
                            <p>{$joinCommunityDesc}</p>
                            <a href="/community" class="btn btn-primary">{$exploreCommunity}</a>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <aside class="main-menu-container">
            <div class="main-menu">
                <h3>{$this->getText('main_menu', 'Quick Navigation')}</h3>
                <ul>
                    <li><a href="/" class="menu-item">{$home}</a></li>
                    <li><a href="/stories" class="menu-item">{$stories}</a></li>
                    <li><a href="/community" class="menu-item">{$community}</a></li>
                    <li><a href="/about" class="menu-item">{$about}</a></li>
                    <li class="menu-separator"></li>
                    <li><a href="/login" class="menu-item login-item">{$login}</a></li>
                    <li><a href="/register" class="menu-item register-item">{$register}</a></li>
                </ul>
            </div>
        </aside>

        <aside class="main-notes-container">
            <div class="content-notes">
                <h3>{$this->getText('important_notes', 'Important Notes')}</h3>
                <div class="note-section">
                    <h4>{$this->getText('about_renal_tales', 'About Renal Tales')}</h4>
                    <p>{$this->getText('renal_tales_description', 'Renal Tales is a supportive community platform where people affected by kidney disorders can share their experiences, find support, and connect with others on similar journeys.')}</p>
                </div>
                <div class="note-section">
                    <h4>{$this->getText('community_guidelines', 'Community Guidelines')}</h4>
                    <ul>
                        <li>{$this->getText('guideline_respectful', 'Be respectful and supportive to all community members')}</li>
                        <li>{$this->getText('guideline_privacy', 'Respect privacy and confidentiality')}</li>
                        <li>{$this->getText('guideline_medical', 'Share experiences, not medical advice')}</li>
                        <li>{$this->getText('guideline_appropriate', 'Keep content appropriate and relevant')}</li>
                    </ul>
                </div>
                <div class="note-section">
                    <h4>{$this->getText('support_resources', 'Support Resources')}</h4>
                    <p>{$this->getText('support_description', 'If you need immediate medical help or are in crisis, please contact your healthcare provider or emergency services.')}</p>
                </div>
            </div>
        </aside>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>{$pageTitle}</h4>
                <p>A supportive community for people affected by kidney disorders.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="/">{$home}</a></li>
                    <li><a href="/stories">{$stories}</a></li>
                    <li><a href="/community">{$community}</a></li>
                    <li><a href="/about">{$about}</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Language</h4>
                <p>Current: {$currentLanguage}</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {$currentYear} {$footerCopyright}. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript Files -->
    <script src="/assets/js/language-switcher.js"></script>
    <script>
        // Mobile navigation toggle
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggler = document.querySelector('.navbar-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');

            if (navbarToggler && navbarCollapse) {
                navbarToggler.addEventListener('click', function() {
                    const isExpanded = navbarCollapse.classList.contains('show');

                    if (isExpanded) {
                        navbarCollapse.classList.remove('show');
                        navbarToggler.setAttribute('aria-expanded', 'false');
                    } else {
                        navbarCollapse.classList.add('show');
                        navbarToggler.setAttribute('aria-expanded', 'true');
                    }
                });

                // Close mobile menu when clicking on a link
                const navLinks = navbarCollapse.querySelectorAll('.nav-link');
                navLinks.forEach(function(link) {
                    link.addEventListener('click', function() {
                        navbarCollapse.classList.remove('show');
                        navbarToggler.setAttribute('aria-expanded', 'false');
                    });
                });

                // Close mobile menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!navbarToggler.contains(e.target) && !navbarCollapse.contains(e.target)) {
                        navbarCollapse.classList.remove('show');
                        navbarToggler.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            // Language switcher keyboard shortcuts help
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.altKey && e.key === 'h') {
                    e.preventDefault();
                    alert('Language Switcher Shortcuts:\n\nCtrl+Alt+L: Focus language selector\nCtrl+Alt+E: Switch to English\nCtrl+Alt+S: Switch to Slovak');
                }
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
   * @return string The language switcher HTML
   */
  private function renderLanguageSwitcher(): string {
    try {
      $currentLanguage = $this->getCurrentLanguage();
      $languageLabel = $this->getText('current_language', 'Language');
      $switchLanguageText = $this->getText('switch_language', 'Switch Language');
      $currentLanguageText = $this->getText('current_language_is', 'Current language is');

        $options = '';
        // Iterate through supported languages to build options
        foreach ($this->supportedLanguages as $code => $name) {
            $selected = $code === $currentLanguage ? 'selected' : '';
            $flagClass = 'flag-' . strtolower($code);
            $displayText = htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') . ' [' . strtoupper(htmlspecialchars((string)$code, ENT_QUOTES, 'UTF-8')) . ']';
            $options .= "<option value=\"" . htmlspecialchars((string)$code, ENT_QUOTES, 'UTF-8') . "\" {$selected} data-flag=\"{$flagClass}\">" . $displayText . "</option>";
        }

        // Get current language name for display
        $currentLanguageName = $this->supportedLanguages[$currentLanguage] ?? 'English';
        $tooltipText = $currentLanguageText . ' ' . $currentLanguageName;

        // HTML structure for language selector
        return <<<HTML
<div class="language-selector">
    <div class="language-switcher" data-tooltip="{$tooltipText}" role="group" aria-label="{$switchLanguageText}">
        <form class="language-form" method="get" action="" onsubmit="this.querySelector('.language-switcher').classList.add('loading')">
            <label for="lang-select" class="language-label sr-only">{$languageLabel}:</label>
            <select name="lang" id="lang-select" class="form-select language-select" 
                    onchange="this.form.submit()" 
                    aria-label="{$switchLanguageText}"
                    title="{$switchLanguageText}">
                {$options} <!-- Render language options -->
            </select>
        </form>
    </div>
</div>
HTML;
    } catch (Exception $e) {
      // Log error and return empty string to prevent page breaking
      error_log("HomeView: Error rendering language switcher - " . $e->getMessage());
      return '';
    }
  }


  /**
   * Get the language model
   *
   * @return LanguageModel The language model instance
   */
  public function getLanguageModel(): ?LanguageModel {
    return $this->languageModel;
  }

  /**
   * Get the application name
   *
   * @return string The application name
   */
  public function getAppName(): string {
    return $this->appName;
  }

  /**
   * Get translated text with fallback support
   *
   * @param string $key The translation key
   * @param string $fallback The fallback text
   * @return string The translated text
   */
  private function getText(string $key, string $fallback): string {
    if ($this->languageModel && method_exists($this->languageModel, 'getText')) {
      try {
        return htmlspecialchars($this->languageModel->getText($key, [], $fallback), ENT_QUOTES, 'UTF-8');
      } catch (Exception $e) {
        error_log("HomeView: Error getting text for key '{$key}' - " . $e->getMessage());
        return htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
      }
    }

    return htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
  }

  /**
   * Get current language with fallback
   *
   * @return string The current language code
   */
  protected function getCurrentLanguage(): string {
    if ($this->languageModel && method_exists($this->languageModel, 'getCurrentLanguage')) {
      try {
        return $this->languageModel->getCurrentLanguage();
      } catch (Exception $e) {
        error_log("HomeView: Error getting current language - " . $e->getMessage());
        return 'en';
      }
    }

    return 'en';
  }

  /**
   * Get error template for safe error display
   *
   * @param string $title The error title
   * @param string $message The error message
   * @return string The error HTML template
   */
  private function getErrorTemplate(string $title, string $message): string {
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $safeAppName = htmlspecialchars($this->appName, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeTitle} - {$safeAppName}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .error-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            margin-top: 50px;
        }

        .error-icon {
            font-size: 64px;
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: 28px;
            color: #e74c3c;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .error-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .btn {
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            background-color: #3498db;
            color: white;
            margin: 0 10px;
        }

        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">{$safeTitle}</h1>
        <p class="error-message">{$safeMessage}</p>
        <a href="javascript:history.back()" class="btn">Go Back</a>
        <a href="/" class="btn">Go Home</a>
    </div>
</body>
</html>
HTML;
  }
}
