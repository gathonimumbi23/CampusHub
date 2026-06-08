<?php
/**
 * Marketplace Dashboard (Buyer View) - Enhanced
 * MKU Student Marketplace
 */
session_start();
require_once 'db.php';

// Security Guard: Check if user is logged in
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? 'Scholar';
$profilePic = $_SESSION['profile_pic'] ?? '';

// 10 Fashion Placeholder Images
$heroImages = [
    "https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1539109132374-348214a30f92?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1529139513477-323c66b8aace?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1445205170230-053b830c6050?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1469334031218-e382a71b716b?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1485230895905-ec40ba36b9bc?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1479064566235-aa2782fb4814?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=1200&auto=format&fit=crop"
];

// Fetch live products from database with search filter
try {
    $search = trim($_GET['search'] ?? '');
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_flagged = 0 AND (title LIKE :search OR description LIKE :search) ORDER BY created_at DESC");
        $stmt->execute([':search' => "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM products WHERE is_flagged = 0 ORDER BY created_at DESC");
    }
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en" x-data="{ profileOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace | MKU Student Hub</title>
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
    </style>
</head>
<body class="bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 min-h-screen text-slate-100 antialiased font-sans">

    <!-- Global Glass Header -->
    <header class="sticky top-0 z-50 w-full px-6 py-4 bg-slate-900/40 backdrop-blur-xl border-b border-blue-500/20">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Brand -->
            <a href="marketplace.php" class="flex items-center space-x-2 group">
                <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20 group-hover:scale-110 transition-transform">
                    <span class="text-white font-bold text-xl">M</span>
                </div>
                <span class="text-lg font-extrabold tracking-tight bg-gradient-to-r from-blue-400 to-white bg-clip-text text-transparent">MKU Marketplace</span>
            </a>

            <!-- Search bar -->
            <div class="flex-1 max-w-md mx-8 hidden lg:block">
                <form action="marketplace.php" method="GET" class="relative group">
                    <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="Search campus thrift..." 
                           class="w-full bg-slate-950/50 border border-blue-500/10 rounded-xl px-10 py-2 text-sm focus:outline-none focus:border-blue-500/40 transition-all placeholder-slate-600">
                    <button type="submit" class="absolute left-3 top-2.5 text-slate-600 group-focus-within:text-blue-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>
            </div>

            <!-- Right Nav & Profile -->
            <div class="flex items-center space-x-6">
                <!-- Navigation Tabs (Repositioned to the right) -->
                <nav class="hidden md:flex items-center space-x-1 border-r border-white/10 pr-6 mr-2">
                    <a href="marketplace.php" class="px-4 py-2 text-sm font-medium text-blue-400 bg-blue-500/10 rounded-lg border border-blue-500/20 transition-all">Home</a>
                    <a href="my_apparel.php" class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-all">My Apparel List</a>
                </nav>

                <!-- Profile & Dropdown -->
                <div class="relative" @click.away="profileOpen = false">
                    <button @click="profileOpen = !profileOpen" class="flex items-center space-x-3 p-1 rounded-full hover:bg-white/5 transition-all focus:outline-none">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-bold text-slate-200"><?= htmlspecialchars($userName) ?></p>
                            <p class="text-[10px] text-slate-500 uppercase tracking-tighter">Buyer Account</p>
                        </div>
                        <div class="w-10 h-10 rounded-full border-2 border-blue-500/30 overflow-hidden bg-slate-800">
                            <?php if($profilePic): ?>
                                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Avatar" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-blue-400 font-bold">
                                    <?= substr($userName, 0, 1) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="profileOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-3 w-56 z-[60]">
                        <div class="bg-slate-900/90 backdrop-blur-2xl border border-blue-500/20 rounded-2xl shadow-2xl overflow-hidden p-2">
                            <a href="#" class="flex items-center space-x-3 px-4 py-3 text-sm text-slate-300 hover:text-white hover:bg-blue-600/10 rounded-xl transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>View Profile</span>
                            </a>
                            <a href="#" class="flex items-center space-x-3 px-4 py-3 text-sm text-slate-300 hover:text-white hover:bg-blue-600/10 rounded-xl transition-all">
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
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-10">
        <!-- Hero Slider (Alpine.js) -->
        <section class="mb-12" x-data="{ 
            activeSlide: 0, 
            slides: <?= htmlspecialchars(json_encode($heroImages)) ?>,
            next() { this.activeSlide = (this.activeSlide + 1) % this.slides.length },
            init() { setInterval(() => this.next(), 5000) }
        }">
            <div class="relative overflow-hidden bg-slate-900/40 backdrop-blur-xl border border-blue-500/20 rounded-[2.5rem] shadow-2xl h-[450px]">
                <!-- Slides -->
                <template x-for="(slide, index) in slides" :key="index">
                    <div x-show="activeSlide === index" 
                         x-transition:enter="transition ease-out duration-1000"
                         x-transition:enter-start="opacity-0 scale-105"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-1000"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute inset-0">
                        <img :src="slide" class="w-full h-full object-cover opacity-60">
                    </div>
                </template>

                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/40 to-transparent flex items-center px-12">
                    <div class="relative z-10 max-w-2xl space-y-4">
                        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-400/20">
                            <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse"></span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-blue-400 font-mono">Curated Fashion Feed</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight">
                            Elevate Your <span class="bg-gradient-to-r from-blue-400 to-indigo-200 bg-clip-text text-transparent italic">Campus Style</span>
                        </h1>
                        <p class="text-slate-300 text-lg md:text-xl">
                            The MKU student-exclusive destination for verified premium thrift and custom apparel.
                        </p>
                        <div class="flex flex-wrap gap-4 pt-4">
                            <a href="#explore" class="px-8 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-blue-600/20 uppercase tracking-wider font-mono">Explore Collection</a>
                        </div>
                    </div>
                </div>

                <!-- Slide Indicators -->
                <div class="absolute bottom-8 left-12 flex space-x-2">
                    <template x-for="(slide, index) in slides" :key="index">
                        <button @click="activeSlide = index" 
                                :class="activeSlide === index ? 'w-8 bg-blue-400' : 'w-2 bg-white/20'"
                                class="h-2 rounded-full transition-all duration-300"></button>
                    </template>
                </div>
            </div>
        </section>

        <!-- Product Grid -->
        <section id="explore" class="space-y-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">Current Arrivals</h2>
                    <p class="text-sm text-slate-500">Live inventory from student sellers</p>
                </div>
                <?php if (isset($dbError)): ?>
                    <div class="text-rose-400 text-xs font-mono">DB Error: <?= htmlspecialchars($dbError) ?></div>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Live Products from Database -->
                <?php if (empty($products)): ?>
                    <div class="col-span-full py-20 text-center space-y-4">
                        <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto border border-white/5">
                            <svg class="w-10 h-10 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <p class="text-slate-400 font-medium">No items listed yet. Be the first to sell!</p>
                    </div>
                <?php else: ?>
                    <?php foreach($products as $p): ?>
                    <div class="glass-card rounded-3xl overflow-hidden hover:border-blue-500/40 transition-all group">
                        <!-- Image -->
                        <div class="relative h-48 overflow-hidden bg-slate-800">
                            <img src="<?= htmlspecialchars($p['image_url'] ?: 'assests/images/clothes-A1.jpg') ?>" 
                                 alt="<?= htmlspecialchars($p['title']) ?>" 
                                 onerror="this.src='assests/images/clothes-A1.jpg'"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-4 right-4 bg-slate-900/80 backdrop-blur-md px-3 py-1 rounded-full border border-white/10">
                                <span class="text-xs font-bold text-blue-400">KSh <?= number_format($p['price']) ?></span>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-5 space-y-4">
                            <h3 class="font-bold text-slate-100 truncate"><?= htmlspecialchars($p['title']) ?></h3>
                            <a href="product_view.php?id=<?= $p['id'] ?>" class="block w-full py-2.5 text-center text-xs font-bold bg-blue-600/10 hover:bg-blue-600 text-blue-400 hover:text-white rounded-xl border border-blue-500/20 transition-all uppercase tracking-wider">
                                View Details
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

</body>
</html>
