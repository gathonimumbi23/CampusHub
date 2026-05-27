<?php
require_once 'includes/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Admin') {
    header('location: login.php');
    exit;
}

// Get system statistics
$total_products = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(*) as count FROM products"))['count'];

$total_vendors = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(*) as count FROM vendors"))['count'];

$total_users = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(*) as count FROM users"))['count'];

$total_customers = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(*) as count FROM users WHERE role = 'Customer'"))['count'];

$total_orders = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(*) as count FROM orders"))['count'];

$total_revenue = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT SUM(total_amount) as total FROM orders"))['total'] ?? 0;

// Recent registrations (last 7 days)
$new_registrations = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(*) as count FROM users WHERE user_id IN (
        SELECT user_id FROM users WHERE DATE(FROM_UNIXTIME(0)) >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    )"))['count'] ?? 0;

// New orders (last 7 days)
$new_orders = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(*) as count FROM orders WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)"))['count'];

// Get marketplace volume data (orders by day for last 7 days)
$volume_data = mysqli_query($link,
    "SELECT DATE(order_date) as date, COUNT(*) as count, SUM(total_amount) as total
     FROM orders
     WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     GROUP BY DATE(order_date)
     ORDER BY date ASC");

// Get products pending approval (simulated - new products added recently)
$pending_products = mysqli_query($link,
    "SELECT p.product_id, p.name, v.business_name as vendor, c.category_name, p.price, p.product_id
     FROM products p
     JOIN vendors v ON p.vendor_id = v.vendor_id
     JOIN categories c ON p.category_id = c.category_id
     ORDER BY p.product_id DESC
     LIMIT 5");

// Get reported items (simulated - we'll create a reports system)
$reported_items = mysqli_query($link,
    "SELECT p.product_id, p.name, v.business_name, COUNT(*) as report_count
     FROM products p
     JOIN vendors v ON p.vendor_id = v.vendor_id
     GROUP BY p.product_id
     LIMIT 5");

// Recent system activity (orders, new users, new products)
$recent_activity = [];

// Get recent orders
$recent_orders = mysqli_query($link,
    "SELECT o.order_id, u.full_name, o.total_amount, o.order_date, 'order' as type
     FROM orders o
     JOIN users u ON o.user_id = u.user_id
     ORDER BY o.order_date DESC
     LIMIT 3");

while($order = mysqli_fetch_assoc($recent_orders)) {
    $recent_activity[] = $order;
}

// Get recent users
$recent_users = mysqli_query($link,
    "SELECT user_id, full_name, email, 'user' as type, DATE(FROM_UNIXTIME(user_id)) as order_date
     FROM users
     WHERE role = 'Customer'
     ORDER BY user_id DESC
     LIMIT 3");

while($user = mysqli_fetch_assoc($recent_users)) {
    $recent_activity[] = $user;
}

// Sort activity by date
usort($recent_activity, function($a, $b) {
    return strtotime($b['order_date']) - strtotime($a['order_date']);
});

$recent_activity = array_slice($recent_activity, 0, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Central - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="admin-logo">
                    <i class="bi bi-shield-check"></i>
                    <span>Admin Central</span>
                </div>
            </div>

            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="admin-users.php"><i class="bi bi-people"></i> Users</a></li>
                <li><a href="admin-products.php"><i class="bi bi-box"></i> Products</a></li>
                <li><a href="admin-transactions.php"><i class="bi bi-receipt"></i> Transactions</a></li>
                <li><a href="admin-reports.php"><i class="bi bi-flag"></i> Reports</a></li>
                <li><a href="admin-analytics.php"><i class="bi bi-graph-up"></i> Analytics</a></li>
                <li><a href="admin-settings.php"><i class="bi bi-gear"></i> Settings</a></li>
            </ul>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="search-section">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Search users, orders, or reports...">
                    </div>
                </div>

                <div class="top-actions">
                    <button class="action-btn" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <span class="badge">3</span>
                    </button>
                    <button class="action-btn" title="Settings">
                        <i class="bi bi-gear"></i>
                    </button>
                    <div class="admin-profile">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=5b2be0&color=fff" alt="Admin" class="avatar">
                        <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Admin Dashboard</h1>
                    <p>Welcome back! Here's a quick overview of your marketplace.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-icon" style="background: rgba(91, 43, 224, 0.1); color: #5b2be0;">
                            <i class="bi bi-box"></i>
                        </div>
                        <div>
                            <p class="stat-label">Total Products</p>
                            <h3 class="stat-value"><?php echo number_format($total_products); ?></h3>
                            <span class="stat-change positive">+8.3%</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-icon" style="background: rgba(255, 159, 28, 0.1); color: #ff9f1c;">
                            <i class="bi bi-shop"></i>
                        </div>
                        <div>
                            <p class="stat-label">Active Sellers</p>
                            <h3 class="stat-value"><?php echo number_format($total_vendors); ?></h3>
                            <span class="stat-change positive">+5.1%</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <div>
                            <p class="stat-label">New Registrations</p>
                            <h3 class="stat-value">+<?php echo $new_registrations; ?></h3>
                            <span class="stat-change positive">+12%</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-icon" style="background: rgba(244, 67, 54, 0.1); color: #f44336;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <p class="stat-label">Reported Items</p>
                            <h3 class="stat-value">24</h3>
                            <span class="stat-change negative">-3%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-row">
                <!-- Marketplace Volume Chart -->
                <div class="chart-card full-width">
                    <div class="chart-header">
                        <h3>Marketplace Volume</h3>
                        <div class="time-range">
                            <button class="time-btn active">Last 7 Days</button>
                            <button class="time-btn">Last 30 Days</button>
                            <button class="time-btn">Last 90 Days</button>
                        </div>
                    </div>
                    <canvas id="volumeChart"></canvas>
                </div>
            </div>

            <!-- Product Approvals & Reported Items -->
            <div class="bottom-row">
                <!-- Product Approvals -->
                <div class="section-card">
                    <div class="section-header">
                        <h3>Product Approvals</h3>
                        <a href="admin-products.php" class="view-all">View All Queue</a>
                    </div>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Seller</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = mysqli_fetch_assoc($pending_products)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars(substr($product['name'], 0, 25)); ?></strong></td>
                                    <td><?php echo htmlspecialchars($product['vendor']); ?></td>
                                    <td><span class="badge-category"><?php echo htmlspecialchars($product['category_name']); ?></span></td>
                                    <td><?php echo date('m/d/Y'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reported Items -->
                <div class="section-card">
                    <div class="section-header">
                        <h3>Reported Items</h3>
                        <a href="admin-reports.php" class="view-all">View All</a>
                    </div>
                    <div class="reported-items">
                        <?php 
                        $item_count = 0;
                        while($item = mysqli_fetch_assoc($reported_items) && $item_count < 3): 
                            $item_count++;
                        ?>
                        <div class="reported-item">
                            <div class="item-icon">
                                <i class="bi bi-exclamation-circle"></i>
                            </div>
                            <div class="item-info">
                                <p class="item-name"><?php echo htmlspecialchars(substr($item['name'], 0, 30)); ?></p>
                                <small class="item-seller">Vendor: <?php echo htmlspecialchars($item['business_name']); ?></small>
                                <small class="item-count"><?php echo $item['report_count']; ?> reports</small>
                            </div>
                            <span class="badge badge-danger">Urgent</span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- System Activity -->
            <div class="section-card" style="margin-top: 20px;">
                <div class="section-header">
                    <h3>System Activity</h3>
                </div>
                <div class="activity-log">
                    <?php foreach($recent_activity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php if($activity['type'] == 'order'): ?>
                                <i class="bi bi-bag"></i>
                            <?php else: ?>
                                <i class="bi bi-person"></i>
                            <?php endif; ?>
                        </div>
                        <div class="activity-info">
                            <?php if($activity['type'] == 'order'): ?>
                                <p class="activity-title">New order placed</p>
                                <small><?php echo htmlspecialchars($activity['full_name']); ?> ordered for ₦<?php echo number_format($activity['total_amount'], 2); ?></small>
                            <?php else: ?>
                                <p class="activity-title">New user registered</p>
                                <small><?php echo htmlspecialchars($activity['full_name']); ?> (<?php echo htmlspecialchars($activity['email']); ?>)</small>
                            <?php endif; ?>
                        </div>
                        <span class="activity-time"><?php echo date('H:i', strtotime($activity['order_date'])); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/admin-dashboard.js"></script>
</body>
</html>
