<?php
// ========================================
// WISHLIST CONTROLLER
// ========================================

require_once __DIR__ . '/../models/wishlist.php';
require_once __DIR__ . '/../models/product.php';
require_once __DIR__ . '/../models/cart.php';

class WishlistController {
    
    public function index() {
        // Check if logged in
        if (!isLoggedIn()) {
            $_SESSION['redirect_after_login'] = BASE_URL . 'wishlist';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $wishlistModel = new Wishlist();
        $userId = $_SESSION['user_id'];
        
        $items = $wishlistModel->getItems($userId);
        $count = $wishlistModel->getCount($userId);
        
        include __DIR__ . '/../views/wishlist/index.php';
    }
    
    public function toggle() {
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
        
        $wishlistModel = new Wishlist();
        $userId = $_SESSION['user_id'];
        
        // Check if already in wishlist
        $exists = $wishlistModel->exists($userId, $productId);
        
        if ($exists) {
            $result = $wishlistModel->remove($userId, $productId);
            $action = 'removed';
            $message = 'Removed from wishlist';
        } else {
            $result = $wishlistModel->add($userId, $productId);
            $action = 'added';
            $message = 'Added to wishlist';
        }
        
        if ($result) {
            $count = $wishlistModel->getCount($userId);
            echo json_encode([
                'success' => true,
                'action' => $action,
                'message' => $message,
                'wishlist_count' => $count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update wishlist']);
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
        
        $wishlistModel = new Wishlist();
        $result = $wishlistModel->remove($_SESSION['user_id'], $productId);
        
        if ($result) {
            $count = $wishlistModel->getCount($_SESSION['user_id']);
            echo json_encode([
                'success' => true,
                'message' => 'Removed from wishlist',
                'wishlist_count' => $count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
        }
    }
    
    public function moveToCart() {
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
        $wishlistModel = new Wishlist();
        $userId = $_SESSION['user_id'];
        
        // Add to cart
        $cartResult = $cartModel->add($userId, $productId, 1);
        
        if ($cartResult) {
            // Remove from wishlist
            $wishlistModel->remove($userId, $productId);
            
            $count = $wishlistModel->getCount($userId);
            echo json_encode([
                'success' => true,
                'message' => 'Moved to cart',
                'wishlist_count' => $count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move to cart']);
        }
    }
}
