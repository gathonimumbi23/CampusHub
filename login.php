<?php
/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */
session_start();
require_once 'db.php';

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') header("Location: admin.php");
    elseif ($_SESSION['user_role'] === 'seller') header("Location: seller.php");
    else header("Location: marketplace.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity']);
    $password = $_POST['password'];

    if (empty($identity) || empty($password)) {
        $error = "Please fill in all authentication fields.";
    } else {
        try {
            // Checks database records for either the admission string, email string, or phone number match
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR admission_number = ? OR phone = ?");
            $stmt->execute([$identity, $identity, $identity]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['profile_pic'] = $user['profile_picture_url'] ?? '';

                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } elseif ($user['role'] === 'seller') {
                    header("Location: seller.php");
                } else {
                    header("Location: marketplace.php");
                }
                exit;
            } else {
                $error = "Invalid credentials combined!";
            }
        } catch (PDOException $e) {
            $error = "Server Database Fault: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MKU Student Marketplace</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 min-h-screen flex items-center justify-center p-4 antialiased text-slate-100">

    <div class="w-full max-w-md bg-slate-900/40 backdrop-blur-xl border border-blue-500/20 p-8 rounded-3xl shadow-2xl relative overflow-hidden group">
        <div class="absolute -top-12 -right-12 w-36 h-36 bg-blue-600/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-36 h-36 bg-indigo-600/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="text-center space-y-2 mb-8">
            <div class="inline-flex items-center justify-center px-3 py-1 text-[10px] font-bold tracking-widest text-blue-400 bg-blue-500/10 rounded-full border border-blue-400/20 uppercase font-mono">
                MKU Platform Hub
            </div>
            <h2 class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-blue-400 via-indigo-200 to-white bg-clip-text text-transparent">
                Welcome Back
            </h2>
            <p class="text-xs text-slate-400">Sign in to browse thrift apparel or manage items</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="p-3.5 mb-5 text-xs font-semibold bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-400 font-mono mb-1.5">Admission, Email, or Phone</label>
                <input type="text" name="identity" required placeholder="BIT/123, email, or 0712..." 
                       class="w-full px-4 py-3 text-xs bg-slate-950/80 text-white rounded-xl border border-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
            </div>

            <div>
                <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-400 font-mono mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       class="w-full px-4 py-3 text-xs bg-slate-950/80 text-white rounded-xl border border-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
            </div>

            <button type="submit" class="w-full py-3.5 mt-2 text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 active:scale-[0.99] rounded-xl transition-all cursor-pointer shadow-lg shadow-blue-600/20 tracking-wider uppercase font-mono">
                Sign In to Platform
            </button>
        </form>

        <div class="mt-8 pt-4 border-t border-slate-800/40 text-center">
            <p class="text-xs text-slate-500">
                Don't have an account? <a href="register.php" class="text-blue-400 hover:text-blue-300 font-semibold underline transition-colors">Create one here</a>
            </p>
        </div>
    </div>

</body>
</html>