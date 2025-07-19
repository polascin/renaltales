<?php
// resources/views/components/language-switcher.php

/**
 * Language Switcher Component
 * Usage: include this file and pass $currentLanguage and $availableLanguages
 * Example: include 'resources/views/components/language-switcher.php';
 *
 * @var string $currentLanguage
 * @var array $availableLanguages (code => name)
 * @var string $languageLabel (optional)
 */

if (!isset($currentLanguage)) {
    $currentLanguage = 'en';
}
if (!isset($supportedLanguages)) {
    $supportedLanguages = [
        'en' => 'English',
        'sk' => 'Slovak',
        'la' => 'Latin',
    ];
}
if (!isset($languageLabel)) {
    $languageLabel = 'Language';
}
?>
<div class="language-selector">
    <div class="language-selector-container">
        <form class="language-form" method="get" action="">
            <label for="lang-select" class="language-label"><?= htmlspecialchars($languageLabel) ?>:</label>
            <select name="lang" id="lang-select" class="language-select" onchange="this.form.submit()">
                <?php foreach ($supportedLanguages as $code => $name): ?>
                <option value="<?= htmlspecialchars($code) ?>" <?= $code === $currentLanguage ? 'selected' : '' ?>>
                    <?= htmlspecialchars($name) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- Language Selector Styles are loaded via CSS files -->
