<?php

// File: /src/Views/View.php

declare(strict_types=1);

namespace RenalTales\Views;

use RenalTales\Core\LanguageManager;
use RenalTales\Core\SessionManager;
use RenalTales\Core\SecurityManager;
use RenalTales\Services\LanguageService;
use RenalTales\Controllers\ViewController;
use RenalTales\Controllers\LanguageController;
use RenalTales\Views\ErrorView;

/**
 * View Class
 *
 * @package RenalTales
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */

class View {

  private LanguageService $languageService;
  private string $requestedPage;
  private string $currentLanguage;
  private string $html;
  private string $pageDescription = '';
  private string $pageTitle = '';

  public function __construct($requestedPage, $currentLanguage, LanguageService $languageService) {
    $this->languageService = $languageService;
    $this->requestedPage = $requestedPage;
    $this->currentLanguage = $currentLanguage;
    $this->html = '';

    // Set page title and description based on requested page
    switch ($requestedPage) {
      case 'home':
        $this->pageTitle = $this->languageService->getText('home_title', [], 'Renal Tales - Home');
        $this->pageDescription = $this->languageService->getText('home_description', [], 'Welcome to Renal Tales, a place to share and read stories about kidney health and wellness.');
        $this->renderHome();
        break;
      case 'stories':
        $this->pageTitle = $this->languageService->getText('stories_title', [], 'Stories');
        $this->pageDescription = $this->languageService->getText('stories_description', [], 'Read and share inspiring kidney health stories.');
        $this->renderStories();
        break;
      case 'community':
        $this->pageTitle = $this->languageService->getText('community_title', [], 'Community');
        $this->pageDescription = $this->languageService->getText('community_description', [], 'Join the Renal Tales community and connect with others.');
        $this->renderCommunity();
        break;
      case 'resources':
        $this->pageTitle = $this->languageService->getText('resources_title', [], 'Resources');
        $this->pageDescription = $this->languageService->getText('resources_description', [], 'Find helpful resources about kidney health.');
        $this->renderResources();
        break;
      case 'about':
        $this->pageTitle = $this->languageService->getText('about_title', [], 'About');
        $this->pageDescription = $this->languageService->getText('about_description', [], 'Learn more about Renal Tales.');
        $this->renderAbout();
        break;
      default:
        $this->pageTitle = $this->languageService->getText('notfound_title', [], 'Page Not Found');
        $this->pageDescription = $this->languageService->getText('notfound_description', [], 'Sorry, the page you are looking for does not exist.');
        $this->renderNotFound();
    }
  }

  private function renderHome() {
    $this->html .= $this->renderHTMLdesignation();
    $this->html .= $this->renderHTMLhead();
    $this->html .= $this->renderHTMLlanguageSwitcher();
    $this->html .= $this->renderHTMLheader();
    $this->html .= $this->renderHTMLwelcomePanel();
    $this->html .= $this->renderHTMLmainContent();
    $this->html .= $this->renderHTMLfootNotes();
    $this->html .= $this->renderHTMLfooter();
  }

  /**
   * Render the complete view
   */
  public function render(): void {
    echo $this->html;
    ob_flush();
  }

  private function renderStories() {
  }
  private function renderCommunity() {
  }
  private function renderResources() {
  }
  private function renderAbout() {
  }
  private function renderNotFound() {
  }

