<?php

require_once 'bootstrap.php';
require_once 'src/Core/SessionManager.php';

use RenalTales\Models\LanguageModel;
use RenalTales\Core\SessionManager;
use Exception;

try {
    // Initialize LanguageModel
    $languageModel = new LanguageModel();
    echo "LanguageModel initialized successfully.\n";

    // Initialize SessionManager with sample data
    $sessionText = [
        'session_init_failed' => 'Session initialization failed',
        'session_name_failed' => 'Failed to set session name',
        'session_cookie_params_failed' => 'Failed to set cookie parameters',
        'security_user_agent_mismatch' => 'User agent mismatch',
        'security_ip_mismatch' => 'IP address mismatch',
        'security_session_timeout' => 'Session timeout'
    ];
    $sessionManager = new SessionManager($sessionText, false, ['127.0.0.1', '::1'], 1800);
    echo "SessionManager initialized successfully.\n";

    // Test language detection and session integration
    $currentLanguage = $languageModel->detectLanguage();
    echo "Detected language: $currentLanguage\n";

    $sessionManager->setSession('language', $currentLanguage);
    echo "Set session language: " . $sessionManager->getSession('language') . "\n";

    // Validate potential interaction between session and language
    $sessionLanguage = $sessionManager->getSession('language');
    if ($languageModel->isSupported($sessionLanguage)) {
        echo "Language $sessionLanguage is supported.\n";
    } else {
        echo "Language $sessionLanguage is not supported.\n";
    }

    // Display session status
    echo "Session ID: " . $sessionManager->getSessionId() . "\n";
    echo "Session Status: " . $sessionManager->getSessionStatus() . "\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
