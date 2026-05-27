<?php
require_once 'includes/config.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Vendor') {
    header('location: login.php');
    exit;
}

// Get vendor information
$user_id = $_SESSION['user_id'];
$vendor_query = mysqli_query($link, "SELECT * FROM vendors WHERE user_id = '$user_id'");
$vendor = mysqli_fetch_assoc($vendor_query);
$vendor_id = $vendor['vendor_id'];

// Calculate vendor statistics
$total_products = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(*) as count FROM products WHERE vendor_id = '$vendor_id'"))['count'];

// Get orders from this vendor's products
$total_orders = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT COUNT(DISTINCT oi.order_id) as count FROM order_items oi 
     JOIN products p ON oi.product_id = p.product_id 
     WHERE p.vendor_id = '$vendor_id'"))['count'];

// Calculate total revenue
$total_revenue = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT SUM(oi.price * oi.quantity) as total FROM order_items oi 
     JOIN products p ON oi.product_id = p.product_id 
     WHERE p.vendor_id = '$vendor_id'"))['total'] ?? 0;

// Get revenue for last 7 days
$last_7_days_revenue = mysqli_fetch_assoc(mysqli_query($link, 
    "SELECT SUM(oi.price * oi.quantity) as total FROM order_items oi 
     JOIN products p ON oi.product_id = p.product_id 
     JOIN orders o ON oi.order_id = o.order_id
     WHERE p.vendor_id = '$vendor_id' AND o.order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)"))['total'] ?? 0;

// Get top performing products
$top_products = mysqli_query($link, 
    "SELECT p.product_id, p.name, COUNT(oi.order_item_id) as sales, SUM(oi.price * oi.quantity) as revenue 
     FROM products p 
     LEFT JOIN order_items oi ON p.product_id = oi.product_id 
     WHERE p.vendor_id = '$vendor_id' 
     GROUP BY p.product_id 
     ORDER BY sales DESC 
     LIMIT 5");

// Get recent orders
$recent_orders = mysqli_query($link, 
    "SELECT o.order_id, o.order_date, o.status, o.total_amount, 
            u.full_name, GROUP_CONCAT(p.name SEPARATOR ', ') as products,
            GROUP_CONCAT(oi.quantity SEPARATOR ', ') as quantities
     FROM orders o 
     JOIN users u ON o.user_id = u.user_id 
     JOIN order_items oi ON o.order_id = oi.order_id 
     JOIN products p ON oi.product_id = p.product_id 
     WHERE p.vendor_id = '$vendor_id' 
     GROUP BY o.order_id 
     ORDER BY o.order_date DESC 
     LIMIT 10");

// Get status breakdown
$status_breakdown = mysqli_query($link,
    "SELECT o.status, COUNT(o.order_id) as count
     FROM orders o
     JOIN order_items oi ON o.order_id = oi.order_id
     JOIN products p ON oi.product_id = p.product_id
     WHERE p.vendor_id = '$vendor_id'
     GROUP BY o.status");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - CampusHub</title>
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
                <li><a href="seller-dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="seller-products.php"><i class="bi bi-box"></i> My Products</a></li>
                <li><a href="seller-orders.php"><i class="bi bi-bag"></i> Orders</a></li>
                <li><a href="seller-analytics.php"><i class="bi bi-graph-up"></i> Analytics</a></li>
                <li><a href="vendor-profile.php"><i class="bi bi-person"></i> Profile</a></li>
            </ul>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="user-section">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <img src="assets/images/<?php echo htmlspecialchars($vendor['profile_image']); ?>" alt="Profile" class="user-avatar">
                </div>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($vendor['business_name']); ?>! Here's what's happening today.</p>
                </div>
                <button class="btn-add-product" onclick="location.href='seller-products.php'">
                    <i class="bi bi-plus"></i> Add New Product
                </button>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Products</span>
                        <i class="bi bi-box"></i>
                    </div>
                    <h2 class="stat-value"><?php echo $total_products; ?></h2>
                    <p class="stat-change">Active listings</p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Orders</span>
                        <i class="bi bi-bag"></i>
                    </div>
                    <h2 class="stat-value"><?php echo $total_orders; ?></h2>
                    <p class="stat-change">All time</p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Revenue</span>
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <h2 class="stat-value">₦<?php echo number_format($total_revenue, 2); ?></h2>
                    <p class="stat-change">Total earnings</p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">This Week</span>
                        <i class="bi bi-calendar"></i>
                    </div>
                    <h2 class="stat-value">₦<?php echo number_format($last_7_days_revenue, 2); ?></h2>
                    <p class="stat-change positive">+12% from last week</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <!-- Sales Overview -->
                <div class="chart-card full-width">
                    <div class="chart-header">
                        <h3>Sales Overview</h3>
                        <select class="time-filter">
                            <option>Last 7 Days</option>
                            <option>Last 30 Days</option>
                            <option>Last 90 Days</option>
                        </select>
                    </div>
                    <canvas id="salesChart"></canvas>
                </div>

                <!-- Status Breakdown -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Order Status</h3>
                    </div>
                    <canvas id="statusChart"></canvas>
                </div>

                <!-- Store Health -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Store Health</h3>
                    </div>
                    <div class="health-metrics">
                        <div class="health-metric">
                            <div class="metric-label">Completion Rate</div>
                            <div class="metric-progress">
                                <div class="progress-bar" style="width: 95%"></div>
                            </div>
                            <div class="metric-value">95%</div>
                        </div>
                        <div class="health-metric">
                            <div class="metric-label">Response Time</div>
                            <div class="metric-progress">
                                <div class="progress-bar" style="width: 88%"></div>
                            </div>
                            <div class="metric-value">2 hours</div>
                        </div>
                        <div class="health-metric">
                            <div class="metric-label">Customer Rating</div>
                            <div class="metric-progress">
                                <div class="progress-bar" style="width: 92%"></div>
                            </div>
                            <div class="metric-value">4.6/5.0</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products & Recent Orders -->
            <div class="bottom-section">
                <!-- Top Performing Products -->
                <div class="section-card">
                    <div class="section-header">
                        <h3>Top Performing Products</h3>
                        <a href="seller-products.php">View All</a>
                    </div>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Sales</th>
                                <th>Revenue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($product = mysqli_fetch_assoc($top_products)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo $product['sales'] ?? 0; ?> units</td>
                                <td>₦<?php echo number_format($product['revenue'] ?? 0, 2); ?></td>
                                <td><span class="badge badge-active">Active</span></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($top_products) == 0): ?>
                            <tr><td colspan="4" class="text-center text-muted">No products yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Orders -->
                <div class="section-card">
                    <div class="section-header">
                        <h3>Recent Orders</h3>
                        <a href="seller-orders.php">View All</a>
                    </div>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td>₦<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($recent_orders) == 0): ?>
                            <tr><td colspan="5" class="text-center text-muted">No orders yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/seller-dashboard.js"></script>
</body>
</html>
