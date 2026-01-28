CREATE DATABASE IF NOT EXISTS wedding_planner;
USE wedding_planner;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer'
);

-- Admin table
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admins (name, email, password) VALUES 
('Admin', 'admin@eventzone.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrO.CQqMJ6khRBbCl5qM5pLd.jt0KFjJEqy');

CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_date DATE NOT NULL,
    venue_type ENUM('indoor', 'outdoor') DEFAULT 'indoor',
    location VARCHAR(255) NOT NULL,
    guest_count INT NOT NULL,
    budget DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'pending_payment', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Decoration Categories
CREATE TABLE decoration_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

-- Decoration Items
CREATE TABLE decoration_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    image_path VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES decoration_categories(category_id)
);

-- Booking Decorations (selected items per booking)
CREATE TABLE booking_decorations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) DEFAULT 0,
    line_total DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (item_id) REFERENCES decoration_items(item_id)
);

-- Insert Categories
INSERT INTO decoration_categories (category_name) VALUES
('Setup'), ('Tables'), ('Chairs'), ('Flowers'), ('Lighting'), ('Welcome Sign');

-- Insert Items with image paths
INSERT INTO decoration_items (category_id, item_name, description, price, image_path) VALUES
-- Setup (category_id = 1)
(1, 'Classic Setup', 'Traditional elegant wedding setup', 2000.00, 'images/items/setup_01.jpeg'),
(1, 'Modern Setup', 'Contemporary minimalist design', 2200.00, 'images/items/setup_02.jpeg'),
(1, 'Rustic Setup', 'Country-style wooden decorations', 1800.00, 'images/items/setup_03.jpeg'),
(1, 'Luxury Setup', 'Premium high-end arrangement', 3500.00, 'images/items/setup_04.jpeg'),
(1, 'Garden Setup', 'Outdoor natural theme', 2100.00, 'images/items/setup_05.jpeg'),

-- Tables (category_id = 2)
(2, 'Round Table', 'Classic round banquet table', 45.00, 'images/items/table1.jpeg'),
(2, 'Rectangular Table', 'Long rectangular table', 55.00, 'images/items/table2.jpeg'),
(2, 'Oval Table', 'Elegant oval shaped table', 60.00, 'images/items/table3.jpeg'),
(2, 'Cocktail Table', 'High standing cocktail table', 35.00, 'images/items/table4.jpeg'),

-- Chairs (category_id = 3)
(3, 'Gold Chiavari Chair', 'Elegant gold chiavari', 8.00, 'images/items/gold.jpeg'),
(3, 'White Folding Chair', 'Simple white folding chair', 3.00, 'images/items/white.jpeg'),
(3, 'Cross Back Chair', 'Rustic cross back wooden chair', 10.00, 'images/items/cross.jpeg'),
(3, 'Acrylic Ghost Chair', 'Modern transparent chair', 15.00, 'images/items/acrylic.jpeg'),

-- Flowers (category_id = 4)
(4, 'Rose Bouquet', 'Classic red and white roses', 150.00, 'images/items/rose.jpeg'),
(4, 'Peony Arrangement', 'Soft pink peonies', 200.00, 'images/items/peony.jpeg'),
(4, 'Calla Lily Bundle', 'Elegant calla lilies', 180.00, 'images/items/calla.jpeg'),
(4, 'Ranunculus Mix', 'Colorful ranunculus flowers', 160.00, 'images/items/ranu.jpeg'),
(4, 'Lily Centerpiece', 'White lily arrangement', 175.00, 'images/items/lily.jpeg'),

-- Lighting (category_id = 5)
(5, 'LED String Lights', 'Warm white fairy lights', 75.00, 'images/items/led.jpeg'),
(5, 'Chandelier', 'Crystal chandelier rental', 250.00, 'images/items/light.jpeg'),
(5, 'Natural Candles', 'Assorted candle setup', 50.00, 'images/items/natural.jpeg'),

-- Welcome Sign (category_id = 6)
(6, 'Mirror Welcome Sign', 'Elegant mirror with calligraphy', 120.00, 'images/items/welcome_mirror.jpeg'),
(6, 'Wooden Welcome Sign', 'Rustic wooden board', 80.00, 'images/items/wood.jpeg'),
(6, 'Acrylic Welcome Sign', 'Modern clear acrylic', 150.00, 'images/items/backdrop.jpeg');