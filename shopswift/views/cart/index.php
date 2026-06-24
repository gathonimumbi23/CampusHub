<?php
$page_title = 'Shopping Cart';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="container" style="padding: var(--space-6) 0;">
    <h1>Shopping Cart</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (empty($cartSummary['items'])): ?>
        <div style="text-align:center;padding:60px 0;">
            <i class="fas fa-shopping-bag" style="font-size:60px;color:var(--text-muted);"></i>
            <h3 style="margin:20px 0;">Your cart is empty</h3>
            <p style="color:var(--text-muted);">Start shopping to add items to your cart.</p>
            <a href="<?php echo BASE_URL; ?>products" class="btn btn-primary" style="margin-top:20px;">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--space-5);">
            <!-- Cart Items -->
            <div>
                <?php foreach ($cartSummary['items'] as $item): ?>
                    <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                        <div style="display:flex;gap:var(--space-4);padding:var(--space-4);border-bottom:1px solid var(--border-color);">
                            <div style="width:100px;height:100px;border-radius:var(--radius-md);overflow:hidden;flex-shrink:0;background:var(--bg-secondary);">
                                <img src="<?php echo htmlspecialchars($item['thumbnail'] ?? 'https://via.placeholder.com/100x100?text=No+Image'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                            <div style="flex:1;">
                                <h4 style="margin-bottom:var(--space-1);">
                                    <a href="<?php echo BASE_URL; ?>product/<?php echo $item['product_id']; ?>" style="color:var(--text-primary);">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>
                                </h4>
                                <div style="color:var(--color-secondary);font-weight:bold;font-size:var(--font-lg);">
                                    KSh <?php echo number_format($item['price'], 2); ?>
                                </div>
                                <div style="display:flex;align-items:center;gap:var(--space-3);margin-top:var(--space-2);">
                                    <button class="btn btn-sm btn-outline" onclick="updateCart(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span id="qty-<?php echo $item['product_id']; ?>"><?php echo $item['quantity']; ?></span>
                                    <button class="btn btn-sm btn-outline" onclick="updateCart(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Cart Summary -->
            <div style="background:var(--bg-card);padding:var(--space-5);border-radius:var(--radius-lg);border:1px solid var(--border-color);position:sticky;top:120px;">
                <h3 style="margin-bottom:var(--space-4);">Order Summary</h3>
                
                <div style="display:flex;justify-content:space-between;padding:var(--space-2) 0;">
                    <span>Subtotal</span>
                    <span>KSh <?php echo number_format($cartSummary['subtotal'], 2); ?></span>
                </div>
                
                <div style="display:flex;justify-content:space-between;padding:var(--space-2) 0;">
                    <span>Shipping</span>
                    <span>
                        <?php if ($cartSummary['free_shipping_eligible']): ?>
                            <span style="color:var(--color-success);">Free</span>
                        <?php else: ?>
                            KSh <?php echo number_format($cartSummary['shipping'], 2); ?>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div style="display:flex;justify-content:space-between;padding:var(--space-2) 0;border-bottom:1px solid var(--border-color);">
                    <span>Tax (<?php echo TAX_RATE * 100; ?>%)</span>
                    <span>KSh <?php echo number_format($cartSummary['tax'], 2); ?></span>
                </div>
                
                <div style="display:flex;justify-content:space-between;padding:var(--space-4) 0;font-size:var(--font-xl);font-weight:var(--weight-bold);">
                    <span>Total</span>
                    <span style="color:var(--color-secondary);">KSh <?php echo number_format($cartSummary['total'], 2); ?></span>
                </div>
                
                <?php if (!$cartSummary['free_shipping_eligible'] && $cartSummary['subtotal'] > 0): ?>
                    <p style="font-size:var(--font-sm);color:var(--text-muted);margin-bottom:var(--space-3);">
                        Add KSh <?php echo number_format(FREE_SHIPPING_THRESHOLD - $cartSummary['subtotal'], 2); ?> more for free shipping!
                    </p>
                <?php endif; ?>
                
                <a href="<?php echo BASE_URL; ?>checkout" class="btn btn-primary" style="width:100%;text-align:center;">
                    Proceed to Checkout
                </a>
                
                <a href="<?php echo BASE_URL; ?>products" style="display:block;text-align:center;margin-top:var(--space-3);color:var(--text-muted);font-size:var(--font-sm);">
                    Continue Shopping
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateCart(productId, quantity) {
    if (quantity < 0) return;
    
    fetch('<?php echo BASE_URL; ?>cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
        },
        body: `product_id=${productId}&quantity=${quantity}`
            + `&csrf_token=${encodeURIComponent(window.ShopSwift.csrfToken)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update quantity display
            document.getElementById('qty-' + productId).textContent = quantity;
            // Reload page to update totals
            window.location.reload();
        } else {
            alert(data.message);
        }
    });
}

function removeFromCart(productId) {
    if (!confirm('Remove this item from cart?')) return;
    
    fetch('<?php echo BASE_URL; ?>cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
        },
        body: `product_id=${productId}`
            + `&csrf_token=${encodeURIComponent(window.ShopSwift.csrfToken)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message);
        }
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
