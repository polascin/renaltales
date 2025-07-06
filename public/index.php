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

// Detect the user's language
$currentLanguage = $languageDetector->detectLanguage();
$currentLanguageName = $languageDetector->getLanguageName($currentLanguage);

// First load to text array English language file
$englishLanguageFile = LANGUAGE_PATH . 'en.php';
if (file_exists($englishLanguageFile)) {
  $text = require_once $englishLanguageFile;
} else {
  // If the English language file does not exist, use an empty array
  $text = [];
}

// Include language file if it exists
$languageFile = LANGUAGE_PATH . $currentLanguage . '.php';
if (file_exists($languageFile)) {
  $text = require_once $languageFile;
}

// Set application title
$appTitle = isset($text['app_title']) ? $text['app_title'] : APP_TITLE;

?>

<!DOCTYPE html>
<html lang="<?php echo $currentLanguage; ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $appTitle; ?></title>
    <link rel="stylesheet" href="assets/css/basic.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
  </head>
  <body>
    <nav class="language-selector">
      <ul>
        <li>
          <form method="get">
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
            <script src="assets/js/addFlags.js"></script>
          </form>
        </li>
      </ul>
    </nav>
    <header class="main-header-container">
      <div class="left-section">
        <img src="assets/images/logos/logo.gif" alt="<?php echo htmlspecialchars($appTitle) . ' logo'; ?>" class="logo">
        <h1><?php echo $appTitle; ?></h1>
        <h2><?php echo APP_TITLE; ?></h2>
        <h3><?php echo isset($text['app_subtitle']) ? $text['app_subtitle'] : 'A Multilingual Application'; ?></h3>
        <h4><?php echo isset($text['app_description']) ? $text['app_description'] : 'A web application for sharing tales and stories from the community of people with kidney disorders, including those on dialysis, and those who have had or are waiting for a renal transplant.'; ?></h4>
        <h5><?php echo isset($text['app_version']) ? $text['app_version'] : 'Version 1.0'; ?></h5>
        <h6><?php echo isset($text['app_author']) ? $text['app_author'] : 'Lumpe Paskuden von Lumpenen aka Walter Kyo aka Walter Csoelle Kyo aka Lubomir Polascin'; ?></h6>
      </div>
      <div class="central-section">
        Tu tiež niečo bude, zatiaľ je to len základná šablóna.
      </div>
      <div class="right-section">
        Niečo tu umiestnime, zatiaľ je to len základná šablóna.
      </div>
    </header>
    <main>
      <hr>
      <section class="language-selection-flags">
        <?php
          // Only show languages for which a language file exists
          $selectableLanguages = $languageDetector->getSupportedLanguages();
          foreach ($selectableLanguages as $lang) {
            $langFile = LANGUAGE_PATH . $lang . '.php';
            if (!file_exists($langFile)) continue; // Skip if language file does not exist
            $langName = $languageDetector->getLanguageName($lang);
            $flagPath = $languageDetector->getFlagPath($lang);
            echo '<a href="?lang=' . $lang . '" title="' . htmlspecialchars($langName) . '">';
            echo '<img src="' . $flagPath . '" alt="' . htmlspecialchars($langName) . '">' . htmlspecialchars($lang);
            echo '</a>';
          }
        ?>
        <p><?php echo isset($text['current_language']) ? $text['current_language'] . ' ' : 'Current language: '; ?> <strong><?php echo $currentLanguageName; ?></strong></p>
        <p><?php echo isset($text['welcome']) ? $text['welcome'] : 'Welcome!'; ?></p>
      </section>
      <hr>
    </main>
    <footer>
      <p>&copy; <?php echo date('Y') ?> Ľubomír Polaščín</p>
    </footer>
  </body>
</html>
