-- Green & Waste Collect App Database Schema
-- Run this against a MySQL or MariaDB database

CREATE DATABASE IF NOT EXISTS green_waste CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE green_waste;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    role ENUM('resident','admin') DEFAULT 'resident',
    address TEXT,
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Collection schedules
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone VARCHAR(100) NOT NULL,
    collection_date DATE NOT NULL,
    collection_time TIME NOT NULL,
    waste_type ENUM('general','recyclable','hazardous','organic') DEFAULT 'general',
    status ENUM('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Truck locations (real-time tracking)
CREATE TABLE IF NOT EXISTS truck_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    truck_id VARCHAR(20) NOT NULL,
    driver_name VARCHAR(100),
    lat DECIMAL(10,8) NOT NULL,
    lng DECIMAL(11,8) NOT NULL,
    status ENUM('idle','collecting','returning') DEFAULT 'idle',
    zone VARCHAR(100),
    eta_minutes INT DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Crowdsourced reports (overflowing bins / waste hotspots)
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    description TEXT NOT NULL,
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    address TEXT,
    photo_path VARCHAR(255),
    status ENUM('pending','reviewed','resolved') DEFAULT 'pending',
    severity ENUM('low','medium','high') DEFAULT 'medium',
    admin_notes TEXT,
    reported_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Recyclable market listings
CREATE TABLE IF NOT EXISTS market_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    material_type ENUM('plastic','metal','paper','glass','electronics','organic','other') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) DEFAULT 'kg',
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255),
    status ENUM('available','sold','reserved') DEFAULT 'available',
    location VARCHAR(200),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Market bids / purchases
CREATE TABLE IF NOT EXISTS market_bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    buyer_id INT NOT NULL,
    bid_price DECIMAL(10,2) NOT NULL,
    message TEXT,
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES market_listings(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('schedule','tracking','report','market','system') DEFAULT 'system',
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample data
INSERT INTO users (name, email, phone, password, role, address, lat, lng) VALUES
('Admin User', 'admin@greenwaste.com', '+95912345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'City Hall, Yangon', 16.8661, 96.1951),
('Ko Aung', 'koaung@example.com', '+95987654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 'No.5, Strand Road, Yangon', 16.7700, 96.1590),
('Ma Hnin', 'mahnin@example.com', '+95976543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 'Kamayut Township, Yangon', 16.8219, 96.1276);

INSERT INTO schedules (zone, collection_date, collection_time, waste_type, status, notes) VALUES
('Zone A - Downtown', CURDATE(), '08:00:00', 'general', 'scheduled', 'Regular weekly collection'),
('Zone B - North', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', 'recyclable', 'scheduled', 'Recyclables only'),
('Zone C - East', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '07:30:00', 'organic', 'scheduled', 'Organic waste collection'),
('Zone A - Downtown', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '08:00:00', 'general', 'scheduled', 'Regular weekly collection'),
('Zone D - South', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', 'hazardous', 'scheduled', 'Hazardous waste – special handling');

INSERT INTO truck_locations (truck_id, driver_name, lat, lng, status, zone, eta_minutes) VALUES
('TRK-001', 'U Kyaw Zin', 16.8100, 96.1600, 'collecting', 'Zone A - Downtown', 12),
('TRK-002', 'U Hla Shwe', 16.8400, 96.1400, 'idle', 'Zone B - North', 0),
('TRK-003', 'U Myo Win', 16.7900, 96.1800, 'returning', 'Zone C - East', 0);

INSERT INTO market_listings (seller_id, title, description, material_type, quantity, unit, price, status, location) VALUES
(2, 'Plastic Bottles (PET)', 'Clean sorted PET plastic bottles, pressed', 'plastic', 50.00, 'kg', 500, 'available', 'Kamayut, Yangon'),
(3, 'Aluminum Cans', 'Crushed aluminum cans, ready for recycling', 'metal', 30.00, 'kg', 1500, 'available', 'Sanchaung, Yangon'),
(2, 'Old Newspapers & Cardboard', 'Sorted paper waste, dry condition', 'paper', 100.00, 'kg', 150, 'available', 'Kamayut, Yangon'),
(3, 'Glass Bottles (Mixed)', 'Various glass bottles, cleaned', 'glass', 20.00, 'kg', 100, 'available', 'Bahan, Yangon'),
(2, 'Copper Wire Scrap', 'Electric copper wire scraps', 'metal', 5.00, 'kg', 8000, 'available', 'Insein, Yangon');

INSERT INTO reports (user_id, description, lat, lng, address, status, severity) VALUES
(2, 'Overflowing bin near the market. Waste spilling onto sidewalk.', 16.7850, 96.1550, 'Bogyoke Market Area', 'pending', 'high'),
(3, 'Illegal dumping behind the school building.', 16.8300, 96.1300, 'Near Kamayut School', 'reviewed', 'medium'),
(2, 'Bin not collected for 3 days. Very smelly.', 16.7700, 96.1700, 'Pazundaung River Road', 'pending', 'high');
