<?php require_once 'includes/config.php'; ?>
<?php
$total_products   = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM products"))['c'];
$total_vendors    = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM vendors"))['c'];
$total_categories = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM categories"))['c'];
$total_users      = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM users"))['c'];

$icons = [
    'Beadwork' => 'bi-gem',
    'Tailoring and Clothes' => 'bi-scissors',
    'Art and Paintings' => 'bi-palette',
    'Cakes and Baking' => 'bi-cake2',
    'Food Delivery' => 'bi-bag-heart',
    'Phone Accessories' => 'bi-phone',
    'Printed T-Shirts' => 'bi-badge-cc',
    'Hair Braiding' => 'bi-person-hearts',
    'Makeup Services' => 'bi-stars',
    'Photography' => 'bi-camera',
    'Graphic Design' => 'bi-vector-pen',
    'Tutoring' => 'bi-book',
    'Typing Services' => 'bi-keyboard',
    'Nail Services' => 'bi-heart'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusHub — Student Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>

<!-- Welcome Banner -->
<?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
<div class="welcome-banner">
    🎉 Welcome back, <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>! Happy shopping! 🛒
</div>
<?php endif; ?>

<!-- ===== TOP NAVBAR ===== -->
<nav class="top-navbar">
    <a href="index.php" class="nav-brand">Campus<span>Hub</span></a>

    <!-- Live Search -->
    <div class="nav-search">
        <i class="bi bi-search"></i>
        <input type="text" id="nav-search" placeholder="Search for textbooks, art, or baked...">
        <div class="search-dropdown" id="search-dropdown"></div>
    </div>

    <!-- Nav Actions -->
    <div class="nav-actions">
        <a href="cart.php" class="nav-icon-btn" title="Cart">
            <i class="bi bi-cart3"></i>
            <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="nav-badge"><?php echo count($_SESSION['cart']); ?></span>
            <?php endif; ?>
        </a>

        <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <?php if($_SESSION['role'] === 'Admin'): ?>
            <a href="dashboard.php" class="nav-icon-btn" title="Dashboard">
                <i class="bi bi-speedometer2"></i>
            </a>
            <?php endif; ?>
            <a href="logout.php" class="nav-avatar" title="Logout">
                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
            </a>
        <?php else: ?>
            <a href="login.php" class="nav-icon-btn" title="Login">
                <i class="bi bi-person"></i>
            </a>
            <a href="register.php" class="btn-hero-main" style="font-size:13px;padding:8px 18px;">Join Free</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Stats Bar -->
<div class="stats-bar" id="stats-data"
     data-products="<?php echo $total_products; ?>"
     data-vendors="<?php echo $total_vendors; ?>"
     data-categories="<?php echo $total_categories; ?>"
     data-users="<?php echo $total_users; ?>">
    <div class="stat-item">
        <span class="stat-number" id="stat-products">0</span>
        <span class="stat-label">Products & Services</span>
    </div>
    <div class="stat-item">
        <span class="stat-number" id="stat-vendors">0</span>
        <span class="stat-label">Student Vendors</span>
    </div>
    <div class="stat-item">
        <span class="stat-number" id="stat-categories">0</span>
        <span class="stat-label">Categories</span>
    </div>
    <div class="stat-item">
        <span class="stat-number" id="stat-users">0</span>
        <span class="stat-label">Students Joined</span>
    </div>
</div>

<!-- Hero Banner -->
<div style="padding: 20px 24px 0;">
    <div class="hero-banner">
        <div>
            <span class="hero-badge">🎓 Campus Community #1</span>
            <h1>Support Student<br>Businesses</h1>
            <p>The ultimate marketplace for university students to buy, sell, and showcase their unique creations and services.</p>
            <div class="hero-btns">
                <a href="marketplace.php" class="btn-hero-main">Shop Now</a>
                <?php if(!isset($_SESSION['loggedin'])): ?>
                <a href="register.php" class="btn-hero-outline">List an Item</a>
                <?php else: ?>
                <a href="<?php echo $_SESSION['role']==='Vendor'||$_SESSION['role']==='Admin' ? 'dashboard.php' : 'register.php'; ?>" class="btn-hero-outline">List an Item</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-illustration">🛍️</div>
    </div>
</div>

<!-- ===== PAGE LAYOUT ===== -->
<div class="page-layout">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">Categories</div>
            <?php
            $cats = mysqli_query($link, "SELECT * FROM categories ORDER BY category_name");
            while($cat = mysqli_fetch_assoc($cats)):
                $icon = isset($icons[$cat['category_name']]) ? $icons[$cat['category_name']] : 'bi-grid';
                $href = isset($_SESSION['loggedin']) ? 'marketplace.php?category='.$cat['category_id'] : 'login.php';
            ?>
            <a href="<?php echo $href; ?>" class="sidebar-cat">
                <i class="bi <?php echo $icon; ?>"></i>
                <?php echo htmlspecialchars($cat['category_name']); ?>
            </a>
            <?php endwhile; ?>

            <a href="<?php echo isset($_SESSION['loggedin']) && ($_SESSION['role']==='Vendor'||$_SESSION['role']==='Admin') ? 'dashboard.php' : 'register.php'; ?>" class="btn-sell">
                <i class="bi bi-plus-circle"></i> Sell an Item
            </a>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">

        <!-- Explore Marketplace -->
        <div class="reveal-section">
            <div class="section-header">
                <h2>Explore <span>Marketplace</span></h2>
                <a href="marketplace.php" class="view-all">View all <i class="bi bi-arrow-right"></i></a>
            </div>

            <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <div class="products-grid">
                <?php
                $sql = "SELECT p.product_id, p.name, p.price, p.type, p.image,
                               v.business_name, c.category_name
                        FROM products p
                        JOIN vendors v ON p.vendor_id = v.vendor_id
                        JOIN categories c ON p.category_id = c.category_id
                        ORDER BY p.product_id DESC LIMIT 6";
                $result = mysqli_query($link, $sql);
                while($row = mysqli_fetch_assoc($result)):
                ?>
                <div class="product-card fade-in">
                    <img src="https://placehold.co/400x160/5b2be0/white?text=<?php echo urlencode($row['name']); ?>"
                         class="product-card-img" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <span class="product-card-badge"><?php echo $row['type']; ?></span>
                    <button class="product-card-wish">
                        <i class="bi bi-heart"></i>
                    </button>
                    <div class="product-card-body">
                        <div class="product-card-name"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="product-card-vendor">
                            <i class="bi bi-shop" style="color:#5b2be0;"></i>
                            <?php echo htmlspecialchars($row['business_name']); ?>
                        </div>
                        <div class="product-card-footer">
                            <span class="product-card-price">Ksh <?php echo number_format($row['price'], 2); ?></span>
                            <a href="product-details.php?id=<?php echo $row['product_id']; ?>" class="btn-buy">Buy Now</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <?php else: ?>
            <!-- Login prompt -->
            <div class="login-prompt">
                <div style="font-size:50px;margin-bottom:15px;">🔒</div>
                <h4>Login to See All Listings!</h4>
                <p>CampusHub is a verified student marketplace. Login with your MKU account to browse products and services.</p>
                <a href="login.php" class="btn-login-prompt">Login to Browse</a>
                <a href="register.php" class="btn-register-prompt">Create Account</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Trending Now -->
        <div class="reveal-section">
            <div class="section-header">
                <h2>⚡ <span>Trending</span> Now</h2>
                <a href="marketplace.php" class="view-all">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="trending-grid">
                <?php
                $trending = mysqli_query($link, "SELECT p.product_id, p.name, p.price 
                    FROM products p ORDER BY p.product_id DESC LIMIT 3");
                while($t = mysqli_fetch_assoc($trending)):
                ?>
                <a href="<?php echo isset($_SESSION['loggedin']) ? 'product-details.php?id='.$t['product_id'] : 'login.php'; ?>" class="trending-card">
                    <div class="trending-img">🏷️</div>
                    <div>
                        <div class="trending-name"><?php echo htmlspecialchars($t['name']); ?></div>
                        <div class="trending-price">Ksh <?php echo number_format($t['price'], 2); ?></div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Recommended for You -->
        <div class="reveal-section">
            <div class="section-header">
                <h2>✨ <span>Recommended</span> for You</h2>
            </div>
            <div class="recommended-grid">
                <div class="rec-card purple">
                    <h5>New Tech for the Semester</h5>
                    <p>Refurbished laptops and gadgets checked by campus IT students.</p>
                    <a href="marketplace.php?category=6" class="btn-rec">Explore Tech</a>
                </div>
                <div class="rec-card orange">
                    <h5>Artist's Corner</h5>
                    <p>Unique prints from our Fine Arts faculty students.</p>
                    <a href="marketplace.php?category=3" class="btn-rec orange-btn">View Art</a>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="footer-brand">Campus<span>Hub</span></div>
                <p class="footer-tagline">Empowering students to build sustainable businesses and trade efficiently within their own campus community.</p>
                <div class="footer-social">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-twitter-x"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <div class="footer-heading">Marketplace</div>
                <ul class="footer-links">
                    <li><a href="marketplace.php">Browse All</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Safety Tips</a></li>
                    <li><a href="#">How it Works</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4">
                <div class="footer-heading">Support</div>
                <ul class="footer-links">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <div class="footer-heading">About CampusHub</div>
                <p style="font-size:13px;color:rgba(255,255,255,0.6);">A verified marketplace exclusively for Mount Kenya University students. Buy, sell and grow your campus business safely.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2025 CampusHub, Inc. All rights reserved.</span>
            <div class="footer-social">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-twitter-x"></i></a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/home.js"></script>
</body>
</html>