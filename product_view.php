<?php
/**
 * Product Detail View
 * MKU Student Marketplace
 */
session_start();

// Security Guard
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

// 1. PHP block for null check on id and fetch from DB
$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$productId) {
    header("Location: marketplace.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT p.*, u.name as seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: marketplace.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$userName = $_SESSION['user_name'] ?? 'Scholar';
$profilePic = $_SESSION['profile_pic'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" x-data="{ 
    profileOpen: false, 
    modalOpen: false, 
    paymentMethod: 'M-Pesa', 
    processing: false, 
    confirmed: false,
    status: 'Pending'
}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | MKU Hub</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="assests/css/style.css">
    <style>
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #f8fafc;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #02061720;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 min-h-screen text-slate-100 antialiased font-sans">

    <!-- Global Glass Header -->
    <header class="sticky top-0 z-50 w-full px-6 py-4 bg-slate-900/40 backdrop-blur-xl border-b border-blue-500/20">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="marketplace.php" class="flex items-center space-x-2 group">
                <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20 group-hover:scale-110 transition-transform">
                    <span class="text-white font-bold text-xl">M</span>
                </div>
                <span class="text-lg font-extrabold tracking-tight bg-gradient-to-r from-blue-400 to-white bg-clip-text text-transparent">MKU Marketplace</span>
            </a>

            <div class="flex items-center space-x-6">
                <!-- Navigation Tabs -->
                <nav class="hidden md:flex items-center space-x-1 border-r border-white/10 pr-6 mr-2">
                    <a href="marketplace.php" class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-all">Home</a>
                    <a href="my_apparel.php" class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-all">My Apparel List</a>
                </nav>

                <!-- Profile Dropdown -->
                <div class="relative" @click.away="profileOpen = false">
                    <button @click="profileOpen = !profileOpen" class="flex items-center space-x-3 p-1 rounded-full hover:bg-white/5 transition-all focus:outline-none">
                        <div class="w-10 h-10 rounded-full border-2 border-blue-500/30 overflow-hidden bg-slate-800">
                            <?php if($profilePic): ?>
                                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Avatar" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-blue-400 font-bold"><?= substr($userName,0,1) ?></div>
                            <?php endif; ?>
                        </div>
                    </button>
                    <div x-show="profileOpen" x-cloak class="absolute right-0 mt-3 w-56 z-[60] bg-slate-900/90 backdrop-blur-2xl border border-blue-500/20 rounded-2xl shadow-2xl overflow-hidden p-2">
                        <a href="logout.php" class="flex items-center space-x-3 px-4 py-3 text-sm text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-xl transition-all">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Product Image Section -->
            <div class="space-y-6">
                <div class="glass-card rounded-[2.5rem] overflow-hidden shadow-2xl p-4">
                    <img src="<?= htmlspecialchars($product['image_url'] ?: 'assests/images/clothes-A1.jpg') ?>" 
                         alt="<?= htmlspecialchars($product['title']) ?>" 
                         onerror="this.src='assests/images/clothes-A1.jpg'"
                         class="w-full h-full object-cover rounded-[2rem]">
                </div>
            </div>

            <!-- Product Details & Checkout Section -->
            <div class="space-y-8">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-400/20 mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-blue-400 font-mono"><?= htmlspecialchars($product['category']) ?></span>
                        </div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-white mb-2"><?= htmlspecialchars($product['title']) ?></h1>
                        <p class="text-3xl font-extrabold text-blue-400">KSh <?= number_format($product['price'], 2) ?></p>
                    </div>

                    <!-- Dynamic Status Button -->
                    <div class="pt-2">
                        <button @click="status = status === 'Active' ? 'Pending' : (status === 'Pending' ? 'Cancelled' : 'Active')"
                                :class="{
                                    'bg-emerald-500/20 text-emerald-400 border-emerald-500/30': status === 'Active',
                                    'bg-amber-500/20 text-amber-400 border-amber-500/30': status === 'Pending',
                                    'bg-rose-500/20 text-rose-400 border-rose-500/30': status === 'Cancelled'
                                }"
                                class="px-4 py-2 rounded-xl border font-bold text-[10px] uppercase tracking-widest transition-all">
                            <span x-text="status"></span>
                        </button>
                    </div>
                </div>

                <div class="glass-card p-6 rounded-3xl">
                    <h3 class="text-lg font-bold text-slate-200 mb-3">Item Description</h3>
                    <p class="text-slate-400 leading-relaxed"><?= htmlspecialchars($product['description'] ?: 'No description provided.') ?></p>
                    <div class="mt-6 flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center border border-blue-500/20 font-bold text-blue-300">
                            <?= substr($product['seller_name'] ?? 'S', 0, 1) ?>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Listed by Student Seller</p>
                            <p class="text-sm font-bold text-slate-300"><?= htmlspecialchars($product['seller_name'] ?? 'MKU Student') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Secure Checkout Module -->
                <div class="glass-card p-8 rounded-[2rem] shadow-2xl space-y-6">
                    <div class="flex items-center space-x-3 mb-2">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <h3 class="text-xl font-bold">Secure Checkout</h3>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-500 ml-1">Select Payment Method</label>
                        
                        <!-- Interactive Payment Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div @click="paymentMethod = 'M-Pesa'" 
                                 :class="paymentMethod === 'M-Pesa' ? 'border-blue-500/60 bg-blue-500/10' : 'border-white/5 bg-slate-950/40'"
                                 class="p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-blue-500/30 group">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center text-green-400">
                                        <span class="text-xs font-bold">M</span>
                                    </div>
                                    <p class="font-bold text-xs text-slate-200">M-Pesa Express</p>
                                </div>
                            </div>

                            <div @click="paymentMethod = 'Cash on Delivery'" 
                                 :class="paymentMethod === 'Cash on Delivery' ? 'border-blue-500/60 bg-blue-500/10' : 'border-white/5 bg-slate-950/40'"
                                 class="p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-blue-500/30 group">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-400">
                                        <span class="text-xs font-bold">C</span>
                                    </div>
                                    <p class="font-bold text-xs text-slate-200">Cash on Delivery</p>
                                </div>
                            </div>
                        </div>

                        <button @click="modalOpen = true; confirmed = false" class="w-full py-4 mt-2 text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 rounded-xl transition-all shadow-lg shadow-blue-600/20 uppercase tracking-widest font-mono">
                            Confirm Transaction
                        </button>
                    </div>
                    
                    <p class="text-[10px] text-center text-slate-500 italic">Funds are held in escrow until item verification</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Overlay (Alpine.js) -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0 bg-slate-950/60 backdrop-blur-md" @click="modalOpen = false"></div>
        
        <div x-show="modalOpen" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative w-full max-w-md bg-slate-900 border border-blue-500/30 rounded-[2.5rem] shadow-2xl p-8 overflow-hidden text-center">
            
            <div x-show="!confirmed" class="space-y-6">
                <div class="mx-auto w-16 h-16 bg-blue-500/10 rounded-full flex items-center justify-center text-blue-400">
                    <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Transaction Pending</h3>
                    <p class="text-sm text-slate-400">Please confirm the request on your phone via <span x-text="paymentMethod" class="text-blue-400 font-bold"></span>.</p>
                </div>
                <button @click="confirmed = true" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold uppercase transition-all">I have confirmed</button>
            </div>

            <div x-show="confirmed" x-cloak class="space-y-6">
                <div class="mx-auto w-16 h-16 bg-green-500/10 rounded-full flex items-center justify-center text-green-400">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Order Confirmed!</h3>
                    <p class="text-sm text-slate-400">Your order #MKU-<?= rand(1000,9999) ?> is being processed. The seller has been notified.</p>
                </div>
                <button @click="modalOpen = false; window.location.href='marketplace.php'" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold uppercase transition-all">Back to Marketplace</button>
            </div>
        </div>
    </div>

</body>
</html>
