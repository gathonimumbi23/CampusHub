// ===================================================
// CampusHub - Homepage JavaScript
// ===================================================

// ===== LIVE SEARCH =====
function initLiveSearch() {
    const input = document.getElementById('nav-search');
    const dropdown = document.getElementById('search-dropdown');
    if (!input || !dropdown) return;

    let searchTimer;

    input.addEventListener('input', function() {
        clearTimeout(searchTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            dropdown.classList.remove('show');
            return;
        }

        searchTimer = setTimeout(() => {
            fetch('search.php?q=' + encodeURIComponent(query))
                .then(r => r.json())
                .then(data => {
                    if (data.length === 0) {
                        dropdown.innerHTML = '<div class="search-no-results">No results found for "' + query + '"</div>';
                    } else {
                        dropdown.innerHTML = data.map(item => `
                            <a href="product-details.php?id=${item.product_id}" class="search-item">
                                <div class="search-item-img">🛍️</div>
                                <div>
                                    <div class="search-item-name">${item.name}</div>
                                    <div class="search-item-price">Ksh ${parseFloat(item.price).toLocaleString()}</div>
                                </div>
                            </a>
                        `).join('');
                    }
                    dropdown.classList.add('show');
                })
                .catch(() => {
                    dropdown.classList.remove('show');
                });
        }, 300);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
}

// ===== ANIMATED COUNTERS =====
function animateCounter(id, target, duration) {
    const el = document.getElementById(id);
    if (!el) return;
    let start = 0;
    const increment = target / (duration / 16);
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            el.textContent = target;
            clearInterval(timer);
        } else {
            el.textContent = Math.floor(start);
        }
    }, 16);
}

// ===== SCROLL REVEAL =====
function initScrollReveal() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal-section').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.7s ease, transform 0.7s ease';
        observer.observe(el);
    });
}

// ===== WISHLIST TOGGLE =====
function initWishlist() {
    document.querySelectorAll('.product-card-wish').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const icon = this.querySelector('i');
            if (icon.classList.contains('bi-heart')) {
                icon.className = 'bi bi-heart-fill';
                this.style.color = '#f44336';
            } else {
                icon.className = 'bi bi-heart';
                this.style.color = '#aaa';
            }
        });
    });
}

// ===== INIT =====
document.addEventListener('DOMContentLoaded', function() {
    initLiveSearch();
    initScrollReveal();
    initWishlist();
});

window.addEventListener('load', function() {
    // Stats are passed from PHP via data attributes
    const statsEl = document.getElementById('stats-data');
    if (statsEl) {
        animateCounter('stat-products',   parseInt(statsEl.dataset.products),   1500);
        animateCounter('stat-vendors',    parseInt(statsEl.dataset.vendors),    1500);
        animateCounter('stat-categories', parseInt(statsEl.dataset.categories), 1500);
        animateCounter('stat-users',      parseInt(statsEl.dataset.users),      1500);
    }
});