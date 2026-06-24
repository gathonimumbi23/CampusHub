<?php
// ========================================
// CART CONTROLLER
// ========================================

require_once __DIR__ . '/../models/cart.php';
require_once __DIR__ . '/../models/product.php';

class CartController {
    
    public function index() {
        // Check if logged in
        if (!isLoggedIn()) {
            $_SESSION['redirect_after_login'] = BASE_URL . 'cart';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $cartModel = new Cart();
        $userId = $_SESSION['user_id'];
        
        $cartSummary = $cartModel->getSummary($userId);
        
        include __DIR__ . '/../views/cart/index.php';
    }
    
    public function add() {
        requireCsrfToken();
        header('Content-Type: application/json');
        // Check if logged in
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Please login first']);
            return;
        }
        
        $productId = $_POST['product_id'] ?? null;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            return;
        }
        
        // Verify product exists
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        $cartModel = new Cart();
        $result = $cartModel->add($_SESSION['user_id'], $productId, $quantity);
        
        if ($result) {
            $count = $cartModel->getItemCount($_SESSION['user_id']);
            echo json_encode([
                'success' => true,
                'message' => 'Added to cart',
                'cart_count' => $count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
        }
    }
    
    public function update() {
        requireCsrfToken();
        header('Content-Type: application/json');
        // Check if logged in
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Please login first']);
            return;
        }
        
        $productId = $_POST['product_id'] ?? null;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            return;
        }
        
        $cartModel = new Cart();
        $result = $cartModel->update($_SESSION['user_id'], $productId, $quantity);
        
        if ($result !== false) {
            $count = $cartModel->getItemCount($_SESSION['user_id']);
            $total = $cartModel->getTotal($_SESSION['user_id']);
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated',
                'cart_count' => $count,
                'total' => $total
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
        }
    }
    
    public function remove() {
        requireCsrfToken();
        header('Content-Type: application/json');
        // Check if logged in
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Please login first']);
            return;
        }
        
        $productId = $_POST['product_id'] ?? null;
        
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            return;
        }
        
        $cartModel = new Cart();
        $result = $cartModel->remove($_SESSION['user_id'], $productId);
        
        if ($result) {
            $count = $cartModel->getItemCount($_SESSION['user_id']);
            echo json_encode([
                'success' => true,
                'message' => 'Removed from cart',
                'cart_count' => $count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from cart']);
        }
    }
    
    public function clear() {
        requireCsrfToken();
        header('Content-Type: application/json');
        // Check if logged in
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Please login first']);
            return;
        }
        
        $cartModel = new Cart();
        $result = $cartModel->clear($_SESSION['user_id']);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cart cleared',
                'cart_count' => 0
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
        }
    }
}
