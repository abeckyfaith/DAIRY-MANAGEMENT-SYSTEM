-- Dairy Shop Management Tables for Dairy Management System

-- Product Categories
CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Suppliers / Milk Suppliers
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    milk_rate_per_liter DECIMAL(10,2),
    payment_terms VARCHAR(100),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products (Dairy Items)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    category_id INT,
    unit VARCHAR(50) DEFAULT 'Liter',
    price_per_unit DECIMAL(10,2) NOT NULL,
    stock_quantity DECIMAL(10,2) DEFAULT 0,
    low_stock_threshold DECIMAL(10,2) DEFAULT 10,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
);

-- Invoices / Bills
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(255),
    customer_phone VARCHAR(20),
    invoice_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Paid', 'Pending', 'Partial') DEFAULT 'Paid',
    payment_method ENUM('Cash', 'Mobile Money', 'Bank Transfer') DEFAULT 'Cash',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Invoice Items
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Sales Records (for reports)
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date DATE NOT NULL,
    total_sales DECIMAL(10,2) NOT NULL,
    total_items INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Low Stock Alerts
CREATE TABLE IF NOT EXISTS stock_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    alert_type ENUM('Low Stock', 'Out of Stock') NOT NULL,
    alert_date DATE NOT NULL,
    is_resolved TINYINT(1) DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Sample Categories
INSERT IGNORE INTO product_categories (category_name, category_description) VALUES 
('Fresh Milk', 'Raw and pasteurized fresh milk'),
('Yogurt', 'Plain and flavored yogurts'),
('Cheese', 'Various cheese products'),
('Butter', 'Dairy butter products'),
('Ice Cream', 'Frozen dairy desserts'),
('Ghee', 'Clarified butter');

-- Sample Suppliers
INSERT IGNORE INTO suppliers (supplier_name, contact_person, phone, email, address, milk_rate_per_liter, payment_terms) VALUES 
('Mukama Dairy Farm', 'John Mukama', '+256772123456', 'john@mukama.com', 'Kampala Road, Kira', 1500, 'Weekly'),
('Green Valley Milk', 'Sarah Nanteza', '+256701234567', 'sarah@greenvalley.com', 'Mukono', 1450, 'Monthly'),
('Best Milk Suppliers', 'Robert Okello', '+256782345678', 'robert@bestmilk.com', 'Entebbe Road', 1600, 'Weekly');

-- Sample Products
INSERT IGNORE INTO products (product_name, category_id, unit, price_per_unit, stock_quantity, low_stock_threshold) VALUES 
('Fresh Milk (1L)', 1, 'Liter', 2500, 50, 10),
('Fresh Milk (500ml)', 1, 'Pack', 1500, 100, 15),
('Yogurt Plain', 2, 'Pack', 2000, 30, 5),
('Yogurt Flavored', 2, 'Pack', 2500, 25, 5),
('Cheddar Cheese', 3, 'Kilogram', 15000, 10, 3),
('Butter (250g)', 4, 'Pack', 8000, 20, 5),
('Ghee (500ml)', 6, 'Pack', 12000, 15, 3),
('Ice Cream Vanilla', 5, 'Liter', 10000, 20, 5);

-- Sample Invoices
INSERT IGNORE INTO invoices (invoice_number, customer_name, customer_phone, invoice_date, total_amount, payment_status) VALUES 
('INV-20260320-1001', 'James Kato', '0772123456', '2026-03-20', 7000, 'Paid'),
('INV-20260321-1002', 'Mary Nakato', '0782567890', '2026-03-21', 4500, 'Paid'),
('INV-20260322-1003', 'Peter Opiyo', '0792123456', '2026-03-22', 12000, 'Paid');

echo "Database tables created and sample data inserted successfully!";