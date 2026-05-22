<?php
require_once 'includes/config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'Admin') {
    header("location: login.php");
    exit;
}

// CRUD Operations
$action = $_GET['action'] ?? 'view';
$product_id = $_GET['id'] ?? null;

// Handle Delete
if ($action == 'delete' && $product_id) {
    $sql = "DELETE FROM products WHERE product_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("location: dashboard.php");
    exit;
}

// Handle Add/Edit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $vendor_id = $_POST['vendor_id'];
    $type = $_POST['type'];
    // Image handling would go here
    $image = "default_product.jpg"; 

    if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
        // Update
        $product_id = $_POST['product_id'];
        $sql = "UPDATE products SET name=?, description=?, price=?, category_id=?, vendor_id=?, type=? WHERE product_id=?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssdiisi", $name, $description, $price, $category_id, $vendor_id, $type, $product_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    } else {
        // Insert
        $sql = "INSERT INTO products (name, description, price, category_id, vendor_id, type, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssdiiss", $name, $description, $price, $category_id, $vendor_id, $type, $image);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header("location: dashboard.php");
    exit;
}

// Fetch data for edit form
$product_to_edit = null;
if ($action == 'edit' && $product_id) {
    $sql = "SELECT * FROM products WHERE product_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product_to_edit = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}

// Fetch lists for dropdowns
$categories = mysqli_query($link, "SELECT * FROM categories");
$vendors = mysqli_query($link, "SELECT vendor_id, business_name FROM vendors");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">
    <h2 class="section-title">Admin Dashboard</h2>

    <!-- Add/Edit Product Form -->
    <div class="card mb-5">
        <div class="card-header">
            <h4><?php echo $product_to_edit ? 'Edit' : 'Add'; ?> Product/Service</h4>
        </div>
        <div class="card-body">
            <form action="dashboard.php" method="post">
                <input type="hidden" name="product_id" value="<?php echo $product_to_edit['product_id'] ?? ''; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $product_to_edit['name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product_to_edit['price'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo $product_to_edit['description'] ?? ''; ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php if(isset($product_to_edit) && $product_to_edit['category_id'] == $cat['category_id']) echo 'selected'; ?>><?php echo $cat['category_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vendor</label>
                        <select name="vendor_id" class="form-select" required>
                             <?php while($ven = mysqli_fetch_assoc($vendors)): ?>
                            <option value="<?php echo $ven['vendor_id']; ?>" <?php if(isset($product_to_edit) && $product_to_edit['vendor_id'] == $ven['vendor_id']) echo 'selected'; ?>><?php echo $ven['business_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="Product" <?php if(isset($product_to_edit) && $product_to_edit['type'] == 'Product') echo 'selected'; ?>>Product</option>
                            <option value="Service" <?php if(isset($product_to_edit) && $product_to_edit['type'] == 'Service') echo 'selected'; ?>>Service</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $product_to_edit ? 'Update' : 'Add'; ?> Product</button>
                 <?php if ($product_to_edit): ?>
                <a href="dashboard.php" class="btn btn-secondary">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Products List -->
    <h4>Manage Products</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Category</th>
                <th>Vendor</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT p.*, c.category_name, v.business_name FROM products p JOIN categories c ON p.category_id = c.category_id JOIN vendors v ON p.vendor_id = v.vendor_id ORDER BY p.product_id DESC";
            $result = mysqli_query($link, $sql);
            while($row = mysqli_fetch_assoc($result)):
            ?>
            <tr>
                <td><?php echo $row['product_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td>Ksh <?php echo htmlspecialchars($row['price']); ?></td>
                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                <td><?php echo htmlspecialchars($row['business_name']); ?></td>
                <td>
                    <a href="dashboard.php?action=edit&id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="dashboard.php?action=delete&id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php include 'includes/footer.php'; ?>
