<?php
session_start();

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once 'db.php';

/*
|--------------------------------------------------------------------------
| GET PRODUCT ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: marketplace.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH PRODUCT
|--------------------------------------------------------------------------
*/

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    if (!$item) {
        header("Location: marketplace.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| HANDLE RESERVATION SUBMISSION (MAPPED TO ORDERS)
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $item) {

    // Logged in user
    $user_id = $_SESSION['user_id'] ?? 0;
    
    if (!$user_id) {
        die("Session error: User ID missing. Please log in.");
    }

    try {
        // We use the 'orders' table instead of 'reservations' to match the schema
        $sql = "INSERT INTO orders (product_id, buyer_id, seller_id, status, payment_method, created_at) 
                VALUES (?, ?, ?, 'pending', 'mpesa', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id, 
            $user_id, 
            $item['seller_id']
        ]);

        header("Location: marketplace.php?success=Order+Placed");
        exit;

    } catch (PDOException $e) {
        die("Transaction Failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Confirm Reservation</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="assests/css/style.css">
</head>

<body class="bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 min-h-screen text-slate-100 antialiased font-sans p-6">

<div class="max-w-2xl mx-auto">

    <!-- BACK BUTTON -->

    <a
        href="marketplace.php"
        class="text-blue-400 text-sm mb-6 inline-block hover:text-blue-300 transition"
    >
        ← Back to Marketplace
    </a>

    <!-- MAIN CARD -->

    <div class="glass-card rounded-3xl p-8 text-white shadow-2xl">

        <!-- PRODUCT TITLE -->

        <h1 class="text-3xl font-bold mb-2">

            <?= htmlspecialchars($item['title'] ?? 'Item Not Found') ?>

        </h1>

        <!-- PRODUCT PRICE -->

        <p class="text-blue-400 font-bold text-2xl mb-6">

            KSh <?= htmlspecialchars($item['price'] ?? '0') ?>

        </p>

        <!-- PRODUCT DESCRIPTION -->

        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-6 mb-8">

            <h2 class="font-bold text-lg mb-3">

                Item Description

            </h2>

            <p class="text-slate-400 leading-relaxed text-sm">

                <?= htmlspecialchars($item['description'] ?? 'No description available.') ?>

            </p>

        </div>

        <!-- FORM -->

        <form method="POST">

            <!-- CHECKOUT SECTION -->

            <div class="border-t border-slate-800 pt-8">

                <!-- TITLE -->

                <h3 class="flex items-center text-xl font-bold mb-6">

                    <span class="mr-3 text-emerald-400 text-2xl">

                        🛡️

                    </span>

                    Secure Reservation Checkout

                </h3>

                <!-- TRANSACTION SUMMARY -->

                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 mb-6">

                    <div class="flex justify-between items-center mb-4">

                        <span class="text-slate-400">

                            Item Price

                        </span>

                        <span class="font-bold text-lg text-white">

                            KSh <?= htmlspecialchars($item['price']) ?>

                        </span>

                    </div>

                    <div class="flex justify-between items-center mb-4">

                        <span class="text-slate-400">

                            Reservation Fee

                        </span>

                        <span class="text-emerald-400 font-semibold">

                            FREE

                        </span>

                    </div>

                    <div class="border-t border-slate-800 pt-4 flex justify-between items-center">

                        <span class="font-bold text-white">

                            Total Amount

                        </span>

                        <span class="text-2xl font-extrabold text-blue-400">

                            KSh <?= htmlspecialchars($item['price']) ?>

                        </span>

                    </div>

                </div>

                <!-- PICKUP LOCATION -->

                <div class="mb-6">

                    <label class="block text-xs text-slate-500 uppercase mb-2">

                        Pickup Location

                    </label>

                    <select
                        name="pickup_location"
                        required
                        class="w-full p-4 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none focus:border-blue-500"
                    >

                        <option value="">
                            Select Pickup Point
                        </option>

                        <option value="Thika Main Campus">
                            Thika Main Campus
                        </option>

                        <option value="Nairobi Campus">
                            Nairobi Campus
                        </option>

                        <option value="Mombasa Campus">
                            Mombasa Campus
                        </option>

                    </select>

                </div>

                <!-- PAYMENT METHOD -->

                <div class="mb-6">

                    <label class="block text-xs text-slate-500 uppercase mb-2">

                        Payment Method

                    </label>

                    <div class="bg-slate-950 border border-slate-700 rounded-xl p-4 flex items-center justify-between">

                        <div class="flex items-center gap-3">

                            <span class="text-2xl text-green-400">

                                📱

                            </span>

                            <div>

                                <p class="font-semibold text-white">

                                    M-Pesa Express

                                </p>

                                <p class="text-xs text-slate-500">

                                    STK Push will be sent to your phone

                                </p>

                            </div>

                        </div>

                        <span class="bg-green-500/20 text-green-400 text-xs px-3 py-1 rounded-full">

                            ACTIVE

                        </span>

                    </div>

                </div>

                <!-- SECURITY / TRUST BADGES -->

                <div class="grid grid-cols-2 gap-4 mb-8">

                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 text-center">

                        <p class="text-2xl mb-2">

                            🔒

                        </p>

                        <p class="text-sm text-slate-300">

                            Encrypted Checkout

                        </p>

                    </div>

                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 text-center">

                        <p class="text-2xl mb-2">

                            ✅

                        </p>

                        <p class="text-sm text-slate-300">

                            Verified Student Seller

                        </p>

                    </div>

                </div>

                <!-- CONFIRM BUTTON -->

                <button
                    type="submit"
                    class="w-full py-4 bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-lg transition-all shadow-lg shadow-blue-600/20"
                >

                    CONFIRM RESERVATION

                </button>

                <!-- DISCLAIMER -->

                <p class="text-xs text-slate-500 text-center mt-4 leading-relaxed">

                    By confirming this reservation, you agree to contact
                    the seller and complete collection arrangements
                    through the selected pickup point.

                </p>

            </div>

        </form>

    </div>

</div>

</body>

</html>