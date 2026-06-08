<?php
/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $adm_no = trim($_POST['admission_number']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($name) || empty($adm_no) || empty($phone) || empty($password) || empty($role)) {
        $error = "All fields are strictly required!";
    } else {
        // Backend email generation: Strips out slashes to match the frontend perfectly
        $clean_adm = strtolower(str_replace(['/', '-'], '', $adm_no));
        $email = "bit" . $clean_adm . "@mku.ac.ke";
        
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE admission_number = ? OR email = ?");
            $stmt->execute([$adm_no, $email]);
            
            if ($stmt->fetch()) {
                $error = "This Admission Number or computed email is already registered!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, admission_number, email, phone, role, password_hash, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $adm_no, $email, $phone, $role, $password_hash]);
                
                $success = "Account created! Use either your Admission Number or email to log in.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | MKU Student Marketplace</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 min-h-screen flex items-center justify-center p-4 antialiased text-slate-100">

    <div class="w-full max-w-md bg-slate-900/40 backdrop-blur-xl border border-blue-500/20 p-8 rounded-3xl shadow-2xl relative overflow-hidden group">
        <div class="absolute -top-12 -right-12 w-36 h-36 bg-blue-600/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-36 h-36 bg-indigo-600/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="text-center space-y-2 mb-6">
            <div class="inline-flex items-center justify-center px-3 py-1 text-[10px] font-bold tracking-widest text-blue-400 bg-blue-500/10 rounded-full border border-blue-400/20 uppercase font-mono">
                MKU Platform Hub
            </div>
            <h2 class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-blue-400 via-indigo-200 to-white bg-clip-text text-transparent">
                Create Account
            </h2>
            <p class="text-xs text-slate-400">Join the ultimate campus thrift community</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="p-3.5 mb-4 text-xs font-semibold bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="p-3.5 mb-4 text-xs bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl space-y-2">
                <p><?= $success ?></p>
                <p class="pt-2"><a href="login.php" class="font-bold text-white underline hover:text-blue-300">Sign In Now &rarr;</a></p>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-400 font-mono mb-1">Full Name</label>
                <input type="text" name="name" required placeholder="Alex Omwamba" 
                       class="w-full px-4 py-2.5 text-xs bg-slate-950/80 text-white rounded-xl border border-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-400 font-mono mb-1">Admission No</label>
                    <input type="text" id="adm_input" name="admission_number" required placeholder="BIT/2024/1234" 
                           class="w-full px-4 py-2.5 text-xs bg-slate-950/80 text-white rounded-xl border border-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
                </div>
                <div>
                    <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-400 font-mono mb-1">Phone Number</label>
                    <input type="tel" name="phone" required placeholder="0712345678" 
                           class="w-full px-4 py-2.5 text-xs bg-slate-950/80 text-white rounded-xl border border-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
                </div>
            </div>

            <div id="email_preview_box" class="hidden p-2.5 bg-blue-500/5 border border-blue-500/10 rounded-xl transition-all">
                <span class="block text-[9px] font-bold tracking-widest text-blue-400 font-mono uppercase">Generated Campus Email</span>
                <span id="email_preview_text" class="text-xs font-semibold text-slate-300 break-all"></span>
            </div>

            <div>
                <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-400 font-mono mb-1">Account Role</label>
                <select name="role" required class="w-full px-4 py-2.5 text-xs bg-slate-950/80 text-white rounded-xl border border-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all cursor-pointer text-slate-300">
                    <option value="" disabled selected class="bg-slate-900 text-slate-500">Select your marketplace role</option>
                    <option value="buyer" class="bg-slate-900 text-white">Buyer (Browse & Thrift Items)</option>
                    <option value="seller" class="bg-slate-900 text-white">Seller (Host Apparel Listings)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold tracking-wider uppercase text-slate-400 font-mono mb-1">Password</label>
                <input type="password" id="password_input" name="password" required placeholder="••••••••" 
                       class="w-full px-4 py-2.5 text-xs bg-slate-950/80 text-white rounded-xl border border-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
                
                <div class="mt-2 space-y-1">
                    <div class="flex justify-between items-center text-[10px] font-mono">
                        <span class="text-slate-500">Strength:</span>
                        <span id="strength_label" class="font-bold text-slate-400">Empty</span>
                    </div>
                    <div class="w-full h-1 bg-slate-950 rounded-full overflow-hidden">
                        <div id="strength_bar" class="w-0 h-full bg-slate-700 transition-all duration-300"></div>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3 mt-2 text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 active:scale-[0.99] rounded-xl transition-all cursor-pointer shadow-lg shadow-blue-600/20 tracking-wider uppercase font-mono">
                Create Account
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-800/40 text-center">
            <p class="text-xs text-slate-500">
                Already have an account? <a href="login.php" class="text-blue-400 hover:text-blue-300 font-semibold underline transition-colors">Sign In here</a>
            </p>
        </div>
    </div>

    <script>
        // Real-time Slash Stripping Admission-to-Email Script
        const admInput = document.getElementById('adm_input');
        const previewBox = document.getElementById('email_preview_box');
        const previewText = document.getElementById('email_preview_text');

        admInput.addEventListener('input', function() {
            const rawValue = this.value.trim();
            if(rawValue.length > 0) {
                // Strips all slashes and dashes automatically
                const cleanValue = rawValue.replace(/[\/\-]/g, '').toLowerCase();
                previewText.innerHTML = `✨ bit${cleanValue}@mku.ac.ke`;
                previewBox.classList.remove('hidden');
            } else {
                previewBox.classList.add('hidden');
            }
        });

        // Password Strength Script
        const passwordInput = document.getElementById('password_input');
        const strengthBar = document.getElementById('strength_bar');
        const strengthLabel = document.getElementById('strength_label');

        passwordInput.addEventListener('input', function() {
            const val = this.value;
            let score = 0;

            if (val.length === 0) {
                strengthLabel.textContent = "Empty";
                strengthLabel.style.color = "#64748b";
                strengthBar.style.width = "0%";
                return;
            }

            if (val.length >= 6) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            if (score <= 1) {
                strengthLabel.textContent = "Weak ⚠️";
                strengthLabel.style.color = "#f43f5e";
                strengthBar.style.width = "25%";
                strengthBar.style.backgroundColor = "#f43f5e";
            } else if (score === 2 || score === 3) {
                strengthLabel.textContent = "Medium ⚡";
                strengthLabel.style.color = "#fbbf24";
                strengthBar.style.width = "65%";
                strengthBar.style.backgroundColor = "#fbbf24";
            } else {
                strengthLabel.textContent = "Strong ✨";
                strengthLabel.style.color = "#10b981";
                strengthBar.style.width = "100%";
                strengthBar.style.backgroundColor = "#10b981";
            }
        });
    </script>
</body>
</html>