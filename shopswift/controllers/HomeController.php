<?php
// ========================================
// HOME CONTROLLER
// ========================================

class HomeController {
    
    public function index() {
        // Include the view
        include __DIR__ . '/../views/home.php';
    }
}