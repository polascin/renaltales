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

if (!isset($currentLanguage)) $currentLanguage = 'en';
if (!isset($supportedLanguages)) $supportedLanguages = [
    'en' => 'English',
    'sk' => 'Slovak',
    'la' => 'Latin',
];
if (!isset($languageLabel)) $languageLabel = 'Language';
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

<style>
/* Language Selector Styles - Consistent with main design */
.language-selector {
    display: table;
    margin: 0 0 0 auto;
    border: thin solid transparent;
    background-color: var(--content-bg, #ffffff);
    border-radius: 0.5rem;
    padding: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.language-selector-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.language-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    border: none;
}

.language-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-color);
    margin: 0;
}

.language-select {
    font-size: 1rem;
    padding: 0.25rem 0.5rem;
    border: 1px solid var(--border-color, #ddd);
    border-radius: 0.25rem;
    background-color: var(--input-bg, #fff);
    color: var(--text-color);
    cursor: pointer;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.language-select:focus {
    outline: none;
    border-color: var(--primary-color, #007cba);
    box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .language-selector {
        margin: 0.5rem 0;
    }
    
    .language-form {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .language-select {
        font-size: 0.875rem;
        width: 100%;
    }
}
</style>