  private function renderHTMLdesignation() {
    $html = "<!DOCTYPE html>";
    $html .= "<html lang='{$this->currentLanguage}'>";
    return $html;
  }
  private function renderHTMLhead() {
    $html = '';
    $html .= "<head>";
    $html .= "<meta charset=\"UTF-8\">";
    $html .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">";
    $html .= "<meta name=\"description\" content=\"{$this->pageDescription}\">";
    $html .= "<meta name=\"author\" content=\"Ľubomír Polaščín\">";
    $html .= "<meta name=\"robots\" content=\"index, follow\">";
    $html .= "<meta name=\"theme-color\" content=\"#a0c4ff\">";
    $html .= "<meta property=\"og:title\" content=\"{$this->pageTitle}\">";
    $html .= "<meta property=\"og:description\" content=\"{$this->pageDescription}\">";
    $html .= "<meta property=\"og:type\" content=\"website\">";
    $html .= "<meta property=\"og:locale\" content=\"{$this->currentLanguage}\">";
    $html .= "<meta name=\"twitter:card\" content=\"summary\">";
    $html .= "<meta name=\"twitter:title\" content=\"{$this->pageTitle}\">";
    $html .= "<meta name=\"twitter:description\" content=\"{$this->pageDescription}\">";
    $html .= "<meta name=\"twitter:site\" content=\"@RenalTales\">";
    $html .= "<meta name=\"twitter:creator\" content=\"@RenalTales\">";
    $html .= "<meta name=\"twitter:locale\" content=\"{$this->currentLanguage}\">";
    $html .= "<title>{$this->pageTitle}</title>";
    $html .= "<link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"/apple-touch-icon.png\">";
    $html .= "<link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"/favicon-32x32.png\">";
    $html .= "<link rel=\"icon\" type=\"image/png\" sizes=\"16x16\" href=\"/favicon-16x16.png\">";
    $html .= "<link rel=\"manifest\" href=\"/site.webmanifest\">";
    $html .= "<link rel=\"mask-icon\" href=\"/safari-pinned-tab.svg\" color=\"#a0c4ff\">";
    $html .= "<link rel=\"stylesheet\" href=\"/assets/css/basic.css\">";
    $html .= "<link rel=\"stylesheet\" href=\"/assets/css/style.css\">";
    $html .= "<link rel=\"stylesheet\" href=\"/assets/css/post-navigation.css\">";
    $html .= "<link rel=\"stylesheet\" href=\"/assets/css/responsive.css\">";
    $html .= "<link rel=\"stylesheet\" href=\"/assets/css/footnotes.css\">";
    $html .= "<link rel=\"stylesheet\" href=\"/assets/css/footnotes-responsive.css\">";
    $html .= "<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css\">";
    $html .= "<link rel=\"stylesheet\" href=\"/assets/css/language-switcher.css\">";
    $html .= "</head>";
    $html .= "<body>";
    return $html;
  }

