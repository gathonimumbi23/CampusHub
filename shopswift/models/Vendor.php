<?php
require_once __DIR__ . '/../config/database.php';

class Vendor {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUserId($userId) {
        return $this->db->fetch("SELECT * FROM vendors WHERE user_id = :user_id", ['user_id' => $userId]);
    }

    public function create($data) {
        return $this->db->insert('vendors', $data);
    }

    public function updateSetup($userId, $data) {
        $data['is_setup_complete'] = 1;
        return $this->db->update('vendors', $data, 'user_id = :user_id', ['user_id' => $userId]);
    }
}