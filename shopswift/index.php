<?php
// ========================================
// FRONT CONTROLLER - ShopSwift
// ========================================

// Include constants FIRST
require_once __DIR__ . '/config/constants.php';

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Include session
require_once __DIR__ . '/config/session.php';

// Include routes
require_once __DIR__ . '/routes/web.php';

// Get request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Remove base path
$basePath = '/CampusHub/shopswift/';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Remove query string
$uri = strtok($uri, '?');

// DEBUG - Uncomment to see what's happening
// echo "<!-- DEBUG: Method: $method, URI: '$uri', Base: '$basePath' -->\n";

// Dispatch
try {
    $router->dispatch($method, $uri);
} catch (Exception $e) {
    error_log("Application error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    echo '<h1>Error</h1>';
    echo '<p>Something went wrong. Please try again later.</p>';
}
