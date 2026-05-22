<?php
require_once 'config/config.php';
require_once 'includes/database.php';

header('Content-Type: text/plain');
echo "=== Database Setup ===\n\n";

$schema = '
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    role_id INT,
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE IF NOT EXISTS breeds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_number VARCHAR(50) NOT NULL UNIQUE,
    breed_id INT,
    birth_date DATE,
    gender ENUM("Male", "Female") NOT NULL,
    weight DECIMAL(10, 2),
    status ENUM("Active", "Sold", "Died", "Culled") DEFAULT "Active",
    parent_sire_id INT,
    parent_dam_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (breed_id) REFERENCES breeds(id),
    FOREIGN KEY (parent_sire_id) REFERENCES animals(id),
    FOREIGN KEY (parent_dam_id) REFERENCES animals(id)
);

CREATE TABLE IF NOT EXISTS milk_production (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT,
    session ENUM("Morning", "Afternoon", "Evening") NOT NULL,
    amount_liters DECIMAL(10, 2) NOT NULL,
    fat_percentage DECIMAL(4, 2),
    protein_percentage DECIMAL(4, 2),
    somatic_cell_count INT,
    recording_date DATE NOT NULL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    recorded_by INT,
    FOREIGN KEY (animal_id) REFERENCES animals(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS veterinary_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_date DATE NOT NULL,
    veterinarian_id INT,
    purpose TEXT,
    notes TEXT,
    FOREIGN KEY (veterinarian_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS treatments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT,
    treatment_date DATE NOT NULL,
    diagnosis TEXT,
    medication VARCHAR(255),
    dosage VARCHAR(100),
    withdrawal_period_days INT,
    vet_visit_id INT,
    FOREIGN KEY (animal_id) REFERENCES animals(id),
    FOREIGN KEY (vet_visit_id) REFERENCES veterinary_visits(id)
);

CREATE TABLE IF NOT EXISTS vaccinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT,
    vaccine_name VARCHAR(100) NOT NULL,
    vaccination_date DATE NOT NULL,
    next_due_date DATE,
    FOREIGN KEY (animal_id) REFERENCES animals(id)
);

CREATE TABLE IF NOT EXISTS inseminations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT,
    insemination_date DATE NOT NULL,
    type ENUM("AI", "Natural") NOT NULL,
    sire_details VARCHAR(255),
    performed_by INT,
    FOREIGN KEY (animal_id) REFERENCES animals(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS pregnancies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT,
    insemination_id INT,
    confirmation_date DATE NOT NULL,
    expected_calving_date DATE,
    status ENUM("Confirmed", "Miscarried", "Calved") DEFAULT "Confirmed",
    FOREIGN KEY (animal_id) REFERENCES animals(id),
    FOREIGN KEY (insemination_id) REFERENCES inseminations(id)
);

CREATE TABLE IF NOT EXISTS calvings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregnancy_id INT,
    calving_date DATE NOT NULL,
    offspring_tag_number VARCHAR(50),
    ease_of_calving ENUM("Easy", "Normal", "Difficult", "Assisted") DEFAULT "Normal",
    notes TEXT,
    FOREIGN KEY (pregnancy_id) REFERENCES pregnancies(id)
);

CREATE TABLE IF NOT EXISTS feed_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feed_name VARCHAR(100) NOT NULL,
    quantity_kg DECIMAL(10, 2) NOT NULL,
    unit_cost DECIMAL(10, 2),
    supplier VARCHAR(255),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS animal_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS rations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT,
    feed_id INT,
    amount_kg DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (group_id) REFERENCES animal_groups(id),
    FOREIGN KEY (feed_id) REFERENCES feed_inventory(id)
);

CREATE TABLE IF NOT EXISTS income (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM("Milk Sales", "Animal Sales", "Other") NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    transaction_date DATE NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM("Feed", "Veterinary", "Labor", "Equipment", "Other") NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    transaction_date DATE NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    purchase_date DATE,
    last_maintenance DATE,
    next_maintenance DATE,
    status ENUM("Functional", "Under Repair", "Broken", "Retired") DEFAULT "Functional"
);

CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    milk_rate_per_liter DECIMAL(10,2),
    payment_terms VARCHAR(100),
    status ENUM("Active", "Inactive") DEFAULT "Active",
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    category_id INT,
    unit VARCHAR(50) DEFAULT "Liter",
    price_per_unit DECIMAL(10,2) NOT NULL,
    stock_quantity DECIMAL(10,2) DEFAULT 0,
    low_stock_threshold DECIMAL(10,2) DEFAULT 10,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
);

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(255),
    customer_phone VARCHAR(20),
    invoice_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM("Paid", "Pending", "Partial") DEFAULT "Paid",
    payment_method ENUM("Cash", "Mobile Money", "Bank Transfer") DEFAULT "Cash",
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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

CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date DATE NOT NULL,
    total_sales DECIMAL(10,2) NOT NULL,
    total_items INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity TEXT NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS stock_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    alert_type ENUM("Low Stock", "Out of Stock") NOT NULL,
    alert_date DATE NOT NULL,
    is_resolved TINYINT(1) DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS health_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    temperature DECIMAL(4,1),
    heart_rate INT,
    respiratory_rate INT,
    condition_score INT,
    notes TEXT,
    recorded_by INT,
    recording_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS shop_sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_sales DECIMAL(10,2) NOT NULL,
    sale_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
';

try {
    $pdo = get_pdo_connection();
    echo "Connected to database.\n\n";

    // Run schema
    $statements = explode(';', $schema);
    $count = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt)) continue;
        try {
            $pdo->exec($stmt);
            $count++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "  Error: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Schema: $count statements processed.\n";

    // Show tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n\n";

    // Breeds
    if ($pdo->query("SELECT COUNT(*) FROM breeds")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO breeds (name) VALUES ('Friesian'),('Jersey'),('Ayrshire'),('Holstein'),('Guernsey'),('Brown Swiss'),('Simmental'),('Local Zebu'),('Crossbreed'),('Other')");
        echo "Seeded breeds.\n";
    }

    // Categories
    if ($pdo->query("SELECT COUNT(*) FROM product_categories")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO product_categories (category_name) VALUES ('Milk'),('Yogurt'),('Cheese'),('Butter'),('Cream'),('Other Dairy')");
        echo "Seeded categories.\n";
    }

    // Products
    if ($pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO products (product_name, category_id, unit, price_per_unit) VALUES ('Fresh Milk (1L)',1,'Liter',5000),('Yogurt (500ml)',2,'Piece',3000),('Local Cheese (250g)',3,'Piece',8000),('Butter (200g)',4,'Piece',6000),('Cream (250ml)',5,'Piece',5000)");
        echo "Seeded products.\n";
    }

    // Users
    if ($pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() == 0) {
        $pdo->exec("INSERT IGNORE INTO roles (name) VALUES ('Owner'),('Veterinarian'),('Worker'),('Staff')");
        $pdo->prepare("INSERT INTO users (username,password,full_name,role_id) VALUES (?,?,'Administrator',(SELECT id FROM roles WHERE name='Owner'))")->execute(['admin', password_hash('admin123',PASSWORD_DEFAULT)]);
        $pdo->prepare("INSERT INTO users (username,password,full_name,role_id) VALUES (?,?,'Staff Member',(SELECT id FROM roles WHERE name='Staff'))")->execute(['staff', password_hash('staff123',PASSWORD_DEFAULT)]);
        $pdo->prepare("INSERT INTO users (username,password,full_name,role_id) VALUES (?,?,'Worker',(SELECT id FROM roles WHERE name='Worker'))")->execute(['worker', password_hash('admin123',PASSWORD_DEFAULT)]);
        echo "Seeded users: admin/admin123, staff/staff123, worker/admin123.\n";
    }

    echo "\n=== Setup Complete ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
