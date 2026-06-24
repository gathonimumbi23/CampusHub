<?php
// ========================================
// PRODUCT MODEL - FULL CRUD
// ========================================

require_once __DIR__ . '/../config/database.php';

class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // READ: Get all products with pagination
    public function findAll($limit = 12, $offset = 0, $sort = 'newest') {
        $orderBy = 'p.created_at DESC';
        
        switch ($sort) {
            case 'price-low':
                $orderBy = 'p.price ASC';
                break;
            case 'price-high':
                $orderBy = 'p.price DESC';
                break;
            case 'rating':
                $orderBy = 'p.rating DESC';
                break;
            case 'best':
                $orderBy = 'p.is_best_seller DESC, p.created_at DESC';
                break;
            case 'new':
            default:
                $orderBy = 'p.created_at DESC';
                break;
        }
        
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name, u.username as seller_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             LEFT JOIN users u ON p.seller_id = u.id 
             WHERE p.status = 'active' 
             ORDER BY $orderBy 
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }
    
    // READ: Get single product
    public function find($id) {
        return $this->db->fetch(
            "SELECT p.*, c.name as category_name, u.username as seller_name, u.email as seller_email
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             LEFT JOIN users u ON p.seller_id = u.id 
             WHERE p.id = ? AND p.status = 'active'",
            [$id]
        );
    }
    
    // READ: Get products by category
    public function findByCategory($categoryId, $limit = 12, $offset = 0) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.category_id = ? AND p.status = 'active' 
             ORDER BY p.created_at DESC 
             LIMIT ? OFFSET ?",
            [$categoryId, $limit, $offset]
        );
    }

    public function findByCategoryTree($categoryId, $limit = 12, $offset = 0) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE (p.category_id = ? OR c.parent_id = ?) AND p.status = 'active' 
             ORDER BY p.created_at DESC 
             LIMIT ? OFFSET ?",
            [$categoryId, $categoryId, $limit, $offset]
        );
    }
    
    // READ: Search products
    public function search($query, $limit = 12, $offset = 0) {
        $searchTerm = "%{$query}%";
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE (p.name LIKE ? OR p.description LIKE ?) 
             AND p.status = 'active' 
             ORDER BY p.created_at DESC 
             LIMIT ? OFFSET ?",
            [$searchTerm, $searchTerm, $limit, $offset]
        );
    }
    
    // READ: Get featured products
    public function getFeatured($limit = 6) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.is_best_seller = 1 AND p.status = 'active' 
             ORDER BY p.created_at DESC 
             LIMIT ?",
            [$limit]
        );
    }
    
    // READ: Get new arrivals
    public function getNewArrivals($limit = 6) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.is_new = 1 AND p.status = 'active' 
             ORDER BY p.created_at DESC 
             LIMIT ?",
            [$limit]
        );
    }
    
    // READ: Get seller's products
    public function getSellerProducts($sellerId) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name,
             (SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = p.id) as total_sold
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.seller_id = ? 
             ORDER BY p.created_at DESC",
            [$sellerId]
        );
    }
    
    // READ: Get product variants
    public function getVariants($productId) {
        return $this->db->fetchAll(
            "SELECT * FROM product_variants WHERE product_id = ?",
            [$productId]
        );
    }
    
    // READ: Get product reviews
    public function getReviews($productId, $limit = 10) {
        return $this->db->fetchAll(
            "SELECT r.*, u.username, u.avatar 
             FROM reviews r 
             JOIN users u ON r.user_id = u.id 
             WHERE r.product_id = ? 
             ORDER BY r.created_at DESC 
             LIMIT ?",
            [$productId, $limit]
        );
    }
    
    // CREATE: Add new product
    public function create($data) {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        
        // Set default values
        $data['status'] = $data['status'] ?? 'active';
        $data['is_new'] = $data['is_new'] ?? 0;
        $data['is_best_seller'] = $data['is_best_seller'] ?? 0;
        
        return $this->db->insert('products', $data);
    }
    
    // UPDATE: Update product
    public function update($id, $data) {
        return $this->db->update(
            'products',
            $data,
            'id = :id',
            ['id' => $id]
        );
    }
    
    // DELETE: Delete product
    public function delete($id) {
        return $this->db->delete(
            'products',
            'id = ?',
            [$id]
        );
    }
    
    // UPDATE: Update stock
    public function updateStock($id, $quantity) {
        return $this->db->update(
            'products',
            ['stock_quantity' => $quantity],
            'id = :id',
            ['id' => $id]
        );
    }
    
    // UPDATE: Update rating
    public function updateRating($productId) {
        $result = $this->db->fetch(
            "SELECT AVG(rating) as avg_rating, COUNT(*) as total 
             FROM reviews 
             WHERE product_id = ?",
            [$productId]
        );
        
        return $this->db->update(
            'products',
            [
                'rating' => $result['avg_rating'] ?? 0,
                'reviews_count' => $result['total'] ?? 0
            ],
            'id = :id',
            ['id' => $productId]
        );
    }
    
    // Helper: Generate slug
    private function generateSlug($string) {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return trim($string, '-');
    }
    
    // HELPER: Get total count
    public function getTotalCount($categoryId = null, $search = null) {
        $sql = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
        $params = [];
        
        if ($categoryId) {
            $sql .= " AND category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($search) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    public function getTotalCountForCategoryTree($categoryId, $search = null) {
        $sql = "SELECT COUNT(*) as total
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' AND (p.category_id = ? OR c.parent_id = ?)";
        $params = [$categoryId, $categoryId];

        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }
}
