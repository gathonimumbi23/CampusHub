<?php
session_start();
require_once 'db.php';

// Security Guard
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    $sellerId = $_SESSION['user_id'];

    try {
        // Ensure the seller owns this product before deleting
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$productId, $sellerId]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_msg'] = "Garment successfully removed from your storefront.";
        } else {
            $_SESSION['error_msg'] = "Unable to delete item. You may not have permission.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
    }
}

header("Location: seller.php#storefront");
exit;
