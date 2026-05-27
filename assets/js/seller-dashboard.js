// Seller Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeSalesChart();
    initializeStatusChart();
    setupEventListeners();
});

// Initialize Sales Chart
function initializeSalesChart() {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    const salesCtx = ctx.getContext('2d');
    const gradient = salesCtx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(91, 43, 224, 0.2)');
    gradient.addColorStop(1, 'rgba(91, 43, 224, 0.01)');

    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
                {
                    label: 'Sales (₦)',
                    data: [12000, 15000, 10000, 18000, 22000, 25000, 28000],
                    borderColor: '#5b2be0',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#5b2be0',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Initialize Status Chart
function initializeStatusChart() {
    const ctx = document.getElementById('statusChart');
    if (!ctx) return;

    const statusCtx = ctx.getContext('2d');

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending', 'Processing', 'Cancelled'],
            datasets: [
                {
                    data: [45, 20, 25, 10],
                    backgroundColor: [
                        '#27ae60',
                        '#f39c12',
                        '#3498db',
                        '#e74c3c'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Setup Event Listeners
function setupEventListeners() {
    // Time filter change
    const timeFilters = document.querySelectorAll('.time-filter');
    timeFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            // Update chart based on selected time range
            console.log('Time filter changed to:', this.value);
            // Add your AJAX call here to fetch new data
        });
    });

    // Add animation to stat cards on scroll
    observeElements('.stat-card');
    observeElements('.section-card');
}

// Intersection Observer for animations
function observeElements(selector) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'slideUp 0.5s ease-out forwards';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll(selector).forEach(el => {
        observer.observe(el);
    });
}

// Add slide-up animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount);
}

// Update statistics with AJAX
function updateStatistics() {
    fetch('api/get-seller-stats.php')
        .then(response => response.json())
        .then(data => {
            // Update stat cards
            updateStatCard('total-products', data.totalProducts);
            updateStatCard('total-orders', data.totalOrders);
            updateStatCard('total-revenue', formatCurrency(data.totalRevenue));
            updateStatCard('weekly-revenue', formatCurrency(data.weeklyRevenue));
        })
        .catch(error => console.error('Error fetching statistics:', error));
}

function updateStatCard(cardId, value) {
    const card = document.querySelector(`[data-stat="${cardId}"]`);
    if (card) {
        card.querySelector('.stat-value').textContent = value;
    }
}

// Refresh data periodically
function startAutoRefresh(interval = 300000) { // 5 minutes
    setInterval(updateStatistics, interval);
}

// Initialize auto-refresh on page load
// Uncomment to enable
// startAutoRefresh();
