<?php

// File: /src/Controllers/ViewController.php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Services\LanguageService;
use RenalTales\Core\SecurityManager;
use RenalTales\Core\SessionManager;
use RenalTales\Views\View;

/**
 * View Controller
 *
 * Handles the display and rendering of views for the application
 * using dependency injection and service layer architecture.
 *
 * @package RenalTales
 * @author Ľubomír Polaščín
 * @version 2025.3.1.dev
 */
class ViewController
{
    /**
     * @var LanguageService The language service
     */
    private LanguageService $languageService;

    /**
     * @var string The current language
     */
    private string $currentLanguage;

    /**
     * @var string The requested page
     */
    private string $requestedPage;

    /**
     * @var View The view instance
     */
    private View $view;

    /**
     * Constructor with dependency injection
     *
     * @param string $requestedPage The requested page
     * @param string $currentLanguage The current language
     * @param LanguageService $languageService The language service
     */
    public function __construct(
        string $requestedPage,
        string $currentLanguage,
        LanguageService $languageService
    ) {
        $this->languageService = $languageService;
        $this->currentLanguage = $currentLanguage;
        $this->requestedPage = $requestedPage;
        
        // Initialize the view
        $this->initializeView();
    }

    /**
     * Initialize the view
     *
     * @return void
     */
    private function initializeView(): void
    {
        $this->view = new View(
            $this->requestedPage,
            $this->currentLanguage,
            $this->languageService
        );
    }

    /**
     * Get the language service
     *
     * @return LanguageService The language service
     */
    public function getLanguageService(): LanguageService
    {
        return $this->languageService;
    }

    /**
     * Get the current language
     *
     * @return string The current language
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * Get the requested page
     *
     * @return string The requested page
     */
    public function getRequestedPage(): string
    {
        return $this->requestedPage;
    }

    /**
     * Get the view instance
     *
     * @return View The view instance
     */
    public function getView(): View
    {
        return $this->view;
    }

    /**
     * Render the view
     *
     * @return void
     */
    public function render(): void
    {
        $this->view->render();
    }
}
