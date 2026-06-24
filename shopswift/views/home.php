<?php
$page_title = 'Home';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="shop-page-header-outer">
    <div class="container">
        <?php if (isset($_SESSION['success_flash'])): ?>
            <div style="background:linear-gradient(135deg,#e8f5e9,#c8e6c9);color:#2e7d32;padding:var(--space-3) var(--space-4);border-radius:var(--radius-md);margin-bottom:var(--space-4);display:flex;align-items:center;gap:var(--space-3);">
                <i class="fas fa-check-circle" style="font-size:20px;"></i>
                <span style="font-weight:500;"><?php echo $_SESSION['success_flash']; unset($_SESSION['success_flash']); ?></span>
            </div>
        <?php endif; ?>
        <div class="shop-page-header" style="text-align:center;">
            <h1 style="font-family: var(--font-family-secondary); font-size: var(--font-5xl);">
                Welcome to ShopSwift
            </h1>
            <p style="font-size: var(--font-xl); color: var(--text-light); margin: var(--space-4) 0;">
                Your premier fashion destination for modern style
            </p>
        </div>
    </div>
</div>

<div style="text-align:center;">
    <div style="display: inline-grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin: var(--space-6) auto; max-width: 800px;">
        <a href="<?php echo BASE_URL; ?>products" class="btn btn-primary" style="padding: var(--space-4);">
            <i class="fas fa-shopping-bag"></i> Shop Now
        </a>
        <a href="<?php echo BASE_URL; ?>products/category/women" class="btn btn-secondary" style="padding: var(--space-4);">
            <i class="fas fa-female"></i> Women's Fashion
        </a>
        <a href="<?php echo BASE_URL; ?>products/category/men" class="btn btn-secondary" style="padding: var(--space-4);">
            <i class="fas fa-male"></i> Men's Fashion
        </a>
    </div>
</div>

<div style="margin-top: var(--space-6); text-align: center;">
    <p style="color: var(--text-muted);">
        <i class="fas fa-check-circle" style="color: var(--color-success);"></i>
        Your ShopSwift application is fully functional!
    </p>
    <p style="color: var(--text-muted); font-size: var(--font-sm); margin-top: var(--space-2);">
        🛒 Add products to your database to start selling
    </p>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
?>