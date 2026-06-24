<?php
// ========================================
// ORDER MODEL
// ========================================

require_once __DIR__ . '/../config/database.php';

class Order {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // READ: Get user orders
    public function getOrders($userId) {
        return $this->db->fetchAll(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }
    
    // READ: Get single order
    public function find($id) {
        return $this->db->fetch(
            "SELECT o.*, u.username, u.email 
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             WHERE o.id = ?",
            [$id]
        );
    }
    
    // READ: Get order items
    public function getItems($orderId) {
        return $this->db->fetchAll(
            "SELECT oi.*, p.name, p.thumbnail 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = ?",
            [$orderId]
        );
    }
    
    // READ: Get seller orders
    public function getSellerOrders($sellerId) {
        return $this->db->fetchAll(
            "SELECT DISTINCT o.*, u.username as customer_name 
             FROM orders o 
             JOIN order_items oi ON o.id = oi.order_id 
             JOIN products p ON oi.product_id = p.id 
             JOIN users u ON o.user_id = u.id 
             WHERE p.seller_id = ? 
             ORDER BY o.created_at DESC",
            [$sellerId]
        );
    }
    
    // CREATE: Create new order
    public function create($data) {
        // Generate order number
        $data['order_number'] = $this->generateOrderNumber();
        
        return $this->db->insert('orders', $data);
    }
    
    // CREATE: Add order items
    public function addItems($orderId, $items) {
        foreach ($items as $item) {
            $this->db->insert('order_items', [
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['price'] * $item['quantity']
            ]);
        }
        return true;
    }
    
    // UPDATE: Update order status
    public function updateStatus($orderId, $status) {
        return $this->db->update(
            'orders',
            ['status' => $status],
            'id = :id',
            ['id' => $orderId]
        );
    }
    
    // READ: Get order stats for seller
    public function getStats($sellerId) {
        $result = $this->db->fetch(
            "SELECT 
                COUNT(DISTINCT o.id) as total_orders,
                SUM(oi.quantity * oi.price) as total_revenue,
                COUNT(DISTINCT CASE WHEN o.status = 'pending' THEN o.id END) as pending_orders,
                COUNT(DISTINCT CASE WHEN o.status = 'shipped' THEN o.id END) as shipped_orders,
                COUNT(DISTINCT CASE WHEN o.status = 'delivered' THEN o.id END) as delivered_orders
             FROM orders o 
             JOIN order_items oi ON o.id = oi.order_id 
             JOIN products p ON oi.product_id = p.id 
             WHERE p.seller_id = ? AND o.status != 'cancelled'",
            [$sellerId]
        );
        return $result;
    }
    
    // READ: Get recent orders for seller
    public function getRecentOrders($sellerId, $limit = 5) {
        return $this->db->fetchAll(
            "SELECT o.*, u.username as customer_name,
             (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) as total
             FROM orders o 
             JOIN order_items oi ON o.id = oi.order_id 
             JOIN products p ON oi.product_id = p.id 
             JOIN users u ON o.user_id = u.id 
             WHERE p.seller_id = ? 
             GROUP BY o.id 
             ORDER BY o.created_at DESC 
             LIMIT ?",
            [$sellerId, $limit]
        );
    }
    
    // Helper: Generate order number
    private function generateOrderNumber() {
        $prefix = 'SW';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        return $prefix . $date . $random;
    }
}