<?php
// ========================================
// CATEGORY MODEL
// ========================================

require_once __DIR__ . '/../config/database.php';

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findAll() {
        return $this->db->fetchAll(
            "SELECT * FROM categories WHERE is_active = 1 ORDER BY name"
        );
    }
    
    public function find($id) {
        return $this->db->fetch(
            "SELECT * FROM categories WHERE id = ?",
            [$id]
        );
    }
    
    public function findByName($name) {
        return $this->db->fetch(
            "SELECT * FROM categories WHERE LOWER(name) = LOWER(?)",
            [$name]
        );
    }
    
    public function getSubcategories($parentId) {
        return $this->db->fetchAll(
            "SELECT * FROM categories WHERE parent_id = ? AND is_active = 1",
            [$parentId]
        );
    }
    
    public function create($data) {
        return $this->db->insert('categories', $data);
    }
    
    public function update($id, $data) {
        return $this->db->update(
            'categories',
            $data,
            'id = :id',
            ['id' => $id]
        );
    }
    
    public function delete($id) {
        return $this->db->delete(
            'categories',
            'id = ?',
            [$id]
        );
    }
}
