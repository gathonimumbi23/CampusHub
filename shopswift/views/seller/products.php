<?php
$page_title = 'My Products';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="shop-page-header-outer">
    <div class="container">
        <div class="shop-page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:var(--space-4);">
            <div>
                <h1 style="margin-bottom:var(--space-2);">My Products</h1>
                <p style="color:rgba(255,255,255,0.85);"><?php echo count($products); ?> product(s) listed</p>
            </div>
            <a href="<?php echo BASE_URL; ?>seller/products/add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>
</div>

<div class="container" style="padding-bottom:var(--space-8);">
    <?php if (isset($_SESSION['success'])): ?>
        <div style="background:#e8f5e9;color:#2e7d32;padding:var(--space-3);border-radius:var(--radius-md);margin-bottom:var(--space-4);">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <div style="text-align:center;padding:60px 0;">
            <i class="fas fa-box-open" style="font-size:60px;color:var(--text-muted);"></i>
            <h3 style="margin:20px 0;">No products yet</h3>
            <p style="color:var(--text-muted);">Add your first product to start selling.</p>
            <a href="<?php echo BASE_URL; ?>seller/products/add" class="btn btn-primary" style="margin-top:20px;">
                <i class="fas fa-plus"></i> Add Your First Product
            </a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php
                        $imgSrc = $product['thumbnail'] ?? '';
                        // If it's a local upload (relative path), prepend BASE_URL
                        if ($imgSrc && strpos($imgSrc, 'uploads/') === 0) {
                            $imgSrc = BASE_URL . $imgSrc;
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             loading="lazy"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="product-image-fallback" style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:var(--bg-secondary);flex-direction:column;gap:8px;min-height:180px;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--text-muted);">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                            <span style="font-size:var(--font-xs);color:var(--text-muted);">No image</span>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-price">KSh <?php echo number_format($product['price'], 2); ?></div>
                        <p style="font-size:var(--font-sm);color:var(--text-muted);margin:4px 0;">
                            Stock: <?php echo $product['stock_quantity']; ?> &bull;
                            Sold: <?php echo $product['total_sold']; ?>
                        </p>
                        <div style="display:flex;gap:var(--space-2);margin-top:var(--space-3);">
                            <a href="<?php echo BASE_URL; ?>seller/products/edit/<?php echo $product['id']; ?>"
                               class="btn btn-secondary btn-sm" style="flex:1;text-align:center;">Edit</a>
                            <form method="POST" action="<?php echo BASE_URL; ?>seller/products/delete/<?php echo $product['id']; ?>"
                                  onsubmit="return confirm('Delete this product?');" style="flex:1;">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-sm"
                                        style="width:100%;background:#fee;color:#c62828;border:1px solid #fca5a5;">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Seller products grid — ensures full-width cards on mobile */
.products-grid {
    display: grid;
    width: 100%;
}
.product-card {
    width: 100%;
    box-sizing: border-box;
}
@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr !important;
        gap: var(--space-3);
    }
}
@media (min-width: 481px) and (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}
@media (min-width: 769px) {
    .products-grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