  private function renderHTMLlanguageSwitcher() {
  }
  private function renderHTMLheader() {
    $html = '';
    $html .= "<!-- Main header container -->";
    $html .= "<header class=\"main-header\">";
    $html .= "<div class=\"header-left\">";
    $html .= "<img src=\"/assets/images/logos/logo_shifted.gif\" alt=\"Renal Tales Logo\" class=\"logo\" onerror=\"this.style.display='none'\">";
    $html .= "<h1>{$this->languageService->getText('app_name', [], 'Renal Tales')}</h1>";
    $html .= "</div>";
    $html .= "<div class=\"header-center\">";
    $html .= "<h2>{$this->languageService->getText('app_subtitle', [], 'A Multilingual Web Application')}</h2>";
    $html .= "</div>";
    $html .= "<div class=\"header-right\">";
    $html .= $this->renderLanguageSwitcher();
    $html .= $this->renderUserInfo();
    $html .= "</div>";
    $html .= "</header>";
    return $html;
  }
  private function renderHTMLwelcomePanel() {
    $html = '';
    $html .= "<div class=\"welcome-panel\">";
    $html .= "<h2>{$this->languageService->getText('welcome_title', [], 'Welcome to Renal Tales')}</h2>";
    $html .= "<p>{$this->languageService->getText('welcome_message', [], 'A place to share and read stories about kidney health and wellness.')}</p>";
    $html .= "</div>";
    return $html;
  }
  private function renderHTMLmainContent() {
    $html = '';
    $html .= "<!-- Main content container -->";
    $html .= "<div class=\"main-container\">";
    $html .= "<!-- Main menu -->";
    $html .= "<aside class=\"main-menu-container\">";
    $html .= $this->renderMainMenu();
    $html .= "</aside>";
    $html .= "<!-- Main content -->";
    $html .= "<main class=\"main-content-container\">";
    $html .= "<div class=\"content-body\">";
    $html .= "<div class=\"home-section\">";
    $html .= "<h2>{$this->languageService->getText('welcome_title', [], 'Welcome to Renal Tales')}</h2>";
    $html .= "<div class=\"home-intro\">";
    $html .= "<p>{$this->languageService->getText('home_intro', [], 'Welcome to our supportive community for people affected by kidney disorders.')}</p>";
    $html .= "<p>{$this->languageService->getText('home_intro2', [], 'This web application is designed to facilitate the sharing of personal experiences among individuals affected by kidney disorders.')}</p>";
    $html .= "</div>";
    $html .= "<div class=\"feature-grid\">";
    $html .= "<div class=\"feature-card\">";
    $html .= "<h3>{$this->languageService->getText('share_story', [], 'Share Your Story')}</h3>";
    $html .= "<p>{$this->languageService->getText('share_story_desc', [], 'Your experience matters. Share your ourney to inspire and support others.')}</p>";
    $html .= "<a href=\"#\" class=\"btn btn-primary\">{$this->languageService->getText('start_sharing', [], 'Start haring')}</a>";
    $html .= "</div>";
    $html .= "<div class=\"feature-card\">";
    $html .= "<h3>{$this->languageService->getText('read_stories', [], 'Read Stories')}</h3>";
    $html .= "<p>{$this->languageService->getText('read_stories_desc', [], 'Find inspiration and comfort in the xperiences of others in our community.')}</p>";
    $html .= "<a href=\"#\" class=\"btn btn-secondary\">{$this->languageService->getText('browse_stories', [], 'Browse Stories')}</a>";
    $html .= "</div>";
    $html .= "<div class=\"feature-card\">";
    $html .= "<h3>{$this->languageService->getText('join_community', [], 'Join Community')}</h3>";
    $html .= "<p>{$this->languageService->getText('join_community_desc', [], 'Connect with others, participate n discussions, and build lasting friendships.')}</p>";
    $html .= "<a href=\"#\" class=\"btn btn-primary\">{$this->languageService->getText('explore_community', [], 'Explore Community')}</a>";
    $html .= "</div>";
    $html .= "</div>"; // feature-grid
    $html .= "</div>"; // home-section
    $html .= "</div>"; // content-body
    $html .= "</main>";
    $html .= "<!-- Notes/sidebar -->";
    $html .= "<aside class=\"main-notes-container\">";
    $html .= $this->renderNotes();
    $html .= "</aside>";
    $html .= "</div>"; // main-container
    return $html;
  }
  private function renderHTMLfootNotes() {
    $html = '';
    $html .= "<div class=\"footnotes\">";
    $html .= "<h2>{$this->languageService->getText('footnotes_title', [], 'Footnotes')}</h2>";
    $html .= "<ul>";
    $html .= "<li>{$this->languageService->getText('footnote_1', [], 'This is the first footnote.')}</li>";
    $html .= "<li>{$this->languageService->getText('footnote_2', [], 'This is the second footnote.')}</li>";
    $html .= "</ul>";
    $html .= "</div>";
    return $html;
  }
  private function renderHTMLfooter() {
    $html = '';
    $html .= "<footer class=\"main-footer\">";
    $html .= "<p>{$this->languageService->getText('footer_text', [], '© 2025 Renal Tales. All rights reserved.')}</p>";
    $html .= "</footer>";
    return $html;
  }

  /**
   * Renders the language switcher HTML.
   * This method is a placeholder and should be implemented to display language options.
   *
   * @return string The HTML for the language switcher.
   */
  private function renderLanguageSwitcher(): string {
    // Placeholder for language switcher logic.
    // This would typically involve iterating through supported languages
    // and creating links or a dropdown to switch languages.
    return '<div class="language-switcher">Language Switcher Placeholder</div>';
  }

  /**
   * Renders the user information section HTML.
   * This method is a placeholder and should be implemented to display user-specific information
   * like login/logout links, username, etc.
   *
   * @return string The HTML for the user information section.
   */
  private function renderUserInfo(): string {
    return '<div class="user-info">User Info Placeholder</div>';
  }

  /**
   * Renders the main menu HTML.
   * This method is a placeholder and should be implemented to display the main navigation menu.
   *
   * @return string The HTML for the main menu.
   */
  private function renderMainMenu(): string {
    // Placeholder for main menu logic.
    // You can replace this with actual menu items as needed.
    return '<nav class="main-menu">Main Menu Placeholder</nav>';
  }

  /**
   * Renders the notes section HTML.
   * This method is a placeholder and should be implemented to display notes or sidebar content.
   *
   * @return string The HTML for the notes section.
   */
  private function renderNotes(): string {
    // Placeholder for notes/sidebar logic.
    return '<div class="notes-section">Notes Placeholder</div>';
  }
}
