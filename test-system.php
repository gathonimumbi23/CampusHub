<?php
/**
 * COMPREHENSIVE SYSTEM TEST
 * Tests the full execution path for all known issues
 */

echo "========================================\n";
echo "SHOPSWIFT SYSTEM DIAGNOSTIC\n";
echo "========================================\n\n";

$passCount = 0;
$failCount = 0;

function test($name, $result, $detail = '') {
    global $passCount, $failCount;
    if ($result) {
        echo "  ✓ $name\n";
        $passCount++;
    } else {
        echo "  ✗ $name\n";
        if ($detail) echo "    Reason: $detail\n";
        $failCount++;
    }
}

// =========================================
// TEST 1: File existence checks
// =========================================
echo "--- FILE EXISTENCE ---\n";

$files = [
    'Front Controller' => __DIR__ . '/shopswift/index.php',
    'Constants Config' => __DIR__ . '/shopswift/config/constants.php',
    'Session Config' => __DIR__ . '/shopswift/config/session.php',
    'Database Config' => __DIR__ . '/shopswift/config/database.php',
    'Routes' => __DIR__ . '/shopswift/routes/web.php',
    'Home Controller' => __DIR__ . '/shopswift/controllers/HomeController.php',
    'Home View' => __DIR__ . '/shopswift/views/home.php',
    'Header Include' => __DIR__ . '/shopswift/includes/header.php',
    'Navbar Include' => __DIR__ . '/shopswift/includes/navbar.php',
    'Footer Include' => __DIR__ . '/shopswift/includes/footer.php',
    'Product Model' => __DIR__ . '/shopswift/models/Product.php',
    '.htaccess' => __DIR__ . '/shopswift/.htaccess',
];

foreach ($files as $name => $path) {
    test("$name exists", file_exists($path), "Missing: $path");
}

echo "\n";

// =========================================
// TEST 2: Constants - no duplicate define
// =========================================
echo "--- CONSTANTS LOADING ---\n";

// Simulate index.php load order (constants FIRST now)
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/CampusHub/shopswift/';

$constantsWarning = null;
set_error_handler(function($errno, $errstr) use (&$constantsWarning) {
    if (strpos($errstr, 'already defined') !== false) {
        $constantsWarning = $errstr;
    }
    return true;
});

// Load in index.php order
require_once __DIR__ . '/shopswift/config/constants.php';
require_once __DIR__ . '/shopswift/config/session.php';

// Now constants.php has SESSION_TIMEOUT defined, and session.php checks !defined()
// Both should coexist without warning

restore_error_handler();

test("No duplicate SESSION_TIMEOUT warning", $constantsWarning === null, 
     $constantsWarning ?? '');
test("SESSION_TIMEOUT = " . SESSION_TIMEOUT, SESSION_TIMEOUT === 3600);
test("BASE_URL = " . BASE_URL, defined('BASE_URL'));
test("ASSET_URL = " . ASSET_URL, defined('ASSET_URL'));

echo "\n";

// =========================================
// TEST 3: Database connection
// =========================================
echo "--- DATABASE CONNECTION ---\n";

// This test must be run inside a function to simulate dispatch() scope
function testDatabaseInFunctionScope() {
    // Simulate the require chain: dispatch() → HomeController → Product → database.php
    require_once __DIR__ . '/shopswift/config/database.php';
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $results = [];
    $results['conn_is_object'] = ($conn instanceof PDO);
    $results['conn_info'] = $conn instanceof PDO ? 'PDO' : gettype($conn);
    
    if ($conn instanceof PDO) {
        try {
            $stmt = $conn->prepare("SELECT 1 as test");
            $stmt->execute();
            $row = $stmt->fetch();
            $results['prepare_works'] = ($row['test'] == 1);
        } catch (Exception $e) {
            $results['prepare_works'] = false;
            $results['prepare_error'] = $e->getMessage();
        }
        
        try {
            $stmt = $conn->query("SELECT COUNT(*) as cnt FROM products");
            $row = $stmt->fetch();
            $results['product_count'] = $row['cnt'];
        } catch (Exception $e) {
            $results['product_count_error'] = $e->getMessage();
        }
    }
    
    return $results;
}

