// ========================================
// WISHLIST - ShopSwift
// ========================================

(function() {
    'use strict';

    const apiUrl = (path) => `${window.ShopSwift?.baseUrl || ''}${path}`;
    const csrfToken = () => window.ShopSwift?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ========================================
    // 1. TOGGLE WISHLIST
    // ========================================

    function toggleWishlist(productId) {
        // Find the wishlist button
        const btn = document.querySelector(`[data-product-id="${productId}"] .wishlist-btn`);
        const icon = btn ? btn.querySelector('i') : null;
        
        // Toggle immediately for better UX
        if (icon) {
            const isActive = icon.classList.contains('fas');
            icon.className = isActive ? 'far fa-heart' : 'fas fa-heart';
            btn.classList.toggle('active');
            
            // Add animation
            btn.style.transform = 'scale(1.3)';
            setTimeout(() => {
                btn.style.transform = 'scale(1)';
            }, 200);
        }

        // Send request to API
        fetch(apiUrl('api/wishlist.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken(),
            },
            body: JSON.stringify({
                product_id: productId,
                csrf_token: csrfToken()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.action === 'added') {
                    showToast('❤️ Added to wishlist!', 'success');
                } else {
                    showToast('Removed from wishlist', 'info');
                }
                updateWishlistBadge(data.wishlist_count);
            } else {
                showToast(data.message || 'Error updating wishlist', 'error');
                // Revert icon if error
                if (icon) {
                    const isActive = icon.classList.contains('fas');
                    icon.className = isActive ? 'far fa-heart' : 'fas fa-heart';
                    btn.classList.toggle('active');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error updating wishlist. Please try again.', 'error');
        });
    }

    // ========================================
    // 2. UPDATE WISHLIST BADGE
    // ========================================

    function updateWishlistBadge(count) {
        const badge = document.querySelector('.nav-links .badge');
        // For wishlist, we need a specific wishlist badge
        // Let's find it by looking for a badge near the wishlist link
        const wishlistLink = document.querySelector('.nav-links a[href*="wishlist"]');
        if (wishlistLink) {
            let badge = wishlistLink.querySelector('.badge');
            if (!badge && count > 0) {
                badge = document.createElement('span');
                badge.className = 'badge';
                wishlistLink.appendChild(badge);
            }
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    }

    // ========================================
    // 3. LOAD WISHLIST ITEMS
    // ========================================

    function loadWishlistItems() {
        const container = document.getElementById('wishlistItems');
        if (!container) return;

        container.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        fetch(apiUrl('api/wishlist.php'), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items.length > 0) {
                renderWishlistItems(data.items);
            } else {
                container.innerHTML = getEmptyWishlistHTML();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = getEmptyWishlistHTML();
        });
    }

    // ========================================
    // 4. RENDER WISHLIST ITEMS
    // ========================================

    function renderWishlistItems(items) {
        const container = document.getElementById('wishlistItems');
        if (!container) return;

        let html = '<div class="wishlist-grid">';
        items.forEach(item => {
            html += `
                <div class="wishlist-item" data-id="${item.id}">
                    <div class="wishlist-item-image">
                        <img src="${item.image_url || 'https://via.placeholder.com/200x200?text=No+Image'}" 
                             alt="${item.name}">
                        <button class="wishlist-remove-btn" onclick="removeFromWishlist(${item.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="wishlist-item-info">
                        <h4>${item.name}</h4>
                        <div class="product-price">$${parseFloat(item.price).toFixed(2)}</div>
                        <div class="wishlist-actions">
                            <button class="btn btn-primary btn-sm" onclick="moveToCart(${item.id})">
                                <i class="fas fa-shopping-cart"></i> Move to Cart
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="removeFromWishlist(${item.id})">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function getEmptyWishlistHTML() {
        return `
            <div class="wishlist-empty">
                <i class="fas fa-heart" style="font-size: 60px; color: var(--border-color);"></i>
                <h3>Your wishlist is empty</h3>
                <p style="color: var(--text-muted);">Save your favorite items here.</p>
                <a href="${window.ShopSwift?.baseUrl || ''}products" class="btn btn-primary">Start Shopping</a>
            </div>
        `;
    }

    // ========================================
    // 5. WISHLIST OPERATIONS
    // ========================================

    function removeFromWishlist(productId) {
        if (!confirm('Remove this item from wishlist?')) return;

        fetch(apiUrl('api/wishlist.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken(),
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId,
                csrf_token: csrfToken()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Removed from wishlist', 'info');
                loadWishlistItems();
                updateWishlistBadge(data.wishlist_count);
                
                // Also update the heart icon on product cards
                const btn = document.querySelector(`[data-product-id="${productId}"] .wishlist-btn`);
                if (btn) {
                    const icon = btn.querySelector('i');
                    icon.className = 'far fa-heart';
                    btn.classList.remove('active');
                }
            } else {
                showToast(data.message || 'Error removing item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error removing item. Please try again.', 'error');
        });
    }

    function moveToCart(productId) {
        // First add to cart
        fetch(apiUrl('api/cart.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken(),
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: 1,
                csrf_token: csrfToken()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Then remove from wishlist
                return fetch(apiUrl('api/wishlist.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken(),
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        product_id: productId,
                        csrf_token: csrfToken()
                    })
                });
            } else {
                throw new Error('Failed to add to cart');
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('✓ Moved to cart!', 'success');
                loadWishlistItems();
                updateWishlistBadge(data.wishlist_count);
                updateCartBadgeFromAPI();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error moving item to cart', 'error');
        });
    }

    function updateCartBadgeFromAPI() {
        fetch(apiUrl('api/cart.php'), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.querySelector('.nav-links .badge');
                if (badge) {
                    if (data.cart_count > 0) {
                        badge.textContent = data.cart_count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }
        })
        .catch(error => console.error('Error updating cart badge:', error));
    }

    // ========================================
    // 6. INITIALIZE
    // ========================================

    // Make functions globally accessible
    window.toggleWishlist = toggleWishlist;
    window.removeFromWishlist = removeFromWishlist;
    window.moveToCart = moveToCart;
    window.loadWishlistItems = loadWishlistItems;

    // Load wishlist items if on wishlist page
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.pathname.includes('/wishlist')) {
            loadWishlistItems();
        }

        console.log('❤️ Wishlist initialized');
    });

})();
