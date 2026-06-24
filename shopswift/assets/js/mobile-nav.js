// ========================================
// MOBILE NAVIGATION
// ========================================

(function() {
    'use strict';

    // Create mobile menu toggle
    function createMobileMenu() {
        const nav = document.querySelector('.nav-links');
        const container = document.querySelector('.nav-container');
        
        if (!nav || !container) return;
        
        // Create toggle button
        const toggle = document.createElement('button');
        toggle.className = 'mobile-menu-toggle';
        toggle.setAttribute('aria-label', 'Toggle menu');
        toggle.innerHTML = '<i class="fas fa-bars"></i>';
        
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'mobile-overlay';
        overlay.id = 'mobileOverlay';
        
        // Insert toggle into nav container
        const navLinks = container.querySelector('.nav-links');
        if (navLinks) {
            container.insertBefore(toggle, navLinks);
        }
        
        document.body.appendChild(overlay);
        
        // Toggle menu function
        function toggleMenu() {
            nav.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : '';
            
            // Update icon
            const icon = toggle.querySelector('i');
            if (nav.classList.contains('open')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        }
        
        // Event listeners
        toggle.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);
        
        // Close menu on link click (for single page navigation)
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (nav.classList.contains('open')) {
                    toggleMenu();
                }
            });
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && nav.classList.contains('open')) {
                toggleMenu();
            }
        });
    }

    // Initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createMobileMenu);
    } else {
        createMobileMenu();
    }

})();