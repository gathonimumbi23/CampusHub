<?php
$page_title = 'Products';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';

$currentSort = $sort ?? ($_GET['sort'] ?? 'newest');
$currentSearch = $search ?? ($_GET['search'] ?? '');
$currentCategory = $category ?? ($_GET['category'] ?? '');
?>

<div class="shop-page-header-outer">
    <div class="container">
        <div class="shop-page-header">
            <div class="section-header">
                <h2><?php echo $currentCategory ? htmlspecialchars($currentCategory) : 'All Products'; ?></h2>
                <form action="<?php echo BASE_URL; ?>products" method="GET" style="display:flex;gap:var(--space-2);flex-wrap:wrap;">
                    <input class="input" type="text" name="search" placeholder="Search products" value="<?php echo htmlspecialchars($currentSearch); ?>">
                    <?php if ($currentCategory): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($currentCategory); ?>">
                    <?php endif; ?>
                    <select class="input" name="sort" style="width:auto;">
                        <option value="newest" <?php echo $currentSort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="price-low" <?php echo $currentSort === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price-high" <?php echo $currentSort === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="rating" <?php echo $currentSort === 'rating' ? 'selected' : ''; ?>>Rating</option>
                        <option value="best" <?php echo $currentSort === 'best' ? 'selected' : ''; ?>>Best Sellers</option>
                    </select>
                    <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i> Apply</button>
                </form>
            </div>
        </div>
    </div>
</div>

<section class="products-section">
    <div class="container">

        <?php if (!empty($categories)): ?>
            <div class="category-filter-bar">
                <a class="badge<?php echo empty($currentCategory) ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>products">All</a>
                <?php foreach ($categories as $cat): ?>
                    <a class="badge<?php echo strtolower($currentCategory) === strtolower($cat['name']) ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>products/category/<?php echo urlencode($cat['name']); ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div style="text-align:center;padding:60px 0;">
                <i class="fas fa-box-open" style="font-size:60px;color:var(--text-muted);"></i>
                <h3 style="margin:20px 0;">No products found</h3>
                <p>Try a different search or category.</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <?php
                        $image = $product['thumbnail'] ?? $product['image_url'] ?? 'https://via.placeholder.com/300x300?text=No+Image';
                    ?>
                    <div class="product-card" data-product-id="<?php echo (int)$product['id']; ?>">
                        <div class="product-image">
                            <a href="<?php echo BASE_URL; ?>product/<?php echo (int)$product['id']; ?>">
                                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <button class="wishlist-btn" onclick="toggleWishlist(<?php echo (int)$product['id']; ?>)" aria-label="Add to wishlist">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="product-badges">
                                <?php if (!empty($product['is_new'])): ?><span class="badge badge-new">New</span><?php endif; ?>
                                <?php if (!empty($product['is_best_seller'])): ?><span class="badge badge-best-seller">Best</span><?php endif; ?>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3><a href="<?php echo BASE_URL; ?>product/<?php echo (int)$product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <span><?php echo number_format((float)($product['rating'] ?? 0), 1); ?></span>
                                <span class="reviews">(<?php echo (int)($product['reviews_count'] ?? 0); ?>)</span>
                            </div>
                            <div class="product-price">KSh <?php echo number_format((float)$product['price'], 2); ?></div>
                            <button class="btn btn-primary btn-sm btn-cart" onclick="addToCart(<?php echo (int)$product['id']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (($totalPages ?? 1) > 1): ?>
                <div style="display:flex;justify-content:center;gap:var(--space-2);margin-top:var(--space-6);flex-wrap:wrap;">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a class="btn btn-sm <?php echo $i === (int)$page ? 'btn-primary' : 'btn-outline'; ?>" href="<?php echo BASE_URL; ?>products?page=<?php echo $i; ?>&sort=<?php echo urlencode($currentSort); ?><?php echo $currentSearch ? '&search=' . urlencode($currentSearch) : ''; ?><?php echo $currentCategory ? '&category=' . urlencode($currentCategory) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
