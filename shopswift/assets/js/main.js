// ========================================
// MAIN JAVASCRIPT - ShopSwift
// ========================================

(function() {
    'use strict';

    const apiUrl = (path) => `${window.ShopSwift?.baseUrl || ''}${path}`;
    const csrfToken = () => window.ShopSwift?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ========================================
    // 1. TOAST NOTIFICATIONS
    // ========================================
    
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(toast);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOut 0.5s forwards';
                setTimeout(() => toast.remove(), 500);
            }
        }, 4000);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }

    // Make showToast globally accessible
    window.showToast = showToast;

    // ========================================
    // 2. CART FUNCTIONALITY
    // ========================================

    function addToCart(productId) {
        // Show loading state
        const btn = document.querySelector(`[data-product-id="${productId}"] .btn-cart`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        }

        // Make AJAX request
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
                showToast('✓ Added to cart successfully!', 'success');
                updateCartBadge(data.cart_count);
            } else {
                showToast(data.message || 'Error adding to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error adding to cart. Please try again.', 'error');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
            }
        });
    }

    function updateCartBadge(count) {
        const badge = document.querySelector('.nav-links .badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // Make addToCart globally accessible
    window.addToCart = addToCart;

    // ========================================
    // 3. WISHLIST FUNCTIONALITY
    // ========================================

    function toggleWishlist(productId) {
        const btn = document.querySelector(`[data-product-id="${productId}"] .wishlist-btn`);
        const icon = btn ? btn.querySelector('i') : null;
        
        if (icon) {
            // Toggle immediately for better UX
            const isActive = icon.classList.contains('fas');
            icon.className = isActive ? 'far fa-heart' : 'fas fa-heart';
            btn.classList.toggle('active');
        }

        // Make AJAX request
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
                    showToast('✓ Added to wishlist!', 'success');
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

    function updateWishlistBadge(count) {
        const badge = document.querySelector('.nav-links .badge');
        // This would need to target the wishlist badge specifically
        // For now, we'll just update the cart badge or you can add a separate one
    }

    // Make toggleWishlist globally accessible
    window.toggleWishlist = toggleWishlist;

    // ========================================
    // 4. QUICK VIEW MODAL
    // ========================================

    function openQuickView(productId) {
        // Show loading
        const modal = document.getElementById('quickViewModal');
        if (!modal) return;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Fetch product details
        fetch(apiUrl(`api/product.php?id=${productId}`))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderQuickView(data.product);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error loading product details', 'error');
            });
    }

    function renderQuickView(product) {
        const content = document.getElementById('quickViewContent');
        if (!content) return;

        content.innerHTML = `
            <div class="quick-view-grid">
                <div class="quick-view-image">
                    <img src="${product.image_url || 'https://via.placeholder.com/400x400?text=No+Image'}" 
                         alt="${product.name}">
                </div>
                <div class="quick-view-info">
                    <h2>${product.name}</h2>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <span>${product.rating || '4.5'}</span>
                        <span class="reviews">(${product.reviews_count || '0'} reviews)</span>
                    </div>
                    <div class="product-price">$${parseFloat(product.price).toFixed(2)}</div>
                    <p class="product-description">${product.description || 'No description available.'}</p>
                    <button class="btn btn-primary" onclick="addToCart(${product.id})">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    <button class="btn btn-outline" onclick="toggleWishlist(${product.id})">
                        <i class="far fa-heart"></i> Add to Wishlist
                    </button>
                </div>
            </div>
        `;
    }

    function closeQuickView() {
        const modal = document.getElementById('quickViewModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Make openQuickView globally accessible
    window.openQuickView = openQuickView;
    window.closeQuickView = closeQuickView;

    // ========================================
    // 5. SEARCH FUNCTIONALITY
    // ========================================

    function performSearch(query) {
        if (!query || query.length < 2) {
            return;
        }
        window.location.href = `${window.ShopSwift?.baseUrl || ''}products?search=${encodeURIComponent(query)}`;
    }

    // ========================================
    // 6. SCROLL TO TOP
    // ========================================

    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ========================================
    // 7. INITIALIZATION
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {
        // Add scroll to top button
        const scrollBtn = document.createElement('button');
        scrollBtn.className = 'scroll-top-btn';
        scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        scrollBtn.addEventListener('click', scrollToTop);
        document.body.appendChild(scrollBtn);

        // Show/hide scroll button
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        });

        // Add quick view modal to body
        const modalHTML = `
            <div id="quickViewModal" class="modal">
                <div class="modal-content">
                    <button class="modal-close" onclick="closeQuickView()">
                        <i class="fas fa-times"></i>
                    </button>
                    <div id="quickViewContent"></div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        console.log('🚀 ShopSwift initialized successfully!');
    });

})();
