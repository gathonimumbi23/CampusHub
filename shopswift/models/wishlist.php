<?php
// ========================================
// WISHLIST MODEL
// ========================================

require_once __DIR__ . '/../config/database.php';

class Wishlist {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // READ: Get wishlist items
    public function getItems($userId) {
        return $this->db->fetchAll(
            "SELECT p.*, w.created_at as added_at
             FROM wishlist w 
             JOIN products p ON w.product_id = p.id 
             WHERE w.user_id = ? AND p.status = 'active'
             ORDER BY w.created_at DESC",
            [$userId]
        );
    }
    
    // READ: Get wishlist count
    public function getCount($userId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?",
            [$userId]
        );
        return $result['total'] ?? 0;
    }
    
    // READ: Check if product is in wishlist
    public function exists($userId, $productId) {
        $result = $this->db->fetch(
            "SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
        return $result !== false;
    }
    
    // CREATE: Add to wishlist
    public function add($userId, $productId) {
        // Check if already exists
        if ($this->exists($userId, $productId)) {
            return false;
        }
        
        return $this->db->insert('wishlist', [
            'user_id' => $userId,
            'product_id' => $productId
        ]);
    }
    
    // DELETE: Remove from wishlist
    public function remove($userId, $productId) {
        return $this->db->delete(
            'wishlist',
            'user_id = ? AND product_id = ?',
            [$userId, $productId]
        );
    }
    
    // DELETE: Clear wishlist
    public function clear($userId) {
        return $this->db->delete(
            'wishlist',
            'user_id = ?',
            [$userId]
        );
    }
}