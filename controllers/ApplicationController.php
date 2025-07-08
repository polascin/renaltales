<?php

require_once 'BaseController.php';

/**
 * ApplicationController - Main application controller
 * 
 * Handles user requests and coordinates between models and views
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

class ApplicationController extends BaseController {
    
    private $languageModel;
    private $sessionManager;
    
    /**
     * Constructor
     * 
     * @param LanguageModel $languageModel
     * @param SessionManager $sessionManager
     */
    public function __construct($languageModel, $sessionManager) {
        $this->languageModel = $languageModel;
        $this->sessionManager = $sessionManager;
    }
    
    /**
     * Handle the main index action
     */
    public function index() {
        // Handle language change if requested
        $this->handleLanguageChange();
        
        // Create and render the main view
        $view = new ApplicationView($this->languageModel, $this->sessionManager);
        return $view->render();
    }
    
    /**
     * Handle language change requests
     */
    private function handleLanguageChange() {
        if (isset($_GET['lang']) && !empty($_GET['lang'])) {
            $requestedLanguage = $_GET['lang'];
            
            // Validate CSRF token
            if ($this->sessionManager && isset($_GET['csrf_token'])) {
                $isValidToken = $this->sessionManager->validateCSRFToken($_GET['csrf_token']);
                
                if ($isValidToken) {
                    // Check if language is supported
                    $supportedLanguages = $this->languageModel->getSupportedLanguages();
                    
                    if (in_array($requestedLanguage, $supportedLanguages)) {
                        // Set language in session or cookie
                        if ($this->sessionManager) {
                            $this->sessionManager->setSession('user_language', $requestedLanguage);
                        }
                        
                        // Redirect to avoid resubmission
                        $this->redirect($_SERVER['PHP_SELF']);
                    }
                }
            }
        }
    }
    
    /**
     * Handle error display
     * 
     * @param Exception $exception
     * @param bool $isDebugMode
     * @return string
     */
    public function error($exception, $isDebugMode = false) {
        $errorView = new ErrorView($exception, $isDebugMode, $this->languageModel);
        return $errorView->render();
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url
     */
    private function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    /**
     * Get language model
     * 
     * @return LanguageModel
     */
    public function getLanguageModel() {
        return $this->languageModel;
    }
    
    /**
     * Get session manager
     * 
     * @return SessionManager
     */
    public function getSessionManager() {
        return $this->sessionManager;
    }
}

?>
