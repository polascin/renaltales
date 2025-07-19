<?php

/**
 * Language Status API
 * Provides real-time language information for the test page
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include bootstrap for autoloading
require_once __DIR__ . '/../bootstrap.php';

// Use PSR-4 autoloaded classes
use RenalTales\Models\LanguageModel;
use Exception;

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
    $languageModel = new LanguageModel();

    // Get current language information
    $currentLanguage = $languageModel->getCurrentLanguage();
    $supportedLanguages = $languageModel->getSupportedLanguages();

    // Get session information
    $sessionInfo = [
        'language' => $_SESSION['language'] ?? 'not set',
        'session_id' => session_id(),
        'started' => isset($_SESSION['language_set_time']) ? date('Y-m-d H:i:s', $_SESSION['language_set_time']) : 'not set'
    ];

    // Get cookie information
    $cookieInfo = [
        'language' => $_COOKIE['language'] ?? 'not set',
        'expires' => isset($_COOKIE['language']) ? 'set (30 days)' : 'not set'
    ];

    // Get browser detection info
    $browserHeaders = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'not available';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'not available';

    // Get flag for current language
    $flag = $languageModel->getFlagCode($currentLanguage);

    // Get all supported languages with their names
    $supportedLanguagesWithNames = [];
    foreach ($supportedLanguages as $lang) {
        $supportedLanguagesWithNames[$lang] = $languageModel->getLanguageName($lang);
    }

    // Prepare response
    $response = [
        'language' => $currentLanguage,
        'language_name' => $languageModel->getLanguageName($currentLanguage),
        'flag' => $flag,
        'session' => json_encode($sessionInfo),
        'cookies' => json_encode($cookieInfo),
        'detection' => json_encode([
            'browser_headers' => substr($browserHeaders, 0, 100) . (strlen($browserHeaders) > 100 ? '...' : ''),
            'user_agent' => substr($userAgent, 0, 100) . (strlen($userAgent) > 100 ? '...' : ''),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'not available',
            'detected_at' => date('Y-m-d H:i:s')
        ]),
        'supported_languages' => $supportedLanguagesWithNames,
        'persistence_status' => [
            'session_active' => isset($_SESSION['language']),
            'cookie_set' => isset($_COOKIE['language']),
            'both_match' => ($_SESSION['language'] ?? null) === ($_COOKIE['language'] ?? null)
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to get language status',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
