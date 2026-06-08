<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = $_POST['product_id'];
    $sid = $_POST['seller_id'];
    $bid = $_SESSION['user_id'];
    $pm = $_POST['payment_method'];
    $st = $_POST['status'] ?? 'pending';

    if($st === 'Paid') $st = 'Awaiting Meetup';

    try {
        $stmt = $pdo->prepare("INSERT INTO orders (product_id, buyer_id, seller_id, status, payment_method) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$pid, $bid, $sid, $st, $pm]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
