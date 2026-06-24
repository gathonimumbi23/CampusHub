<?php
$page_title = 'Wishlist';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="container wishlist-page">
    <h1>My Wishlist</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div style="text-align:center;padding:60px 0;">
            <i class="fas fa-heart" style="font-size:60px;color:var(--text-muted);"></i>
            <h3 style="margin:20px 0;">Your wishlist is empty</h3>
            <p style="color:var(--text-muted);">Save items you love by tapping the heart icon while you shop.</p>
            <a href="<?php echo BASE_URL; ?>products" class="btn btn-primary" style="margin-top:20px;">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="wishlist-items">
            <?php foreach ($items as $item): ?>
                <div class="product-card" data-product-id="<?php echo $item['id']; ?>">
                    <div class="product-image">
                        <a href="<?php echo BASE_URL; ?>product/<?php echo $item['id']; ?>">
                            <img src="<?php echo htmlspecialchars($item['thumbnail'] ?? 'https://via.placeholder.com/300x300?text=No+Image'); ?>"
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" loading="lazy">
                        </a>
                        <?php if (!empty($item['is_new'])): ?>
                            <div class="product-badges"><span class="badge badge-new">New</span></div>
                        <?php elseif (!empty($item['is_best_seller'])): ?>
                            <div class="product-badges"><span class="badge badge-best-seller">Best Seller</span></div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><a href="<?php echo BASE_URL; ?>product/<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                        <div class="product-price">KSh <?php echo number_format($item['price'], 2); ?></div>
                        <div style="display:flex;gap:var(--space-2);margin-top:var(--space-3);">
                            <button class="btn btn-primary btn-sm" style="flex:1;" onclick="moveWishlistItemToCart(<?php echo $item['id']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Move to Cart
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="removeWishlistItem(<?php echo $item['id']; ?>)" aria-label="Remove from wishlist">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function removeWishlistItem(productId) {
    if (!confirm('Remove this item from your wishlist?')) return;
    fetch('<?php echo BASE_URL; ?>wishlist/remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => { if (data.success) { window.location.reload(); } else { alert(data.message); } });
}

function moveWishlistItemToCart(productId) {
    fetch('<?php echo BASE_URL; ?>wishlist/move-to-cart', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => { if (data.success) { window.location.reload(); } else { alert(data.message); } });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>