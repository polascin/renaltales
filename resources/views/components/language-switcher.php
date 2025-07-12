<?php
/**
 * Language Switcher Component
 * 
 * Displays a dropdown for language selection
 */

// Check if we have language model available
$availableLanguages = [];
$currentLanguage = 'en'; // Default fallback
$languageModel = null;

// Try to get language model from global scope or initialize it
if (class_exists('LanguageModel')) {
    $languageModel = new LanguageModel();
    $availableLanguages = $languageModel->getSupportedLanguages();
    $currentLanguage = $languageModel->getCurrentLanguage();
}

// Fallback to basic languages if model not available
if (empty($availableLanguages)) {
    $availableLanguages = [
        ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag_icon' => 'us'],
        ['code' => 'sk', 'name' => 'Slovak', 'native_name' => 'Slovenčina', 'flag_icon' => 'sk'],
        ['code' => 'cs', 'name' => 'Czech', 'native_name' => 'Čeština', 'flag_icon' => 'cz'],
        ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'flag_icon' => 'de'],
    ];
}

// Get translation function
function translateText($key, $group = 'common') {
    // Check if global translation function exists
    if (function_exists('__')) {
        return call_user_func('__', $key, $group);
    }
    
    // Fallback translations
    $translations = [
        'change_language' => 'Change Language',
    ];
    
    return $translations[$key] ?? $key;
}
?>

<div class="language-switcher">
    <div class="dropdown">
        <button class="dropdown-toggle" type="button" id="languageDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php 
            $currentLangData = null;
            foreach ($availableLanguages as $lang) {
                if ($lang['code'] === $currentLanguage) {
                    $currentLangData = $lang;
                    break;
                }
            }
            if ($currentLangData && isset($currentLangData['flag_icon'])): ?>
                <img src="/assets/flags/<?php echo htmlspecialchars($currentLangData['flag_icon']); ?>.png" alt="<?php echo htmlspecialchars($currentLangData['name']); ?>" class="flag-icon">
            <?php endif; ?>
            <span class="language-name"><?php echo htmlspecialchars($currentLangData['native_name'] ?? $currentLanguage); ?></span>
            <span class="caret"></span>
        </button>
        
        <div class="dropdown-menu" aria-labelledby="languageDropdown">
            <h6 class="dropdown-header"><?php echo translateText('change_language', 'common'); ?></h6>
            <div class="dropdown-divider"></div>
            
            <?php foreach ($availableLanguages as $language): ?>
                <form method="POST" style="display: inline;" class="language-form">
                    <input type="hidden" name="lang" value="<?php echo htmlspecialchars($language['code']); ?>">
                    <?php if (isset($securityManager)): ?>
                        <?= $securityManager->getCSRFTokenField() ?>
                    <?php endif; ?>
                    <button type="submit" class="dropdown-item <?php echo $language['code'] === $currentLanguage ? 'active' : ''; ?>" 
                            data-lang="<?php echo htmlspecialchars($language['code']); ?>">
                        <?php if (isset($language['flag_icon']) && $language['flag_icon']): ?>
                            <img src="/assets/flags/<?php echo htmlspecialchars($language['flag_icon']); ?>.png" 
                                 alt="<?php echo htmlspecialchars($language['name']); ?>" 
                                 class="flag-icon">
                        <?php endif; ?>
                        <span class="language-name"><?php echo htmlspecialchars($language['native_name']); ?></span>
                        <small class="text-muted">(<?php echo htmlspecialchars($language['name']); ?>)</small>
                    </button>
                </form>
                <!-- Fallback GET link for compatibility -->
                <a class="dropdown-item-fallback" href="?lang=<?php echo htmlspecialchars($language['code']); ?>" style="display: none;">
                    Fallback link
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.language-switcher {
    position: relative;
    display: inline-block;
}

.language-switcher .dropdown-toggle {
    background: none;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #333;
    text-decoration: none;
}

.language-switcher .dropdown-toggle:hover {
    background-color: #f8f9fa;
    border-color: #adb5bd;
}

.language-switcher .flag-icon {
    width: 20px;
    height: 15px;
    object-fit: cover;
    border-radius: 2px;
}

.language-switcher .caret {
    margin-left: auto;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 4px solid #666;
    display: inline-block;
    width: 0;
    height: 0;
}

.language-switcher .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    display: none;
    min-width: 200px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 8px 0;
}

.language-switcher .dropdown-menu.show {
    display: block;
}

.language-switcher .dropdown-header {
    padding: 8px 16px;
    margin: 0;
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
}

.language-switcher .dropdown-divider {
    height: 0;
    margin: 4px 0;
    overflow: hidden;
    border-top: 1px solid #e9ecef;
}

.language-switcher .dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    text-decoration: none;
    color: #333;
    cursor: pointer;
}

.language-switcher .dropdown-item:hover {
    background-color: #f8f9fa;
}

.language-switcher .dropdown-item.active {
    background-color: #007bff;
    color: #fff;
}

.language-switcher .dropdown-item.active .text-muted {
    color: rgba(255, 255, 255, 0.7) !important;
}

.language-switcher .text-muted {
    color: #6c757d;
    font-size: 12px;
}

/* RTL Support */
.language-switcher[dir="rtl"] .dropdown-menu {
    left: auto;
    right: 0;
}

.language-switcher[dir="rtl"] .caret {
    margin-left: 0;
    margin-right: auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.querySelector('.language-switcher .dropdown');
    const toggle = dropdown.querySelector('.dropdown-toggle');
    const menu = dropdown.querySelector('.dropdown-menu');
    const items = menu.querySelectorAll('.dropdown-item');
    
    // Toggle dropdown
    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        menu.classList.toggle('show');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            menu.classList.remove('show');
        }
    });
    
    // Handle language selection - Use form submission instead of direct links
    const forms = menu.querySelectorAll('.language-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const lang = form.querySelector('input[name="lang"]').value;
            console.log('Switching to language:', lang);
            
            // Close dropdown
            menu.classList.remove('show');
            
            // Form will submit via POST with CSRF token (secure method)
            // No need to prevent default, let form submit naturally
        });
    });
    
    // Fallback for browsers without JS or if POST fails
    const fallbackLinks = menu.querySelectorAll('.dropdown-item-fallback');
    fallbackLinks.forEach(item => {
        item.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            console.log('Using fallback method for language switch');
            window.location.href = href;
        });
    });
    
    // Keyboard navigation
    toggle.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            menu.classList.toggle('show');
        }
    });
    
    menu.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            menu.classList.remove('show');
            toggle.focus();
        }
    });
});
</script>
