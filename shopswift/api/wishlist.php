<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/wishlist.php';
require_once __DIR__ . '/../models/product.php';
require_once __DIR__ . '/../models/cart.php';

function wishlistInput() {
    $raw = json_decode(file_get_contents('php://input'), true);
    return is_array($raw) ? $raw : $_POST;
}

function wishlistRequireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit;
    }
}

function wishlistRequireCsrf($input) {
    $token = $input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!verifyCsrfToken($token)) {
        http_response_code(419);
        echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh and try again.']);
        exit;
    }
}

wishlistRequireLogin();

$wishlist = new Wishlist();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $items = array_map(function ($item) {
        $item['image_url'] = $item['thumbnail'] ?? $item['image_url'] ?? null;
        return $item;
    }, $wishlist->getItems($userId));

    echo json_encode([
        'success' => true,
        'items' => $items,
        'wishlist_count' => $wishlist->getCount($userId)
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = wishlistInput();
wishlistRequireCsrf($input);

$productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$action = $input['action'] ?? 'toggle';

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

$productModel = new Product();
if (!$productModel->find($productId)) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

if ($action === 'remove') {
    $result = $wishlist->remove($userId, $productId);
    echo json_encode([
        'success' => $result !== false,
        'action' => 'removed',
        'message' => 'Removed from wishlist',
        'wishlist_count' => $wishlist->getCount($userId)
    ]);
    exit;
}

if ($action === 'move-to-cart') {
    $cart = new Cart();
    $cartResult = $cart->add($userId, $productId, 1);
    if ($cartResult) {
        $wishlist->remove($userId, $productId);
    }

    echo json_encode([
        'success' => (bool)$cartResult,
        'message' => $cartResult ? 'Moved to cart' : 'Failed to move to cart',
        'wishlist_count' => $wishlist->getCount($userId),
        'cart_count' => $cart->getItemCount($userId)
    ]);
    exit;
}

if ($wishlist->exists($userId, $productId)) {
    $result = $wishlist->remove($userId, $productId);
    $action = 'removed';
    $message = 'Removed from wishlist';
} else {
    $result = $wishlist->add($userId, $productId);
    $action = 'added';
    $message = 'Added to wishlist';
}

echo json_encode([
    'success' => $result !== false,
    'action' => $action,
    'message' => $message,
    'wishlist_count' => $wishlist->getCount($userId)
]);
