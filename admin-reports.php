<?php
require_once 'includes/config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Admin') {
    header('location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin CampusHub</title>
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
                <li><a href="admin-transactions.php"><i class="bi bi-receipt"></i> Transactions</a></li>
                <li><a href="admin-reports.php" class="active"><i class="bi bi-flag"></i> Reports</a></li>
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
                        <input type="text" placeholder="Search reports...">
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
                    <h1>Reported Items</h1>
                    <p>Review and manage user reports</p>
                </div>
            </div>

            <div class="section-card" style="text-align: center; padding: 80px 30px;">
                <i class="bi bi-inbox" style="font-size: 64px; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <h3>Reports Management</h3>
                <p class="text-muted">This page is for managing reported items. Implementation coming soon!</p>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
