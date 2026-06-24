# Week 6: Database Integration and CRUD Operations

## 📋 Overview
This week we implemented full CRUD (Create, Read, Update, Delete) operations for all database tables and built the seller dashboard.

## ✅ Completed Tasks

### 1. Models (Full CRUD)
- [x] User Model - Complete CRUD
- [x] Product Model - Complete CRUD with search, filters, pagination
- [x] Category Model - Complete CRUD
- [x] Cart Model - Complete CRUD with summary
- [x] Wishlist Model - Complete CRUD
- [x] Order Model - Complete CRUD with stats

### 2. Controllers
- [x] ProductController - Listing, detail, category filtering
- [x] CartController - Add, update, remove, clear
- [x] WishlistController - Toggle, remove, move to cart
- [x] AuthController - Login, register, logout, profile

### 3. Views
- [x] Products index with filters and pagination
- [x] Product detail with variants and reviews
- [x] Cart page with summary and checkout
- [x] Wishlist page with move to cart
- [x] Login page
- [x] Register page

### 4. Features
- [x] Product search functionality
- [x] Category filtering
- [x] Sorting (newest, price, rating)
- [x] Pagination
- [x] Cart total calculation with shipping/tax
- [x] Free shipping threshold
- [x] Wishlist toggle with AJAX
- [x] Move to cart from wishlist
- [x] User authentication (login/register)
- [x] Session management

## 📁 Files Created

| File | Location | Purpose |
|------|----------|---------|
| Category.php | `models/` | Category CRUD |
| Product.php | `models/` | Product CRUD with filters |
| Cart.php | `models/` | Cart CRUD with summary |
| Wishlist.php | `models/` | Wishlist CRUD |
| Order.php | `models/` | Order CRUD with stats |
| ProductController.php | `controllers/` | Product logic |
| CartController.php | `controllers/` | Cart logic |
| WishlistController.php | `controllers/` | Wishlist logic |
| AuthController.php | `controllers/` | Auth logic |
| index.php | `views/products/` | Product listing |
| detail.php | `views/products/` | Product detail |
| index.php | `views/cart/` | Cart page |
| index.php | `views/wishlist/` | Wishlist page |
| login.php | `views/auth/` | Login page |
| register.php | `views/auth/` | Register page |

## 🚀 How to Test

1. **Start XAMPP**: Apache & MySQL
2. **Open Browser**: `http://localhost/CampusHub/shopswift/`
3. **Test Product Listing**:
   - Browse all products
   - Filter by category
   - Search for products
   - Sort by price/rating
4. **Test Cart**:
   - Add items to cart
   - Update quantities
   - Remove items
   - View cart summary
5. **Test Wishlist**:
   - Add items to wishlist (heart icon)
   - View wishlist page
   - Move items to cart
   - Remove from wishlist
6. **Test Authentication**:
   - Register new account
   - Login with credentials
   - Logout

## 📸 Screenshots
1. `product-listing.png` - Products page with filters
2. `product-detail.png` - Single product view
3. `cart-page.png` - Shopping cart with summary
4. `wishlist-page.png` - Wishlist with move to cart
5. `login-page.png` - Login page
6. `register-page.png` - Registration page

## 📅 Week 6 Completion Date
June 20, 2026