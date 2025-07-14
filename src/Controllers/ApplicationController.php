<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Core\LanguageManager;
use RenalTales\Core\SessionManager;
use RenalTales\Views\HomeView;

/**
 * Application Controller
 *
 * This controller handles the main application logic
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class ApplicationController {
    private LanguageManager $languageManager;
    private SessionManager $sessionManager;

    /**
     * Constructor
     */
    public function __construct(LanguageManager $languageManager, SessionManager $sessionManager) {
        $this->languageManager = $languageManager;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Main index method
     *
     * @return string
     */
    public function index(): string {
        $lang = $this->languageManager->getCurrentLanguage();
        $appName = $_ENV['APP_NAME'] ?? 'Renal Tales';
        $view = new HomeView($lang, $appName);
        return $view->render();
    }
}
