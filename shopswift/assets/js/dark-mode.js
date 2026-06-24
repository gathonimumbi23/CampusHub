// ========================================
// DARK MODE TOGGLE
// ========================================

(function() {
    'use strict';

    // Check for saved theme preference
    const savedTheme = localStorage.getItem('shopswift-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Set initial theme
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    } else if (prefersDark) {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('shopswift-theme', 'dark');
    }

    // Function to toggle the theme
    function toggleTheme() {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Apply new theme
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('shopswift-theme', newTheme);
        
        // Update the toggle button icon
        const toggle = document.querySelector('#darkModeToggle');
        if (toggle) {
            if (newTheme === 'dark') {
                toggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                toggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        }
    }

    // Expose toggleTheme globally so onclick="toggleTheme()" in navbar.php works
    window.toggleTheme = toggleTheme;

    // On page load, sync the existing #darkModeToggle icon to the current theme
    function syncToggleIcon() {
        const theme = document.documentElement.getAttribute('data-theme');
        const toggle = document.querySelector('#darkModeToggle');
        if (toggle) {
            if (theme === 'dark') {
                toggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                toggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        }
    }

    // Run once DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncToggleIcon);
    } else {
        syncToggleIcon();
    }

})();