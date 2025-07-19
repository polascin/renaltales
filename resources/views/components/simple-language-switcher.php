<?php
/**
 * Simple Language Switcher Component
 *
 * A streamlined language switcher using the new Translation system.
 * Provides both dropdown and inline button styles.
 *
 * @package RenalTales\Views\Components
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

// Use global translation instance
global $translation;

// Get current language and supported languages
$currentLanguage = getCurrentLanguage();
$supportedLanguages = getSupportedLanguages();

// Language names mapping (simple version)
$languageNames = [
    'en' => 'English',
    'sk' => 'Slovenčina',
    'cs' => 'Čeština',
    'de' => 'Deutsch',
    'fr' => 'Français',
    'es' => 'Español',
    'it' => 'Italiano',
    'pt' => 'Português',
    'ru' => 'Русский',
    'ja' => '日本語',
    'zh' => '中文',
    'ar' => 'العربية',
    'hi' => 'हिन्दी',
    'ko' => '한국어',
    'th' => 'ไทย',
    'vi' => 'Tiếng Việt',
    'tr' => 'Türkçe',
    'pl' => 'Polski',
    'nl' => 'Nederlands',
    'sv' => 'Svenska',
    'da' => 'Dansk',
    'no' => 'Norsk',
    'fi' => 'Suomi',
    'el' => 'Ελληνικά',
    'he' => 'עברית',
    'hu' => 'Magyar',
    'ro' => 'Română',
    'bg' => 'Български',
    'hr' => 'Hrvatski',
    'sr' => 'Српски',
    'sl' => 'Slovenščina',
    'et' => 'Eesti',
    'lv' => 'Latviešu',
    'lt' => 'Lietuvių',
    'uk' => 'Українська',
    'be' => 'Беларуская',
    'ca' => 'Català',
    'eu' => 'Euskera',
    'gl' => 'Galego',
    'cy' => 'Cymraeg',
    'ga' => 'Gaeilge',
    'gd' => 'Gàidhlig',
    'is' => 'Íslenska',
    'fo' => 'Føroyskt',
    'mt' => 'Malti',
    'sq' => 'Shqip',
    'mk' => 'Македонски',
    'la' => 'Latina'
];

// Get style parameter (default: 'dropdown')
$style = $style ?? 'dropdown';
$showFlags = $showFlags ?? true;
$maxLanguages = $maxLanguages ?? 10;

// Limit languages for better UX
$displayLanguages = array_slice($supportedLanguages, 0, $maxLanguages);
?>

<div  data-style="<?= htmlspecialchars($style, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($style === 'dropdown'): ?>
        <!-- Dropdown Style Language Switcher -->
        <div class="language-dropdown">
            <label for="language-selector" class="language-label">
                <?= __('current_language', 'Language') ?>:
            </label>
            <select id="language-selector" name="language" class="language-select" onchange="switchLanguage(this.value)">
                <?php foreach ($displayLanguages as $langCode): ?>
                    <option value="<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>" 
                            <?= $langCode === $currentLanguage ? 'selected' : '' ?>>
                        <?php if ($showFlags): ?>🌐 <?php endif ?>
                        <?= htmlspecialchars($languageNames[$langCode] ?? ucfirst($langCode), ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($langCode === $currentLanguage): ?> (<?= __('current', 'Current') ?>)<?php endif ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    <?php elseif ($style === 'buttons'): ?>
        <!-- Button Style Language Switcher -->
        <div class="language-buttons">
            <span class="language-label"><?= __('current_language', 'Language') ?>:</span>
            <div class="language-button-group">
                <?php foreach ($displayLanguages as $langCode): ?>
                    <button type="button" 
                            class="language-button <?= $langCode === $currentLanguage ? 'active' : '' ?>" 
                            data-lang="<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>"
                            onclick="switchLanguage('<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>')"
                            title="<?= htmlspecialchars($languageNames[$langCode] ?? ucfirst($langCode), ENT_QUOTES, 'UTF-8') ?>">
                        <?= strtoupper($langCode) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Inline Style Language Switcher -->
        <div class="language-inline">
            <span class="language-label"><?= __('current_language', 'Language') ?>:</span>
            <span class="current-language">
                <?= htmlspecialchars($languageNames[$currentLanguage] ?? ucfirst($currentLanguage), ENT_QUOTES, 'UTF-8') ?>
            </span>
            <div class="language-links">
                <?php foreach ($displayLanguages as $langCode): ?>
                    <?php if ($langCode !== $currentLanguage): ?>
                        <a href="#" 
                           class="language-link" 
                           data-lang="<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>"
                           onclick="event.preventDefault(); switchLanguage('<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>')">
                            <?= htmlspecialchars($languageNames[$langCode] ?? ucfirst($langCode), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Simple CSS for the language switcher -->
<style>
.simple-language-switcher {
    margin: 10px 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.language-label {
    font-weight: 500;
    margin-right: 8px;
    color: #333;
}

/* Dropdown Style */
.language-dropdown {
    display: flex;
    align-items: center;
    gap: 8px;
}

.language-select {
    padding: 6px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    font-size: 14px;
    min-width: 150px;
}

.language-select:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

/* Button Style */
.language-buttons {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.language-button-group {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.language-button {
    padding: 4px 8px;
    border: 1px solid #ddd;
    background: white;
    color: #333;
    font-size: 12px;
    font-weight: 500;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.language-button:hover {
    background: #f5f5f5;
    border-color: #ccc;
}

.language-button.active {
    background: #4CAF50;
    color: white;
    border-color: #4CAF50;
}

/* Inline Style */
.language-inline {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.current-language {
    font-weight: 500;
    color: #4CAF50;
}

.language-links {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.language-link {
    color: #666;
    text-decoration: none;
    font-size: 14px;
    padding: 2px 4px;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.language-link:hover {
    color: #4CAF50;
    background: #f9f9f9;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .language-buttons,
    .language-inline {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .language-button-group,
    .language-links {
        margin-top: 4px;
    }
}
</style>

<!-- JavaScript for language switching -->
<script>
function switchLanguage(languageCode) {
    // Simple AJAX language switch
    fetch('/api/language/switch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            language: languageCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to apply the new language
            window.location.reload();
        } else {
            console.error('Language switch failed:', data.error);
            alert('Failed to switch language. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback: simple form submission
        switchLanguageFallback(languageCode);
    });
}

function switchLanguageFallback(languageCode) {
    // Fallback method using form submission
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/language/switch';
    
    const langInput = document.createElement('input');
    langInput.type = 'hidden';
    langInput.name = 'language';
    langInput.value = languageCode;
    
    form.appendChild(langInput);
    document.body.appendChild(form);
    form.submit();
}

// Initialize language switcher
document.addEventListener('DOMContentLoaded', function() {
    console.log('Simple Language Switcher initialized');
    console.log('Current language:', '<?= $currentLanguage ?>');
    console.log('Supported languages:', <?= json_encode($displayLanguages) ?>);
});
</script>

