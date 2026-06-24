<?php
// ========================================
// GUEST MIDDLEWARE
// ========================================

class GuestMiddleware {
    public static function check() {
        if (isset($_SESSION['user_id'])) {
            header('Location: /CampusHub/shopswift/');
            exit;
        }
        return true;
    }
}