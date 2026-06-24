<?php
// ========================================
// APPLICATION CONSTANTS
// ========================================

// Application name
define('APP_NAME', 'ShopSwift');

// Application version
define('APP_VERSION', '1.0.0');

// Environment (development, staging, production)
define('ENVIRONMENT', 'development');

// Base URL (for links and assets)
define('BASE_URL', 'http://localhost/CampusHub/shopswift/');

// Asset URL
define('ASSET_URL', BASE_URL . 'assets/');

// Session timeout (in seconds) — only define if not already defined (by session.php)
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600); // 1 hour
}

// Pagination settings
define('ITEMS_PER_PAGE', 12);

// Cart settings
define('CART_MAX_ITEMS', 50);

// Shipping settings
define('FREE_SHIPPING_THRESHOLD', 100.00);
define('SHIPPING_COST', 5.99);

// Tax settings
define('TAX_RATE', 0.08); // 8%