$dbResults = testDatabaseInFunctionScope();

test("PDO connection inside function scope", 
     isset($dbResults['conn_is_object']) && $dbResults['conn_is_object'],
     $dbResults['conn_info'] ?? 'NULL');

if (isset($dbResults['prepare_works'])) {
    test("PDO prepare() works", $dbResults['prepare_works'], 
         $dbResults['prepare_error'] ?? '');
}

if (isset($dbResults['product_count'])) {
    test("Products table query works", true);
    echo "    Products in DB: " . $dbResults['product_count'] . "\n";
}

echo "\n";

// =========================================
// TEST 4: Product model queries
// =========================================
echo "--- PRODUCT MODEL QUERIES ---\n";

// Since database.php was already loaded in test 3, require_once won't reload it
// But we need it in this scope, so we need to re-enter a function or ensure scope
function testProductModel() {
    require_once __DIR__ . '/shopswift/models/Product.php';
    
    $product = new Product();
    $errors = [];
    
    try {
        $featured = $product->getFeatured(4);
        $errors['featured_ok'] = true;
        $errors['featured_count'] = count($featured);
    } catch (Exception $e) {
        $errors['featured_ok'] = false;
        $errors['featured_error'] = $e->getMessage();
    }
    
    try {
        $newArrivals = $product->getNewArrivals(4);
        $errors['new_ok'] = true;
        $errors['new_count'] = count($newArrivals);
    } catch (Exception $e) {
        $errors['new_ok'] = false;
        $errors['new_error'] = $e->getMessage();
    }
    
    try {
        $allProducts = $product->findAll(5, 0);
        $errors['all_ok'] = true;
        $errors['all_count'] = count($allProducts);
    } catch (Exception $e) {
        $errors['all_ok'] = false;
        $errors['all_error'] = $e->getMessage();
    }
    
    return $errors;
}

$modelResults = testProductModel();

test("getFeatured(4) works", 
     isset($modelResults['featured_ok']) && $modelResults['featured_ok'],
     $modelResults['featured_error'] ?? '');
if (isset($modelResults['featured_count'])) {
    echo "    Featured products: " . $modelResults['featured_count'] . "\n";
}

test("getNewArrivals(4) works", 
     isset($modelResults['new_ok']) && $modelResults['new_ok'],
     $modelResults['new_error'] ?? '');
if (isset($modelResults['new_count'])) {
    echo "    New arrivals: " . $modelResults['new_count'] . "\n";
}

test("findAll(5,0) works", 
     isset($modelResults['all_ok']) && $modelResults['all_ok'],
     $modelResults['all_error'] ?? '');

echo "\n";

// =========================================
// TEST 5: Check for common routing edge cases
// =========================================
echo "--- ROUTING EDGE CASES ---\n";

$uri = '';
$trimmed = trim($uri, '/');
test("Empty URI trims to empty string", $trimmed === '');
test("Pattern for '/' matches empty", preg_match('#^$#', '') === 1);

// Verify route registration
require_once __DIR__ . '/shopswift/routes/web.php';
test("Router instance created", isset($router) && $router instanceof Router);

// Check the / route exists
$routerRef = new ReflectionClass($router);
$routesProp = $routerRef->getProperty('routes');
$routesProp->setAccessible(true);
$routes = $routesProp->getValue($router);

$homeRouteFound = false;
foreach ($routes as $route) {
    if ($route['path'] === '/' && $route['callback'] === 'Home@index') {
        $homeRouteFound = true;
        break;
    }
}
test("Home route '/' registered", $homeRouteFound);

echo "\n";

// =========================================
// SUMMARY
// =========================================
echo "========================================\n";
echo "DIAGNOSTIC RESULTS\n";
echo "========================================\n";
echo "  Passed: $passCount\n";
echo "  Failed: $failCount\n";

if ($failCount === 0) {
    echo "\n  ✓ SYSTEM APPEARS FUNCTIONAL\n";
} else {
    echo "\n  ✗ $failCount ISSUE(S) REMAIN\n";
}
echo "========================================\n";