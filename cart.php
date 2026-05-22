<?php
require_once 'includes/config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Remove from cart
if (isset($_GET['remove'])) {
    $id_to_remove = intval($_GET['remove']);
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($id) use ($id_to_remove) {
        return $id != $id_to_remove;
    });
    header('Location: cart.php');
    exit;
}

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    if (!in_array($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $product_id;
    }
    header('Location: cart.php');
    exit;
}

// Clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header('Location: cart.php');
    exit;
}

$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $in_cart = implode(',', array_map('intval', $_SESSION['cart']));
    $sql = "SELECT p.product_id, p.name, p.price, p.image, v.business_name 
            FROM products p 
            JOIN vendors v ON p.vendor_id = v.vendor_id
            WHERE p.product_id IN ($in_cart)";
    $result = mysqli_query($link, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $cart_items[] = $row;
        $total_price += $row['price'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-wrapper { min-height: 70vh; padding: 40px 0; }
        .cart-card {
            border-radius: 20px;
            box-shadow: 0 5px 30px rgba(91,43,224,0.1);
            border: none;
            padding: 30px;
        }
        .cart-title { font-weight: 700; color: #5b2be0; margin-bottom: 30px; }
        .cart-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
        }
        .btn-remove {
            background: #ff4757;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 13px;
        }
        .btn-remove:hover { background: #ff6b81; color: white; }
        .btn-checkout {
            background: linear-gradient(90deg, #5b2be0, #7b68ee);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 35px;
            font-weight: 600;
            font-size: 16px;
        }
        .btn-checkout:hover { background: #7b68ee; color: white; }
        .total-box {
            background: #f5f0ff;
            border-radius: 15px;
            padding: 20px 25px;
        }
        .empty-cart { text-align: center; padding: 60px 0; }
        .empty-cart .icon { font-size: 70px; }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container cart-wrapper">
    <h2 class="cart-title">🛒 Your Cart
        <span class="badge bg-secondary" style="font-size:16px;">
            <?php echo count($cart_items); ?> item(s)
        </span>
    </h2>

    <?php if (!empty($cart_items)): ?>
    <div class="row">
        <!-- Cart Items -->
        <div class="col-md-8 mb-4">
            <div class="cart-card">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Name</th>
                            <th>Vendor</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>