<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/cart.php';
require_once __DIR__ . '/../models/product.php';

function apiInput() {
    $raw = json_decode(file_get_contents('php://input'), true);
    return is_array($raw) ? $raw : $_POST;
}

function apiRequireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit;
    }
}

function apiRequireCsrf($input) {
    $token = $input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!verifyCsrfToken($token)) {
        http_response_code(419);
        echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh and try again.']);
        exit;
    }
}

function mapCartItem($item) {
    $item['id'] = (int)$item['product_id'];
    $item['image_url'] = $item['thumbnail'] ?? $item['image_url'] ?? null;
    return $item;
}

apiRequireLogin();

$cart = new Cart();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $items = array_map('mapCartItem', $cart->getItems($_SESSION['user_id']));
    echo json_encode([
        'success' => true,
        'items' => $items,
        'cart_count' => $cart->getItemCount($_SESSION['user_id']),
        'total' => $cart->getTotal($_SESSION['user_id'])
    ]);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = apiInput();
apiRequireCsrf($input);

$action = $input['action'] ?? 'add';
$productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

$productModel = new Product();
$product = $productModel->find($productId);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

switch ($action) {
    case 'add':
        $result = $cart->add($_SESSION['user_id'], $productId, max(1, $quantity));
        $message = 'Added to cart';
        break;
    case 'update':
        $result = $cart->update($_SESSION['user_id'], $productId, $quantity);
        $message = 'Cart updated';
        break;
    case 'remove':
        $result = $cart->remove($_SESSION['user_id'], $productId);
        $message = 'Item removed';
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown cart action']);
        exit;
}

echo json_encode([
    'success' => $result !== false,
    'message' => $result !== false ? $message : 'Cart update failed',
    'cart_count' => $cart->getItemCount($_SESSION['user_id']),
    'total' => $cart->getTotal($_SESSION['user_id'])
]);
