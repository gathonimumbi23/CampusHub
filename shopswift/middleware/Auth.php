<?php
// ========================================
// AUTHENTICATION MIDDLEWARE
// ========================================

class AuthMiddleware {
    
    public static function check() {
        // Check if user is logged in via session
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
            return true;
        }
        
        // Check remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            $user = $userModel->validateRememberToken($token);
            
            if ($user) {
                // Auto-login user
                regenerateSession();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
                $_SESSION['user_role'] = $user['role'] ?? 'customer';
                
                return true;
            }
        }
        
        // Store the requested URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    
    public static function guest() {
        // If user is logged in, redirect to home
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        return true;
    }
    
    public static function role($role) {
        // Check if user is logged in
        self::check();
        
        // Check user role
        if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL);
            exit;
        }
        return true;
    }
}
