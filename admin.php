<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$s = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$p = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$v = $pdo->query("SELECT SUM(price) FROM products p JOIN orders o ON p.id = o.product_id")->fetchColumn() ?: 0;

$products = $pdo->query("SELECT p.*, u.name as sn FROM products p JOIN users u ON p.seller_id = u.id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head><meta charset="UTF-8"><title>Admin</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-950 text-white p-8">
    <div class="grid grid-cols-3 gap-6 mb-8">
        <div class="bg-zinc-900 p-6 rounded-2xl">Students: <?php echo $s; ?></div>
        <div class="bg-zinc-900 p-6 rounded-2xl">Items: <?php echo $p; ?></div>
        <div class="bg-zinc-900 p-6 rounded-2xl">Sales: KES <?php echo $v; ?></div>
    </div>
    <div class="bg-zinc-900 rounded-2xl overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-black/50"><tr><th class="p-4">Item</th><th class="p-4">Seller</th><th class="p-4 text-right">Action</th></tr></thead>
            <tbody>
                <?php foreach($products as $i): ?>
                <tr class="border-t border-white/5"><td class="p-4"><?php echo $i['title']; ?></td><td class="p-4"><?php echo $i['sn']; ?></td><td class="p-4 text-right"><button class="bg-red-500 text-xs px-3 py-1 rounded">Flag</button></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
