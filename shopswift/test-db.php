<?php
// Include database connection
include 'C:/xampp/htdocs/shopswift/config/database.php';

echo "<br><br>";
echo "📊 Database Status:<br>";
echo "-------------------<br>";

try {
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Tables found: " . count($tables) . "<br>";
    foreach ($tables as $table) {
        echo "   - $table<br>";
    }
    
    // Check products count
    $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    echo "<br>📦 Products: $count<br>";
    
    // Check users count
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "👤 Users: $count<br>";
    
    // Show sample products
    $products = $pdo->query("SELECT id, name, price, category_id FROM products LIMIT 5")->fetchAll();
    echo "<br>🛍️ Sample Products:<br>";
    foreach ($products as $product) {
        echo "   - " . $product['name'] . " ($" . number_format($product['price'], 2) . ")<br>";
    }
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>