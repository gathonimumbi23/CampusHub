<?php
/**
 * Seller Dashboard (Merchant View)
 * MKU Student Marketplace
 */
session_start();
require_once 'db.php';

$userName = $_SESSION['user_name'] ?? 'Scholar';
$userEmail = $_SESSION['user_email'] ?? '';
$profilePic = $_SESSION['profile_pic'] ?? '';
$sellerId = $_SESSION['user_id'] ?? 0;

// Fetch Live Seller Stats & Products
try {
    // 1. Fetch all products owned by this seller
    $stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
    $stmt->execute([$sellerId]);
    $sellerProducts = $stmt->fetchAll();

    // 2. Fetch Stats
    $totalActive = count($sellerProducts);
    
    // Simulate Total Sales (fetch from orders table if needed)
    $stmtSales = $pdo->prepare("SELECT SUM(p.price) as total_revenue FROM orders o JOIN products p ON o.product_id = p.id WHERE p.seller_id = ? AND o.status = 'completed'");
    $stmtSales->execute([$sellerId]);
    $revenueData = $stmtSales->fetch();
    $totalRevenue = $revenueData['total_revenue'] ?? 0;

} catch (PDOException $e) {
    $sellerProducts = [];
    $totalActive = 0;
    $totalRevenue = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Studio | MKU Student Hub</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="assests/css/style.css">
    <style>
        /* Modern Webkit Autofill Fix */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #f8fafc;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #02061720;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 min-h-screen text-slate-100 antialiased font-sans">

    <!-- Global Glass Header -->
    <header class="sticky top-0 z-50 w-full px-6 py-4 bg-slate-900/40 backdrop-blur-xl border-b border-blue-500/20">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <a href="seller.php" class="flex items-center space-x-2 group">
                    <div class="w-10 h-10 bg-gradient-to-tr from-indigo-600 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/20 group-hover:scale-110 transition-transform">
                        <span class="text-white font-bold text-xl">S</span>
                    </div>
                    <span class="text-lg font-extrabold tracking-tight bg-gradient-to-r from-indigo-400 to-white bg-clip-text text-transparent">Seller Studio</span>
                </a>

                <!-- Navigation Tabs -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="#storefront" class="px-4 py-2 text-sm font-medium text-indigo-400 bg-indigo-500/10 rounded-lg border border-indigo-500/20 transition-all">My Storefront</a>
                    <a href="#add-garment" class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-all">Add New Garment</a>
                </nav>
            </div>

            <!-- Profile & Dropdown -->
            <div class="relative" id="profile-dropdown-container">
                <button id="profile-btn" class="flex items-center space-x-3 p-1 rounded-full hover:bg-white/5 transition-all focus:outline-none">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold text-slate-200"><?= htmlspecialchars($userName) ?></p>
                        <p class="text-[10px] text-slate-500">Merchant Account</p>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-indigo-500/30 overflow-hidden bg-slate-800">
                        <?php if($profilePic): ?>
                            <img src="<?= htmlspecialchars($profilePic) ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-indigo-400 font-bold">
                                <?= substr($userName, 0, 1) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </button>

                <!-- Dropdown Menu -->
                <div id="profile-dropdown" class="absolute right-0 mt-3 w-56 opacity-0 scale-95 pointer-events-none transition-all duration-200 origin-top-right">
                    <div class="bg-slate-900/90 backdrop-blur-2xl border border-indigo-500/20 rounded-2xl shadow-2xl overflow-hidden p-2">
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-sm text-slate-300 hover:text-white hover:bg-indigo-600/10 rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>View Profile</span>
                        </a>
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-sm text-slate-300 hover:text-white hover:bg-indigo-600/10 rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Edit Profile</span>
                        </a>
                        <div class="my-1 border-t border-slate-800/60"></div>
                        <a href="logout.php" class="flex items-center space-x-3 px-4 py-3 text-sm text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-10">
        <!-- Seller Performance Overview -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="glass-card p-6 rounded-3xl shadow-xl">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Total Sales</p>
                <h3 class="text-3xl font-extrabold text-white">KSh <?= number_format($totalRevenue) ?></h3>
                <div class="mt-4 flex items-center space-x-2 text-[10px] text-emerald-400 font-bold bg-emerald-500/10 px-2 py-1 rounded-lg w-fit">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path></svg>
                    <span>+12% from last month</span>
                </div>
            </div>
            <div class="glass-card p-6 rounded-3xl shadow-xl">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Active Items</p>
                <h3 class="text-3xl font-extrabold text-white"><?= $totalActive ?></h3>
                <p class="mt-4 text-xs text-slate-400">Items currently live in marketplace</p>
            </div>
            <div class="glass-card p-6 rounded-3xl shadow-xl">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Customer Rating</p>
                <h3 class="text-3xl font-extrabold text-white">4.9<span class="text-sm text-slate-500">/5</span></h3>
                <div class="mt-4 flex items-center space-x-1">
                    <div class="flex text-amber-400">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Dashboard Actions -->
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Side: Active Listings -->
            <div id="storefront" class="flex-1 space-y-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">Current Storefront</h2>
                    <button class="px-4 py-1.5 text-[10px] font-bold text-slate-400 hover:text-white bg-white/5 hover:bg-indigo-600/20 rounded-lg border border-white/10 hover:border-indigo-500/40 transition-all uppercase tracking-widest">Manage All</button>
                </div>

                <!-- Flash Notifications -->
                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center space-x-3 text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-xs font-bold"><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl flex items-center space-x-3 text-rose-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-xs font-bold"><?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="space-y-4">
                    <?php if (empty($sellerProducts)): ?>
                        <div class="p-8 text-center glass-card rounded-2xl">
                            <p class="text-slate-500 text-sm italic">You haven't listed any items yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sellerProducts as $product): ?>
                            <div class="group flex items-center justify-between p-4 bg-slate-900/40 backdrop-blur-xl border border-white/5 rounded-2xl hover:border-indigo-500/40 transition-all">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 rounded-xl bg-slate-800 overflow-hidden">
                                        <img src="<?= htmlspecialchars($product['image_url'] ?: 'assests/images/clothes-A1.jpg') ?>" 
                                             onerror="this.src='assests/images/clothes-A1.jpg'"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-200"><?= htmlspecialchars($product['title']) ?></h4>
                                        <p class="text-xs text-slate-500">Listed on <?= date('M d, Y', strtotime($product['created_at'])) ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="text-right mr-4">
                                        <p class="font-bold text-indigo-400">KSh <?= number_format($product['price']) ?></p>
                                        <span class="text-[10px] px-2 py-0.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full">Active</span>
                                    </div>
                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-2">
                                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="p-2.5 bg-indigo-500/10 hover:bg-indigo-600 text-indigo-400 hover:text-white rounded-xl border border-indigo-500/20 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                        <form action="delete_product.php" method="POST" onsubmit="return confirm('Are you sure you want to remove this garment?');" class="inline">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <button type="submit" class="p-2.5 bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white rounded-xl border border-rose-500/20 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Side: Quick Add Form -->
            <div id="add-garment" class="w-full lg:w-96">
                <div class="sticky top-28 glass-card p-8 rounded-[2rem] shadow-2xl overflow-hidden relative z-10">
                    <div class="absolute -top-12 -right-12 w-36 h-36 bg-indigo-600/20 rounded-full blur-2xl pointer-events-none"></div>
                    
                    <h3 class="text-xl font-bold mb-6 flex items-center space-x-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add New Garment</span>
                    </h3>

                    <form action="add_product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <?php if (isset($_SESSION['success_msg'])): ?>
                            <div class="p-3 mb-4 text-xs bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl">
                                <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error_msg'])): ?>
                            <div class="p-3 mb-4 text-xs bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl">
                                <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                            </div>
                        <?php endif; ?>

                        <div>
                            <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-500 mb-1.5 ml-1">Garment Name</label>
                            <input type="text" name="garment_name" required placeholder="e.g. Vintage Polo Shirt" class="w-full px-4 py-3 text-xs bg-slate-950/80 text-white rounded-xl border border-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-500 mb-1.5 ml-1">Price (KSh)</label>
                                <input type="number" name="price" required placeholder="500" class="w-full px-4 py-3 text-xs bg-slate-950/80 text-white rounded-xl border border-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-500 mb-1.5 ml-1">Size</label>
                                <select name="size" required class="w-full px-4 py-3 text-xs bg-slate-950/80 text-white rounded-xl border border-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                                    <option value="" disabled selected>Select Size</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-500 mb-1.5 ml-1">Garment Image</label>
                            <input type="file" name="garment_image" id="garment_image" class="hidden" accept="image/*">
                            <div onclick="document.getElementById('garment_image').click()" 
                                 class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-800 border-dashed rounded-xl hover:border-indigo-500/50 transition-all cursor-pointer bg-slate-950/40 group/upload">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-8 w-8 text-slate-500 group-hover/upload:text-indigo-400 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    <p class="text-xs text-slate-500 group-hover/upload:text-slate-300">Click to Upload Photo</p>
                                    <p id="file-name" class="text-[10px] text-indigo-400 mt-2 italic"></p>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="w-full py-4 mt-2 text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 rounded-xl transition-all shadow-lg shadow-indigo-600/20 uppercase tracking-widest font-mono">List My Item</button>

                        <script>
                            document.getElementById('garment_image').addEventListener('change', function(e) {
                                const fileName = e.target.files[0] ? e.target.files[0].name : '';
                                document.getElementById('file-name').textContent = fileName ? 'Selected: ' + fileName : '';
                            });
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Interactive Profile Dropdown Logic
        const profileBtn = document.getElementById('profile-btn');
        const profileDropdown = document.getElementById('profile-dropdown');
        const container = document.getElementById('profile-dropdown-container');

        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = profileDropdown.classList.contains('opacity-100');
            
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        function openDropdown() {
            profileDropdown.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
            profileDropdown.classList.add('opacity-100', 'scale-100');
        }

        function closeDropdown() {
            profileDropdown.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            profileDropdown.classList.remove('opacity-100', 'scale-100');
        }

        // Click-away listener
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });
    </script>
</body>
</html>
