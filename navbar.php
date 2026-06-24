<?php
// ========================================
// NAVIGATION BAR
// ========================================

$isLoggedIn = isLoggedIn();
$userName = getCurrentUserName();
$userRole = getCurrentUserRole();

// Get cart count
$cartCount = 0;
if ($isLoggedIn) {
    require_once __DIR__ . '/../models/cart.php';
    $cart = new Cart();
    $cartCount = $cart->getItemCount($_SESSION['user_id']);
}
?>

<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="nav-container">
        <a href="<?php echo BASE_URL; ?>" class="nav-logo">
            <i class="fas fa-store"></i>
            <span>ShopSwift</span>
        </a>
        
        <div class="nav-search">
            <form action="<?php echo BASE_URL; ?>products" method="GET">
                <input type="text" name="search" placeholder="Search for products...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="nav-links" id="navLinks">
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'seller'): ?>
                <a href="<?php echo BASE_URL; ?>seller/dashboard" class="nav-link">
                    <i class="fas fa-store"></i> My Shop
                </a>
                <a href="<?php echo BASE_URL; ?>seller/products" class="nav-link">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="<?php echo BASE_URL; ?>seller/products/add" class="nav-link">
                    <i class="fas fa-plus"></i> Add Product
                </a>
                <span class="nav-link" style="color:var(--text-muted);">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>
                </span>
                <a href="<?php echo BASE_URL; ?>logout" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>"><i class="fas fa-home"></i> <span>Home</span></a>
                <a href="<?php echo BASE_URL; ?>wishlist"><i class="fas fa-heart"></i> <span>Wishlist</span></a>
                <a href="<?php echo BASE_URL; ?>cart">
                    <i class="fas fa-shopping-cart"></i> <span>Cart</span>
                    <?php if ($cartCount > 0): ?>
                        <span class="badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
                
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo BASE_URL; ?>profile"><i class="fas fa-user"></i> <span><?php echo htmlspecialchars($userName); ?></span></a>
                    <a href="<?php echo BASE_URL; ?>logout"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login"><i class="fas fa-sign-in-alt"></i> <span>Login</span></a>
                <?php endif; ?>
            <?php endif; ?>
            
            <button id="darkModeToggle" class="dark-mode-toggle" onclick="toggleTheme()">
                <i class="fas fa-moon"></i>
            </button>
        </div>
        
        <!-- Mobile overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
    </div>
</nav>

<!-- Categories Bar -->
<div class="categories-bar">
    <div class="nav-container">
        <ul class="categories-list">
            <li class="dropdown">
                <a href="<?php echo BASE_URL; ?>products/category/women">
                    <i class="fas fa-female"></i> Women <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo BASE_URL; ?>products/category/women?subcategory=dresses">Dresses</a></li>
                    <li><a href="<?php echo BASE_URL; ?>products/category/women?subcategory=tops">Tops</a></li>
                    <li><a href="<?php echo BASE_URL; ?>products/category/women?subcategory=pants">Pants</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="<?php echo BASE_URL; ?>products/category/men">
                    <i class="fas fa-male"></i> Men <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo BASE_URL; ?>products/category/men?subcategory=shirts">Shirts</a></li>
                    <li><a href="<?php echo BASE_URL; ?>products/category/men?subcategory=pants">Pants</a></li>
                    <li><a href="<?php echo BASE_URL; ?>products/category/men?subcategory=jackets">Jackets</a></li>
                </ul>
            </li>
            <li><a href="<?php echo BASE_URL; ?>products?sort=new">New Arrivals</a></li>
            <li><a href="<?php echo BASE_URL; ?>products?sort=best">Best Sellers</a></li>
        </ul>
    </div>
</div>

<style>
.categories-bar {
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
    padding: 0;
    position: sticky;
    top: 60px;
    z-index: 999;
}
.categories-list {
    display: flex;
    gap: 0;
    list-style: none;
    margin: 0;
    padding: 0;
    flex-wrap: wrap;
}
.categories-list > li {
    position: relative;
}
.categories-list > li > a {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 12px 18px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 14px;
}
.categories-list > li > a:hover {
    color: var(--color-secondary);
}
.categories-list .dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 180px;
    background: var(--bg-card);
    border-radius: 8px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-color);
    padding: 8px;
    z-index: 1000;
}
.categories-list .dropdown:hover .dropdown-menu {
    display: block;
}
.categories-list .dropdown-menu a {
    display: block;
    padding: 8px 16px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 14px;
}
.categories-list .dropdown-menu a:hover {
    background: var(--bg-hover);
    color: var(--color-secondary);
}

/* Mobile nav-link wrapping — prevents clipping */
@media (max-width: 768px) {
    .nav-container {
        flex-wrap: wrap;
    }
    .nav-links a span {
        display: inline;
    }
    .nav-links a {
        white-space: nowrap;
    }
}
@media (max-width: 480px) {
    .nav-links a span {
        display: inline;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('mobileMenuToggle');
    var navLinks = document.getElementById('navLinks');
    var overlay = document.getElementById('mobileOverlay');
    
    if (toggle && navLinks && overlay) {
        function openMenu() {
            navLinks.classList.add('open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            navLinks.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (navLinks.classList.contains('open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });
        overlay.addEventListener('click', closeMenu);
    }
});
</script>
