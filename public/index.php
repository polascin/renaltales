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
    <nav class="language-selector">
        <ul>
            <li>
                <form method="get" action="">
                    <select name="lang" onchange="this.form.submit()">
                        <?php 
                            // Only show languages for which a language file exists
                            $selectableLanguages = $languageDetector->getSupportedLanguages(); 
                            foreach ($selectableLanguages as $lang) {
                                $langFile = LANGUAGE_PATH . $lang . '.php';
                                if (!file_exists($langFile)) continue; // Skip if language file does not exist
                                $langName = $languageDetector->getLanguageName($lang);
                                $flagPath = $languageDetector->getFlagPath($lang);
                                echo '<option value="' . $lang . '"';
                                if ($currentLanguage === $lang) {
                                    echo ' selected';
                                }
                                echo ' data-flag="' . $flagPath . '">';
                                echo $langName . ' (' . $lang . ')';
                                echo '</option>';
                            }
                        ?>
                    </select>
                    <noscript><input type="submit" value="Change"></noscript>
                    <script>
                    // Add flags to the language selector
                    document.addEventListener('DOMContentLoaded', function() {
                        var select = document.querySelector('select[name="lang"]');
                        if (!select) return;
                        
                        function updateFlag() {
                            // Remove existing flag if any
                            var existingFlag = select.parentNode.querySelector('.lang-flag');
                            if (existingFlag) {
                                existingFlag.remove();
                            }
                            
                            // Add flag for current selection
                            var selectedOption = select.options[select.selectedIndex];
                            var flagSrc = selectedOption.getAttribute('data-flag');
                            if (flagSrc) {
                                var flagImg = document.createElement('img');
                                flagImg.src = flagSrc;
                                flagImg.alt = selectedOption.textContent;
                                flagImg.className = 'lang-flag';
                                flagImg.style.height = '1.2em';
                                flagImg.style.width = 'auto';
                                flagImg.style.verticalAlign = 'middle';
                                flagImg.style.marginRight = '0.5em';
                                flagImg.style.border = '1px solid #ccc';
                                flagImg.style.borderRadius = '2px';
                                select.parentNode.insertBefore(flagImg, select);
                            }
                        }
                        
                        // Initial flag display
                        updateFlag();
                        
                        // Update flag when selection changes
                        select.addEventListener('change', updateFlag);
                    });
                    </script>
                </form>
            </li>
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
