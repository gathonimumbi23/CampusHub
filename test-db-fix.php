<?php
/**
 * VALIDATION: Database connection through full execution path
 * Tests: Router -> HomeController -> Product -> Database -> query()
 */

echo "========================================\n";
echo "PHASE 4: VALIDATION - Database Fix\n";
echo "========================================\n\n";

// =========================================
// TEST 1: Direct PDO connection in the same scope chain as the app
// =========================================
echo "--- TEST 1: Simulate app execution path (inside function scope) ---\n";

function simulateRouterDispatch() {
    // This simulates what happens when web.php's dispatch() runs
    // and require's HomeController -> Product -> database.php
    
    // Step 1: Load database.php inside this function scope (like dispatch())
    require_once __DIR__ . '/shopswift/config/database.php';
    
    // Step 2: Get Database singleton instance
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "  \$db->getConnection() returned: " . (is_object($conn) ? get_class($conn) : "NULL") . "\n";
    
    if ($conn instanceof PDO) {
        echo "  ✓ \$this->pdo is a valid PDO object\n";
        
        // Test query
        try {
            $stmt = $conn->prepare("SELECT 1 as test");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "  ✓ prepare() executed successfully\n";
            echo "  Test query result: " . $result['test'] . "\n\n";
        } catch (PDOException $e) {
            echo "  ✗ prepare() failed: " . $e->getMessage() . "\n\n";
        }
    } else {
        echo "  ✗ \$this->pdo is NULL - prepare() would crash\n\n";
    }
    
    return $db;
}

$db = simulateRouterDispatch();

// =========================================
// TEST 2: Product model -> getFeatured
// =========================================
echo "--- TEST 2: Product model -> getFeatured(4) ---\n";

require_once __DIR__ . '/shopswift/models/Product.php';

$productModel = new Product();
try {
    $featured = $productModel->getFeatured(4);
    echo "  ✓ getFeatured(4) executed without error\n";
    echo "  Returned " . count($featured) . " products\n";
} catch (Exception $e) {
    echo "  ✗ getFeatured(4) failed: " . $e->getMessage() . "\n";
}

// =========================================
// TEST 3: Product model -> getNewArrivals
// =========================================
echo "\n--- TEST 3: Product model -> getNewArrivals(4) ---\n";

try {
    $newArrivals = $productModel->getNewArrivals(4);
    echo "  ✓ getNewArrivals(4) executed without error\n";
    echo "  Returned " . count($newArrivals) . " products\n";
} catch (Exception $e) {
    echo "  ✗ getNewArrivals(4) failed: " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
echo "ALL TESTS COMPLETE\n";
echo "========================================\n";