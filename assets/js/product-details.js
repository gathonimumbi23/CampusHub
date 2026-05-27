// Product Details Page JavaScript

let currentRating = 0;

document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
});

function setupEventListeners() {
    // Initialize quantity input
    const qtyInput = document.getElementById('quantity');
    if (qtyInput) {
        qtyInput.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
            if (this.value > 100) this.value = 100;
        });
    }

    // Rating stars hover effect
    const ratingStars = document.querySelectorAll('.rating-input i');
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            currentRating = parseInt(this.dataset.rating);
            updateStarDisplay();
        });

        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            ratingStars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
    });

    // Reset rating display on mouse leave
    const ratingInput = document.querySelector('.rating-input');
    if (ratingInput) {
        ratingInput.addEventListener('mouseleave', updateStarDisplay);
    }
}

function changeImage(imageSrc) {
    const mainImage = document.getElementById('mainImage');
    if (mainImage) {
        mainImage.src = imageSrc;
    }

    // Update thumbnail active state
    const thumbs = document.querySelectorAll('.thumb');
    thumbs.forEach(thumb => {
        thumb.classList.remove('active');
    });
    event.target.closest('.thumb').classList.add('active');
}

function increaseQty() {
    const input = document.getElementById('quantity');
    if (input && input.value < 100) {
        input.value = parseInt(input.value) + 1;
    }
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    if (input && input.value > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function addToCart(productId) {
    const quantity = document.getElementById('quantity').value;
    
    // Create form and submit to add to cart
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'cart.php';
    
    const productInput = document.createElement('input');
    productInput.type = 'hidden';
    productInput.name = 'product_id';
    productInput.value = productId;
    
    const qtyInput = document.createElement('input');
    qtyInput.type = 'hidden';
    qtyInput.name = 'quantity';
    qtyInput.value = quantity;
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'add_to_cart';
    actionInput.value = '1';
    
    form.appendChild(productInput);
    form.appendChild(qtyInput);
    form.appendChild(actionInput);
    
    document.body.appendChild(form);
    form.submit();
    
    showNotification('Added to cart!', 'success');
}

function buyNow(productId) {
    const quantity = document.getElementById('quantity').value;
    
    // Store product info in session and redirect to checkout
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'checkout.php';
    
    const productInput = document.createElement('input');
    productInput.type = 'hidden';
    productInput.name = 'product_id';
    productInput.value = productId;
    
    const qtyInput = document.createElement('input');
    qtyInput.type = 'hidden';
    qtyInput.name = 'quantity';
    qtyInput.value = quantity;
    
    form.appendChild(productInput);
    form.appendChild(qtyInput);
    
    document.body.appendChild(form);
    form.submit();
}

function toggleWishlist(productId) {
    const btn = event.target.closest('.wishlist-btn');
    btn.classList.toggle('added');
    
    // Make AJAX call to save/remove from wishlist
    const isAdded = btn.classList.contains('added');
    const action = isAdded ? 'add' : 'remove';
    
    fetch('api/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=${action}&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        const message = isAdded ? 'Added to wishlist!' : 'Removed from wishlist!';
        showNotification(message, isAdded ? 'success' : 'info');
    })
    .catch(error => console.error('Error:', error));
}

function toggleReviewForm() {
    const form = document.getElementById('reviewForm');
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        if (form.style.display === 'block') {
            form.scrollIntoView({ behavior: 'smooth' });
        }
    }
}

function setRating(rating) {
    currentRating = rating;
    updateStarDisplay();
}

function updateStarDisplay() {
    const stars = document.querySelectorAll('.rating-input i');
    stars.forEach((star, index) => {
        if (index < currentRating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

function submitReview(productId) {
    const title = document.getElementById('reviewTitle').value.trim();
    const text = document.getElementById('reviewText').value.trim();
    
    if (!currentRating) {
        showNotification('Please select a rating', 'error');
        return;
    }
    
    if (!title) {
        showNotification('Please enter a review title', 'error');
        return;
    }
    
    if (!text) {
        showNotification('Please enter your review', 'error');
        return;
    }
    
    // Submit review via AJAX
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('rating', currentRating);
    formData.append('review_title', title);
    formData.append('review_text', text);
    
    fetch('api/submit-review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Review submitted successfully!', 'success');
            document.getElementById('reviewTitle').value = '';
            document.getElementById('reviewText').value = '';
            currentRating = 0;
            updateStarDisplay();
            toggleReviewForm();
            // Reload reviews (you'd implement this)
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Error submitting review', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error submitting review', 'error');
    });
}

function markHelpful() {
    const btn = event.target.closest('.btn-helpful');
    const currentCount = parseInt(btn.textContent.match(/\d+/)[0]);
    btn.textContent = '👍 Helpful (' + (currentCount + 1) + ')';
    btn.disabled = true;
    showNotification('Thanks for your feedback!', 'success');
}

function markNotHelpful() {
    showNotification('Thanks for your feedback!', 'success');
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        background: ${type === 'success' ? '#10b981' : (type === 'error' ? '#e74c3c' : '#3498db')};
        color: white;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount);
}

// Initialize on load
window.addEventListener('load', () => {
    console.log('Product details page initialized');
});
