<?php
require_once 'includes/config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Admin') {
    header('location: login.php');
    exit;
}

// Get all products with search/filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($link, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : '';

$query = "SELECT p.*, c.category_name, v.business_name FROM products p
          JOIN categories c ON p.category_id = c.category_id
          JOIN vendors v ON p.vendor_id = v.vendor_id WHERE 1=1";

if ($search) {
    $query .= " AND p.name LIKE '%$search%'";
}
if ($category_filter) {
    $query .= " AND p.category_id = $category_filter";
}
$query .= " ORDER BY p.product_id DESC";

$products = mysqli_query($link, $query);
$product_count = mysqli_num_rows($products);

// Get categories for filter
$categories = mysqli_query($link, "SELECT * FROM categories ORDER BY category_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Admin CampusHub</title>
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
                <li><a href="admin-products.php" class="active"><i class="bi bi-box"></i> Products</a></li>
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
                        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
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
                    <h1>Products Management</h1>
                    <p>Review and manage marketplace products</p>
                </div>
            </div>

            <!-- Filter by Category -->
            <div style="margin-bottom: 20px;">
                <label style="font-weight: 600; margin-bottom: 10px; display: block;">Filter by Category:</label>
                <select class="form-select" onchange="filterCategory(this.value)" style="width: 200px;">
                    <option value="">All Categories</option>
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_filter == $cat['category_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Products Table -->
            <div class="section-card">
                <div class="section-header" style="margin-bottom: 15px;">
                    <h3>All Products (<?php echo $product_count; ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Vendor</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($product = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td><strong>#<?php echo $product['product_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($product['name'], 0, 30)); ?></td>
                                <td><?php echo htmlspecialchars($product['business_name']); ?></td>
                                <td><span class="badge-category"><?php echo htmlspecialchars($product['category_name']); ?></span></td>
                                <td>₦<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['type']; ?></td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewProduct(<?php echo $product['product_id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $product['product_id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>

                            <?php if($product_count == 0): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    No products found
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
        function filterCategory(categoryId) {
            if (categoryId) {
                window.location.href = '?category=' + categoryId;
            } else {
                window.location.href = '?';
            }
        }

        function viewProduct(productId) {
            alert('Product details view coming soon! Product #' + productId);
        }

        function confirmDelete(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                alert('Product deletion coming soon! Product #' + productId);
            }
        }
    </script>
</body>
</html>
