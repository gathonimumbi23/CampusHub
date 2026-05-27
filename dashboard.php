<?php
require_once 'includes/config.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin'){
    header("location: login.php");
    exit;
}

$action = $_GET['action'] ?? 'view';
$product_id = $_GET['id'] ?? null;

// Handle Delete
if($action == 'delete' && $product_id){
    $sql = "DELETE FROM products WHERE product_id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("location: dashboard.php?msg=deleted");
    exit;
}

// Handle Add/Edit
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = $_POST['price'];
    $category_id = $_POST['category_id'];
    $vendor_id   = $_POST['vendor_id'];
    $type        = $_POST['type'];
    $image       = "default_product.jpg";

    if(isset($_POST['product_id']) && !empty($_POST['product_id'])){
        $pid = $_POST['product_id'];
        $sql = "UPDATE products SET name=?, description=?, price=?, category_id=?, vendor_id=?, type=? WHERE product_id=?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssdiii i", $name, $description, $price, $category_id, $vendor_id, $type, $pid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        header("location: dashboard.php?msg=updated");
    } else {
        $sql = "INSERT INTO products (name, description, price, category_id, vendor_id, type, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssdiiis", $name, $description, $price, $category_id, $vendor_id, $type, $image);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        header("location: dashboard.php?msg=added");
    }
    exit;
}

