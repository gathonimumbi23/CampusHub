<?php
// ========================================
// PRODUCT CONTROLLER
// ========================================

require_once __DIR__ . '/../models/product.php';
require_once __DIR__ . '/../models/category.php';

class ProductController {
    
    public function index() {
        $productModel = new Product();
        $categoryModel = new Category();
        
        // Get filter parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        
        // Get products
        if ($search) {
            $products = $productModel->search($search, $limit, $offset);
            $total = $productModel->getTotalCount(null, $search);
        } elseif ($category) {
            $categoryData = $categoryModel->findByName($category);
            if ($categoryData) {
                $products = $productModel->findByCategoryTree($categoryData['id'], $limit, $offset);
                $total = $productModel->getTotalCountForCategoryTree($categoryData['id']);
            } else {
                $products = [];
                $total = 0;
            }
        } else {
            $products = $productModel->findAll($limit, $offset, $sort);
            $total = $productModel->getTotalCount();
        }
        
        // Get categories for filter
        $categories = $categoryModel->findAll();
        
        // Calculate pagination
        $totalPages = ceil($total / $limit);
        
        // Include view
        include __DIR__ . '/../views/products/index.php';
    }
    
    public function show($id) {
        $productModel = new Product();
        $product = $productModel->find($id);
        
        if (!$product) {
            http_response_code(404);
            echo "Product not found";
            return;
        }
        
        // Get variants
        $variants = $productModel->getVariants($id);
        
        // Get reviews
        $reviews = $productModel->getReviews($id);
        
        // Get related products
        $related = $productModel->findByCategory($product['category_id'], 4);
        
        include __DIR__ . '/../views/products/detail.php';
    }

    public function category($category) {
        $_GET['category'] = $category;
        return $this->index();
    }
}
