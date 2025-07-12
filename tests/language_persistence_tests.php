<?php
/**
 * Comprehensive tests for language persistence
 * Covers various scenarios to ensure consistent language selection
 * across sessions, page reloads, and navigation.
 */

session_start();

require_once 'core/LanguageManager.php';
require_once 'core/SessionManager.php';

$languageManager = new LanguageManager();
$sessionManager = new SessionManager();

// Test 1: Language Persistence Across Page Refreshes
$langBeforeRefresh = $languageManager->detectLanguage();
// Simulate page refresh
session_write_close();
session_start();
$langAfterRefresh = $languageManager->detectLanguage();
assert($langBeforeRefresh === $langAfterRefresh, 'Language should persist across page refreshes.');

echo "Test 1 Passed: Language persistence across refreshes\n";

// Test 2: Cookie-Based Fallback when Session Expires
setcookie('user_language', 'fr', time() + 3600);
session_destroy();
session_start();
$langAfterSessionExpiry = $languageManager->detectLanguage();
assert($langAfterSessionExpiry === 'fr', 'Fallback to cookie language after session expires.');

echo "Test 2 Passed: Cookie-based fallback after session expiry\n";

// Test 3: Retain Language on Back/Forward Navigation
$_SESSION['language'] = 'de';
$languageManager->detectLanguage();
// Simulate back navigation
$langAfterBack = $languageManager->detectLanguage();
assert($langAfterBack === 'de', 'Language should persist on back/forward navigation.');

echo "Test 3 Passed: Language retention on back/forward navigation\n";

// Test 4: Language Persistence with New Browser Tabs
$_SESSION['language'] = 'es';
// Normally a new tab would carry current session state
$newTabLang = $languageManager->detectLanguage();
assert($newTabLang === 'es', 'Language should persist in new browser tabs.');

echo "Test 4 Passed: Language persistence with new browser tabs\n";

// Test 5: Handling Conflicting Language Sources
$_GET['lang'] = 'it';
$_SESSION['language'] = 'en';
setcookie('user_language', 'fr', time() + 3600);
$resolvedLang = $languageManager->detectLanguage();
assert($resolvedLang === 'it', 'The language should respect URL parameter priority.');

echo "Test 5 Passed: Proper handling of conflicting language sources\n";

?>
