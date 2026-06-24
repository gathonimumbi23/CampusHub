<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/product.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

$productModel = new Product();
$product = $productModel->find($productId);

if (!$product) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$variants = $productModel->getVariants($productId);
$product['image_url'] = $product['thumbnail'] ?? $product['image_url'] ?? null;
$product['category'] = $product['category_name'] ?? null;
$product['sizes'] = array_values(array_filter(array_map(function ($variant) {
    return $variant['size'] ?? $variant['name'] ?? $variant['value'] ?? null;
}, $variants)));
$product['colors'] = array_values(array_filter(array_map(function ($variant) {
    return $variant['color'] ?? null;
}, $variants)));

echo json_encode([
    'success' => true,
    'product' => $product
]);
