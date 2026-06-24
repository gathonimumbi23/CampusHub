// ========================================
// CART DRAWER - ShopSwift
// ========================================

(function() {
    'use strict';

    const apiUrl = (path) => `${window.ShopSwift?.baseUrl || ''}${path}`;
    const csrfToken = () => window.ShopSwift?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Cart data
    let cartItems = [];
    let cartTotal = 0;

    // ========================================
    // 1. OPEN/CLOSE CART DRAWER
    // ========================================

    function openCart() {
        const overlay = document.getElementById('cartOverlay');
        const drawer = document.getElementById('cartDrawer');
        if (overlay && drawer) {
            overlay.classList.add('active');
            drawer.classList.add('open');
            document.body.style.overflow = 'hidden';
            loadCartItems();
        }
    }

    function closeCart() {
        const overlay = document.getElementById('cartOverlay');
        const drawer = document.getElementById('cartDrawer');
        if (overlay && drawer) {
            overlay.classList.remove('active');
            drawer.classList.remove('open');
            document.body.style.overflow = '';
        }
    }

    // Make functions globally accessible
    window.openCart = openCart;
    window.closeCart = closeCart;

    // ========================================
    // 2. LOAD CART ITEMS
    // ========================================

    function loadCartItems() {
        const container = document.getElementById('cartItems');
        const totalEl = document.getElementById('cartTotal');
        const countEl = document.getElementById('cartCount');

        if (!container) return;

        // Show loading
        container.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        // Fetch cart from API
        fetch(apiUrl('api/cart.php'), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cartItems = data.items || [];
                renderCartItems(cartItems);
                updateCartTotal(cartItems);
                updateCartBadge(cartItems.length);
            } else {
                container.innerHTML = getEmptyCartHTML();
            }
        })
        .catch(error => {
            console.error('Error loading cart:', error);
            // For demo, use sample data
            cartItems = getSampleCartItems();
            renderCartItems(cartItems);
            updateCartTotal(cartItems);
        });
    }

    // ========================================
    // 3. RENDER CART ITEMS
    // ========================================

    function renderCartItems(items) {
        const container = document.getElementById('cartItems');
        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = getEmptyCartHTML();
            return;
        }

        let html = '';
        items.forEach(item => {
            html += `
                <div class="cart-item" data-id="${item.id}">
                    <div class="cart-item-image">
                        <img src="${item.image_url || 'https://via.placeholder.com/80x80?text=No+Image'}" 
                             alt="${item.name}">
                    </div>
                    <div class="cart-item-details">
                        <h4>${item.name}</h4>
                        <div class="cart-item-price">$${parseFloat(item.price).toFixed(2)}</div>
                        <div class="cart-item-controls">
                            <button onclick="updateCartQuantity(${item.id}, -1)" aria-label="Decrease quantity">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span>${item.quantity || 1}</span>
                            <button onclick="updateCartQuantity(${item.id}, 1)" aria-label="Increase quantity">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <button class="cart-item-remove" onclick="removeFromCart(${item.id})" aria-label="Remove item">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function getEmptyCartHTML() {
        return `
            <div class="cart-empty">
                <i class="fas fa-shopping-bag"></i>
                <h4>Your cart is empty</h4>
                <p style="color: var(--text-muted);">Start shopping to add items to your cart.</p>
                <button class="btn btn-primary" onclick="closeCart()">Continue Shopping</button>
            </div>
        `;
    }

    // ========================================
    // 4. UPDATE CART TOTAL
    // ========================================

    function updateCartTotal(items) {
        const totalEl = document.getElementById('cartTotal');
        const countEl = document.getElementById('cartCount');
        
        if (!totalEl) return;

        let total = 0;
        let count = 0;
        
        if (items) {
            items.forEach(item => {
                const price = parseFloat(item.price) || 0;
                const qty = parseInt(item.quantity) || 1;
                total += price * qty;
                count += qty;
            });
        }

        cartTotal = total;
        totalEl.textContent = `$${total.toFixed(2)}`;
        
        if (countEl) {
            countEl.textContent = count;
        }
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

    // ========================================
    // 5. CART OPERATIONS
    // ========================================

    function addToCart(productId, quantity = 1) {
        // Show loading state on button
        const btn = document.querySelector(`[data-product-id="${productId}"] .btn-cart`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        }

        // Send to API
        fetch(apiUrl('api/cart.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken(),
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: quantity,
                csrf_token: csrfToken()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('✓ Added to cart!', 'success');
                // Reload cart
                loadCartItems();
                // Update badge
                updateCartBadge(data.cart_count || 1);
                // Auto-open cart after short delay
                setTimeout(() => {
                    openCart();
                }, 500);
            } else {
                showToast(data.message || 'Error adding to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // For demo, add to cart locally
            addToCartLocal(productId);
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
            }
        });
    }

    function updateCartQuantity(productId, change) {
        const item = cartItems.find(i => i.id === productId);
        if (!item) return;

        const newQuantity = (item.quantity || 1) + change;
        
        if (newQuantity <= 0) {
            removeFromCart(productId);
            return;
        }

        // Update via API
        fetch(apiUrl('api/cart.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken(),
            },
            body: JSON.stringify({
                action: 'update',
                product_id: productId,
                quantity: newQuantity,
                csrf_token: csrfToken()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update local data
                item.quantity = newQuantity;
                renderCartItems(cartItems);
                updateCartTotal(cartItems);
                updateCartBadge(cartItems.length);
            } else {
                showToast(data.message || 'Error updating cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Update locally
            item.quantity = newQuantity;
            renderCartItems(cartItems);
            updateCartTotal(cartItems);
        });
    }

    function removeFromCart(productId) {
        // Show confirmation
        if (!confirm('Remove this item from cart?')) return;

        fetch(apiUrl('api/cart.php'), {
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
                // Remove from local data
                cartItems = cartItems.filter(i => i.id !== productId);
                renderCartItems(cartItems);
                updateCartTotal(cartItems);
                updateCartBadge(cartItems.length);
                showToast('Item removed from cart', 'info');
            } else {
                showToast(data.message || 'Error removing item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Remove locally
            cartItems = cartItems.filter(i => i.id !== productId);
            renderCartItems(cartItems);
            updateCartTotal(cartItems);
            updateCartBadge(cartItems.length);
        });
    }

    // ========================================
    // 6. SAMPLE DATA (for demo)
    // ========================================

    function getSampleCartItems() {
        return [
            {
                id: 1,
                name: 'Modern Tailored Blazer',
                price: 240.00,
                quantity: 1,
                image_url: 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=80&h=80&fit=crop'
            },
            {
                id: 2,
                name: 'Silk Evening Gown',
                price: 189.00,
                quantity: 2,
                image_url: 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?w=80&h=80&fit=crop'
            }
        ];
    }

    function addToCartLocal(productId) {
        // Sample local add
        const existing = cartItems.find(i => i.id === productId);
        if (existing) {
            existing.quantity = (existing.quantity || 1) + 1;
        } else {
            cartItems.push({
                id: productId,
                name: `Product ${productId}`,
                price: 99.99,
                quantity: 1,
                image_url: 'https://via.placeholder.com/80x80?text=No+Image'
            });
        }
        renderCartItems(cartItems);
        updateCartTotal(cartItems);
        updateCartBadge(cartItems.length);
        showToast('Added to cart!', 'success');
        setTimeout(() => openCart(), 500);
    }

    // ========================================
    // 7. INITIALIZE
    // ========================================

    // Make functions globally accessible
    window.addToCart = addToCart;
    window.updateCartQuantity = updateCartQuantity;
    window.removeFromCart = removeFromCart;
    window.loadCartItems = loadCartItems;

    // Close cart on overlay click
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('cartOverlay');
        if (overlay) {
            overlay.addEventListener('click', closeCart);
        }

        // Add keyboard support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCart();
            }
        });

        console.log('🛒 Cart drawer initialized');
    });

})();
