<?php
require_once 'includes/config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Vendor') {
    header('location: login.php');
    exit;
}

// FIX: use $_SESSION['id'] not $_SESSION['user_id']
$user_id = $_SESSION['id'];
$vendor_query = mysqli_query($link, "SELECT * FROM vendors WHERE user_id = '$user_id'");
$vendor = mysqli_fetch_assoc($vendor_query);

// Auto-create vendor profile if missing
if(!$vendor){
    mysqli_query($link, "INSERT INTO vendors (user_id, business_name, description) 
        VALUES ('$user_id', '{$_SESSION['name']}\'s Shop', 'Welcome to my shop!')");
    $vendor_query = mysqli_query($link, "SELECT * FROM vendors WHERE user_id = '$user_id'");
    $vendor = mysqli_fetch_assoc($vendor_query);
}
$vendor_id = $vendor['vendor_id'];

$message = '';

// Handle Add Product
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])){
    $name        = mysqli_real_escape_string($link, trim($_POST['name']));
    $description = mysqli_real_escape_string($link, trim($_POST['description']));
    $price       = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $type        = mysqli_real_escape_string($link, $_POST['type']);
    $image       = 'default_product.jpg';

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target = 'assets/images/' . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    $query = "INSERT INTO products (vendor_id, category_id, name, description, price, image, type) 
              VALUES ('$vendor_id', '$category_id', '$name', '$description', '$price', '$image', '$type')";

    if(mysqli_query($link, $query)){
        $message = 'success';
    } else {
        $message = 'error';
    }
}

// Handle Delete Product
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])){
    $pid = intval($_GET['id']);
    mysqli_query($link, "DELETE FROM products WHERE product_id = '$pid' AND vendor_id = '$vendor_id'");
    header("location: seller-products.php?msg=deleted");
    exit;
}

// Fetch products
$products = mysqli_query($link,
    "SELECT p.*, c.category_name FROM products p
     JOIN categories c ON p.category_id = c.category_id
     WHERE p.vendor_id = '$vendor_id'
     ORDER BY p.product_id DESC");

$total_products = mysqli_num_rows($products);

// Fetch categories
$categories = mysqli_query($link, "SELECT * FROM categories ORDER BY category_name");

$first_name = explode(' ', $_SESSION['name'])[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/seller-dashboard.css">
    <style>
        body { background: #f5f7fb; }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.06);
            transition: 0.3s;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(91,43,224,0.12); }
        .product-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
            background: #ede9fe;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-body { padding: 16px; }
        .product-name { font-weight: 700; font-size: 15px; color: #1a1a2e; margin-bottom: 4px; }
        .product-category { font-size: 12px; color: #aaa; margin-bottom: 10px; }
        .product-price { font-size: 18px; font-weight: 800; color: #5b2be0; margin-bottom: 12px; }
        .product-actions { display: flex; gap: 8px; }
        .product-actions a, .product-actions button {
            flex: 1; padding: 7px; border: none;
            border-radius: 8px; font-size: 12px;
            font-weight: 600; cursor: pointer;
            text-align: center; text-decoration: none;
            transition: 0.2s;
        }
        .btn-edit-p { background: #e3f2fd; color: #2196f3; }
        .btn-edit-p:hover { background: #2196f3; color: white; }
        .btn-del-p { background: #ffebee; color: #f44336; }
        .btn-del-p:hover { background: #f44336; color: white; }
        .empty-state { grid-column: 1/-1; text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 60px; color: #ddd; display: block; margin-bottom: 15px; }
        .type-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .type-product { background: #e8f5e9; color: #4caf50; }
        .type-service { background: #fff3e0; color: #ff9800; }
    </style>
</head>
<body>
<div class="dashboard-container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3 class="mb-0">CampusHub</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="seller-dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="seller-products.php" class="active"><i class="bi bi-box"></i> My Products</a></li>
            <li><a href="seller-orders.php"><i class="bi bi-bag"></i> Orders</a></li>
            <li><a href="index.php"><i class="bi bi-house"></i> Homepage</a></li>
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
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#5b2be0,#7b68ee);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;">
                    <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if($message === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;">
            <i class="bi bi-check-circle"></i> Product added successfully! It's now live on the marketplace.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif($message === 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;">
            <i class="bi bi-exclamation-circle"></i> Error adding product. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-warning alert-dismissible fade show" style="border-radius:12px;">
            <i class="bi bi-trash"></i> Product deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>My Products 📦</h1>
                <p>Hey <?php echo htmlspecialchars($first_name); ?>! You have <strong><?php echo $total_products; ?></strong> listing(s) on CampusHub.</p>
            </div>
            <button class="btn-add-product" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus"></i> Add New Product
            </button>
        </div>

        <!-- Products Grid -->
        <div class="product-grid">
            <?php
            mysqli_data_seek($products, 0);
            while($product = mysqli_fetch_assoc($products)):
            ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="https://placehold.co/400x160/5b2be0/white?text=<?php echo urlencode($product['name']); ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-body">
                    <span class="type-badge type-<?php echo strtolower($product['type']); ?>">
                        <?php echo $product['type']; ?>
                    </span>
                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-category">
                        <i class="bi bi-tag" style="color:#5b2be0;"></i>
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </div>
                    <div class="product-price">Ksh <?php echo number_format($product['price'], 2); ?></div>
                    <div class="product-actions">
                        <a href="seller-products.php?action=edit&id=<?php echo $product['product_id']; ?>" class="btn-edit-p">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="seller-products.php?action=delete&id=<?php echo $product['product_id']; ?>"
                           class="btn-del-p"
                           onclick="return confirm('Delete this product?')">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>

            <?php if($total_products == 0): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h4>No products yet</h4>
                <p class="text-muted">Start by adding your first product to your store</p>
                <button class="btn-add-product" data-bs-toggle="modal" data-bs-target="#addProductModal"
                        style="margin-top:15px;">
                    <i class="bi bi-plus"></i> Add Your First Product
                </button>
            </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:20px;border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,#5b2be0,#7b68ee);color:white;border-radius:20px 20px 0 0;">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body" style="padding:30px;">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product / Service Name</label>
                        <input type="text" class="form-control" name="name"
                               placeholder="e.g. Colorful Beaded Bracelet" required
                               style="border-radius:10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="3"
                                  placeholder="Describe your product or service..."
                                  style="border-radius:10px;"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Price (Ksh)</label>
                            <input type="number" class="form-control" name="price"
                                   placeholder="0.00" step="0.01" min="0" required
                                   style="border-radius:10px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select class="form-select" name="category_id" required style="border-radius:10px;">
                                <option value="">Select Category...</option>
                                <?php
                                mysqli_data_seek($categories, 0);
                                while($cat = mysqli_fetch_assoc($categories)):
                                ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Type</label>
                            <select class="form-select" name="type" required style="border-radius:10px;">
                                <option value="Product">Product</option>
                                <option value="Service">Service</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Product Image</label>
                            <input type="file" class="form-control" name="image"
                                   accept="image/*" style="border-radius:10px;">
                            <small class="text-muted">Optional — placeholder used if not uploaded</small>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="border:none;padding:20px 30px;">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal" style="border-radius:30px;padding:10px 25px;">
                        Cancel
                    </button>
                    <button type="submit" name="add_product" class="btn"
                            style="background:linear-gradient(135deg,#5b2be0,#7b68ee);color:white;border-radius:30px;padding:10px 30px;font-weight:700;border:none;">
                        <i class="bi bi-plus-circle"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>