<?php
require_once 'db.php';
try {
    $stmt = $pdo->query("SELECT * FROM users LIMIT 3");
    $users = $stmt->fetchAll();
    echo "USER_DATA_START\n";
    print_r($users);
    echo "USER_DATA_END\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
