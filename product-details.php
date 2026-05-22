<?php
require_once 'includes/config.php';

// Check if product ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: marketplace.php");
    exit;
}

$product_id = $_GET['id'];

// Fetch product details
$sql = "SELECT p.*, v.business_name, v.vendor_id, c.category_name 
        FROM products p 
        JOIN vendors v ON p.vendor_id = v.vendor_id 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = ?";

if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    echo "Error fetching product details.";
    $product = null;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name'] ?? 'Product Details'); ?> - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">
    <?php if($product): ?>
    <div class="row">
        <div class="col-md-6">
            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p class="text-muted">Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <h3>Ksh <?php echo htmlspecialchars($product['price']); ?></h3>
            <p>Sold by: <a href="vendor-profile.php?id=<?php echo $product['vendor_id']; ?>"><?php echo htmlspecialchars($product['business_name']); ?></a></p>
            
            <form action="cart.php" method="post">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
            </form>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-danger">Product not found.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
