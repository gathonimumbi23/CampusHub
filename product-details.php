<?php
require_once 'includes/config.php';

// Check if product ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: marketplace.php");
    exit;
}

$product_id = intval($_GET['id']);

// Fetch product details
$sql = "SELECT p.*, v.business_name, v.vendor_id, v.profile_image, c.category_name 
        FROM products p 
        JOIN vendors v ON p.vendor_id = v.vendor_id 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = ?";

if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    $product = null;
}

if(!$product) {
    header("location: marketplace.php");
    exit;
}

// Get product ratings and reviews
$reviews_query = "SELECT * FROM product_reviews WHERE product_id = ? ORDER BY created_date DESC";
if($stmt = mysqli_prepare($link, $reviews_query)){
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $reviews = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    $reviews = mysqli_query($link, "SELECT * FROM product_reviews WHERE product_id = '$product_id' ORDER BY created_date DESC");
}

// Calculate average rating
$rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM product_reviews WHERE product_id = ?";
if($stmt = mysqli_prepare($link, $rating_query)){
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $rating_result = mysqli_stmt_get_result($stmt);
    $rating_data = mysqli_fetch_assoc($rating_result);
    mysqli_stmt_close($stmt);
} else {
    $rating_data = ['avg_rating' => 0, 'total_reviews' => 0];
}

// Get related products
$related_query = "SELECT * FROM products WHERE category_id = ? AND product_id != ? LIMIT 4";
if($stmt = mysqli_prepare($link, $related_query)){
    mysqli_stmt_bind_param($stmt, "ii", $product['category_id'], $product_id);
    mysqli_stmt_execute($stmt);
    $related = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    $related = mysqli_query($link, "SELECT * FROM products WHERE category_id = '{$product['category_id']}' AND product_id != '$product_id' LIMIT 4");
}

