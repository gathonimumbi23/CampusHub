<!-- Dark Mode Toggle -->
<script src="<?php echo ASSET_URL; ?>js/dark-mode.js"></script>

<!-- Main JavaScript (cart, wishlist, quick view, search, toast, scroll-to-top) -->
<script src="<?php echo ASSET_URL; ?>js/main.js"></script>

<!-- Cart Drawer -->
<script src="<?php echo ASSET_URL; ?>js/cart-drawer.js"></script>

<!-- Quick View -->
<script src="<?php echo ASSET_URL; ?>js/quick-view.js"></script>

<!-- Wishlist Page -->
<script src="<?php echo ASSET_URL; ?>js/wishlist.js"></script>

<!-- Mobile Navigation -->
<script src="<?php echo ASSET_URL; ?>js/mobile-nav.js"></script>

<!-- Lazy Loading for Images -->
<script>
(function() {
    'use strict';

    if ('IntersectionObserver' in window) {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        document.querySelectorAll('img[data-src]').forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
})();
</script>