<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

echo "<h3>Creating Dairy Shop Tables...</h3>";

// Product Categories
$conn->query("CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "<p>✓ product_categories table created</p>";

// Suppliers
$conn->query("CREATE TABLE IF NOT EXISTS suppliers (
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
)");
echo "<p>✓ suppliers table created</p>";

// Products
$conn->query("CREATE TABLE IF NOT EXISTS products (
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
)");
echo "<p>✓ products table created</p>";

// Invoices
$conn->query("CREATE TABLE IF NOT EXISTS invoices (
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
)");
echo "<p>✓ invoices table created</p>";

// Invoice Items
$conn->query("CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
)");
echo "<p>✓ invoice_items table created</p>";

// Sales
$conn->query("CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date DATE NOT NULL,
    total_sales DECIMAL(10,2) NOT NULL,
    total_items INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "<p>✓ sales table created</p>";

// Stock Alerts
$conn->query("CREATE TABLE IF NOT EXISTS stock_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    alert_type ENUM('Low Stock', 'Out of Stock') NOT NULL,
    alert_date DATE NOT NULL,
    is_resolved TINYINT(1) DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id)
)");
echo "<p>✓ stock_alerts table created</p>";

echo "<h3>Inserting Sample Data...</h3>";

// Categories
$categories = [
    ['Fresh Milk', 'Raw and pasteurized fresh milk'],
    ['Yogurt', 'Plain and flavored yogurts'],
    ['Cheese', 'Various cheese products'],
    ['Butter', 'Dairy butter products'],
    ['Ice Cream', 'Frozen dairy desserts'],
    ['Ghee', 'Clarified butter']
];
foreach ($categories as $cat) {
    $conn->query("INSERT IGNORE INTO product_categories (category_name, category_description) VALUES ('{$cat[0]}', '{$cat[1]}')");
}
echo "<p>✓ Categories inserted</p>";

// Suppliers
$suppliers = [
    ['Mukama Dairy Farm', 'John Mukama', '+256772123456', 'john@mukama.com', 'Kampala Road, Kira', 1500, 'Weekly'],
    ['Green Valley Milk', 'Sarah Nanteza', '+256701234567', 'sarah@greenvalley.com', 'Mukono', 1450, 'Monthly'],
    ['Best Milk Suppliers', 'Robert Okello', '+256782345678', 'robert@bestmilk.com', 'Entebbe Road', 1600, 'Weekly']
];
foreach ($suppliers as $sup) {
    $conn->query("INSERT IGNORE INTO suppliers (supplier_name, contact_person, phone, email, address, milk_rate_per_liter, payment_terms) VALUES ('{$sup[0]}', '{$sup[1]}', '{$sup[2]}', '{$sup[3]}', '{$sup[4]}', {$sup[5]}, '{$sup[6]}')");
}
echo "<p>✓ Suppliers inserted</p>";

// Products
$products = [
    ['Fresh Milk (1L)', 1, 'Liter', 2500, 50],
    ['Fresh Milk (500ml)', 1, 'Pack', 1500, 100],
    ['Yogurt Plain', 2, 'Pack', 2000, 30],
    ['Yogurt Flavored', 2, 'Pack', 2500, 25],
    ['Cheddar Cheese', 3, 'Kilogram', 15000, 10],
    ['Butter (250g)', 4, 'Pack', 8000, 20],
    ['Ghee (500ml)', 6, 'Pack', 12000, 15],
    ['Ice Cream Vanilla', 5, 'Liter', 10000, 20]
];
foreach ($products as $prod) {
    $conn->query("INSERT IGNORE INTO products (product_name, category_id, unit, price_per_unit, stock_quantity, low_stock_threshold) VALUES ('{$prod[0]}', {$prod[1]}, '{$prod[2]}', {$prod[3]}, {$prod[4]}, 10)");
}
echo "<p>✓ Products inserted</p>";

// Invoices
$conn->query("INSERT IGNORE INTO invoices (invoice_number, customer_name, customer_phone, invoice_date, total_amount, payment_status) VALUES ('INV-20260320-1001', 'James Kato', '0772123456', '2026-03-20', 7000, 'Paid')");
$conn->query("INSERT IGNORE INTO invoices (invoice_number, customer_name, customer_phone, invoice_date, total_amount, payment_status) VALUES ('INV-20260321-1002', 'Mary Nakato', '0782567890', '2026-03-21', 4500, 'Paid')");
$conn->query("INSERT IGNORE INTO invoices (invoice_number, customer_name, customer_phone, invoice_date, total_amount, payment_status) VALUES ('INV-20260322-1003', 'Peter Opiyo', '0792123456', '2026-03-22', 12000, 'Paid')");
echo "<p>✓ Sample invoices inserted</p>";

$conn->close();

echo "<h3 style='color:green;'>All done! Tables created and sample data added.</h3>";
echo "<p><a href='index.php'>Go to Dashboard</a></p>";