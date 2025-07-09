<?php
/**
 * Language Switcher Component
 * 
 * Displays a dropdown for language selection
 */

use RenalTales\Helpers\TranslationHelper;

$availableLanguages = available_languages();
$currentLanguage = current_language();
?>

<div class="language-switcher">
    <div class="dropdown">
        <button class="dropdown-toggle" type="button" id="languageDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php if (language_flag()): ?>
                <img src="/assets/flags/<?php echo htmlspecialchars(language_flag()); ?>.png" alt="<?php echo htmlspecialchars(language_name()); ?>" class="flag-icon">
            <?php endif; ?>
            <span class="language-name"><?php echo htmlspecialchars(language_native_name()); ?></span>
            <span class="caret"></span>
        </button>
        
        <div class="dropdown-menu" aria-labelledby="languageDropdown">
            <h6 class="dropdown-header"><?php echo __('change_language', 'common'); ?></h6>
            <div class="dropdown-divider"></div>
            
            <?php foreach ($availableLanguages as $language): ?>
                <a class="dropdown-item <?php echo $language['code'] === $currentLanguage ? 'active' : ''; ?>" 
                   href="?lang=<?php echo htmlspecialchars($language['code']); ?>"
                   data-lang="<?php echo htmlspecialchars($language['code']); ?>">
                    <?php if ($language['flag_icon']): ?>
                        <img src="/assets/flags/<?php echo htmlspecialchars($language['flag_icon']); ?>.png" 
                             alt="<?php echo htmlspecialchars($language['name']); ?>" 
                             class="flag-icon">
                    <?php endif; ?>
                    <span class="language-name"><?php echo htmlspecialchars($language['native_name']); ?></span>
                    <small class="text-muted">(<?php echo htmlspecialchars($language['name']); ?>)</small>
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
    
    // Handle language selection
    items.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.getAttribute('data-lang');
            
            // Send AJAX request to change language
            fetch('?action=change_language', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'language=' + encodeURIComponent(lang)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page or update content
                    window.location.reload();
                } else {
                    console.error('Failed to change language');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback to URL parameter method
                window.location.href = '?lang=' + encodeURIComponent(lang);
            });
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
