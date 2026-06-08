<?php
session_start();
require_once 'db.php';

// Security Guard
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$productId = $_GET['id'] ?? 0;
$sellerId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$productId, $sellerId]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error_msg'] = "Item not found or access denied.";
        header("Location: seller.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Garment | MKU Studio</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="assests/css/style.css">
</head>
<body class="bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 min-h-screen text-slate-100 flex items-center justify-center p-6">

    <div class="glass-card max-w-lg w-full p-8 rounded-[2.5rem] shadow-2xl relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-blue-600/10 rounded-full blur-3xl"></div>
        
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">Edit Listing</h2>
            <a href="seller.php" class="text-xs font-bold text-slate-400 hover:text-white transition-colors">Cancel</a>
        </div>

        <form action="update_product.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Garment Name</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($product['title']) ?>"
                       class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-indigo-500/50 outline-none transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Price (KSh)</label>
                    <input type="number" name="price" required value="<?= (int)$product['price'] ?>"
                           class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-indigo-500/50 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Category</label>
                    <select name="category" class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-indigo-500/50 outline-none transition-all appearance-none cursor-pointer">
                        <option value="clothes" <?= $product['category'] === 'clothes' ? 'selected' : '' ?>>Clothes</option>
                        <option value="shoes" <?= $product['category'] === 'shoes' ? 'selected' : '' ?>>Shoes</option>
                        <option value="accessories" <?= $product['category'] === 'accessories' ? 'selected' : '' ?>>Accessories</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-indigo-500/50 outline-none transition-all"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition-all uppercase tracking-widest">
                Save Changes
            </button>
        </form>
    </div>

</body>
</html>
