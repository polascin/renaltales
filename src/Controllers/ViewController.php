<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Services\LanguageService;
use RenalTales\Contracts\ViewInterface;
use RenalTales\Views\HomeView;
use RenalTales\Views\ErrorView;

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
class ViewController implements ViewInterface
{
    private LanguageService $languageService;
    private string $requestedPage;
    private ViewInterface $view;
    private array $data = [];

    /**
     * Constructor with dependency injection
     *
     * @param string $requestedPage The requested page
     * @param LanguageService $languageService The language service
     */
    public function __construct(
        string $requestedPage,
        LanguageService $languageService
    ) {
        $this->languageService = $languageService;
        $this->requestedPage = $requestedPage;
        
        // Initialize the view
        $this->initializeView();
    }

    /**
     * Initialize the view based on requested page
     *
     * @return void
     */
    private function initializeView(): void
    {
        switch ($this->requestedPage) {
            case 'home':
                $this->view = new HomeView(
                    $this->languageService->getCurrentLanguage(),
                    'RenalTales',
                    $this->languageService->getSupportedLanguagesWithNames()
                );
                break;
            case 'login':
                // TODO: Implement LoginView
                $this->view = new HomeView(
                    $this->languageService->getCurrentLanguage(),
                    'RenalTales',
                    $this->languageService->getSupportedLanguagesWithNames()
                );
                break;
            default:
                $this->view = new HomeView(
                    $this->languageService->getCurrentLanguage(),
                    'RenalTales',
                    $this->languageService->getSupportedLanguagesWithNames()
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $data = []): string
    {
        $mergedData = array_merge($this->data, $data);
        return $this->view->render($mergedData);
    }

    /**
     * {@inheritdoc}
     */
    public function with(array $data): ViewInterface
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ViewController:' . $this->requestedPage;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(): bool
    {
        return $this->view->exists();
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
        return $this->languageService->getCurrentLanguage();
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
     * @return ViewInterface The view instance
     */
    public function getView(): ViewInterface
    {
        return $this->view;
    }
}
