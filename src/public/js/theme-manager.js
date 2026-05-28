/**
 * Theme Management System - FIXED VERSION
 * Handles dark/light/system mode with localStorage and database persistence
 * 
 * KEY FIX: Clarified the distinction between SAVED theme (user preference) 
 * and APPLIED theme (what's actually visible on the page)
 */

class ThemeManager {
    constructor() {
        this.STORAGE_KEY = 'badnet-theme';
        this.THEMES = {
            LIGHT: 'light',
            DARK: 'dark',
            SYSTEM: 'system'
        };
        this.init();
    }

    /**
     * Initialize theme on page load
     */
    init() {
        // Get what was previously saved
        const savedTheme = this.getSavedTheme();
        
        // Apply it immediately
        this.setTheme(savedTheme, true);
        
        // Setup listeners for system preference changes
        this.setupListeners();
        
        // Sync with database
        this.syncWithDatabase();
    }

    /**
     * Get the theme the user has CHOSEN (light, dark, or system)
     * This is what's stored in localStorage
     */
    getSavedTheme() {
        return localStorage.getItem(this.STORAGE_KEY) || this.THEMES.DARK;
    }

    /**
     * Get the system preference (what the OS prefers)
     */
    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches 
            ? this.THEMES.DARK 
            : this.THEMES.LIGHT;
    }

    /**
     * Get what theme is ACTUALLY APPLIED to the page right now
     * If user chose "system", this resolves it to dark or light
     */
    getAppliedTheme() {
        const saved = this.getSavedTheme();
        
        if (saved === this.THEMES.SYSTEM) {
            return this.getSystemTheme();
        }
        
        return saved;
    }

    /**
     * Apply a theme to the DOM - this is what makes the page look dark or light
     */
    applyThemeToDOM(appliedTheme) {
        const isDark = appliedTheme === this.THEMES.DARK;
        const html = document.documentElement;

        // Set Tailwind's dark class - this activates all .dark\:* utilities
        html.classList.toggle('dark', isDark);
        
        // Set data attribute for CSS-based styling (e.g., :root[data-theme="light"])
        html.setAttribute('data-theme', appliedTheme);
        
        // Set color-scheme for native elements (inputs, scrollbars, etc.)
        html.style.colorScheme = isDark ? 'dark' : 'light';
        
        // Dispatch event so other code knows theme changed
        window.dispatchEvent(new CustomEvent('themeChange', { 
            detail: { 
                saved: this.getSavedTheme(),
                applied: appliedTheme 
            } 
        }));
    }

    /**
     * Set a specific theme and update everything
     */
    setTheme(theme, skipSync = false) {
        // Validate the theme value
        if (!Object.values(this.THEMES).includes(theme)) {
            console.warn(`Invalid theme: ${theme}. Using 'dark' instead.`);
            theme = this.THEMES.DARK;
        }

        // Save the CHOICE (could be "system")
        localStorage.setItem(this.STORAGE_KEY, theme);

        // Figure out what to APPLY (resolve "system" to actual dark/light)
        const appliedTheme = this.getAppliedTheme();

        // Make it actually happen on the page
        this.applyThemeToDOM(appliedTheme);

        // Tell the server
        if (!skipSync) {
            this.syncWithDatabase();
        }
    }

    /**
     * Toggle between themes
     * Cycles: dark → light → system → dark
     */
    toggleTheme() {
        const current = this.getSavedTheme();
        let next;

        if (current === this.THEMES.DARK) {
            next = this.THEMES.LIGHT;
        } else if (current === this.THEMES.LIGHT) {
            next = this.THEMES.SYSTEM;
        } else {
            // current === SYSTEM or anything else
            next = this.THEMES.DARK;
        }

        this.setTheme(next);
    }

    /**
     * Get button display state based on SAVED theme (not applied)
     * This shows what the button should display
     */
    getButtonState() {
        const saved = this.getSavedTheme();
        
        if (saved === this.THEMES.DARK) {
            return {
                icon: '🌙',
                label: 'Light',
                nextTheme: this.THEMES.LIGHT
            };
        } else if (saved === this.THEMES.LIGHT) {
            return {
                icon: '☀️',
                label: 'Dark',
                nextTheme: this.THEMES.SYSTEM
            };
        } else {
            // SYSTEM mode
            return {
                icon: '🌐',
                label: 'System',
                nextTheme: this.THEMES.DARK
            };
        }
    }

    /**
     * Setup event listeners for system preference changes
     */
    setupListeners() {
        // Listen when OS changes dark mode preference
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            const saved = this.getSavedTheme();
            
            // Only re-apply if user has "system" mode selected
            if (saved === this.THEMES.SYSTEM) {
                const newSystemTheme = e.matches ? this.THEMES.DARK : this.THEMES.LIGHT;
                this.applyThemeToDOM(newSystemTheme);
            }
        });
    }

    /**
     * Sync theme preference with the server database
     */
    syncWithDatabase() {
        const theme = this.getSavedTheme();
        const userId = this.getCurrentUserId();

        if (!userId) {
            return; // Not logged in, skip sync
        }

        fetch('/api/theme/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
            },
            body: JSON.stringify({ theme }),
        }).catch(err => console.error('Theme sync error:', err));
    }

    /**
     * Get the current user ID from the page
     */
    getCurrentUserId() {
        return window.currentUserId 
            || document.documentElement.getAttribute('data-user-id')
            || document.querySelector('[name="user_id"]')?.value
            || null;
    }

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.themeManager = new ThemeManager();
    });
} else {
    window.themeManager = new ThemeManager();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
}
