// ========================================
// QUICK VIEW - ShopSwift
// ========================================

(function() {
    'use strict';

    const apiUrl = (path) => `${window.ShopSwift?.baseUrl || ''}${path}`;

    // ========================================
    // 1. OPEN QUICK VIEW
    // ========================================

    function openQuickView(productId) {
        const modal = document.getElementById('quickViewModal');
        if (!modal) {
            createQuickViewModal();
            return;
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Show loading
        const content = document.getElementById('quickViewContent');
        if (content) {
            content.innerHTML = `
                <div style="text-align:center;padding:60px;">
                    <i class="fas fa-spinner fa-spin" style="font-size:40px;color:var(--color-secondary);"></i>
                    <p style="margin-top:20px;color:var(--text-muted);">Loading product details...</p>
                </div>
            `;
        }

        // Fetch product details
        fetch(apiUrl(`api/product.php?id=${productId}`))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderQuickView(data.product);
                } else {
                    showToast('Error loading product details', 'error');
                    closeQuickView();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // For demo, use sample data
                const sampleProduct = getSampleProduct(productId);
                renderQuickView(sampleProduct);
            });
    }

    // ========================================
    // 2. RENDER QUICK VIEW
    // ========================================

    function renderQuickView(product) {
        const content = document.getElementById('quickViewContent');
        if (!content) return;

        const hasDiscount = product.compare_price && product.compare_price > product.price;
        const discountPercent = hasDiscount ? Math.round(((product.compare_price - product.price) / product.compare_price) * 100) : 0;

        content.innerHTML = `
            <div class="quick-view-grid">
                <div class="quick-view-image">
                    <img src="${product.image_url || 'https://via.placeholder.com/400x400?text=No+Image'}" 
                         alt="${product.name}">
                </div>
                <div class="quick-view-info">
                    <div class="quick-view-category">
                        ${product.category || 'Fashion'}
                    </div>
                    <h2>${product.name}</h2>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <span>${product.rating || '4.5'}</span>
                        <span class="reviews">(${product.reviews_count || '0'} reviews)</span>
                    </div>
                    <div class="product-price">
                        $${parseFloat(product.price).toFixed(2)}
                        ${hasDiscount ? `<span class="compare">$${parseFloat(product.compare_price).toFixed(2)}</span>` : ''}
                        ${hasDiscount ? `<span class="discount-badge">-${discountPercent}%</span>` : ''}
                    </div>
                    <p class="product-description">${product.description || 'No description available.'}</p>
                    
                    ${product.sizes ? `
                        <div class="quick-view-options">
                            <label>Size:</label>
                            <div class="size-options">
                                ${product.sizes.map(size => `<button class="size-btn">${size}</button>`).join('')}
                            </div>
                        </div>
                    ` : ''}
                    
                    ${product.colors ? `
                        <div class="quick-view-options">
                            <label>Color:</label>
                            <div class="color-options">
                                ${product.colors.map(color => `<button class="color-btn" style="background:${color};"></button>`).join('')}
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="quick-view-actions">
                        <button class="btn btn-primary btn-lg" onclick="addToCart(${product.id})">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="btn btn-outline btn-lg" onclick="toggleWishlist(${product.id})">
                            <i class="far fa-heart"></i> Add to Wishlist
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Add size selection functionality
        document.querySelectorAll('.size-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Add color selection functionality
        document.querySelectorAll('.color-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // ========================================
    // 3. CLOSE QUICK VIEW
    // ========================================

    function closeQuickView() {
        const modal = document.getElementById('quickViewModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // ========================================
    // 4. CREATE QUICK VIEW MODAL
    // ========================================

    function createQuickViewModal() {
        const modalHTML = `
            <div id="quickViewModal" class="quick-view-modal">
                <div class="quick-view-content">
                    <button class="quick-view-close" onclick="closeQuickView()">
                        <i class="fas fa-times"></i>
                    </button>
                    <div id="quickViewContent"></div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Close on overlay click
        document.getElementById('quickViewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuickView();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeQuickView();
            }
        });
    }

    // ========================================
    // 5. SAMPLE PRODUCT DATA
    // ========================================

    function getSampleProduct(id) {
        const products = {
            1: {
                id: 1,
                name: 'Modern Tailored Blazer',
                price: 240.00,
                compare_price: 280.00,
                description: 'Elegant blazer perfect for any occasion. Made with premium fabric.',
                image_url: 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=400&h=400&fit=crop',
                category: 'Women',
                rating: 4.8,
                reviews_count: 1200,
                sizes: ['S', 'M', 'L', 'XL'],
                colors: ['#1a1a2e', '#2d3436', '#636e72']
            },
            2: {
                id: 2,
                name: 'Silk Evening Gown',
                price: 189.00,
                compare_price: null,
                description: 'Luxurious silk gown for special events. Flowing design with elegant draping.',
                image_url: 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?w=400&h=400&fit=crop',
                category: 'Women',
                rating: 4.6,
                reviews_count: 856,
                sizes: ['S', 'M', 'L'],
                colors: ['#e84393', '#6c5ce7', '#fd79a8']
            },
            3: {
                id: 3,
                name: 'Acoustic Pro One',
                price: 299.00,
                compare_price: 350.00,
                description: 'Premium quality jacket designed for modern professionals. Water-resistant and breathable.',
                image_url: 'https://images.unsplash.com/photo-1539533018447-63fcce2678e3?w=400&h=400&fit=crop',
                category: 'Men',
                rating: 4.8,
                reviews_count: 2400,
                sizes: ['M', 'L', 'XL', 'XXL'],
                colors: ['#2d3436', '#636e72', '#b2bec3']
            },
            4: {
                id: 4,
                name: 'Velocity Knit Sneakers',
                price: 85.00,
                compare_price: null,
                description: 'Comfortable running sneakers with responsive cushioning and breathable mesh upper.',
                image_url: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop',
                category: 'Men',
                rating: 4.5,
                reviews_count: 890,
                sizes: ['7', '8', '9', '10', '11'],
                colors: ['#2d3436', '#ffffff', '#e17055']
            }
        };

        return products[id] || {
            id: id,
            name: `Product ${id}`,
            price: 99.99,
            compare_price: null,
            description: 'Product description goes here.',
            image_url: 'https://via.placeholder.com/400x400?text=No+Image',
            category: 'Fashion',
            rating: 4.5,
            reviews_count: 100,
            sizes: ['S', 'M', 'L'],
            colors: ['#2d3436', '#636e72']
        };
    }

    // ========================================
    // 6. INITIALIZE
    // ========================================

    // Make functions globally accessible
    window.openQuickView = openQuickView;
    window.closeQuickView = closeQuickView;

    // Create modal on load
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.getElementById('quickViewModal')) {
            createQuickViewModal();
        }

        // Add quick view buttons to product cards
        document.querySelectorAll('.product-card').forEach(card => {
            const productId = card.dataset.productId;
            if (productId) {
                const btn = document.createElement('button');
                btn.className = 'quick-view-btn';
                btn.innerHTML = 'Quick View';
                btn.onclick = (e) => {
                    e.preventDefault();
                    openQuickView(productId);
                };
                
                const imageContainer = card.querySelector('.product-image');
                if (imageContainer) {
                    imageContainer.style.position = 'relative';
                    btn.style.cssText = `
                        position: absolute;
                        bottom: 10px;
                        left: 50%;
                        transform: translateX(-50%);
                        padding: 6px 16px;
                        background: var(--bg-primary);
                        border: 1px solid var(--border-color);
                        border-radius: var(--radius-full);
                        font-size: var(--font-xs);
                        cursor: pointer;
                        opacity: 0;
                        transition: opacity 0.3s;
                        z-index: 5;
                        box-shadow: var(--shadow-md);
                    `;
                    imageContainer.appendChild(btn);
                    
                    imageContainer.addEventListener('mouseenter', () => {
                        btn.style.opacity = '1';
                    });
                    imageContainer.addEventListener('mouseleave', () => {
                        btn.style.opacity = '0';
                    });
                }
            }
        });

        console.log('👁️ Quick View initialized');
    });

})();
