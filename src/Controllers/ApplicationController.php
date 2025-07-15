<?php

// File: /src/Controllers/ApplicationController.php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Services\LanguageService;
use RenalTales\Core\SessionManager;
use RenalTales\Core\SecurityManager;
use RenalTales\Views\HomeView;
use RenalTales\Views\ErrorView;

/**
 * Application Controller
 *
 * Handles the main application logic using dependency injection
 * and service layer architecture.
 *
 * @package RenalTales
 * @author Ľubomír Polaščín
 * @version 2025.3.1.dev
 */
class ApplicationController
{
    /**
     * @var LanguageService The language service
     */
    private LanguageService $languageService;

    /**
     * @var SessionManager The session manager
     */
    private SessionManager $sessionManager;

    /**
     * @var SecurityManager The security manager
     */
    private SecurityManager $securityManager;

    /**
     * @var ViewController The view controller
     */
    private ViewController $viewController;

    /**
     * @var string The current language
     */
    private string $currentLanguage;

    /**
     * @var string The requested page
     */
    private string $requestedPage;

    /**
     * Constructor with dependency injection
     *
     * @param LanguageService $languageService The language service
     * @param SessionManager $sessionManager The session manager
     * @param SecurityManager $securityManager The security manager
     */
    public function __construct(
        LanguageService $languageService,
        SessionManager $sessionManager,
        SecurityManager $securityManager
    ) {
        $this->languageService = $languageService;
        $this->sessionManager = $sessionManager;
        $this->securityManager = $securityManager;

        // Initialize the application
        $this->initialize();
    }

    /**
     * Initialize the application
     *
     * @return void
     */
    private function initialize(): void
    {
        // Get current language from service
        $this->currentLanguage = $this->languageService->getCurrentLanguage();

        // Determine requested page
        $this->requestedPage = $this->determineRequestedPage();

        // Create view controller with dependencies
        $this->viewController = new ViewController(
            $this->requestedPage,
            $this->currentLanguage,
            $this->languageService
        );
    }

    /**
     * Determine the requested page from request parameters
     *
     * @return string The requested page
     */
    private function determineRequestedPage(): string
    {
        if (isset($_GET['page']) && is_string($_GET['page'])) {
            return trim($_GET['page']);
        }

        if (isset($_POST['page']) && is_string($_POST['page'])) {
            return trim($_POST['page']);
        }

        return 'home';
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
     * Get the language service
     *
     * @return LanguageService The language service
     */
    public function getLanguageService(): LanguageService
    {
        return $this->languageService;
    }

    /**
     * Get the session manager
     *
     * @return SessionManager The session manager
     */
    public function getSessionManager(): SessionManager
    {
        return $this->sessionManager;
    }

    /**
     * Get the security manager
     *
     * @return SecurityManager The security manager
     */
    public function getSecurityManager(): SecurityManager
    {
        return $this->securityManager;
    }

    /**
     * Get the view controller
     *
     * @return ViewController The view controller
     */
    public function getViewController(): ViewController
    {
        return $this->viewController;
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

}
