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

// Get all orders for this vendor's products with filtering
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = "WHERE p.vendor_id = '$vendor_id'";

if ($status_filter && $status_filter !== 'All') {
    $where_clause .= " AND o.status = '" . mysqli_real_escape_string($link, $status_filter) . "'";
}

$orders = mysqli_query($link, 
    "SELECT o.*, u.full_name, u.email, GROUP_CONCAT(p.name SEPARATOR ', ') as product_names,
            GROUP_CONCAT(oi.quantity SEPARATOR ', ') as quantities
     FROM orders o
     JOIN users u ON o.user_id = u.user_id
     JOIN order_items oi ON o.order_id = oi.order_id
     JOIN products p ON oi.product_id = p.product_id
     $where_clause
     GROUP BY o.order_id
     ORDER BY o.order_date DESC");

// Get order statuses
$statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - CampusHub Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/seller-dashboard.css">
    <style>
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 20px;
            border: 2px solid #e8e8e8;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: 0.3s;
        }
        .filter-btn.active {
            background: linear-gradient(135deg, #5b2be0, #7b68ee);
            color: white;
            border-color: #5b2be0;
        }
        .orders-table-wrapper {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            overflow-x: auto;
        }
    </style>
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
                <li><a href="seller-orders.php" class="active"><i class="bi bi-bag"></i> Orders</a></li>
                <li><a href="seller-analytics.php"><i class="bi bi-graph-up"></i> Analytics</a></li>
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
                    <h1>Orders</h1>
                    <p>Manage customer orders and shipments</p>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <button class="filter-btn <?php echo !$status_filter || $status_filter === 'All' ? 'active' : ''; ?>" onclick="filterOrders('All')">
                    All Orders
                </button>
                <?php foreach($statuses as $status): ?>
                <button class="filter-btn <?php echo $status_filter === $status ? 'active' : ''; ?>" onclick="filterOrders('<?php echo $status; ?>')">
                    <?php echo $status; ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Orders Table -->
            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Products</th>
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
                            <td>
                                <div><?php echo htmlspecialchars($order['full_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars(substr($order['product_names'], 0, 30)); ?>...</td>
                            <td><strong>₦<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                    <i class="bi bi-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>

                        <?php if(mysqli_num_rows($orders) == 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.3;"></i>
                                No orders found
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterOrders(status) {
            const url = new URL(window.location);
            if (status === 'All') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', status);
            }
            window.location = url.toString();
        }

        function viewOrderDetails(orderId) {
            alert('Order details view coming soon! Order #' + orderId);
        }
    </script>
</body>
</html>
