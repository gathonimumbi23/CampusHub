<?php
// ========================================
// SELLER MIDDLEWARE
// ========================================

class SellerMiddleware {
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /CampusHub/shopswift/login');
            exit;
        }
        
        if ($_SESSION['user_role'] !== 'seller' && $_SESSION['user_role'] !== 'admin') {
            header('Location: /CampusHub/shopswift/');
            exit;
        }
        
        return true;
    }
}