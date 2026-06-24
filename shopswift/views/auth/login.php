<?php
$page_title = 'Login';
include __DIR__ . '/../../includes/header.php';
?>

<div class="auth-page-wrapper">
    <div class="container" style="max-width:480px;">
        <div class="auth-card">
        <div style="text-align:center;"><span class="eyebrow-badge">MKU PLATFORM HUB</span></div>
        <h1 style="text-align:center;margin-bottom:var(--space-4);color:var(--text-light);">Welcome Back</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background:#fee;color:#c62828;padding:var(--space-3);border-radius:var(--radius-md);margin-bottom:var(--space-4);">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background:#e8f5e9;color:#2e7d32;padding:var(--space-3);border-radius:var(--radius-md);margin-bottom:var(--space-4);">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>login" method="POST">
            <div style="margin-bottom:var(--space-4);">
                <label class="label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="input" required
                       value="<?php echo htmlspecialchars($_SESSION['old']['email'] ?? ''); ?>">
            </div>

            <div style="margin-bottom:var(--space-4);">
                <label class="label" for="password">Password</label>
                <input type="password" id="password" name="password" class="input" required>
            </div>

            <div style="display:flex;align-items:center;gap:var(--space-2);margin-bottom:var(--space-5);">
                <input type="checkbox" id="remember" name="remember" style="width:auto;">
                <label for="remember" style="margin:0;font-size:var(--font-sm);color:rgba(255,255,255,0.85);">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
        </form>

        <p style="text-align:center;margin-top:var(--space-4);color:rgba(255,255,255,0.85);font-size:var(--font-sm);">
            Don't have an account? <a href="<?php echo BASE_URL; ?>register" style="color:white;font-weight:600;text-decoration:underline;">Register here</a>
        </p>

        <?php unset($_SESSION['old']); ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>