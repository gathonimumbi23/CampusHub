<?php
require_once 'includes/config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Admin') {
    header('location: login.php');
    exit;
}

// Get all transactions/orders
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT o.*, u.full_name, u.email, COUNT(oi.order_item_id) as item_count
          FROM orders o
          JOIN users u ON o.user_id = u.user_id
          LEFT JOIN order_items oi ON o.order_id = oi.order_id
          WHERE 1=1";

if ($status_filter) {
    $query .= " AND o.status = '" . mysqli_real_escape_string($link, $status_filter) . "'";
}

$query .= " GROUP BY o.order_id ORDER BY o.order_date DESC";

$orders = mysqli_query($link, $query);
$order_count = mysqli_num_rows($orders);

// Get total revenue
$total_revenue = mysqli_fetch_assoc(mysqli_query($link, "SELECT SUM(total_amount) as total FROM orders"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Admin CampusHub</title>
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
                <li><a href="admin-dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="admin-users.php"><i class="bi bi-people"></i> Users</a></li>
                <li><a href="admin-products.php"><i class="bi bi-box"></i> Products</a></li>
                <li><a href="admin-transactions.php" class="active"><i class="bi bi-receipt"></i> Transactions</a></li>
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
            <div class="top-bar">
                <div class="search-section">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Search transactions...">
                    </div>
                </div>

                <div class="top-actions">
                    <button class="action-btn" title="Notifications">
                        <i class="bi bi-bell"></i>
                    </button>
                    <button class="action-btn" onclick="location.href='admin-settings.php'" title="Settings">
                        <i class="bi bi-gear"></i>
                    </button>
                    <div class="admin-profile">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=5b2be0&color=fff" alt="Admin" class="avatar">
                        <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    </div>
                </div>
            </div>

            <div class="page-header">
                <div>
                    <h1>Transactions</h1>
                    <p>Manage all marketplace transactions and payments</p>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="stats-grid" style="margin-bottom: 30px; grid-template-columns: repeat(3, 1fr);">
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-icon" style="background: rgba(91, 43, 224, 0.1); color: #5b2be0;">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div>
                            <p class="stat-label">Total Orders</p>
                            <h3 class="stat-value"><?php echo $order_count; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div>
                            <p class="stat-label">Total Revenue</p>
                            <h3 class="stat-value">₦<?php echo number_format($total_revenue, 0); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-icon" style="background: rgba(255, 159, 28, 0.1); color: #ff9f1c;">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div>
                            <p class="stat-label">Avg. Order Value</p>
                            <h3 class="stat-value">₦<?php echo number_format($order_count > 0 ? $total_revenue / $order_count : 0, 0); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                <a href="admin-transactions.php" class="btn btn-sm <?php echo !$status_filter ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                <a href="?status=Pending" class="btn btn-sm <?php echo $status_filter === 'Pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">Pending</a>
                <a href="?status=Processing" class="btn btn-sm <?php echo $status_filter === 'Processing' ? 'btn-primary' : 'btn-outline-primary'; ?>">Processing</a>
                <a href="?status=Completed" class="btn btn-sm <?php echo $status_filter === 'Completed' ? 'btn-primary' : 'btn-outline-primary'; ?>">Completed</a>
                <a href="?status=Cancelled" class="btn btn-sm <?php echo $status_filter === 'Cancelled' ? 'btn-primary' : 'btn-outline-primary'; ?>">Cancelled</a>
            </div>

            <!-- Transactions Table -->
            <div class="section-card">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = mysqli_fetch_assoc($orders)): ?>
                            <tr>
                                <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                <td><?php echo $order['item_count']; ?> item(s)</td>
                                <td><strong>₦<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $order['status'] === 'Completed' ? 'success' : ($order['status'] === 'Pending' ? 'warning' : 'info'); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction(<?php echo $order['order_id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>

                            <?php if($order_count == 0): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    No transactions found
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewTransaction(orderId) {
            alert('Transaction details view coming soon! Order #' + orderId);
        }
    </script>
</body>
</html>
