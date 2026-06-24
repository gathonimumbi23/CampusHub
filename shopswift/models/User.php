<?php
// ========================================
// USER MODEL - FULL AUTHENTICATION
// ========================================

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // ========================================
    // AUTHENTICATION METHODS
    // ========================================
    
    public function find($id) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
    }
    
    public function findByEmail($email) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }
    
    public function findByUsername($username) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE username = ?",
            [$username]
        );
    }
    
    public function findByRememberToken($token) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE remember_token = ?",
            [$token]
        );
    }
    
    // ========================================
    // REGISTER
    // ========================================
    
    public function create($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        
        // Set default role if not set
        $data['role'] = $data['role'] ?? 'customer';
        
        return $this->db->insert('users', $data);
    }
    
    // ========================================
    // LOGIN
    // ========================================
    
    public function login($email, $password, $remember = false) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        if (!$this->verifyPassword($user['password'], $password)) {
            return false;
        }
        
        // Update last login
        $this->updateLastLogin($user['id']);
        
        // Handle remember me
        if ($remember) {
            $this->setRememberToken($user['id']);
        }
        
        return $user;
    }
    
    public function verifyPassword($hashed, $password) {
        return password_verify($password, $hashed);
    }
    
    public function updateLastLogin($userId) {
        return $this->db->update(
            'users',
            ['last_login' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $userId]
        );
    }
    
    // ========================================
    // REMEMBER ME
    // ========================================
    
    public function setRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        
        $this->db->update(
            'users',
            ['remember_token' => $token],
            'id = :id',
            ['id' => $userId]
        );
        
        // Set cookie for 30 days
        setcookie('remember_token', $token, time() + 86400 * 30, '/');
        
        return $token;
    }
    
    public function validateRememberToken($token) {
        return $this->findByRememberToken($token);
    }
    
    public function clearRememberToken($userId) {
        $this->db->update(
            'users',
            ['remember_token' => null],
            'id = :id',
            ['id' => $userId]
        );
        
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // ========================================
    // PROFILE MANAGEMENT
    // ========================================
    
    public function update($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        
        return $this->db->update(
            'users',
            $data,
            'id = :id',
            ['id' => $id]
        );
    }
    
    public function updatePassword($id, $newPassword) {
        return $this->db->update(
            'users',
            ['password' => password_hash($newPassword, PASSWORD_BCRYPT)],
            'id = :id',
            ['id' => $id]
        );
    }
    
    // ========================================
    // ADDRESS MANAGEMENT
    // ========================================
    
    public function getAddresses($userId) {
        return $this->db->fetchAll(
            "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC",
            [$userId]
        );
    }
    
    public function getDefaultAddress($userId) {
        return $this->db->fetch(
            "SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1",
            [$userId]
        );
    }
    
    public function addAddress($userId, $data) {
        $data['user_id'] = $userId;
        return $this->db->insert('user_addresses', $data);
    }
    
    public function setDefaultAddress($userId, $addressId) {
        // Clear any existing default
        $this->db->update(
            'user_addresses',
            ['is_default' => 0],
            'user_id = :user_id',
            ['user_id' => $userId]
        );
        
        // Set new default
        return $this->db->update(
            'user_addresses',
            ['is_default' => 1],
            'id = :id AND user_id = :user_id',
            ['id' => $addressId, 'user_id' => $userId]
        );
    }
    
    public function deleteAddress($userId, $addressId) {
        return $this->db->delete(
            'user_addresses',
            'id = ? AND user_id = ?',
            [$addressId, $userId]
        );
    }
    
    // ========================================
    // ORDERS
    // ========================================
    
    public function getOrders($userId, $limit = 10) {
        return $this->db->fetchAll(
            "SELECT o.*, 
             (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
             (SELECT SUM(quantity * price) FROM order_items WHERE order_id = o.id) as total_amount
             FROM orders o 
             WHERE o.user_id = ? 
             ORDER BY o.created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        );
    }
    
    public function getOrder($userId, $orderId) {
        return $this->db->fetch(
            "SELECT o.*, 
             (SELECT SUM(quantity * price) FROM order_items WHERE order_id = o.id) as total_amount
             FROM orders o 
             WHERE o.user_id = ? AND o.id = ?",
            [$userId, $orderId]
        );
    }
    
    public function getOrderItems($orderId) {
        return $this->db->fetchAll(
            "SELECT oi.*, p.name, p.thumbnail 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = ?",
            [$orderId]
        );
    }
    
    // ========================================
    // LOGOUT
    // ========================================
    
    public function logout($userId = null) {
        if ($userId) {
            $this->clearRememberToken($userId);
        }
        
        session_unset();
        session_destroy();
        return true;
    }
    
    // ========================================
    // EMAIL VERIFICATION
    // ========================================
    
    public function verifyEmail($userId) {
        return $this->db->update(
            'users',
            ['email_verified_at' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $userId]
        );
    }
    
    // ========================================
    // PASSWORD RESET
    // ========================================
    
    public function createPasswordReset($email) {
        $token = bin2hex(random_bytes(32));
        
        // Delete any existing reset tokens for this email
        $this->db->delete(
            'password_resets',
            'email = ?',
            [$email]
        );
        
        return $this->db->insert('password_resets', [
            'email' => $email,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600) // 1 hour
        ]);
    }
    
    public function getPasswordReset($token) {
        return $this->db->fetch(
            "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()",
            [$token]
        );
    }
    
    public function resetPassword($email, $newPassword) {
        $this->updatePasswordByEmail($email, $newPassword);
        
        // Delete reset token
        return $this->db->delete(
            'password_resets',
            'email = ?',
            [$email]
        );
    }
    
    public function updatePasswordByEmail($email, $newPassword) {
        return $this->db->update(
            'users',
            ['password' => password_hash($newPassword, PASSWORD_BCRYPT)],
            'email = :email',
            ['email' => $email]
        );
    }
}