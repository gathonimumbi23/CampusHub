<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<header class="hero-section">
    <div class="container">
        <h1>Welcome to CampusHub </h1>
        <p>Your one-stop marketplace for student-led businesses and services.</p>
        <a href="marketplace.php" class="hero-btn btn">Explore Marketplace</a>
    </div>
</header>

<div class="container mt-5">

    <!-- Categories from DB -->
    <section class="categories-section">
        <h2 class="section-title text-center">Browse Categories</h2>
        <div class="row">
            <?php
            $cat_sql = "SELECT * FROM categories LIMIT 8";
            $cat_result = mysqli_query($link, $cat_sql);
            while($cat = mysqli_fetch_assoc($cat_result)):
            ?>
            <div class="col-md-3 col-6 mb-4">
                <a href="marketplace.php?category=<?php echo $cat['category_id']; ?>" class="text-decoration-none">
                    <div class="category-card">
                        <h6><?php echo htmlspecialchars($cat['category_name']); ?></h6>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Featured Products from DB -->
    <section class="featured-products mt-4">
        <h2 class="section-title text-center">Featured Listings</h2>
        <div class="row">
            <?php
            $sql = "SELECT p.product_id, p.name, p.price, p.image, p.type, v.business_name 
                    FROM products p 
                    JOIN vendors v ON p.vendor_id = v.vendor_id 
                    LIMIT 4";
            $result = mysqli_query($link, $sql);
            while($row = mysqli_fetch_assoc($result)):
            ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <img src="https://placehold.co/400x220/5b2be0/white?text=<?php echo urlencode($row['name']); ?>"
                         class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                        <p class="price">Ksh <?php echo number_format($row['price'], 2); ?></p>
                        <p class="vendor">🏪 <?php echo htmlspecialchars($row['business_name']); ?></p>
                        <span class="badge bg-secondary mb-2"><?php echo $row['type']; ?></span>
                        <a href="product-details.php?id=<?php echo $row['product_id']; ?>" 
                           class="btn btn-primary mt-auto">View Details</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>