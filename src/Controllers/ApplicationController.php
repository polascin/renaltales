<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Models\LanguageModel;
use RenalTales\Controllers\LanguageController;
use RenalTales\Core\SessionManager;
use RenalTales\Core\SecurityManager;
use RenalTales\Views\HomeView;
use RenalTales\Views\ErrorView;

/**
 * Application Controller
 *
 * This controller handles the main application logic
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class ApplicationController {
    private LanguageModel $languageModel;
    private SessionManager $sessionManager;

    /**
     * Constructor
     */
    public function __construct(LanguageModel $languageModel, SessionManager $sessionManager) {
        $this->languageModel = $languageModel;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Main index method
     *
     * @return string
     */
    public function index(): string {
        $lang = $this->languageModel->getCurrentLanguage();
        $appName = $_ENV['APP_NAME'] ?? 'Renal Tales';
        $view = new HomeView($lang, $appName);
        return $view->render();
    }
}
