<?php

// Set the application constants
define('APP_DIR', dirname(__DIR__));
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/languages/');
define('APP_TITLE', 'Renal Tales');


// Start session if it is not already started before including the detector
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the LanguageDetector class
require_once APP_DIR . '/core/LanguageDetector.php';

// Create an instance of the language detector
$languageDetector = new LanguageDetector();

// Set the default language and its name
$defaultLanguage = DEFAULT_LANGUAGE;
$defaultLanguageName = $languageDetector->getLanguageName($defaultLanguage);

// Detect the user's language
$currentLanguage = $languageDetector->detectLanguage();
$currentLanguageName = $languageDetector->getLanguageName($currentLanguage);

// Include language file if it exists
$languageFile = LANGUAGE_PATH . $currentLanguage . '.php';
$defaultLangMissing = false;
if (file_exists($languageFile)) {
    $text = require_once $languageFile;
} else {
    // If the language file does not exist, use the default language
    $defaultLangFile = LANGUAGE_PATH . $defaultLanguage . '.php';
    if (file_exists($defaultLangFile)) {
        $text = require_once $defaultLangFile;
        $currentLanguage = $defaultLanguage;
        $currentLanguageName = $defaultLanguageName;
    } else {
        $defaultLangMissing = true;
    }
}

// Set application title
$appTitle = isset($text['app_title']) ? $text['app_title'] : APP_TITLE;

// Debugging: Uncomment the line below to see the current language and its name
echo "Current Language: $currentLanguage ($currentLanguageName)";
echo "<br>";
echo "App Title: $appTitle";
echo "<br>";
echo "Default Language Missing: " . ($defaultLangMissing ? 'Yes' : 'No');
echo "<br>";
echo "Language File Path: $languageFile";


?>



<!DOCTYPE html>
<html lang="<?php if (isset($currentLanguage)) echo $currentLanguage; else echo DEFAULT_LANGUAGE; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php if (isset($appTitle)) echo $appTitle; else echo APP_TITLE; ?></title>
    <link rel="stylesheet" href="assets/css/basic.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <?php require_once 'assets/js/defaultLanguageFileMissing.php'; ?> 
</head>
<body>
    <header>
        <h1><?php echo $appTitle; ?></h1>
    </header>
    <nav>
        <ul>
            <li></li>
        </ul>
    </nav>
    <main>
        <p><?= $text['current_language']; ?></p> <strong><?php echo $currentLanguageName; ?></strong></p>
        <p><?= $text['welcome']; ?></p>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y') ?> Ľubomír Polaščín</p>
    </footer>
</body>
</html>
