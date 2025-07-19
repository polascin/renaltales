/**
 * Theme Switcher
 * Provides automatic theme detection, manual toggle functionality, and accessibility features
 */
class ThemeSwitcher {
    constructor() {
        this.themes = {
            LIGHT: 'light',
            DARK: 'dark',
            SYSTEM: 'system'
        };
        
        this.storageKey = 'theme-preference';
        this.currentTheme = this.getStoredTheme() || this.themes.SYSTEM;
        this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        this.init();
    }

    /**
     * Initialize the theme switcher
     */
    init() {
        this.addTransitionClass();
        this.applyTheme();
        this.setupEventListeners();
        this.announceThemeChange(this.getEffectiveTheme(), true);
    }

    /**
     * Add CSS class for smooth transitions
     */
    addTransitionClass() {
        const style = document.createElement('style');
        style.textContent = `
            .theme-transition,
            .theme-transition *,
            .theme-transition *:before,
            .theme-transition *:after {
                transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease !important;
                transition-delay: 0 !important;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Get the stored theme preference
     */
    getStoredTheme() {
        try {
            return localStorage.getItem(this.storageKey);
        } catch (e) {
            console.warn('localStorage not available, using system preference');
            return null;
        }
    }

    /**
     * Store the theme preference
     */
    setStoredTheme(theme) {
        try {
            localStorage.setItem(this.storageKey, theme);
        } catch (e) {
            console.warn('localStorage not available, theme preference not persisted');
        }
    }

    /**
     * Get the effective theme (resolves 'system' to actual theme)
     */
    getEffectiveTheme() {
        if (this.currentTheme === this.themes.SYSTEM) {
            return this.mediaQuery.matches ? this.themes.DARK : this.themes.LIGHT;
        }
        return this.currentTheme;
    }

    /**
     * Apply the current theme to the document
     */
    applyTheme() {
        const effectiveTheme = this.getEffectiveTheme();
        const html = document.documentElement;
        
        // Add transition class for smooth animation
        html.classList.add('theme-transition');
        
        // Remove existing theme classes
        html.classList.remove('theme-light', 'theme-dark');
        
        // Add current theme class
        html.classList.add(`theme-${effectiveTheme}`);
        
        // Set data attribute for CSS targeting
        html.setAttribute('data-theme', effectiveTheme);
        
        // Update theme-color meta tag if it exists
        this.updateThemeColorMeta(effectiveTheme);
        
        // Remove transition class after animation completes
        setTimeout(() => {
            html.classList.remove('theme-transition');
        }, 300);
    }

    /**
     * Update theme-color meta tag for mobile browsers
     */
    updateThemeColorMeta(theme) {
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            const colors = {
                light: '#ffffff',
                dark: '#1a1a1a'
            };
            metaThemeColor.setAttribute('content', colors[theme] || colors.light);
        }
    }

    /**
     * Toggle between light and dark themes
     */
    toggle() {
        const currentEffective = this.getEffectiveTheme();
        const newTheme = currentEffective === this.themes.LIGHT ? this.themes.DARK : this.themes.LIGHT;
        
        this.setTheme(newTheme);
    }

    /**
     * Set a specific theme
     */
    setTheme(theme) {
        if (!Object.values(this.themes).includes(theme)) {
            console.warn(`Invalid theme: ${theme}`);
            return;
        }

        const oldTheme = this.getEffectiveTheme();
        this.currentTheme = theme;
        this.setStoredTheme(theme);
        this.applyTheme();
        
        const newTheme = this.getEffectiveTheme();
        if (oldTheme !== newTheme) {
            this.announceThemeChange(newTheme);
        }
        
        // Dispatch custom event for other components
        this.dispatchThemeChangeEvent(newTheme);
    }

    /**
     * Get the current theme preference
     */
    getCurrentTheme() {
        return this.currentTheme;
    }

    /**
     * Check if dark mode is currently active
     */
    isDarkMode() {
        return this.getEffectiveTheme() === this.themes.DARK;
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for system theme changes
        this.mediaQuery.addEventListener('change', (e) => {
            if (this.currentTheme === this.themes.SYSTEM) {
                this.applyTheme();
                this.announceThemeChange(this.getEffectiveTheme());
                this.dispatchThemeChangeEvent(this.getEffectiveTheme());
            }
        });

        // Listen for storage changes (sync across tabs)
        window.addEventListener('storage', (e) => {
            if (e.key === this.storageKey && e.newValue !== this.currentTheme) {
                this.currentTheme = e.newValue || this.themes.SYSTEM;
                this.applyTheme();
                this.announceThemeChange(this.getEffectiveTheme());
                this.dispatchThemeChangeEvent(this.getEffectiveTheme());
            }
        });

        // Setup keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Shift + T to toggle theme
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
                e.preventDefault();
                this.toggle();
            }
        });

        // Setup toggle buttons
        this.setupToggleButtons();
    }

    /**
     * Setup theme toggle buttons
     */
    setupToggleButtons() {
        const toggleButtons = document.querySelectorAll('[data-theme-toggle]');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });
            
            // Add aria-label for accessibility
            if (!button.hasAttribute('aria-label')) {
                button.setAttribute('aria-label', 'Toggle theme');
            }
            
            // Update button state
            this.updateToggleButtonState(button);
        });
    }

    /**
     * Update toggle button state
     */
    updateToggleButtonState(button) {
        const isDark = this.isDarkMode();
        button.setAttribute('aria-pressed', isDark.toString());
        
        // Update button text/icon if needed
        const lightText = button.getAttribute('data-light-text') || 'Light';
        const darkText = button.getAttribute('data-dark-text') || 'Dark';
        
        if (button.textContent && (button.textContent.includes(lightText) || button.textContent.includes(darkText))) {
            button.textContent = isDark ? lightText : darkText;
        }
    }

    /**
     * Announce theme changes for screen readers
     */
    announceThemeChange(theme, isInitial = false) {
        const announcement = isInitial 
            ? `Theme initialized: ${theme} mode`
            : `Theme changed to ${theme} mode`;
        
        this.announceToScreenReader(announcement);
    }

    /**
     * Announce message to screen readers
     */
    announceToScreenReader(message) {
        const announcer = document.createElement('div');
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        announcer.className = 'sr-only';
        announcer.style.cssText = `
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        `;
        
        document.body.appendChild(announcer);
        announcer.textContent = message;
        
        // Remove after announcement
        setTimeout(() => {
            document.body.removeChild(announcer);
        }, 1000);
    }

    /**
     * Dispatch custom theme change event
     */
    dispatchThemeChangeEvent(theme) {
        const event = new CustomEvent('themechange', {
            detail: {
                theme: theme,
                isDark: theme === this.themes.DARK
            }
        });
        
        document.dispatchEvent(event);
    }

    /**
     * Add CSS custom properties for current theme
     */
    addThemeProperties() {
        const style = document.createElement('style');
        style.id = 'theme-properties';
        
        style.textContent = `
            :root {
                --theme-transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
            }
            
            [data-theme="light"] {
                --bg-primary: #ffffff;
                --bg-secondary: #f8f9fa;
                --text-primary: #212529;
                --text-secondary: #6c757d;
                --border-color: #dee2e6;
                --shadow: rgba(0, 0, 0, 0.1);
            }
            
            [data-theme="dark"] {
                --bg-primary: #1a1a1a;
                --bg-secondary: #2d2d2d;
                --text-primary: #f8f9fa;
                --text-secondary: #adb5bd;
                --border-color: #404040;
                --shadow: rgba(255, 255, 255, 0.1);
            }
            
            @media (prefers-reduced-motion: reduce) {
                .theme-transition,
                .theme-transition *,
                .theme-transition *:before,
                .theme-transition *:after {
                    transition: none !important;
                }
            }
        `;
        
        document.head.appendChild(style);
    }
}

// Initialize theme switcher when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.themeSwitcher = new ThemeSwitcher();
    });
} else {
    window.themeSwitcher = new ThemeSwitcher();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeSwitcher;
}
