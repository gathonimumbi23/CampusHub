// Admin Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupEventListeners();
    loadSystemStats();
});

// Initialize Charts
function initializeCharts() {
    initializeVolumeChart();
}

// Marketplace Volume Chart
function initializeVolumeChart() {
    const ctx = document.getElementById('volumeChart');
    if (!ctx) return;

    const chartCtx = ctx.getContext('2d');
    const gradient = chartCtx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(91, 43, 224, 0.3)');
    gradient.addColorStop(1, 'rgba(91, 43, 224, 0.01)');

    new Chart(chartCtx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
                {
                    label: 'Orders',
                    data: [45, 52, 38, 65, 78, 92, 88],
                    backgroundColor: '#5b2be0',
                    borderRadius: 8,
                    borderSkipped: false,
                    barPercentage: 0.6
                },
                {
                    label: 'Revenue (₦1000s)',
                    data: [35, 42, 28, 55, 65, 75, 70],
                    backgroundColor: '#e0e7ff',
                    borderRadius: 8,
                    borderSkipped: false,
                    barPercentage: 0.6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            height: 300,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 13,
                            weight: '600'
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: '#f0f0f0'
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
}

// Setup Event Listeners
function setupEventListeners() {
    // Time range buttons
    const timeButtons = document.querySelectorAll('.time-btn');
    timeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            timeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateChartData(this.textContent);
        });
    });

    // Search functionality
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            performSearch(this.value);
        }, 300));
    }

    // Notification button
    const notificationBtn = document.querySelector('[title="Notifications"]');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            showNotifications();
        });
    }
}

// Update chart data based on time range
function updateChartData(timeRange) {
    console.log('Updating chart for:', timeRange);
    // Add AJAX call here to fetch data for the selected time range
    // For now, this is a placeholder
}

// Perform search
function performSearch(query) {
    if (!query.trim()) return;
    
    console.log('Searching for:', query);
    // Add AJAX call here to search for users, orders, or reports
}

// Show notifications
function showNotifications() {
    alert('You have 3 new notifications:\n- 2 new product reports\n- 1 payment pending review\n- Platform activity spike detected');
}

// Load system statistics
function loadSystemStats() {
    // This could be loaded via AJAX for real-time updates
    console.log('System statistics loaded');
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount);
}

// Animate stat cards
function animateStatCards() {
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            requestAnimationFrame(() => {
                card.style.transition = 'all 0.5s ease-out';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            });
        }, index * 50);
    });
}

// Initialize animations on page load
window.addEventListener('load', animateStatCards);

// Auto-refresh data every 5 minutes
setInterval(() => {
    loadSystemStats();
}, 300000);

// Export data functionality
function exportData(format) {
    console.log('Exporting data as:', format);
    // Add export functionality here
}

// Filter functions
function filterByStatus(status) {
    console.log('Filtering by status:', status);
    // Add filter functionality here
}

function filterByDate(startDate, endDate) {
    console.log('Filtering by date range:', startDate, '-', endDate);
    // Add date filter functionality here
}

// Real-time updates simulation
function simulateRealTimeUpdates() {
    // This would connect to a WebSocket or SSE for real-time updates
    setInterval(() => {
        const randomIncrease = Math.floor(Math.random() * 10);
        console.log('New activity detected: +' + randomIncrease + ' orders');
    }, 30000);
}

// Initialize on document ready
document.addEventListener('DOMContentLoaded', () => {
    // Add any additional initialization code here
    console.log('Admin Dashboard initialized');
});
