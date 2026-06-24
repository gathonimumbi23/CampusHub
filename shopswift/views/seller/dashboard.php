<?php
$page_title = 'Seller Dashboard';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="shop-page-header-outer">
    <div class="container">
        <div class="shop-page-header">
            <h1>Welcome back, <?php echo htmlspecialchars($vendor['shop_name']); ?></h1>
            <p style="color:var(--text-light);">Here's an overview of your shop.</p>
        </div>
    </div>
</div>

<div class="container" style="padding-bottom:var(--space-8);">
    <?php if (isset($_SESSION['success_flash'])): ?>
        <div style="background:linear-gradient(135deg,#e8f5e9,#c8e6c9);color:#2e7d32;padding:var(--space-3) var(--space-4);border-radius:var(--radius-md);margin-bottom:var(--space-4);display:flex;align-items:center;gap:var(--space-3);">
            <i class="fas fa-check-circle" style="font-size:20px;"></i>
            <span style="font-weight:500;"><?php echo $_SESSION['success_flash']; unset($_SESSION['success_flash']); ?></span>
        </div>
    <?php endif; ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-4);">
        <div class="auth-card" style="text-align:center;">
            <p style="color:var(--text-secondary);font-size:var(--font-sm);margin-bottom:var(--space-2);">Products Listed</p>
            <p style="font-size:var(--font-2xl);font-weight:600;color:var(--text-primary);"><?php echo $productCount ?? 0; ?></p>
        </div>
        <div class="auth-card" style="text-align:center;">
            <p style="color:var(--text-secondary);font-size:var(--font-sm);margin-bottom:var(--space-2);">Pending Orders</p>
            <p style="font-size:var(--font-2xl);font-weight:600;color:var(--text-primary);">0</p>
        </div>
        <div class="auth-card" style="text-align:center;">
            <p style="color:var(--text-secondary);font-size:var(--font-sm);margin-bottom:var(--space-2);">Total Sales</p>
            <p style="font-size:var(--font-2xl);font-weight:600;color:var(--text-primary);">KSh 0</p>
        </div>
    </div>

    <div style="display:flex;gap:var(--space-3);margin-top:var(--space-6);">
        <a href="<?php echo BASE_URL; ?>seller/products/add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
        <a href="<?php echo BASE_URL; ?>seller/products" class="btn btn-secondary">
            <i class="fas fa-box"></i> View My Products
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>