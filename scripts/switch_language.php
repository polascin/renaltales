<?php
/**
 * Switch Language API
 * Allows manual switching of language via URL parameter
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

$lang = $_GET['lang'] ?? null;

try {
    if (!$lang) {
        throw new Exception('No language specified');
    }

$languageModel = new LanguageModel();
    
if ($languageModel->isSupported($lang)) {
$result = $languageModel->setLanguage($lang);
        if ($result) {
            // Get updated language information
$currentLanguage = $languageModel->getCurrentLanguage();
$flag = $languageModel->getFlagCode($currentLanguage);
            
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
            
            $response = [
                'success' => true,
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
                'persistence_status' => [
                    'session_active' => isset($_SESSION['language']),
                    'cookie_set' => isset($_COOKIE['language']),
                    'both_match' => ($_SESSION['language'] ?? null) === ($_COOKIE['language'] ?? null)
                ],
                'message' => 'Language switched successfully'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to set language'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Language not supported: ' . $lang
        ];
    }

    // Return the status
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to switch language',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
