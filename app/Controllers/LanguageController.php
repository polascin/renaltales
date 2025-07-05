<?php

/**
 * Language Controller
 * Handles language switching functionality
 */

require_once ROOT_PATH . '/src/Core/LanguageManager.php';
require_once ROOT_PATH . '/src/Core/Config.php';

use RenalTales\Core\LanguageManager;
use RenalTales\Core\Config;

class LanguageController extends Controller {
    protected $languageManager;
    
    public function __construct() {
        parent::__construct();
        $config = new Config(ROOT_PATH . '/config/config.php');
        $this->languageManager = new LanguageManager($config);
        $this->languageManager->initialize();
    }
    
    public function switch() {
        $code = $_GET['code'] ?? $_POST['code'] ?? null;
        
        if (!$code) {
            $this->redirect('/');
            return;
        }
        
        // Set the language
        if ($this->languageManager->setLanguage($code)) {
            // Store in cookie for longer persistence (30 days)
            setcookie('language', $code, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            
            // Flash success message
            $_SESSION['flash']['success'] = 'Language changed successfully';
        } else {
            // Flash error message
            $_SESSION['flash']['error'] = 'Invalid language selected';
        }
        
        // Redirect back to the referring page or home
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referrer);
    }
    
    public function getCurrentLanguage(): string {
        return $this->languageManager->getCurrentLanguage();
    }
    
    public function getSupportedLanguages(): array {
        return $this->languageManager->getSupportedLanguages();
    }
}
