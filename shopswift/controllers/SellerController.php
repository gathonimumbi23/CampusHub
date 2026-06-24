<?php
require_once __DIR__ . '/../models/Vendor.php';

class SellerController {

    public function dashboard() {
        require_once __DIR__ . '/../middleware/Seller.php';
        SellerMiddleware::check();

        $vendorModel = new Vendor();
        $vendor = $vendorModel->findByUserId($_SESSION['user_id']);

        if (!$vendor || !$vendor['is_setup_complete']) {
            header('Location: ' . BASE_URL . 'seller/setup');
            exit;
        }

        require_once __DIR__ . '/../models/product.php';
        $productModel = new Product();
        $sellerProducts = $productModel->getSellerProducts($_SESSION['user_id']);
        $productCount = count($sellerProducts);

        include __DIR__ . '/../views/seller/dashboard.php';
    }

    public function setup() {
        require_once __DIR__ . '/../middleware/Seller.php';
        SellerMiddleware::check();

        include __DIR__ . '/../views/seller/setup.php';
    }

    public function storeSetup() {
        require_once __DIR__ . '/../middleware/Seller.php';
        SellerMiddleware::check();
        requireCsrfToken();

        $shopName = trim($_POST['shop_name'] ?? '');
        $shopDescription = trim($_POST['shop_description'] ?? '');
        $mpesaNumber = trim($_POST['mpesa_number'] ?? '');

        $errors = [];
        if (empty($shopName)) {
            $errors[] = 'Shop name is required';
        }
        if (empty($mpesaNumber)) {
            $errors[] = 'M-Pesa number is required';
        } elseif (!preg_match('/^[0-9+\s\-]{7,15}$/', $mpesaNumber)) {
            $errors[] = 'Invalid M-Pesa number format';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: ' . BASE_URL . 'seller/setup');
            exit;
        }

        $vendorModel = new Vendor();
        $existing = $vendorModel->findByUserId($_SESSION['user_id']);

        $data = [
            'shop_name' => $shopName,
            'shop_description' => $shopDescription,
            'mpesa_number' => $mpesaNumber,
        ];

        if ($existing) {
            $vendorModel->updateSetup($_SESSION['user_id'], $data);
        } else {
            $data['user_id'] = $_SESSION['user_id'];
            $data['is_setup_complete'] = 1;
            $vendorModel->create($data);
        }

        $_SESSION['success'] = 'Shop setup complete! Welcome to your dashboard.';
        header('Location: ' . BASE_URL . 'seller/dashboard');
        exit;
    }

    public function products() {
        require_once __DIR__ . '/../middleware/Seller.php';
        SellerMiddleware::check();

        require_once __DIR__ . '/../models/product.php';
        $productModel = new Product();
        $products = $productModel->getSellerProducts($_SESSION['user_id']);

        require_once __DIR__ . '/../models/Vendor.php';
        $vendorModel = new Vendor();
        $vendor = $vendorModel->findByUserId($_SESSION['user_id']);

        include __DIR__ . '/../views/seller/products.php';
    }

    public function addProduct() {
        require_once __DIR__ . '/../middleware/Seller.php';
        SellerMiddleware::check();

        require_once __DIR__ . '/../models/category.php';
        $categoryModel = new Category();
        $categories = $categoryModel->findAll();

        include __DIR__ . '/../views/seller/add-product.php';
    }

    public function storeProduct() {
        require_once __DIR__ . '/../middleware/Seller.php';
        SellerMiddleware::check();
        requireCsrfToken();

        require_once __DIR__ . '/../models/product.php';
        $productModel = new Product();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock_quantity'] ?? 0);
        $categoryId = intval($_POST['category_id'] ?? 0);
        $thumbnail = trim($_POST['thumbnail'] ?? '');

        $errors = [];
        if (empty($name)) $errors[] = 'Product name is required';
        if ($price <= 0) $errors[] = 'Price must be greater than 0';
        if ($stock < 0) $errors[] = 'Stock cannot be negative';
        if (empty($categoryId)) $errors[] = 'Please select a category';

        // Handle file upload
        $uploadedImagePath = '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $_FILES['product_image']['tmp_name']);
            finfo_close($fileInfo);

            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'Invalid image type. Allowed: JPG, PNG, WebP, GIF.';
            } else {
                $uploadDir = __DIR__ . '/../uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('prod_', true) . '.' . $ext;
                $destPath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destPath)) {
                    $uploadedImagePath = 'uploads/products/' . $filename;
                } else {
                    $errors[] = 'Failed to save uploaded image.';
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'seller/products/add');
            exit;
        }

        // Determine final image path: uploaded file > URL > placeholder
        if ($uploadedImagePath) {
            $finalImage = $uploadedImagePath;
        } elseif ($thumbnail) {
            $finalImage = $thumbnail;
        } else {
            $finalImage = 'https://via.placeholder.com/300x300?text=' . urlencode($name);
        }

        $productId = $productModel->create([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock_quantity' => $stock,
            'category_id' => $categoryId,
            'seller_id' => $_SESSION['user_id'],
            'thumbnail' => $finalImage,
            'status' => 'active',
        ]);

        if ($productId) {
            $_SESSION['success'] = 'Product added successfully!';
            header('Location: ' . BASE_URL . 'seller/products');
        } else {
            $_SESSION['error'] = 'Failed to add product. Please try again.';
            header('Location: ' . BASE_URL . 'seller/products/add');
        }
        exit;
    }

    public function deleteProduct() {
        require_once __DIR__ . '/../middleware/Seller.php';
        SellerMiddleware::check();
        requireCsrfToken();

        require_once __DIR__ . '/../models/product.php';
        $productModel = new Product();
        $productId = intval($_POST['product_id'] ?? 0);

        // Safety check — only delete if this product belongs to this seller
        $products = $productModel->getSellerProducts($_SESSION['user_id']);
        $owns = false;
        foreach ($products as $p) {
            if ($p['id'] === $productId) { $owns = true; break; }
        }

        if ($owns) {
            $productModel->delete($productId);
            $_SESSION['success'] = 'Product deleted.';
        } else {
            $_SESSION['error'] = 'Product not found or access denied.';
        }
        header('Location: ' . BASE_URL . 'seller/products');
        exit;
    }
}