// Fetch product for edit
$product_to_edit = null;
if($action == 'edit' && $product_id){
    $sql = "SELECT * FROM products WHERE product_id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product_to_edit = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}

// Stats
$total_users    = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM users"))['c'];
$total_products = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM products"))['c'];
$total_cats     = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM categories"))['c'];
$total_orders   = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM orders"))['c'];

// Dropdowns
$categories = mysqli_query($link, "SELECT * FROM categories ORDER BY category_name");
$vendors     = mysqli_query($link, "SELECT vendor_id, business_name FROM vendors");

// Products list
$products_result = mysqli_query($link, "SELECT p.*, c.category_name, v.business_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.category_id 
    JOIN vendors v ON p.vendor_id = v.vendor_id 
    ORDER BY p.product_id DESC");

// Users list
$users_result = mysqli_query($link, "SELECT * FROM users ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f5f7fb; margin: 0; }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, #1a0533, #3b1d8a, #5b2be0);
            min-height: 100vh;
            width: 240px;
            position: fixed;
            top: 0; left: 0;
            padding: 25px 0;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }
        .sidebar-brand {
            color: white;
            font-size: 20px;
            font-weight: 800;
            text-align: center;
            padding: 0 20px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 15px;
        }
        .sidebar-brand span { color: #ffd166; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 13px 25px;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.12);
            color: white;
            border-left: 4px solid #ffd166;
        }
        .sidebar-menu li a i { font-size: 17px; }
        .sidebar-footer {
            position: absolute;
            bottom: 25px;
            left: 0; right: 0;
            padding: 0 25px;
        }
        .sidebar-footer a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .sidebar-footer a:hover { color: white; }

        /* Main */
        .main-content { margin-left: 240px; padding: 25px; }

        /* Topbar */
        .topbar {
            background: white;
            border-radius: 16px;
            padding: 16px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 3px 15px rgba(91,43,224,0.08);
            margin-bottom: 25px;
            animation: fadeInDown 0.5s ease;
        }
        .topbar h5 { margin: 0; font-weight: 700; color: #333; }
        .admin-badge {
            background: linear-gradient(90deg, #5b2be0, #7b68ee);
            color: white;
            border-radius: 30px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Stat cards */
        .stat-card {
            background: white;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 5px 20px rgba(91,43,224,0.08);
            display: flex;
            align-items: center;
            gap: 18px;
            transition: 0.3s;
            animation: fadeInUp 0.5s ease both;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(91,43,224,0.15); }
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-icon {
            width: 55px; height: 55px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; flex-shrink: 0;
        }
        .si-purple { background: #ede9fe; color: #5b2be0; }
        .si-orange { background: #fff3e0; color: #ff9800; }
        .si-green  { background: #e8f5e9; color: #4caf50; }
        .si-blue   { background: #e3f2fd; color: #2196f3; }
        .stat-num { font-size: 30px; font-weight: 800; color: #333; display: block; line-height: 1; }
        .stat-lbl { font-size: 12px; color: gray; margin-top: 4px; display: block; }

        /* Section card */
        .section-card {
            background: white;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 5px 20px rgba(91,43,224,0.08);
            margin-bottom: 22px;
            animation: fadeInUp 0.6s ease both;
        }
        .section-card-title {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0ebff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-card-title i { color: #5b2be0; font-size: 18px; }

        /* Table */
        .dash-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .dash-table th {
            background: #f5f0ff;
            color: #5b2be0;
            font-weight: 600;
            padding: 11px 14px;
            text-align: left;
        }
        .dash-table td {
            padding: 11px 14px;
            border-bottom: 1px solid #f5f5f5;
            color: #444;
            vertical-align: middle;
        }
        .dash-table tr:hover td { background: #faf8ff; }

        /* Buttons */
        .btn-edit {
            background: #e3f2fd; color: #2196f3;
            border: none; border-radius: 8px;
            padding: 4px 12px; font-size: 12px;
            font-weight: 600; text-decoration: none;
            margin-right: 4px; transition: 0.2s; display: inline-block;
        }
        .btn-edit:hover { background: #2196f3; color: white; }
        .btn-del {
            background: #ffebee; color: #f44336;
            border: none; border-radius: 8px;
            padding: 4px 12px; font-size: 12px;
            font-weight: 600; text-decoration: none;
            transition: 0.2s; display: inline-block; cursor: pointer;
        }
        .btn-del:hover { background: #f44336; color: white; }

        /* Form */
        .form-label { font-weight: 600; font-size: 13px; color: #555; }
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            padding: 9px 13px;
            font-size: 13px;
            transition: 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #7b68ee;
            box-shadow: 0 0 0 0.2rem rgba(91,43,224,0.12);
        }
        .btn-save {
            background: linear-gradient(90deg, #5b2be0, #7b68ee);
            color: white; border: none;
            border-radius: 30px; padding: 11px 30px;
            font-weight: 700; font-size: 14px;
            transition: 0.3s;
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(91,43,224,0.3); color: white; }

        /* Role badges */
        .badge-admin    { background: #ede9fe; color: #5b2be0; border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; }
        .badge-vendor   { background: #e8f5e9; color: #4caf50; border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; }
        .badge-customer { background: #e3f2fd; color: #2196f3; border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">Campus<span>Hub</span></div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="active"><i class="bi bi-grid-1x2"></i> Overview</a></li>
        <li><a href="#products"><i class="bi bi-box-seam"></i> Products</a></li>
        <li><a href="#add-product"><i class="bi bi-plus-circle"></i> Add Product</a></li>
        <li><a href="#users"><i class="bi bi-people"></i> Users</a></li>
        <li><a href="marketplace.php"><i class="bi bi-shop"></i> Marketplace</a></li>
        <li><a href="index.php"><i class="bi bi-house"></i> Homepage</a></li>
    </ul>
    <div class="sidebar-footer">
        <a href="logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Topbar -->
    <div class="topbar">
        <div>
            <h5>Admin Dashboard</h5>
            <small style="color:gray;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong> 👋</small>
        </div>
        <span class="admin-badge"><i class="bi bi-shield-check"></i> Admin Panel</span>
    </div>

    <!-- Alerts -->
    <?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-<?php echo $_GET['msg']==='deleted' ? 'warning' : 'success'; ?> alert-dismissible fade show" style="border-radius:12px;">
        <?php
        if($_GET['msg']==='added')   echo '<i class="bi bi-check-circle"></i> Product added successfully!';
        if($_GET['msg']==='updated') echo '<i class="bi bi-check-circle"></i> Product updated successfully!';
        if($_GET['msg']==='deleted') echo '<i class="bi bi-trash"></i> Product deleted.';
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon si-purple"><i class="bi bi-people"></i></div>
                <div>
                    <span class="stat-num" id="d-users">0</span>
                    <span class="stat-lbl">Total Users</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon si-orange"><i class="bi bi-box-seam"></i></div>
                <div>
                    <span class="stat-num" id="d-products">0</span>
                    <span class="stat-lbl">Products</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon si-green"><i class="bi bi-grid"></i></div>
                <div>
                    <span class="stat-num" id="d-cats">0</span>
                    <span class="stat-lbl">Categories</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon si-blue"><i class="bi bi-bag-check"></i></div>
                <div>
                    <span class="stat-num" id="d-orders">0</span>
                    <span class="stat-lbl">Orders</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ADD/EDIT PRODUCT FORM -->
    <div class="section-card" id="add-product">
        <div class="section-card-title">
            <i class="bi bi-<?php echo $product_to_edit ? 'pencil' : 'plus-circle'; ?>"></i>
            <?php echo $product_to_edit ? 'Edit Product' : 'Add New Product'; ?>
        </div>
        <form action="dashboard.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo $product_to_edit['product_id'] ?? ''; ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control"
                           placeholder="e.g. Colorful Bracelet"
                           value="<?php echo htmlspecialchars($product_to_edit['name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Price (Ksh)</label>
                    <input type="number" step="0.01" name="price" class="form-control"
                           placeholder="0.00"
                           value="<?php echo $product_to_edit['price'] ?? ''; ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Describe the product or service..."><?php echo htmlspecialchars($product_to_edit['description'] ?? ''); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select category...</option>
                        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $cat['category_id']; ?>"
                            <?php if(isset($product_to_edit) && $product_to_edit['category_id'] == $cat['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select" required>
                        <option value="">Select vendor...</option>
                        <?php while($ven = mysqli_fetch_assoc($vendors)): ?>
                        <option value="<?php echo $ven['vendor_id']; ?>"
                            <?php if(isset($product_to_edit) && $product_to_edit['vendor_id'] == $ven['vendor_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($ven['business_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        <option value="">Select type...</option>
                        <option value="Product" <?php if(isset($product_to_edit) && $product_to_edit['type']=='Product') echo 'selected'; ?>>Product</option>
                        <option value="Service" <?php if(isset($product_to_edit) && $product_to_edit['type']=='Service') echo 'selected'; ?>>Service</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn-save btn">
                    <i class="bi bi-<?php echo $product_to_edit ? 'check-circle' : 'plus-circle'; ?>"></i>
                    <?php echo $product_to_edit ? 'Update Product' : 'Add Product'; ?>
                </button>
                <?php if($product_to_edit): ?>
                <a href="dashboard.php" class="btn btn-outline-secondary" style="border-radius:30px; padding:11px 25px;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- PRODUCTS TABLE -->
    <div class="section-card" id="products">
        <div class="section-card-title">
            <i class="bi bi-box-seam"></i> Manage Products
            <span style="font-size:12px;color:gray;font-weight:400;margin-left:auto;"><?php echo $total_products; ?> total</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Vendor</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while($row = mysqli_fetch_assoc($products_result)): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td style="color:#5b2be0;font-weight:600;">Ksh <?php echo number_format($row['price'],2); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td>
                            <span style="background:<?php echo $row['type']==='Product'?'#e8f5e9':'#fff3e0'; ?>;
                                         color:<?php echo $row['type']==='Product'?'#4caf50':'#ff9800'; ?>;
                                         border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;">
                                <?php echo $row['type']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['business_name']); ?></td>
                        <td>
                            <a href="dashboard.php?action=edit&id=<?php echo $row['product_id']; ?>" class="btn-edit">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="dashboard.php?action=delete&id=<?php echo $row['product_id']; ?>"
                               class="btn-del"
                               onclick="return confirm('Delete this product?')">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- USERS TABLE -->
    <div class="section-card" id="users">
        <div class="section-card-title">
            <i class="bi bi-people"></i> Registered Users
            <span style="font-size:12px;color:gray;font-weight:400;margin-left:auto;"><?php echo $total_users; ?> total</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $j = 1; while($user = mysqli_fetch_assoc($users_result)): ?>
                    <tr>
                        <td><?php echo $j++; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? '—'); ?></td>
                        <td>
                            <span class="badge-<?php echo strtolower($user['role']); ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Animated counters
function animateCounter(id, target) {
    let start = 0;
    const increment = target / 60;
    const timer = setInterval(() => {
        start += increment;
        if(start >= target){
            document.getElementById(id).textContent = target;
            clearInterval(timer);
        } else {
            document.getElementById(id).textContent = Math.floor(start);
        }
    }, 16);
}
window.addEventListener('load', () => {
    animateCounter('d-users',    <?php echo $total_users; ?>);
    animateCounter('d-products', <?php echo $total_products; ?>);
    animateCounter('d-cats',     <?php echo $total_cats; ?>);
    animateCounter('d-orders',   <?php echo $total_orders; ?>);
});
</script>
</body>
</html>