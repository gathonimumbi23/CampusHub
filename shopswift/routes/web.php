<?php
// ========================================
// ROUTE DEFINITIONS
// ========================================

// Include configuration
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';

// ========================================
// ROUTER CLASS
// ========================================

class Router {
    private $routes = [];
    
    public function add($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }
    
    public function get($path, $callback) {
        $this->add('GET', $path, $callback);
    }
    
    public function post($path, $callback) {
        $this->add('POST', $path, $callback);
    }
    
    public function dispatch($method, $uri) {
        // Remove query string
        $uri = strtok($uri, '?');
        $uri = trim($uri, '/');
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            
            // Convert route path to regex pattern
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $route['path']);
            $pattern = '#^' . trim($pattern, '/') . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                
                if (is_callable($route['callback'])) {
                    return call_user_func_array($route['callback'], $matches);
                } else {
                    list($controller, $method) = explode('@', $route['callback']);
                    $controllerClass = "{$controller}Controller";
                    $controllerFile = __DIR__ . "/../controllers/{$controllerClass}.php";
                    
                    if (!file_exists($controllerFile)) {
                        throw new Exception("Controller not found: {$controllerClass}");
                    }
                    
                    require_once $controllerFile;
                    $instance = new $controllerClass();
                    
                    if (!method_exists($instance, $method)) {
                        throw new Exception("Method not found: {$method} in {$controllerClass}");
                    }
                    
                    return call_user_func_array([$instance, $method], $matches);
                }
            }
        }
        
        // No route found - 404
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>The page you're looking for doesn't exist.</p>";
        echo "<p>Requested: " . htmlspecialchars($uri) . "</p>";
        echo "<a href='" . BASE_URL . "'>Go Home</a>";
    }
}

// ========================================
// CREATE ROUTER INSTANCE
// ========================================

$router = new Router();

// ========================================
// ROUTE DEFINITIONS
// ========================================

// HOME ROUTE - This is what you need!
$router->get('/', 'Home@index');
$router->get('index', 'Home@index');

// Product routes
$router->get('products', 'Product@index');
$router->get('product/{id}', 'Product@show');
$router->get('products/category/{category}', 'Product@category');

// Cart routes
$router->get('cart', 'Cart@index');
$router->post('cart/add', 'Cart@add');
$router->post('cart/update', 'Cart@update');
$router->post('cart/remove', 'Cart@remove');
$router->post('cart/clear', 'Cart@clear');

// Wishlist routes
$router->get('wishlist', 'Wishlist@index');
$router->post('wishlist/toggle', 'Wishlist@toggle');
$router->post('wishlist/remove', 'Wishlist@remove');
$router->post('wishlist/move-to-cart', 'Wishlist@moveToCart');

// Auth routes
$router->get('login', 'Auth@login');
$router->get('register', 'Auth@register');
$router->post('login', 'Auth@authenticate');
$router->post('register', 'Auth@store');
$router->get('logout', 'Auth@logout');
$router->get('profile', 'Auth@profile');
$router->post('profile/update', 'Auth@updateProfile');
$router->post('profile/change-password', 'Auth@changePassword');

// Seller routes
$router->get('seller/setup', 'Seller@setup');
$router->post('seller/setup', 'Seller@storeSetup');
$router->get('seller/dashboard', 'Seller@dashboard');
$router->get('seller/products', 'Seller@products');
$router->get('seller/products/add', 'Seller@addProduct');
$router->post('seller/products/add', 'Seller@storeProduct');
$router->get('seller/products/edit/{id}', 'Seller@editProduct');
$router->post('seller/products/edit/{id}', 'Seller@updateProduct');
$router->post('seller/products/delete/{id}', 'Seller@deleteProduct');
