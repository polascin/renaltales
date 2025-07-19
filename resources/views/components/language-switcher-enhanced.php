<?php
// resources/views/components/language-switcher-enhanced.php

/**
 * Enhanced Language Switcher Component with Accessibility Features
 * 
 * Features:
 * - ARIA labels and descriptions for screen readers
 * - Keyboard navigation support
 * - Loading states and visual feedback
 * - Mobile-responsive design
 * - RTL language support
 * 
 * Usage: include this file and pass $currentLanguage and $availableLanguages
 * Example: include 'resources/views/components/language-switcher-enhanced.php';
 *
 * @var string $currentLanguage Current language code
 * @var array $supportedLanguages Array of language codes and names
 * @var string $languageLabel Optional label for the switcher
 * @var bool $showFlags Whether to display flag icons
 */

if (!isset($currentLanguage)) $currentLanguage = 'en';
if (!isset($supportedLanguages)) $supportedLanguages = [
    'en' => 'English',
    'sk' => 'Slovak',  
    'la' => 'Latin',
];
if (!isset($languageLabel)) $languageLabel = 'Language';
if (!isset($showFlags)) $showFlags = true;

// Get current language name
$currentLanguageName = $supportedLanguages[$currentLanguage] ?? 'English';

// Flag mapping for supported languages
$flags = [
    'en' => '🇺🇸',
    'sk' => '🇸🇰', 
    'la' => '⚛️'
];
?>

<div  role="region" aria-label="Language selection">
    <div >
        <form class="language-form" method="get" action="" novalidate>
            <fieldset>
                <legend class="sr-only">Select your preferred language</legend>
                
                <div  
                     data-tooltip="Current language is <?= htmlspecialchars($currentLanguageName) ?>"
                     role="group"
                     aria-label="Language selection">
                    
                    <label for="lang-select" class="language-label">
                        <?= htmlspecialchars($languageLabel) ?>:
                        <span class="sr-only">Currently <?= htmlspecialchars($currentLanguageName) ?></span>
                    </label>
                    
                    <?php if ($showFlags && isset($flags[$currentLanguage])): ?>
                    <span class="flag" 
                          role="img" 
                          aria-label="<?= htmlspecialchars($currentLanguageName) ?> flag">
                        <?= $flags[$currentLanguage] ?>
                    </span>
                    <?php endif; ?>
                    
                    <select name="lang" 
                            id="lang-select" 
                            class="language-select" 
                            onchange="this.form.submit()"
                            aria-describedby="language-help language-announcements"
                            aria-label="Select language. Current language: <?= htmlspecialchars($currentLanguageName) ?>">
                        <?php foreach ($supportedLanguages as $code => $name): ?>
                        <option value="<?= htmlspecialchars($code) ?>" 
                                <?= $code === $currentLanguage ? 'selected' : '' ?>
                                data-flag="<?= $flags[$code] ?? '' ?>"
                                <?php if ($code === $currentLanguage): ?>
                                aria-describedby="current-language"
                                <?php endif; ?>>
                            <?= htmlspecialchars($name) ?>
                            <?php if ($showFlags && isset($flags[$code])): ?>
                            <?= $flags[$code] ?>
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- Hidden help text for screen readers -->
                    <div id="language-help" class="sr-only">
                        Use arrow keys to navigate between languages, then press Enter to select.
                        Keyboard shortcut: Ctrl+Alt+L to focus this control.
                    </div>
                    
                    <!-- Current language indicator for screen readers -->
                    <?php if ($currentLanguage): ?>
                    <div id="current-language" class="sr-only">
                        This is the currently selected language
                    </div>
                    <?php endif; ?>
                    
                    <!-- Loading indicator -->
                    <div class="loading-indicator sr-only" aria-live="polite" aria-atomic="true">
                        Switching language, please wait...
                    </div>
                    
                    <!-- Success/Error messages -->
                    <div class="language-status sr-only" aria-live="polite" aria-atomic="true"></div>
                </div>
            </fieldset>
            
            <!-- Additional hidden fields can be added here -->
            <?php if (isset($_GET['page'])): ?>
            <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page']) ?>">
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ARIA live region for announcements -->
<div id="language-announcements" 
     class="sr-only" 
     aria-live="polite" 
     aria-atomic="true"
     role="status">