// Rating distribution
$rating_dist_query = "SELECT rating, COUNT(*) as count FROM product_reviews WHERE product_id = ? GROUP BY rating ORDER BY rating DESC";
if($stmt = mysqli_prepare($link, $rating_dist_query)){
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $rating_dist = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    $rating_dist = mysqli_query($link, "SELECT rating, COUNT(*) as count FROM product_reviews WHERE product_id = '$product_id' GROUP BY rating ORDER BY rating DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/product-details.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="product-container">
    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="marketplace.php">Marketplace</a></li>
                    <li class="breadcrumb-item"><a href="marketplace.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars(substr($product['name'], 0, 50)); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Product Details Section -->
    <div class="container product-details-main">
        <div class="row">
            <!-- Product Images -->
            <div class="col-lg-5">
                <div class="product-image-section">
                    <div class="main-image">
                        <img id="mainImage" src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                        <button class="wishlist-btn" onclick="toggleWishlist(<?php echo $product['product_id']; ?>)">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                    <div class="image-thumbnails">
                        <div class="thumb active" onclick="changeImage('assets/images/<?php echo htmlspecialchars($product['image']); ?>')">
                            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-lg-7">
                <div class="product-info">
                    <!-- Product Name & Category -->
                    <div class="product-header">
                        <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
                        <p class="product-category">
                            <a href="marketplace.php?category=<?php echo $product['category_id']; ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        </p>
                    </div>

                    <!-- Rating Section -->
                    <div class="rating-section">
                        <div class="rating-display">
                            <span class="rating-number"><?php echo number_format($rating_data['avg_rating'], 1); ?></span>
                            <div class="stars">
                                <?php 
                                $avg = $rating_data['avg_rating'];
                                for($i = 1; $i <= 5; $i++) {
                                    if($i <= floor($avg)) {
                                        echo '<i class="bi bi-star-fill"></i>';
                                    } elseif($i - $avg < 1) {
                                        echo '<i class="bi bi-star-half"></i>';
                                    } else {
                                        echo '<i class="bi bi-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <span class="review-count">(<?php echo $rating_data['total_reviews']; ?> reviews)</span>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="price-section">
                        <h2 class="product-price">₦<?php echo number_format($product['price'], 2); ?></h2>
                    </div>

                    <!-- Seller Info -->
                    <div class="seller-section">
                        <div class="seller-card">
                            <img src="assets/images/<?php echo htmlspecialchars($product['profile_image']); ?>" alt="Seller" class="seller-avatar">
                            <div class="seller-info">
                                <p class="seller-name"><?php echo htmlspecialchars($product['business_name']); ?></p>
                                <span class="seller-badge">Premium Seller</span>
                            </div>
                            <button class="btn-contact-seller">
                                <i class="bi bi-chat-dots"></i> Contact Seller
                            </button>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="description-section">
                        <h4>Product Description</h4>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>

                    <!-- Quantity & Buttons -->
                    <div class="purchase-section">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <div class="qty-input">
                                <button type="button" class="qty-btn" onclick="decreaseQty()">−</button>
                                <input type="number" id="quantity" value="1" min="1" max="100">
                                <button type="button" class="qty-btn" onclick="increaseQty()">+</button>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button class="btn-add-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                            <button class="btn-buy-now" onclick="buyNow(<?php echo $product['product_id']; ?>)">
                                <i class="bi bi-flash"></i> Buy Now
                            </button>
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="additional-options">
                        <button class="option-btn">
                            <i class="bi bi-shuffle"></i> Compare Products
                        </button>
                        <button class="option-btn">
                            <i class="bi bi-share"></i> Share
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products Section -->
    <div class="related-products-section">
        <div class="container">
            <div class="section-header">
                <h2>Related Products</h2>
                <a href="marketplace.php?category=<?php echo $product['category_id']; ?>" class="view-all">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="products-grid">
                <?php while($rel = mysqli_fetch_assoc($related)): ?>
                <div class="product-card">
                    <div class="product-image-card">
                        <img src="assets/images/<?php echo htmlspecialchars($rel['image']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                    </div>
                    <div class="product-card-info">
                        <h5><?php echo htmlspecialchars(substr($rel['name'], 0, 40)); ?></h5>
                        <p class="product-price">₦<?php echo number_format($rel['price'], 2); ?></p>
                        <button class="btn-view" onclick="location.href='product-details.php?id=<?php echo $rel['product_id']; ?>'">
                            View Details
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="reviews-section">
        <div class="container">
            <div class="reviews-container">
                <!-- Reviews Summary -->
                <div class="reviews-summary">
                    <div class="rating-summary">
                        <div class="big-rating">
                            <span class="rating-number"><?php echo number_format($rating_data['avg_rating'], 1); ?></span>
                            <div class="rating-stars">
                                <?php 
                                for($i = 1; $i <= 5; $i++) {
                                    if($i <= floor($rating_data['avg_rating'])) {
                                        echo '<i class="bi bi-star-fill"></i>';
                                    } else {
                                        echo '<i class="bi bi-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <p class="reviews-count"><?php echo $rating_data['total_reviews']; ?> reviews</p>
                        </div>
                    </div>

                    <!-- Rating Distribution -->
                    <div class="rating-distribution">
                        <?php
                        $total = $rating_data['total_reviews'] ?: 1;
                        for($i = 5; $i >= 1; $i--) {
                            $count = 0;
                            while($row = mysqli_fetch_assoc($rating_dist)) {
                                if($row['rating'] == $i) {
                                    $count = $row['count'];
                                    break;
                                }
                            }
                            mysqli_data_seek($rating_dist, 0);
                            $percentage = ($count / $total) * 100;
                        ?>
                        <div class="distribution-row">
                            <span class="stars-label"><?php echo $i; ?> <i class="bi bi-star-fill"></i></span>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="count-label"><?php echo $count; ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <button class="btn-write-review" onclick="toggleReviewForm()">
                        <i class="bi bi-pencil"></i> Write a Review
                    </button>
                </div>

                <!-- Reviews List -->
                <div class="reviews-list">
                    <h3>Student Reviews</h3>
                    
                    <!-- Write Review Form (Hidden by default) -->
                    <div class="review-form" id="reviewForm" style="display: none;">
                        <div class="form-group">
                            <label for="reviewRating">Rating:</label>
                            <div class="rating-input">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star" onclick="setRating(<?php echo $i; ?>)" data-rating="<?php echo $i; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="reviewTitle">Review Title:</label>
                            <input type="text" id="reviewTitle" placeholder="Brief title of your review" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="reviewText">Your Review:</label>
                            <textarea id="reviewText" placeholder="Share your experience with this product..." class="form-control" rows="4"></textarea>
                        </div>
                        <div class="form-actions">
                            <button class="btn-submit-review" onclick="submitReview(<?php echo $product_id; ?>)">Submit Review</button>
                            <button class="btn-cancel-review" onclick="toggleReviewForm()">Cancel</button>
                        </div>
                    </div>

                    <!-- Individual Reviews -->
                    <?php 
                    $review_count = 0;
                    mysqli_data_seek($reviews, 0);
                    while($review = mysqli_fetch_assoc($reviews) && $review_count < 5): 
                        $review_count++;
                    ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <img src="https://ui-avatars.com/api/?name=<?php echo htmlspecialchars($review['reviewer_name']); ?>&background=random" alt="" class="reviewer-avatar">
                                <div class="reviewer-details">
                                    <p class="reviewer-name"><?php echo htmlspecialchars($review['reviewer_name']); ?></p>
                                    <small class="review-date"><?php echo date('M d, Y', strtotime($review['created_date'])); ?></small>
                                </div>
                            </div>
                            <div class="review-rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?php echo $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="review-content">
                            <p class="review-title"><strong><?php echo htmlspecialchars($review['review_title']); ?></strong></p>
                            <p class="review-text"><?php echo htmlspecialchars($review['review_text']); ?></p>
                        </div>
                        <div class="review-footer">
                            <button class="btn-helpful" onclick="markHelpful()">
                                <i class="bi bi-hand-thumbs-up"></i> Helpful (0)
                            </button>
                            <button class="btn-not-helpful" onclick="markNotHelpful()">
                                <i class="bi bi-hand-thumbs-down"></i>
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>

                    <?php if($review_count == 0): ?>
                    <div class="no-reviews">
                        <p>No reviews yet. Be the first to review this product!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/product-details.js"></script>

</body>
</html>