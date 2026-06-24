<?php
$page_title = $product['name'] ?? 'Product';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';

$image = $product['thumbnail'] ?? $product['image_url'] ?? 'https://via.placeholder.com/600x600?text=No+Image';
?>

<section class="products-section">
    <div class="container">
        <div style="display:grid;grid-template-columns:minmax(280px,1fr) minmax(280px,1fr);gap:var(--space-6);align-items:start;">
            <div class="product-image" style="border-radius:var(--radius-lg);">
                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <div>
                <p style="color:var(--color-primary);font-weight:var(--weight-semibold);">
                    <?php echo htmlspecialchars($product['category_name'] ?? 'Product'); ?>
                </p>
                <h1 style="font-size:var(--font-5xl);margin:var(--space-2) 0;"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-rating">
                    <i class="fas fa-star"></i>
                    <span><?php echo number_format((float)($product['rating'] ?? 0), 1); ?></span>
                    <span class="reviews">(<?php echo (int)($product['reviews_count'] ?? 0); ?> reviews)</span>
                </div>
                <div class="product-price" style="font-size:var(--font-3xl);">KSh <?php echo number_format((float)$product['price'], 2); ?></div>

                <p style="margin:var(--space-4) 0;">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?>
                </p>

                <?php if (!empty($variants)): ?>
                    <div style="margin-bottom:var(--space-4);">
                        <h4>Options</h4>
                        <div style="display:flex;gap:var(--space-2);flex-wrap:wrap;margin-top:var(--space-2);">
                            <?php foreach ($variants as $variant): ?>
                                <span class="badge"><?php echo htmlspecialchars($variant['name'] ?? $variant['value'] ?? 'Option'); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;margin-top:var(--space-5);">
                    <button class="btn btn-primary btn-cart" onclick="addToCart(<?php echo (int)$product['id']; ?>)">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    <button class="btn btn-outline" onclick="toggleWishlist(<?php echo (int)$product['id']; ?>)">
                        <i class="far fa-heart"></i> Add to Wishlist
                    </button>
                </div>
            </div>
        </div>

        <?php if (!empty($reviews)): ?>
            <section style="margin-top:var(--space-8);">
                <h2>Reviews</h2>
                <div style="display:grid;gap:var(--space-3);margin-top:var(--space-4);">
                    <?php foreach ($reviews as $review): ?>
                        <article style="padding:var(--space-4);border:1px solid var(--border-color);border-radius:var(--radius-md);background:var(--bg-card);">
                            <strong><?php echo htmlspecialchars($review['username'] ?? 'Customer'); ?></strong>
                            <span style="color:var(--color-warning);margin-left:var(--space-2);"><?php echo str_repeat('*', (int)($review['rating'] ?? 0)); ?></span>
                            <p><?php echo htmlspecialchars($review['comment'] ?? ''); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($related)): ?>
            <section style="margin-top:var(--space-8);">
                <div class="section-header"><h2>Related Products</h2></div>
                <div class="products-grid">
                    <?php foreach ($related as $item): if ((int)$item['id'] === (int)$product['id']) continue; ?>
                        <?php $relatedImage = $item['thumbnail'] ?? $item['image_url'] ?? 'https://via.placeholder.com/300x300?text=No+Image'; ?>
                        <div class="product-card" data-product-id="<?php echo (int)$item['id']; ?>">
                            <div class="product-image">
                                <a href="<?php echo BASE_URL; ?>product/<?php echo (int)$item['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($relatedImage); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </a>
                            </div>
                            <div class="product-info">
                                <h3><a href="<?php echo BASE_URL; ?>product/<?php echo (int)$item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                                <div class="product-price">KSh <?php echo number_format((float)$item['price'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
