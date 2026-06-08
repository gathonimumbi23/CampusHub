CREATE DATABASE IF NOT EXISTS mku_marketplace;
USE mku_marketplace;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    admission_number VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    role ENUM('buyer', 'seller', 'admin') DEFAULT 'buyer',
    profile_picture_url VARCHAR(500) DEFAULT 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8dXNlcnxlbnwwfHwwfHx8MA%3D%3D',
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) NOT NULL, -- strictly lowercase
    image_url VARCHAR(500),
    seller_id INT,
    is_flagged BOOLEAN DEFAULT FALSE,
    flag_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    buyer_id INT,
    seller_id INT,
    status ENUM('pending', 'Paid', 'Awaiting Meetup', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('cash', 'mpesa') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Mock Items
INSERT INTO users (name, admission_number, email, phone, role, password_hash) 
VALUES ('Main Admin', 'ADMIN001', 'admin@mku.ac.ke', '0700000000', 'admin', '$2y$10$vY8U2vK2xXF5b6G8vO8vOeGf6z6z6z6z6z6z6z6z6z6z6z6z6z6z6'); -- Default pass: 123456

INSERT INTO users (name, admission_number, email, phone, role, password_hash) 
VALUES ('John Seller', '252000', 'bit252000@mku.ac.ke', '0712345678', 'seller', '$2y$10$vY8U2vK2xXF5b6G8vO8vOeGf6z6z6z6z6z6z6z6z6z6z6z6z6z6z6');

-- Mock Products
INSERT INTO products (title, description, price, category, image_url, seller_id) VALUES
('Vintage Denim Jacket', 'Classic oversized denim jacket, perfect for campus cool.', 1500.00, 'clothes', 'https://images.unsplash.com/photo-1551537482-f2075a1d41f2?q=80&w=1974&auto=format&fit=crop', 2),
('Retro Nike Sneakers', 'Slightly worn but authentic vintage Nike shoes.', 3200.00, 'shoes', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=2070&auto=format&fit=crop', 2),
('Handcrafted Beaded Necklace', 'Authentic Maasai beadwork jewelry.', 450.00, 'jewelry & crafts', 'https://images.unsplash.com/photo-1629224316810-9d8805b95e76?q=80&w=2070&auto=format&fit=crop', 2),
('Sleek Silver Ring', 'Minimalist silver ring for androgynous fashion.', 600.00, 'jewelry & crafts', 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?q=80&w=2070&auto=format&fit=crop', 2),
('Canvas Tote Bag', 'Eco-friendly tote for books and groceries.', 350.00, 'accessories', 'https://images.unsplash.com/photo-1544816153-12ad582b312f?q=80&w=2070&auto=format&fit=crop', 2);
