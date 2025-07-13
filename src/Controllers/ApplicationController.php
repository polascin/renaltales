<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Models\LanguageModel;
use RenalTales\Core\SessionManager;

/**
 * Application Controller
 *
 * This controller handles the main application logic
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class ApplicationController
{
    private LanguageModel $languageModel;
    private SessionManager $sessionManager;

    /**
     * Constructor
     */
    public function __construct(LanguageModel $languageModel, SessionManager $sessionManager)
    {
        $this->languageModel = $languageModel;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Main index method
     *
     * @return string
     */
    public function index(): string
    {
        // Your application logic here
        $lang = $this->languageModel->getCurrentLanguage();
        $content = "Welcome to Renal Tales! Your current language is: " . $lang;

        return $content;
    }
}
