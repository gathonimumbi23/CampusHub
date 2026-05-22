<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

    <h2 class="section-title text-center">🛍️ Marketplace</h2>

    <!-- Search + Filter Bar -->
    <form method="GET" action="marketplace.php" class="row g-2 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search products or services..."
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php
                $cats = mysqli_query($link, "SELECT * FROM categories ORDER BY category_name");
                while($cat = mysqli_fetch_assoc($cats)):
                    $selected = (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : '';
                ?>
                <option value="<?php echo $cat['category_id']; ?>" <?php echo $selected; ?>>
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Products Grid -->
    <div class="row">
        <?php
        // Build query based on filters
        $where = [];
        $params = [];

        if (!empty($_GET['search'])) {
            $search = mysqli_real_escape_string($link, $_GET['search']);
            $where[] = "(p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
        }

        if (!empty($_GET['category'])) {
            $cat_id = (int)$_GET['category'];
            $where[] = "p.category_id = $cat_id";
        }

        $where_clause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT p.product_id, p.name, p.description, p.price, p.image, p.type,
                       v.business_name, c.category_name
                FROM products p
                JOIN vendors v ON p.vendor_id = v.vendor_id
                JOIN categories c ON p.category_id = c.category_id
                $where_clause
                ORDER BY p.product_id DESC";

        $result = mysqli_query($link, $sql);
        $count = mysqli_num_rows($result);

        if ($count === 0):
        ?>
        <div class="col-12 text-center py-5">
            <h5 class="text-muted">No listings found. Try a different search or category.</h5>
            <a href="marketplace.php" class="btn btn-primary mt-3">Clear Filters</a>
        </div>
        <?php else: ?>
        <!-- Results count -->
        <div class="col-12 mb-3">
            <p class="text-muted"><?php echo $count; ?> listing(s) found</p>
        </div>

        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="https://placehold.co/400x220/5b2be0/white?text=<?php echo urlencode($row['name']); ?>"
                     class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-secondary mb-2 w-fit"><?php echo $row['type']; ?> &bull; <?php echo htmlspecialchars($row['category_name']); ?></span>
                    <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars(substr($row['description'], 0, 90)); ?>...</p>
                    <p class="price">Ksh <?php echo number_format($row['price'], 2); ?></p>
                    <p class="vendor">🏪 <?php echo htmlspecialchars($row['business_name']); ?></p>
                    <a href="product-details.php?id=<?php echo $row['product_id']; ?>"
                       class="btn btn-primary mt-auto">View Details</a>
                </div>
            </div>
        </div>
        <?php endwhile; endif; ?>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>