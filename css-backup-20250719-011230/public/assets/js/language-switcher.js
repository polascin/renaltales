/**
 * Enhanced Language Switcher JavaScript
 * Provides advanced functionality for the language switcher component
 * 
 * Features:
 * - Loading states
 * - Success/Error feedback
 * - Keyboard navigation
 * - Local storage persistence
 * - Smooth transitions
 * - Accessibility enhancements
 */

class LanguageSwitcher {
    constructor() {
        this.switcher = document.querySelector('.language-switcher');
        this.select = document.querySelector('.language-select');
        this.form = document.querySelector('.language-form');
        this.currentLanguage = this.getCurrentLanguage();
        this.storageKey = 'renaltales_language_preference';
        
        this.init();
    }

    init() {
        if (!this.switcher || !this.select || !this.form) {
            console.warn('Language switcher elements not found');
            return;
        }

        this.setupEventListeners();
        this.loadSavedPreference();
        this.enhanceAccessibility();
        this.setupKeyboardNavigation();
        this.preloadLanguageData();
    }

    setupEventListeners() {
        // Enhanced form submission with loading state
        this.form.addEventListener('submit', (e) => {
            this.showLoading();
            this.savePreference();
            this.trackLanguageChange();
        });

        // Select change event with validation
        this.select.addEventListener('change', (e) => {
            const selectedLanguage = e.target.value;
            if (this.isValidLanguage(selectedLanguage)) {
                this.setLanguagePreference(selectedLanguage);
                this.updateTooltip(selectedLanguage);
            } else {
                this.showError('Invalid language selection');
                e.target.value = this.currentLanguage;
            }
        });

        // Focus events for enhanced UX
        this.select.addEventListener('focus', () => {
            this.switcher.classList.add('focused');
        });

        this.select.addEventListener('blur', () => {
            this.switcher.classList.remove('focused');
        });

        // Mouse events for better interaction
        this.switcher.addEventListener('mouseenter', () => {
            this.preloadLanguageData();
        });
    }

    setupKeyboardNavigation() {
        // Enhanced keyboard navigation
        this.select.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'Enter':
                    e.preventDefault();
                    this.form.submit();
                    break;
                case 'Escape':
                    this.select.blur();
                    break;
                case 'ArrowUp':
                case 'ArrowDown':
                    // Let browser handle arrow navigation
                    break;
            }
        });

        // Quick language switching with keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.altKey) {
                switch (e.key) {
                    case 'l':
                        e.preventDefault();
                        this.select.focus();
                        break;
                    case 'e':
                        e.preventDefault();
                        this.switchToLanguage('en');
                        break;
                    case 's':
                        e.preventDefault();
                        this.switchToLanguage('sk');
                        break;
                }
            }
        });
    }

    enhanceAccessibility() {
        // Add ARIA live region for announcements
        const liveRegion = document.createElement('div');
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.className = 'sr-only';
        liveRegion.id = 'language-announcements';
        document.body.appendChild(liveRegion);

        // Enhanced ARIA labels
        this.select.setAttribute('aria-describedby', 'language-announcements');
        
        // Add role and state information
        this.switcher.setAttribute('role', 'group');
        this.switcher.setAttribute('aria-label', 'Language selection');
    }

    showLoading() {
        this.switcher.classList.add('loading');
        this.select.disabled = true;
        this.announceToScreenReader('Switching language, please wait...');
    }

    hideLoading() {
        this.switcher.classList.remove('loading');
        this.select.disabled = false;
    }

    showSuccess(message = 'Language switched successfully') {
        this.switcher.classList.add('success');
        this.announceToScreenReader(message);
        
        setTimeout(() => {
            this.switcher.classList.remove('success');
        }, 3000);
    }

    showError(message = 'Error switching language') {
        this.switcher.classList.add('error');
        this.announceToScreenReader(message);
        
        setTimeout(() => {
            this.switcher.classList.remove('error');
        }, 5000);
    }

    announceToScreenReader(message) {
        const liveRegion = document.getElementById('language-announcements');
        if (liveRegion) {
            liveRegion.textContent = message;
        }
    }

    getCurrentLanguage() {
        return document.documentElement.lang || 'en';
    }

    isValidLanguage(code) {
        const validLanguages = ['en', 'sk', 'la']; // Add more as needed
        return validLanguages.includes(code);
    }

    setLanguagePreference(language) {
        if (this.isValidLanguage(language)) {
            this.currentLanguage = language;
            localStorage.setItem(this.storageKey, language);
        }
    }

    loadSavedPreference() {
        const saved = localStorage.getItem(this.storageKey);
        if (saved && this.isValidLanguage(saved) && saved !== this.currentLanguage) {
            this.select.value = saved;
            this.updateTooltip(saved);
        }
    }

    savePreference() {
        this.setLanguagePreference(this.select.value);
    }

    updateTooltip(language) {
        const languageNames = {
            'en': 'English',
            'sk': 'Slovak',
            'la': 'Latin'
        };
        
        const languageName = languageNames[language] || 'Unknown';
        const tooltipText = `Current language is ${languageName}`;
        this.switcher.setAttribute('data-tooltip', tooltipText);
    }

    switchToLanguage(code) {
        if (this.isValidLanguage(code)) {
            this.select.value = code;
            this.form.submit();
        }
    }

    preloadLanguageData() {
        // Preload language-specific resources
        const currentLang = this.select.value;
        const languages = ['en', 'sk', 'la'];
        
        languages.forEach(lang => {
            if (lang !== currentLang) {
                // Preload critical language resources
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = `/assets/lang/${lang}.json`;
                document.head.appendChild(link);
            }
        });
    }

    trackLanguageChange() {
        // Analytics tracking for language changes
        const newLanguage = this.select.value;
        const oldLanguage = this.currentLanguage;
        
        if (newLanguage !== oldLanguage) {
            // Track language change event
            if (typeof gtag !== 'undefined') {
                gtag('event', 'language_change', {
                    'old_language': oldLanguage,
                    'new_language': newLanguage,
                    'event_category': 'user_interface'
                });
            }
            
            // Custom event for other scripts
            const event = new CustomEvent('languageChanged', {
                detail: {
                    oldLanguage: oldLanguage,
                    newLanguage: newLanguage
                }
            });
            document.dispatchEvent(event);
        }
    }

    // Public API methods
    getSelectedLanguage() {
        return this.select.value;
    }

    setLanguage(code) {
        this.switchToLanguage(code);
    }

    getSupportedLanguages() {
        const options = this.select.querySelectorAll('option');
        return Array.from(options).map(option => ({
            code: option.value,
            name: option.textContent.replace(/\s*\[.*?\]$/, ''),
            flag: option.getAttribute('data-flag')
        }));
    }

    // Error recovery
    handleError(error) {
        console.error('Language switcher error:', error);
        this.showError('An error occurred while switching languages');
        this.hideLoading();
        
        // Reset to previous state
        this.select.value = this.currentLanguage;
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    try {
        window.languageSwitcher = new LanguageSwitcher();
    } catch (error) {
        console.error('Failed to initialize language switcher:', error);
    }
});

// Handle page visibility changes
document.addEventListener('visibilitychange', () => {
    if (!document.hidden && window.languageSwitcher) {
        // Refresh language state when page becomes visible
        window.languageSwitcher.loadSavedPreference();
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LanguageSwitcher;
}
