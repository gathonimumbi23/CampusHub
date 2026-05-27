<?php
require_once 'includes/config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Admin') {
    header('location: login.php');
    exit;
}

// Get all users with search/filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($link, $_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

$query = "SELECT * FROM users WHERE 1=1";
if ($search) {
    $query .= " AND (full_name LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($role_filter) {
    $query .= " AND role = '$role_filter'";
}
$query .= " ORDER BY user_id DESC";

$users = mysqli_query($link, $query);
$user_count = mysqli_num_rows($users);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin CampusHub</title>
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
                <li><a href="admin-users.php" class="active"><i class="bi bi-people"></i> Users</a></li>
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
            <div class="top-bar">
                <div class="search-section">
                    <form class="search-box" method="GET" style="flex: 1; margin: 0;">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" placeholder="Search users by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>

                <div class="top-actions">
                    <button class="action-btn" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <span class="badge">2</span>
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
                    <h1>User Management</h1>
                    <p>Manage all users on the CampusHub marketplace</p>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                <a href="admin-users.php" class="btn btn-sm <?php echo !$role_filter ? 'btn-primary' : 'btn-outline-primary'; ?>">All Users (<?php echo $user_count; ?>)</a>
                <a href="?role=Admin" class="btn btn-sm <?php echo $role_filter === 'Admin' ? 'btn-primary' : 'btn-outline-primary'; ?>">Admins</a>
                <a href="?role=Vendor" class="btn btn-sm <?php echo $role_filter === 'Vendor' ? 'btn-primary' : 'btn-outline-primary'; ?>">Vendors</a>
                <a href="?role=Customer" class="btn btn-sm <?php echo $role_filter === 'Customer' ? 'btn-primary' : 'btn-outline-primary'; ?>">Customers</a>
            </div>

            <!-- Users Table -->
            <div class="section-card">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><strong>#<?php echo $user['user_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge" style="background: <?php echo $user['role'] === 'Admin' ? '#5b2be0' : ($user['role'] === 'Vendor' ? '#10b981' : '#3498db'); ?>">
                                        <?php echo $user['role']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewUser(<?php echo $user['user_id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $user['user_id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>

                            <?php if($user_count == 0): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    No users found
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
        function viewUser(userId) {
            alert('User details view coming soon! User #' + userId);
        }

        function confirmDelete(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                alert('User deletion coming soon! User #' + userId);
            }
        }
    </script>
</body>
</html>
