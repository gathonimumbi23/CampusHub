<?php
// ========================================
// CART MODEL - FULL CRUD
// ========================================

require_once __DIR__ . '/../config/database.php';

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // READ: Get cart items with product details
    public function getItems($userId) {
        return $this->db->fetchAll(
            "SELECT c.*, p.name, p.price, p.thumbnail, p.slug,
             (p.price * c.quantity) as subtotal
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = ? 
             ORDER BY c.created_at DESC",
            [$userId]
        );
    }
    
    // READ: Get cart item count
    public function getItemCount($userId) {
        $result = $this->db->fetch(
            "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?",
            [$userId]
        );
        return $result['total'] ?? 0;
    }
    
    // READ: Get cart total
    public function getTotal($userId) {
        $result = $this->db->fetch(
            "SELECT SUM(c.quantity * p.price) as total 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = ?",
            [$userId]
        );
        return $result['total'] ?? 0;
    }
    
    // READ: Check if item exists in cart
    public function exists($userId, $productId) {
        $result = $this->db->fetch(
            "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
        return $result !== false;
    }
    
    // CREATE: Add item to cart
    public function add($userId, $productId, $quantity = 1) {
        // Check if item already exists
        $existing = $this->db->fetch(
            "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
        
        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            return $this->db->update(
                'cart',
                ['quantity' => $newQuantity],
                'id = :id',
                ['id' => $existing['id']]
            );
        } else {
            // Insert new item
            return $this->db->insert('cart', [
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }
    }
    
    // UPDATE: Update cart item quantity
    public function update($userId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->remove($userId, $productId);
        }
        
        return $this->db->update(
            'cart',
            ['quantity' => $quantity],
            'user_id = :user_id AND product_id = :product_id',
            ['user_id' => $userId, 'product_id' => $productId]
        );
    }
    
    // DELETE: Remove item from cart
    public function remove($userId, $productId) {
        return $this->db->delete(
            'cart',
            'user_id = ? AND product_id = ?',
            [$userId, $productId]
        );
    }
    
    // DELETE: Clear entire cart
    public function clear($userId) {
        return $this->db->delete(
            'cart',
            'user_id = ?',
            [$userId]
        );
    }
    
    // GET: Cart summary for checkout
    public function getSummary($userId) {
        $items = $this->getItems($userId);
        $subtotal = $this->getTotal($userId);
        
        $shipping = 0;
        $tax = 0;
        $total = $subtotal;
        
        if ($subtotal > 0) {
            // Calculate shipping
            if ($subtotal < FREE_SHIPPING_THRESHOLD) {
                $shipping = SHIPPING_COST;
            }
            
            // Calculate tax
            $tax = $subtotal * TAX_RATE;
            
            // Calculate total
            $total = $subtotal + $shipping + $tax;
        }
        
        return [
            'items' => $items,
            'item_count' => count($items),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
            'free_shipping_eligible' => $subtotal >= FREE_SHIPPING_THRESHOLD
        ];
    }
}