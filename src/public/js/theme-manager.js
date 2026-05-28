/**
 * Theme Management System
 * Handles dark/light/system mode with localStorage and database persistence
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
        const savedTheme = this.getSavedTheme();
        const systemTheme = this.getSystemTheme();
        const themeToApply = this.resolveTheme(savedTheme, systemTheme);
        
        this.applyTheme(themeToApply);
        this.setupListeners();
        this.syncWithDatabase();
    }

    /**
     * Get saved theme from localStorage
     */
    getSavedTheme() {
        return localStorage.getItem(this.STORAGE_KEY) || this.THEMES.DARK;
    }

    /**
     * Get system preference theme
     */
    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches 
            ? this.THEMES.DARK 
            : this.THEMES.LIGHT;
    }

    /**
     * Resolve which theme to apply
     */
    resolveTheme(savedTheme, systemTheme) {
        if (savedTheme === this.THEMES.SYSTEM) {
            return systemTheme;
        }
        return savedTheme;
    }

    /**
     * Apply theme to the page
     */
    applyTheme(theme) {
        const isDark = theme === this.THEMES.DARK;
        const htmlElement = document.documentElement;

        // Set data attribute
        htmlElement.setAttribute('data-theme', theme);
        
        // Toggle dark class for Tailwind
        htmlElement.classList.toggle('dark', isDark);
        
        // Set color-scheme for inputs and scrollbars
        htmlElement.style.colorScheme = isDark ? 'dark' : 'light';
        
        // Store the actual theme applied
        localStorage.setItem(this.STORAGE_KEY, this.getSavedTheme());
        
        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('themeChange', { detail: { theme } }));
    }

    /**
     * Toggle between themes
     */
    toggleTheme() {
        const current = this.getSavedTheme();
        let next;

        // Cycle: dark -> light -> system -> dark
        switch (current) {
            case this.THEMES.DARK:
                next = this.THEMES.LIGHT;
                break;
            case this.THEMES.LIGHT:
                next = this.THEMES.SYSTEM;
                break;
            case this.THEMES.SYSTEM:
                next = this.THEMES.DARK;
                break;
            default:
                next = this.THEMES.DARK;
        }

        this.setTheme(next);
    }

    /**
     * Set a specific theme
     */
    setTheme(theme) {
        if (!Object.values(this.THEMES).includes(theme)) {
            console.warn(`Invalid theme: ${theme}`);
            return;
        }

        localStorage.setItem(this.STORAGE_KEY, theme);
        
        const systemTheme = this.getSystemTheme();
        const themeToApply = this.resolveTheme(theme, systemTheme);
        
        this.applyTheme(themeToApply);
        this.syncWithDatabase();
    }

    /**
     * Get current active theme
     */
    getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') || this.THEMES.DARK;
    }

    /**
     * Setup event listeners
     */
    setupListeners() {
        // Listen for system preference changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            const current = this.getSavedTheme();
            if (current === this.THEMES.SYSTEM) {
                const systemTheme = e.matches ? this.THEMES.DARK : this.THEMES.LIGHT;
                this.applyTheme(systemTheme);
            }
        });
    }

    /**
     * Sync theme with database
     */
    syncWithDatabase() {
        const theme = this.getSavedTheme();
        const userId = this.getCurrentUserId();

        if (!userId) return;

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
     * Get current user ID from page data
     */
    getCurrentUserId() {
        // Try to get from window object, data attribute, or hidden input
        return window.currentUserId 
            || document.documentElement.getAttribute('data-user-id')
            || document.querySelector('[name="user_id"]')?.value
            || null;
    }

    /**
     * Get CSRF token from meta tag or hidden input
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('[name="_token"]')?.value
            || '';
    }
}

// Initialize theme manager when DOM is ready
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
