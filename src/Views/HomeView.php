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
 * @version 2025.v4.0dev
 */
class HomeView
{
    private ?LanguageModel $languageModel;
    private string $appName;
    private array $supportedLanguages;

    /**
     * HomeView constructor
     *
     * @param string|LanguageModel $language The language string or LanguageModel instance
     * @param string $appName The application name
     * @param array $supportedLanguages Array of supported languages (code => name)
     */
    public function __construct(string|LanguageModel|null $language, string $appName, array $supportedLanguages = [])
    {
        // Handle missing or invalid language model gracefully
        try {
            if ($language === null) {
                $this->languageModel = null;
            } elseif (is_string($language)) {
                $this->languageModel = new LanguageModel();
                $this->languageModel->setLanguage($language);
            } else {
                $this->languageModel = $language;
            }
        } catch (Exception $e) {
            // Log error and fallback to null
            error_log("HomeView: Failed to initialize language model - " . $e->getMessage());
            $this->languageModel = null;
        }
        
        $this->appName = htmlspecialchars($appName, ENT_QUOTES, 'UTF-8');
        $this->supportedLanguages = $supportedLanguages ?: [
            'en' => 'English',
            'sk' => 'Slovak',
            'la' => 'Latin',
        ];
    }

    /**
     * Render the home page
     *
     * @return string The rendered home page HTML
     */
    public function render(): string
    {
        try {
            $currentLanguage = $this->getCurrentLanguage();
            
            // Get all text content using new translation keys with fallback values
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
            
            // Navigation items using new navigation keys with fallback values
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
    {$this->getHomePageStyles()}
</head>
<body>
    <!-- Header Section -->
    <header class="main-header-container">
        <div class="left-section">
            <img src="/assets/images/logo.png" alt="{$pageTitle} Logo" class="logo" onerror="this.style.display='none'">
            <h1>{$pageTitle}</h1>
        </div>
        <div class="central-section">
            <nav class="main-navigation">
                <ul>
                    <li><a href="/" class="nav-item active">{$home}</a></li>
                    <li><a href="/stories" class="nav-item">{$stories}</a></li>
                    <li><a href="/community" class="nav-item">{$community}</a></li>
                    <li><a href="/about" class="nav-item">{$about}</a></li>
                </ul>
            </nav>
        </div>
        <div class="right-section">
            <div class="auth-section">
                <a href="/login" class="btn btn-primary">{$login}</a>
                <a href="/register" class="btn btn-secondary">{$register}</a>
            </div>
            {$languageSwitcher}
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
</body>
</html>
HTML;
    }

    /**
     * Render the language switcher component
     *
     * @return string The language switcher HTML
     */
    private function renderLanguageSwitcher(): string
    {
        try {
            $currentLanguage = $this->getCurrentLanguage();
            $languageLabel = $this->getText('current_language', 'Language');
            
            $options = '';
            foreach ($this->supportedLanguages as $code => $name) {
                $selected = $code === $currentLanguage ? 'selected' : '';
                $options .= "<option value=\"" . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . "\" {$selected}>" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</option>";
            }
            
            return <<<HTML
<div class="language-selector">
    <div class="language-selector-container">
        <form class="language-form" method="get" action="">
            <label for="lang-select" class="language-label">{$languageLabel}:</label>
            <select name="lang" id="lang-select" class="language-select" onchange="this.form.submit()">
                {$options}
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
     * Get home page specific styles
     *
     * @return string The CSS styles
     */
    private function getHomePageStyles(): string
    {
        return <<<CSS
<style>
    /* Hero Section Styles */
    .hero-section {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        padding: 3rem 1rem;
        text-align: center;
        margin-bottom: 2rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .hero-content {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .hero-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .hero-intro {
        font-size: 1.25rem;
        line-height: 1.6;
        margin-bottom: 0;
        opacity: 0.95;
    }
    
    /* Navigation Styles */
    .main-navigation ul {
        list-style: none;
        display: flex;
        gap: 1rem;
        margin: 0;
        padding: 0;
        justify-content: center;
    }
    
    .main-navigation .nav-item {
        text-decoration: none;
        color: var(--text-color);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .main-navigation .nav-item:hover,
    .main-navigation .nav-item.active {
        background-color: var(--primary-color);
        color: white;
    }
    
    /* Auth Section Styles */
    .auth-section {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .auth-section .btn {
        padding: 0.5rem 1rem;
        text-decoration: none;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: var(--primary-dark);
    }
    
    .btn-secondary {
        background-color: var(--secondary-color);
        color: var(--text-color);
    }
    
    .btn-secondary:hover {
        background-color: var(--secondary-dark);
    }
    
    /* Footer Styles */
    .main-footer {
        background-color: var(--panel-bg);
        border-top: 1px solid var(--panel-border);
        margin-top: 3rem;
        padding: 2rem 1rem 1rem;
        border-radius: 1rem 1rem 0 0;
    }
    
    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .footer-section h4 {
        color: var(--primary-color);
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }
    
    .footer-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .footer-section ul li {
        margin-bottom: 0.5rem;
    }
    
    .footer-section a {
        color: var(--text-color);
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .footer-section a:hover {
        color: var(--primary-color);
    }
    
    .footer-bottom {
        text-align: center;
        padding-top: 1rem;
        border-top: 1px solid var(--panel-border);
        color: var(--text-color);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .main-navigation ul {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .auth-section {
            flex-direction: column;
            width: 100%;
        }
        
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-intro {
            font-size: 1.1rem;
        }
        
        .main-header-container {
            flex-direction: column;
            text-align: center;
        }
        
        .main-header-container .left-section,
        .main-header-container .central-section,
        .main-header-container .right-section {
            margin-right: 0;
            margin-bottom: 1rem;
        }
    }
</style>
CSS;
    }

    /**
     * Get the language model
     *
     * @return LanguageModel The language model instance
     */
    public function getLanguageModel(): ?LanguageModel
    {
        return $this->languageModel;
    }

    /**
     * Get the application name
     *
     * @return string The application name
     */
    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * Get translated text with fallback support
     *
     * @param string $key The translation key
     * @param string $fallback The fallback text
     * @return string The translated text
     */
    private function getText(string $key, string $fallback): string
    {
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
    private function getCurrentLanguage(): string
    {
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
    private function getErrorTemplate(string $title, string $message): string
    {
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
