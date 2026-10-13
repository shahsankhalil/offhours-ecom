CREATE DATABASE IF NOT EXISTS offhour_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE offhour_db;

CREATE TABLE IF NOT EXISTS watches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(80) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_watches_category (category),
    INDEX idx_watches_stock (stock)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(30) NOT NULL,
    payment_method VARCHAR(40) NOT NULL DEFAULT 'Cash on Delivery',
    total DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orders_status (status),
    INDEX idx_orders_created_at (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    watch_id INT UNSIGNED NULL,
    watch_name VARCHAR(150) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_watch FOREIGN KEY (watch_id) REFERENCES watches(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO admins (username, password_hash)
SELECT 'admin', '$2y$10$Y9mlIhy7UssDovqFno5BweOYnn2oMk1vJlWqq5tZB4DbEImVEa0ji'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE username = 'admin');

INSERT INTO watches (name, description, category, price, image, stock)
SELECT 'Afterglow 38', 'A compact brushed steel watch with a clean midnight dial.', 'Minimal', 189.00, 'images/afterglow.jpg', 8
WHERE NOT EXISTS (SELECT 1 FROM watches WHERE name = 'Afterglow 38');

INSERT INTO watches (name, description, category, price, image, stock)
SELECT 'Night Shift', 'A bold black case and luminous hands built for late hours.', 'Automatic', 249.00, 'images/night-shift.jpg', 5
WHERE NOT EXISTS (SELECT 1 FROM watches WHERE name = 'Night Shift');

INSERT INTO watches (name, description, category, price, image, stock)
SELECT 'Blue Hour', 'A deep blue sunray dial paired with a comfortable leather strap.', 'Classic', 219.00, 'images/blue-hour.jpg', 6
WHERE NOT EXISTS (SELECT 1 FROM watches WHERE name = 'Blue Hour');

INSERT INTO watches (name, description, category, price, image, stock)
SELECT 'Quiet Mark', 'A refined everyday timepiece with a slim silver profile.', 'Everyday', 159.00, 'images/quiet-mark.jpg', 10
WHERE NOT EXISTS (SELECT 1 FROM watches WHERE name = 'Quiet Mark');