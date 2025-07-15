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
 * @version 2025.v3.0dev
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
        // Check if language parameter is provided and set it
        if (isset($_GET['lang']) && is_string($_GET['lang'])) {
            $requestedLang = trim($_GET['lang']);
            if ($this->languageModel->isSupported($requestedLang)) {
                $this->languageModel->setLanguage($requestedLang);
            }
        }

        $lang = $this->languageModel->getCurrentLanguage();
        $appName = $this->languageModel->getText('app_title', [], 'RenalTales');

        // Get supported languages from the language model
        $supportedLanguages = $this->languageModel->getSupportedLanguages();

        $view = new HomeView($this->languageModel, $appName, $supportedLanguages);
        return $view->render();
    }
}