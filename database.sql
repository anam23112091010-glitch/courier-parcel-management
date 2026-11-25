-- =====================================================
--  Courier & Parcel Management System  -  Database
--  Import this file in phpMyAdmin (it creates courier_db)
-- =====================================================
CREATE DATABASE IF NOT EXISTS courier_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE courier_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS tracking_history, parcels, messages, branches, settings, users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------- users ----------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    city VARCHAR(60) DEFAULT 'Dhaka',
    role ENUM('admin','user','rider') NOT NULL DEFAULT 'user',
    status ENUM('active','blocked') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------- branches ----------------
CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(60) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------- parcels ----------------
CREATE TABLE parcels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_no VARCHAR(25) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    sender_name VARCHAR(100) NOT NULL,
    sender_phone VARCHAR(20) NOT NULL,
    sender_address VARCHAR(255) NOT NULL,
    receiver_name VARCHAR(100) NOT NULL,
    receiver_phone VARCHAR(20) NOT NULL,
    receiver_address VARCHAR(255) NOT NULL,
    receiver_city VARCHAR(60) NOT NULL,
    parcel_type VARCHAR(40) NOT NULL,
    weight DECIMAL(6,2) NOT NULL,
    service_type ENUM('standard','express','sameday') NOT NULL DEFAULT 'standard',
    payment_method ENUM('prepaid','cod') NOT NULL DEFAULT 'prepaid',
    cod_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    delivery_charge DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Unpaid','Paid') NOT NULL DEFAULT 'Unpaid',
    status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    rider_id INT DEFAULT NULL,
    branch_id INT DEFAULT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ---------------- tracking_history ----------------
