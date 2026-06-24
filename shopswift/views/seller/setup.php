<?php
$page_title = 'Set Up Your Shop';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="auth-page-wrapper">
    <div class="container" style="max-width:480px;">
        <div class="auth-card">
            <div style="text-align:center;"><span class="eyebrow-badge">SELLER SETUP</span></div>
            <h1 style="text-align:center;margin-bottom:var(--space-4);color:var(--text-light);">Set Up Your Shop</h1>
            <p style="text-align:center;color:rgba(255,255,255,0.85);margin-bottom:var(--space-5);">Tell buyers a bit about your shop before you start listing products.</p>

            <?php if (!empty($_SESSION['errors'])): ?>
                <div style="background:#fee;color:#c62828;padding:var(--space-3);border-radius:var(--radius-md);margin-bottom:var(--space-4);">
                    <ul style="margin-left:20px;">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>seller/setup" method="POST">
                <?php echo csrfField(); ?>
                <div style="margin-bottom:var(--space-4);">
                    <label class="label" for="shop_name">Shop Name</label>
                    <input type="text" id="shop_name" name="shop_name" class="input" required placeholder="e.g. Rose's Closet">
                </div>

                <div style="margin-bottom:var(--space-4);">
                    <label class="label" for="shop_description">Shop Description (optional)</label>
                    <textarea id="shop_description" name="shop_description" class="input" rows="3" placeholder="What do you sell?"></textarea>
                </div>

                <div style="margin-bottom:var(--space-5);">
                    <label class="label" for="mpesa_number">M-Pesa Number</label>
                    <input type="tel" id="mpesa_number" name="mpesa_number" class="input" required placeholder="07XXXXXXXX">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Launch My Shop</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>