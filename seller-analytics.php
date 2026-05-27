<?php
require_once 'includes/config.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Vendor') {
    header('location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$vendor_query = mysqli_query($link, "SELECT * FROM vendors WHERE user_id = '$user_id'");
$vendor = mysqli_fetch_assoc($vendor_query);
$vendor_id = $vendor['vendor_id'];

// Get analytics data
$total_products = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(*) as count FROM products WHERE vendor_id = '$vendor_id'"))['count'];

$total_orders = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(DISTINCT oi.order_id) as count FROM order_items oi 
     JOIN products p ON oi.product_id = p.product_id 
     WHERE p.vendor_id = '$vendor_id'"))['count'];

$total_revenue = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT SUM(oi.price * oi.quantity) as total FROM order_items oi 
     JOIN products p ON oi.product_id = p.product_id 
     WHERE p.vendor_id = '$vendor_id'"))['total'] ?? 0;

$avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

// Get products by category
$products_by_category = mysqli_query($link,
    "SELECT c.category_name, COUNT(p.product_id) as count
     FROM products p
     JOIN categories c ON p.category_id = c.category_id
     WHERE p.vendor_id = '$vendor_id'
     GROUP BY c.category_id
     ORDER BY count DESC
     LIMIT 8");

// Get monthly revenue (simulated data for demo)
$monthly_revenue = [
    'Jan' => 45000,
    'Feb' => 52000,
    'Mar' => 48000,
    'Apr' => 65000,
    'May' => 72000,
    'Jun' => 81000
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - CampusHub Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/seller-dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3 class="mb-0">SellerHub</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="seller-dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="seller-products.php"><i class="bi bi-box"></i> My Products</a></li>
                <li><a href="seller-orders.php"><i class="bi bi-bag"></i> Orders</a></li>
                <li><a href="seller-analytics.php" class="active"><i class="bi bi-graph-up"></i> Analytics</a></li>
                <li><a href="vendor-profile.php"><i class="bi bi-person"></i> Profile</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <div class="user-section">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <img src="assets/images/<?php echo htmlspecialchars($vendor['profile_image']); ?>" alt="Profile" class="user-avatar">
                </div>
            </div>

            <div class="page-header">
                <div>
                    <h1>Analytics</h1>
                    <p>Detailed insights about your store performance</p>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Revenue</span>
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <h2 class="stat-value">₦<?php echo number_format($total_revenue, 0); ?></h2>
                    <p class="stat-change">All time earnings</p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Average Order Value</span>
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h2 class="stat-value">₦<?php echo number_format($avg_order_value, 0); ?></h2>
                    <p class="stat-change">Per transaction</p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Orders</span>
                        <i class="bi bi-bag"></i>
                    </div>
                    <h2 class="stat-value"><?php echo $total_orders; ?></h2>
                    <p class="stat-change">Completed sales</p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Active Products</span>
                        <i class="bi bi-box"></i>
                    </div>
                    <h2 class="stat-value"><?php echo $total_products; ?></h2>
                    <p class="stat-change">In your store</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <!-- Revenue Chart -->
                <div class="chart-card full-width">
                    <div class="chart-header">
                        <h3>Monthly Revenue Trend</h3>
                        <select class="time-filter">
                            <option>Last 6 Months</option>
                            <option>Last Year</option>
                            <option>All Time</option>
                        </select>
                    </div>
                    <canvas id="revenueChart"></canvas>
                </div>

                <!-- Products by Category -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Products by Category</h3>
                    </div>
                    <canvas id="categoryChart"></canvas>
                </div>

                <!-- Sales Distribution -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Sales Distribution</h3>
                    </div>
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>

            <!-- Top Categories Table -->
            <div class="section-card" style="margin-top: 30px;">
                <div class="section-header">
                    <h3>Top Categories</h3>
                </div>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Products</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_cat_products = 0;
                        $categories_data = [];
                        while($row = mysqli_fetch_assoc($products_by_category)) {
                            $categories_data[] = $row;
                            $total_cat_products += $row['count'];
                        }
                        ?>
                        <?php foreach($categories_data as $cat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                            <td><?php echo $cat['count']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="flex: 1; height: 20px; background: #f0f0f0; border-radius: 10px; overflow: hidden;">
                                        <div style="height: 100%; background: linear-gradient(135deg, #5b2be0, #7b68ee); width: <?php echo ($total_cat_products > 0 ? ($cat['count'] / $total_cat_products * 100) : 0); ?>%;"></div>
                                    </div>
                                    <span style="font-weight: 600; min-width: 40px;"><?php echo round(($cat['count'] / $total_cat_products * 100), 1); ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Monthly Revenue Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            const gradient = revenueCtx.getContext('2d').createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(91, 43, 224, 0.2)');
            gradient.addColorStop(1, 'rgba(91, 43, 224, 0.01)');

            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue (₦)',
                        data: [45000, 52000, 48000, 65000, 72000, 81000],
                        borderColor: '#5b2be0',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#5b2be0',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: true } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: function(v) { return '₦' + (v/1000).toFixed(0) + 'k'; } }
                        }
                    }
                }
            });
        }

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Beadwork', 'Tailoring', 'Art', 'Others'],
                    datasets: [{
                        data: [30, 25, 20, 25],
                        backgroundColor: ['#5b2be0', '#7b68ee', '#9a87de', '#b8a5e8']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // Distribution Chart
        const distributionCtx = document.getElementById('distributionChart');
        if (distributionCtx) {
            new Chart(distributionCtx, {
                type: 'bar',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Orders',
                        data: [12, 19, 15, 25],
                        backgroundColor: '#5b2be0'
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    plugins: { legend: { display: true } }
                }
            });
        }
    </script>
</body>
</html>
