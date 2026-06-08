<h1 style="color: red; position: fixed; top: 0; z-index: 9999;">I AM LOADING THIS FILE!</h1>
<?php
$result = $conn->query("SELECT * FROM reservations");
if (!$result) {
    die("Database Error: " . $conn->error);
}
echo "Connection is working! Found " . $result->num_rows . " reservations.";
/**
 * MKU Student Marketplace - My Apparel & Reservations
 * Unified design integrating reservation list and checkout flow.
 */

// Database Configuration
$host = 'localhost';
$db   = 'mku_marketplace';
$user = 'root';
$pass = ''; 

// Database Connection using mysqli
$conn = new mysqli($host, $user, $pass, $db);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch reservations for the logged-in user (simulated user_id for demonstration)
// In a real scenario, this would come from session: $_SESSION['user_id']
$user_id = 1; 
$sql = "SELECT id, user_id, item_name, date, price, status, location, seller_name, seller_phone FROM reservations";
$result = $conn->query($sql);

?>
<?php die("SUCCESS: The server is reading this file!"); ?>
<!DOCTYPE html>
<html lang="en" x-data="{ profileOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Apparel List | MKU Student Hub</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body { font-family: 'Inter', sans-serif; }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(59, 130, 246, 0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(59, 130, 246, 0.4); }

        .glass-navbar {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 min-h-screen text-slate-100 antialiased">

    <!-- Fixed Glassmorphic Navbar -->
    <header class="fixed top-0 left-0 right-0 z-50 glass-navbar px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Brand -->
            <a href="marketplace.php" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20 group-hover:rotate-6 transition-transform">
                    <span class="text-white font-bold text-xl uppercase">M</span>
                </div>
                <span class="text-xl font-black tracking-tight bg-gradient-to-r from-blue-400 to-white bg-clip-text text-transparent">MKU MARKETPLACE</span>
            </a>

            <!-- Right Aligned Nav -->
            <div class="flex items-center space-x-8">
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="marketplace.php" class="text-sm font-semibold text-slate-400 hover:text-blue-400 transition-colors uppercase tracking-widest">Home</a>
                    <a href="my_apparel.php" class="text-sm font-semibold text-blue-400 border-b-2 border-blue-500 pb-1 uppercase tracking-widest">My Apparel List</a>
                </nav>

                <!-- Profile Avatar -->
                <div class="w-10 h-10 rounded-full border-2 border-blue-500/50 p-0.5 bg-slate-800 cursor-pointer overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1633332755192-727a05c4013d?fm=jpg&q=60&w=3000" alt="Avatar" class="w-full h-full object-cover rounded-full">
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-6 pt-32 pb-20">
        <div class="mb-12 space-y-2">
            <h1 class="text-3xl font-extrabold tracking-tight">Checkout & Reservations</h1>
            <p class="text-slate-400 text-sm">Manage your pending campus apparel transactions</p>
        </div>

        <div class="space-y-6">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <!-- Unified Card Component -->
                    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden text-slate-900 transition-all hover:shadow-blue-500/5">
                        <div class="p-8">
                            <!-- Card Header -->
                            <div class="flex justify-between items-start mb-6">
                                <div class="space-y-1">
                                    <h2 class="text-2xl font-bold tracking-tight text-slate-800"><?= htmlspecialchars($row['item_name']) ?></h2>
                                    <div class="flex items-center space-x-3 text-sm text-slate-500 font-medium">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <?= date('F j, Y', strtotime($row['date'])) ?>
                                        </span>
                                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <?= htmlspecialchars($row['location']) ?>
                                        </span>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-orange-100 text-orange-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-orange-200">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </div>

                            <!-- Seller Info -->
                            <div class="bg-slate-50 rounded-2xl p-5 mb-8 border border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">Listed by Student Seller</p>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <p class="text-lg font-bold text-slate-800"><?= htmlspecialchars($row['seller_name']) ?></p>
                                    <p class="text-sm font-semibold text-blue-600 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg>
                                        <?= htmlspecialchars($row['seller_phone']) ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Transaction Form -->
                            <form action="process_transaction.php" method="POST" class="space-y-6">
                                <input type="hidden" name="reservation_id" value="<?= $row['id'] ?>">
                                
                                <div class="space-y-3">
                                    <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Payment Method</label>
                                    <div class="relative">
                                        <select name="payment_method" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-5 py-3.5 text-slate-800 font-bold focus:outline-none focus:border-blue-500 appearance-none transition-all cursor-pointer">
                                            <option value="mpesa">M-Pesa Express</option>
                                            <option value="cash">Cash on Delivery</option>
                                        </select>
                                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none">
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-extrabold py-5 rounded-2xl transition-all shadow-xl shadow-blue-200 uppercase tracking-[0.25em] text-sm active:scale-[0.98]">
                                    Confirm Transaction
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-20 bg-slate-900/50 rounded-3xl border border-blue-500/10 border-dashed">
                    <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 11-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <p class="text-slate-400 font-medium">No reservations found yet.</p>
                    <a href="marketplace.php" class="inline-block mt-4 text-blue-400 font-bold hover:underline">Explore Marketplace</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php $conn->close(); ?>
</body>
</html>
