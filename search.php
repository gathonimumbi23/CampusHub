<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

$q = isset($_GET['q']) ? mysqli_real_escape_string($link, trim($_GET['q'])) : '';

if(strlen($q) < 2){
    echo json_encode([]);
    exit;
}

$sql = "SELECT p.product_id, p.name, p.price, c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.name LIKE '%$q%' OR p.description LIKE '%$q%' OR c.category_name LIKE '%$q%'
        LIMIT 6";

$result = mysqli_query($link, $sql);
$data = [];
while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}
echo json_encode($data);