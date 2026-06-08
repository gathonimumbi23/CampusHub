<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)$_POST['product_id'];
    $title = trim($_POST['title']);
    $price = (float)$_POST['price'];
    $category = $_POST['category'];
    $description = trim($_POST['description']);
    $sellerId = $_SESSION['user_id'];

    if (empty($title) || $price <= 0) {
        $_SESSION['error_msg'] = "Invalid title or price.";
        header("Location: edit_product.php?id=$productId");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE products SET title = ?, price = ?, category = ?, description = ? WHERE id = ? AND seller_id = ?");
        $stmt->execute([$title, $price, $category, $description, $productId, $sellerId]);

        $_SESSION['success_msg'] = "Garment '{$title}' updated successfully!";
        header("Location: seller.php#storefront");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Update Failed: " . $e->getMessage();
        header("Location: seller.php");
        exit;
    }
}
