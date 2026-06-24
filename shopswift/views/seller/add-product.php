<?php
$page_title = 'Add Product';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="auth-page-wrapper">
    <div class="container" style="max-width:720px;">
        <div class="auth-card">
            <div style="text-align:center;"><span class="eyebrow-badge">SELLER TOOLS</span></div>
            <h1 style="text-align:center;color:white;margin-bottom:var(--space-5);">Add New Product</h1>

            <?php if (!empty($_SESSION['errors'])): ?>
                <div style="background:rgba(254,202,202,0.9);color:#c62828;padding:var(--space-3);border-radius:var(--radius-md);margin-bottom:var(--space-4);">
                    <ul style="margin-left:20px;">
                        <?php foreach ($_SESSION['errors'] as $e): ?>
                            <li><?php echo $e; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>seller/products/add" method="POST" enctype="multipart/form-data">
                <?php echo csrfField(); ?>

                <div class="seller-form-row">
                    <div class="seller-form-fields">
                        <div style="margin-bottom:var(--space-4);">
                            <label class="label" for="name">Product Name</label>
                            <input type="text" id="name" name="name" class="input" required
                                   value="<?php echo htmlspecialchars($_SESSION['old']['name'] ?? ''); ?>">
                        </div>

                        <div style="margin-bottom:var(--space-4);">
                            <label class="label" for="description">Description</label>
                            <textarea id="description" name="description" class="input" rows="3"
                                      placeholder="Describe your product..."><?php echo htmlspecialchars($_SESSION['old']['description'] ?? ''); ?></textarea>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-3);margin-bottom:var(--space-4);">
                            <div>
                                <label class="label" for="price">Price (KSh)</label>
                                <input type="number" id="price" name="price" class="input" required
                                       min="1" step="0.01"
                                       value="<?php echo htmlspecialchars($_SESSION['old']['price'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="label" for="stock_quantity">Stock Quantity</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" class="input" required
                                       min="0"
                                       value="<?php echo htmlspecialchars($_SESSION['old']['stock_quantity'] ?? '0'); ?>">
                            </div>
                        </div>

                        <div style="margin-bottom:var(--space-4);">
                            <label class="label" for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="input" required>
                                <option value="">-- Select a category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"
                                        <?php echo (($_SESSION['old']['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom:var(--space-4);">
                            <label class="label" for="product_image">Product Image</label>
                            <input type="file" id="product_image" name="product_image" class="input"
                                   accept="image/png,image/jpeg,image/webp,image/gif"
                                   style="padding:8px;line-height:1.5;">
                            <p style="color:rgba(255,255,255,0.75);font-size:var(--font-xs);margin-top:4px;">
                                Upload a product image (PNG, JPG, WebP, or GIF).
                            </p>
                        </div>

                        <div style="margin-bottom:var(--space-5);">
                            <label class="label" for="thumbnail">Or paste an image URL</label>
                            <input type="url" id="thumbnail" name="thumbnail" class="input"
                                   placeholder="https://example.com/image.jpg"
                                   value="<?php echo htmlspecialchars($_SESSION['old']['thumbnail'] ?? ''); ?>">
                            <p style="color:rgba(255,255,255,0.75);font-size:var(--font-xs);margin-top:4px;">
                                Paste a direct image URL. Leave blank for a placeholder.
                            </p>
                        </div>
                    </div>

                    <div class="seller-image-preview-box">
                        <div id="imagePreviewContainer" class="preview-container">
                            <div id="imagePlaceholder" class="preview-placeholder">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);margin-bottom:8px;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                                <span>No image selected</span>
                            </div>
                            <img id="imagePreview" class="preview-image" src="#" alt="Product preview" style="display:none;">
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:var(--space-3);">
                    <a href="<?php echo BASE_URL; ?>seller/products" class="btn btn-secondary" style="flex:1;text-align:center;">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="flex:1;">Add Product</button>
                </div>

                <?php unset($_SESSION['old']); ?>
            </form>
        </div>
    </div>
</div>

<style>
.seller-form-row {
    display: flex;
    gap: var(--space-5);
    align-items: flex-start;
}
.seller-form-fields {
    flex: 1;
    min-width: 0;
}
.seller-image-preview-box {
    flex-shrink: 0;
    width: 220px;
    position: sticky;
    top: 80px;
}
.preview-container {
    width: 100%;
    aspect-ratio: 1 / 1;
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-md);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary);
    transition: border-color 0.2s;
}
.preview-container.has-image {
    border-style: solid;
    border-color: var(--color-primary);
}
.preview-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: var(--font-sm);
    text-align: center;
    padding: 16px;
}
.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Mobile: stack vertically */
@media (max-width: 640px) {
    .seller-form-row {
        flex-direction: column;
    }
    .seller-image-preview-box {
        width: 100%;
        max-width: 280px;
        margin: 0 auto var(--space-4) auto;
        position: static;
    }
    .preview-container {
        aspect-ratio: 1 / 1;
    }
}
</style>

<script>
(function() {
    const fileInput = document.getElementById('product_image');
    const urlInput = document.getElementById('thumbnail');
    const previewImg = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePlaceholder');
    const container = document.getElementById('imagePreviewContainer');

    function showImage(src) {
        previewImg.src = src;
        previewImg.style.display = 'block';
        placeholder.style.display = 'none';
        container.classList.add('has-image');
    }

    function showPlaceholder() {
        previewImg.style.display = 'none';
        placeholder.style.display = 'flex';
        container.classList.remove('has-image');
        previewImg.src = '#';
    }

    // File input change — use FileReader for live preview
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                showImage(ev.target.result);
            };
            reader.readAsDataURL(file);
        } else {
            // If file input cleared, fall back to URL input value
            if (urlInput.value.trim()) {
                showImage(urlInput.value.trim());
            } else {
                showPlaceholder();
            }
        }
    });

    // URL input change — preview the URL
    urlInput.addEventListener('input', function(e) {
        const val = e.target.value.trim();
        if (val) {
            // If a file is selected, file preview takes priority
            if (!fileInput.files.length) {
                showImage(val);
            }
        } else {
            if (!fileInput.files.length) {
                showPlaceholder();
            }
        }
    });

    // On page load, if there's an old URL value, show it
    const oldUrl = urlInput.value.trim();
    if (oldUrl) {
        showImage(oldUrl);
    }
})();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>