</div>

<script>
// Enhanced Language Switcher Initialization
document.addEventListener('DOMContentLoaded', function() {
    const languageSwitcher = document.querySelector('');
    const languageSelect = document.getElementById('lang-select');
    const languageForm = document.querySelector('.language-form');
    
    if (!languageSwitcher || !languageSelect || !languageForm) {
        return;
    }
    
    // Add enhanced accessibility attributes
    languageSelect.setAttribute('aria-expanded', 'false');
    
    // Handle select opening/closing for ARIA
    languageSelect.addEventListener('focus', function() {
        languageSwitcher.classList.add('focused');
        this.setAttribute('aria-expanded', 'true');
    });
    
    languageSelect.addEventListener('blur', function() {
        languageSwitcher.classList.remove('focused');
        this.setAttribute('aria-expanded', 'false');
    });
    
    // Enhanced keyboard navigation
    languageSelect.addEventListener('keydown', function(e) {
        switch (e.key) {
            case 'Enter':
                e.preventDefault();
                showLoading();
                languageForm.submit();
                break;
            case 'Escape':
                this.blur();
                break;
        }
    });
    
    // Form submission with loading state
    languageForm.addEventListener('submit', function() {
        showLoading();
        announceToScreenReader('Switching to ' + languageSelect.options[languageSelect.selectedIndex].text + ', please wait...');
    });
    
    // Change event with validation
    languageSelect.addEventListener('change', function() {
        const selectedLanguage = this.value;
        const languageName = this.options[this.selectedIndex].text;
        
        // Update tooltip
        languageSwitcher.setAttribute('data-tooltip', 'Current language is ' + languageName);
        
        // Announce change to screen readers
        announceToScreenReader('Language changed to ' + languageName);
        
        // Save preference to localStorage
        try {
            localStorage.setItem('renaltales_language_preference', selectedLanguage);
        } catch (e) {
            console.warn('Could not save language preference:', e);
        }
    });
    
    // Loading state management
    function showLoading() {
        languageSwitcher.classList.add('loading');
        languageSelect.disabled = true;
        languageSelect.setAttribute('aria-busy', 'true');
    }
    
    function hideLoading() {
        languageSwitcher.classList.remove('loading');
        languageSelect.disabled = false;
        languageSelect.setAttribute('aria-busy', 'false');
    }
    
    // Screen reader announcements
    function announceToScreenReader(message) {
        const announcer = document.getElementById('language-announcements');
        const statusElement = document.querySelector('.language-status');
        
        if (announcer) {
            announcer.textContent = message;
        }
        
        if (statusElement) {
            statusElement.textContent = message;
        }
        
        // Clear message after 3 seconds
        setTimeout(() => {
            if (announcer) announcer.textContent = '';
            if (statusElement) statusElement.textContent = '';
        }, 3000);
    }
    
    // Global keyboard shortcut (Ctrl+Alt+L)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.altKey && e.key.toLowerCase() === 'l') {
            e.preventDefault();
            languageSelect.focus();
            announceToScreenReader('Language selector focused');
        }
    });
    
    // Load saved preference
    try {
        const savedLanguage = localStorage.getItem('renaltales_language_preference');
        if (savedLanguage && savedLanguage !== languageSelect.value) {
            // Optionally update if different from current
            const option = languageSelect.querySelector(`option[value="${savedLanguage}"]`);
            if (option) {
                languageSelect.value = savedLanguage;
            }
        }
    } catch (e) {
        console.warn('Could not load language preference:', e);
    }
    
    // Handle page visibility changes
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            hideLoading(); // Clear loading state if page becomes visible
        }
    });
    
    // Error handling
    window.addEventListener('error', function() {
        hideLoading();
        languageSwitcher.classList.add('error');
        announceToScreenReader('An error occurred while switching languages');
        
        setTimeout(() => {
            languageSwitcher.classList.remove('error');
        }, 5000);
    });
});
</script>

