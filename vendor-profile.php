<?php
require_once 'includes/config.php';

// Check if vendor ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: index.php");
    exit;
}

$vendor_id = $_GET['id'];

// Fetch vendor details
$sql_vendor = "SELECT v.business_name, v.description, v.profile_image, u.full_name, u.phone, v.payment_info 
               FROM vendors v 
               JOIN users u ON v.user_id = u.user_id 
               WHERE v.vendor_id = ?";

if($stmt_vendor = mysqli_prepare($link, $sql_vendor)){
    mysqli_stmt_bind_param($stmt_vendor, "i", $vendor_id);
    mysqli_stmt_execute($stmt_vendor);
    $result_vendor = mysqli_stmt_get_result($stmt_vendor);
    $vendor = mysqli_fetch_assoc($result_vendor);
    mysqli_stmt_close($stmt_vendor);
} else {
    echo "Error fetching vendor details.";
    $vendor = null;
}

// Fetch vendor's products
$sql_products = "SELECT * FROM products WHERE vendor_id = ?";
if($stmt_products = mysqli_prepare($link, $sql_products)){
    mysqli_stmt_bind_param($stmt_products, "i", $vendor_id);
    mysqli_stmt_execute($stmt_products);
    $result_products = mysqli_stmt_get_result($stmt_products);
} else {
    echo "Error fetching products.";
    $result_products = null;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vendor['business_name'] ?? 'Vendor Profile'); ?> - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">
    <?php if($vendor): ?>
    <div class="card">
        <div class="card-header">
            <h2><?php echo htmlspecialchars($vendor['business_name']); ?></h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <img src="assets/images/<?php echo htmlspecialchars($vendor['profile_image']); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($vendor['business_name']); ?>">
                </div>
                <div class="col-md-8">
                    <p><strong>Owner:</strong> <?php echo htmlspecialchars($vendor['full_name']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($vendor['description']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($vendor['phone']); ?></p>
                    <p><strong>Payment Info:</strong> <?php echo htmlspecialchars($vendor['payment_info']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Products & Services</h3>
    <div class="row">
        <?php while($product = mysqli_fetch_assoc($result_products)): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                    <p class="card-text"><strong>Price:</strong> Ksh <?php echo htmlspecialchars($product['price']); ?></p>
                    <a href="product-details.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if($result_products) mysqli_free_result($result_products); ?>
    </div>
    <?php else: ?>
        <div class="alert alert-danger">Vendor not found.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
