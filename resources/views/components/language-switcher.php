<?php
// resources/views/components/language-switcher.php

/**
 * Language Switcher Component
 * Usage: include this file and pass $currentLanguage and $availableLanguages
 * Example: include 'resources/views/components/language-switcher.php';
 *
 * @var string $currentLanguage
 * @var array $availableLanguages (code => name)
 */

if (!isset($currentLanguage)) $currentLanguage = 'en';
if (!isset($availableLanguages)) $availableLanguages = [
    'en' => 'English',
    'sk' => 'Slovak',
    'la' => 'Latin',
];
?>
<div class="language-switcher">
    <form method="get" action="">
        <label for="lang-select">Language:</label>
        <select name="lang" id="lang-select" onchange="this.form.submit()">
            <?php foreach ($availableLanguages as $code => $name): ?>
                <option value="<?= htmlspecialchars($code) ?>" <?= $code === $currentLanguage ? 'selected' : '' ?>>
                    <?= htmlspecialchars($name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<style>
    .language-switcher {
        margin: 1em 0;
    }

    .language-switcher select {
        padding: 0.2em;
    }
</style>