CREATE TABLE tracking_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parcel_id INT NOT NULL,
    status VARCHAR(30) NOT NULL,
    location VARCHAR(150) DEFAULT NULL,
    remarks VARCHAR(255) DEFAULT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parcel_id) REFERENCES parcels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------- messages (contact form) ----------------
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------- settings ----------------
CREATE TABLE settings (
    skey VARCHAR(60) PRIMARY KEY,
    svalue VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- =====================================================
--  SEED DATA
--  Admin  : admin@courier.com / admin123
--  Rider  : rider@courier.com / rider123
--  User   : user@courier.com  / user123
-- =====================================================
INSERT INTO users (id, name, email, phone, password, address, city, role) VALUES
(1, 'System Admin',   'admin@courier.com',  '01700000001', '$2y$10$Gsjv2nZxIKz6yAFTzLSX/.Uu35iyEwd8pNSqHCg2QDmOmryxOLVJq', 'Head Office, Dhanmondi, Dhaka', 'Dhaka', 'admin'),
(2, 'Rahim Uddin',    'rider@courier.com',  '01711000002', '$2y$10$s4BpDrsz1v7o3fecEDh1fuHwPXsuEEI4JnLDJGRqQdCnNUS4fe1Om', 'Mirpur-10, Dhaka', 'Dhaka', 'rider'),
(3, 'Mahi Anam',      'user@courier.com',   '01811000003', '$2y$10$jteGT0PevpJXEDhv3EiLFu1RwTr.2.f8Z.xQV790.k.RPVhZ5qNEu', 'House 12, Road 5, Uttara, Dhaka', 'Dhaka', 'user'),
(4, 'Karim Hossain',  'karim@courier.com',  '01922000004', '$2y$10$s4BpDrsz1v7o3fecEDh1fuHwPXsuEEI4JnLDJGRqQdCnNUS4fe1Om', 'Mohammadpur, Dhaka', 'Dhaka', 'rider'),
(5, 'Nusrat Jahan',   'nusrat@example.com', '01633000005', '$2y$10$jteGT0PevpJXEDhv3EiLFu1RwTr.2.f8Z.xQV790.k.RPVhZ5qNEu', 'Agrabad, Chattogram', 'Chattogram', 'user'),
(6, 'Tanvir Ahmed',   'tanvir@example.com', '01544000006', '$2y$10$jteGT0PevpJXEDhv3EiLFu1RwTr.2.f8Z.xQV790.k.RPVhZ5qNEu', 'Zindabazar, Sylhet', 'Sylhet', 'user');

INSERT INTO branches (id, name, city, address, phone) VALUES
(1, 'Dhaka Head Office',    'Dhaka',      'Level 5, Dhanmondi 27, Dhaka-1209',  '01700000100'),
(2, 'Uttara Hub',           'Dhaka',      'Sector 7, Uttara, Dhaka-1230',       '01700000101'),
(3, 'Chattogram Branch',    'Chattogram', 'Agrabad C/A, Chattogram',            '01700000102'),
(4, 'Sylhet Branch',        'Sylhet',     'Zindabazar, Sylhet',                 '01700000103'),
(5, 'Rajshahi Branch',      'Rajshahi',   'Shaheb Bazar, Rajshahi',             '01700000104');

INSERT INTO settings (skey, svalue) VALUES
('company_name',    'SwiftParcel Courier'),
('company_address', 'Level 5, Dhanmondi 27, Dhaka-1209, Bangladesh'),
('company_phone',   '+880 1700-000100'),
('company_email',   'info@swiftparcel.com'),
('standard_base',   '60'),
('standard_perkg',  '20'),
('express_base',    '100'),
('express_perkg',   '30'),
('sameday_base',    '180'),
('sameday_perkg',   '50'),
('outside_extra',   '50'),
('cod_percent',     '1');

INSERT INTO parcels (id, tracking_no, user_id, sender_name, sender_phone, sender_address, receiver_name, receiver_phone, receiver_address, receiver_city, parcel_type, weight, service_type, payment_method, cod_amount, delivery_charge, total_amount, payment_status, status, rider_id, branch_id, note, created_at) VALUES
(1, 'CPM-DEMO-0001', 3, 'Mahi Anam', '01811000003', 'House 12, Road 5, Uttara, Dhaka', 'Sadia Rahman',  '01755111111', 'Flat 4B, Banani Road 11, Dhaka', 'Dhaka',      'Document',    1.00, 'standard', 'prepaid', 0,    60,  60,   'Paid',   'Delivered',        2, 1, 'Handle with care', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(2, 'CPM-DEMO-0002', 3, 'Mahi Anam', '01811000003', 'House 12, Road 5, Uttara, Dhaka', 'Imran Hossain', '01766222222', 'GEC Circle, Chattogram',          'Chattogram', 'Electronics', 2.50, 'express',  'cod',     2000, 230, 2230, 'Unpaid', 'In Transit',       NULL, 3, 'Phone accessories', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 'CPM-DEMO-0003', 3, 'Mahi Anam', '01811000003', 'House 12, Road 5, Uttara, Dhaka', 'Farhana Akter', '01777333333', 'Gulshan-2, Dhaka',                'Dhaka',      'Cosmetics',   0.50, 'sameday',  'prepaid', 0,    180, 180,  'Paid',   'Out for Delivery', 2, 2, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 'CPM-DEMO-0004', 5, 'Nusrat Jahan', '01633000005', 'Agrabad, Chattogram', 'Jamal Uddin',   '01788444444', 'Ambarkhana, Sylhet',               'Sylhet',     'Clothing',    3.00, 'standard', 'cod',     1500, 165, 1665, 'Unpaid', 'Confirmed',        NULL, 3, 'Eid collection', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 'CPM-DEMO-0005', 5, 'Nusrat Jahan', '01633000005', 'Agrabad, Chattogram', 'Rokeya Begum',  '01799555555', 'Greater Road, Rajshahi',           'Rajshahi',   'Books',       1.20, 'standard', 'prepaid', 0,    130, 130,  'Unpaid', 'Pending',          NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(6, 'CPM-DEMO-0006', 6, 'Tanvir Ahmed', '01544000006', 'Zindabazar, Sylhet', 'Shakib Khan',    '01611666666', 'Mirpur-12, Dhaka',                 'Dhaka',      'Electronics', 4.00, 'express',  'cod',     3500, 225, 3725, 'Paid',   'Delivered',        4, 1, 'Laptop bag', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(7, 'CPM-DEMO-0007', 6, 'Tanvir Ahmed', '01544000006', 'Zindabazar, Sylhet', 'Mizanur Rahman', '01522777777', 'Sonadanga, Khulna',                'Khulna',     'Other',       0.80, 'standard', 'prepaid', 0,    110, 110,  'Unpaid', 'Cancelled',        NULL, NULL, 'Customer cancelled', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(8, 'CPM-DEMO-0008', 3, 'Mahi Anam', '01811000003', 'House 12, Road 5, Uttara, Dhaka', 'Habib Ullah',   '01933888888', 'Kandirpar, Cumilla',               'Cumilla',    'Food Item',   2.00, 'standard', 'cod',     800,  138, 938,  'Unpaid', 'Picked Up',        4, 2, 'Homemade pitha', DATE_SUB(NOW(), INTERVAL 2 DAY));

INSERT INTO tracking_history (parcel_id, status, location, remarks, updated_by, created_at) VALUES
(1, 'Pending',          'Dhaka',              'Booking received',                         3, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(1, 'Confirmed',        'Dhaka Head Office',  'Booking confirmed by admin',               1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(1, 'Picked Up',        'Uttara, Dhaka',      'Parcel picked up by rider Rahim Uddin',    2, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(1, 'Out for Delivery', 'Banani, Dhaka',      'Out for delivery',                         2, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(1, 'Delivered',        'Banani, Dhaka',      'Delivered to receiver',                    2, DATE_SUB(NOW(), INTERVAL 8 DAY)),

(2, 'Pending',          'Dhaka',              'Booking received',                         3, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 'Confirmed',        'Dhaka Head Office',  'Booking confirmed by admin',               1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 'Picked Up',        'Uttara, Dhaka',      'Parcel picked up',                         2, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(2, 'In Transit',       'Dhaka Sorting Hub',  'Departed for Chattogram',                  1, DATE_SUB(NOW(), INTERVAL 3 DAY)),

(3, 'Pending',          'Dhaka',              'Booking received',                         3, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'Confirmed',        'Uttara Hub',         'Booking confirmed',                        1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'Picked Up',        'Uttara, Dhaka',      'Picked up by rider',                       2, DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(3, 'Out for Delivery', 'Gulshan, Dhaka',     'Rider is on the way',                      2, DATE_SUB(NOW(), INTERVAL 2 HOUR)),

(4, 'Pending',          'Chattogram',         'Booking received',                         5, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 'Confirmed',        'Chattogram Branch',  'Booking confirmed',                        1, DATE_SUB(NOW(), INTERVAL 1 DAY)),

(5, 'Pending',          'Chattogram',         'Booking received',                         5, DATE_SUB(NOW(), INTERVAL 3 HOUR)),

(6, 'Pending',          'Sylhet',             'Booking received',                         6, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(6, 'Confirmed',        'Sylhet Branch',      'Booking confirmed',                        1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(6, 'Picked Up',        'Zindabazar, Sylhet', 'Picked up',                                1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(6, 'In Transit',       'Sylhet Highway',     'On the way to Dhaka',                      1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 'Out for Delivery', 'Mirpur, Dhaka',      'Out for delivery with Karim Hossain',      4, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6, 'Delivered',        'Mirpur, Dhaka',      'Delivered. COD amount collected',          4, DATE_SUB(NOW(), INTERVAL 4 DAY)),

(7, 'Pending',          'Sylhet',             'Booking received',                         6, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(7, 'Cancelled',        'Sylhet',             'Cancelled by customer',                    6, DATE_SUB(NOW(), INTERVAL 4 DAY)),

(8, 'Pending',          'Dhaka',              'Booking received',                         3, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(8, 'Confirmed',        'Uttara Hub',         'Booking confirmed',                        1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(8, 'Picked Up',        'Uttara, Dhaka',      'Picked up by rider Karim Hossain',         4, DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO messages (name, email, subject, message, is_read, created_at) VALUES
('Sabbir Hasan',  'sabbir@example.com', 'Corporate delivery plan', 'Hello, we run an online shop and ship about 80 parcels per week. Do you offer a corporate rate?', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Anika Tabassum','anika@example.com',  'Pickup from Mirpur',       'Is it possible to schedule a pickup from Mirpur-1 after 6 PM?', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Rafiq Islam',   'rafiq@example.com',  'Thanks!',                  'My parcel arrived in just one day. Great service, keep it up.', 1, DATE_SUB(NOW(), INTERVAL 6 DAY));
