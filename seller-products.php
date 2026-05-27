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

// Handle product actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $name = mysqli_real_escape_string($link, $_POST['name']);
        $description = mysqli_real_escape_string($link, $_POST['description']);
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        $type = mysqli_real_escape_string($link, $_POST['type']);
        $image = 'default_product.jpg';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = basename($_FILES['image']['name']);
            $target = 'assets/images/' . $image;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
        }

        $query = "INSERT INTO products (vendor_id, category_id, name, description, price, image, type) 
                  VALUES ('$vendor_id', '$category_id', '$name', '$description', '$price', '$image', '$type')";
        
        if (mysqli_query($link, $query)) {
            $message = '<div class="alert alert-success">Product added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error adding product!</div>';
        }
    }
}

// Get all products for this vendor
$products = mysqli_query($link, 
    "SELECT p.*, c.category_name FROM products p 
     JOIN categories c ON p.category_id = c.category_id 
     WHERE p.vendor_id = '$vendor_id' 
     ORDER BY p.product_id DESC");

// Get categories for dropdown
$categories = mysqli_query($link, "SELECT * FROM categories ORDER BY category_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - CampusHub Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/seller-dashboard.css">
    <style>
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            transition: 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        .product-image {
            width: 100%;
            height: 180px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-body {
            padding: 20px;
        }
        .product-name {
            font-weight: 700;
            margin-bottom: 8px;
        }
        .product-category {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 12px;
        }
        .product-price {
            font-size: 20px;
            font-weight: 800;
            color: #5b2be0;
            margin-bottom: 15px;
        }
        .product-actions {
            display: flex;
            gap: 8px;
        }
        .product-actions button {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-edit {
            background: #3498db;
            color: white;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        .modal-body {
            padding: 30px;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
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
                <li><a href="seller-products.php" class="active"><i class="bi bi-box"></i> My Products</a></li>
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
            <div class="top-bar">
                <div class="user-section">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <img src="assets/images/<?php echo htmlspecialchars($vendor['profile_image']); ?>" alt="Profile" class="user-avatar">
                </div>
            </div>

            <div class="page-header">
                <div>
                    <h1>My Products</h1>
                    <p>Manage all your products and listings</p>
                </div>
                <button class="btn-add-product" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus"></i> Add New Product
                </button>
            </div>

            <?php echo $message; ?>

            <div class="product-grid">
                <?php while($product = mysqli_fetch_assoc($products)): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="product-body">
                        <h5 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?> • <?php echo htmlspecialchars($product['type']); ?></p>
                        <div class="product-price">₦<?php echo number_format($product['price'], 2); ?></div>
                        <div class="product-actions">
                            <button class="btn-edit" onclick="editProduct(<?php echo $product['product_id']; ?>)">Edit</button>
                            <button class="btn-delete" onclick="deleteProduct(<?php echo $product['product_id']; ?>)">Delete</button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>

                <?php if(mysqli_num_rows($products) == 0): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 20px;"></i>
                    <h4>No products yet</h4>
                    <p class="text-muted">Start by adding your first product to your store</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (₦)</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-control" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-control" name="type" required>
                                    <option value="Product">Product</option>
                                    <option value="Service">Service</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_product" class="btn btn-primary" style="background: linear-gradient(135deg, #5b2be0, #7b68ee); border: none;">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(productId) {
            alert('Edit functionality coming soon!');
        }
        function deleteProduct(productId) {
            if(confirm('Are you sure you want to delete this product?')) {
                alert('Delete functionality coming soon!');
            }
        }
    </script>
</body>
</html>
