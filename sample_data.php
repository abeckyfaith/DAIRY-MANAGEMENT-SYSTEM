<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Insert breeds
$breeds = ['Holstein', 'Jersey', 'Guernsey', 'Ayrshire', 'Brown Swiss', 'Crossbred'];
foreach ($breeds as $breed) {
    $conn->query("INSERT IGNORE INTO breeds (name) VALUES ('$breed')");
}

// Insert animal groups
$groups = ['Milking Cows', 'Dry Cows', 'Heifers', 'Calves', 'Bulls'];
foreach ($groups as $group) {
    $conn->query("INSERT IGNORE INTO animal_groups (group_name) VALUES ('$group')");
}

// Insert sample animals
$animals = [
    ['TAG001', 1, '2020-03-15', 'Female', 450, 'Active'],
    ['TAG002', 1, '2019-08-22', 'Female', 480, 'Active'],
    ['TAG003', 2, '2021-05-10', 'Female', 380, 'Active'],
    ['TAG004', 3, '2022-01-20', 'Female', 420, 'Active'],
    ['TAG005', 1, '2018-11-05', 'Male', 600, 'Active'],
    ['TAG006', 2, '2023-02-14', 'Female', 350, 'Active'],
    ['TAG007', 4, '2022-09-30', 'Female', 400, 'Active'],
    ['TAG008', 1, '2021-07-18', 'Female', 460, 'Active'],
];

foreach ($animals as $animal) {
    $conn->query("INSERT IGNORE INTO animals (tag_number, breed_id, birth_date, gender, weight, status) 
                  VALUES ('{$animal[0]}', {$animal[1]}, '{$animal[2]}', '{$animal[3]}', {$animal[4]}, '{$animal[5]}')");
}

// Insert feed inventory
$feeds = [
    ['Alfalfa Hay', 500, 25.00, 'Green Valley Suppliers'],
    ['Corn Silage', 1200, 15.00, 'Farm Feed Co'],
    ['Concentrate Mix', 300, 35.00, 'Agri Products'],
    ['Wheat Bran', 200, 18.00, 'Grain Masters'],
    ['Soybean Meal', 150, 40.00, 'Protein Supplies'],
];

foreach ($feeds as $feed) {
    $conn->query("INSERT IGNORE INTO feed_inventory (feed_name, quantity_kg, unit_cost, supplier) 
                  VALUES ('{$feed[0]}', {$feed[1]}, {$feed[2]}, '{$feed[3]}')");
}

// Insert sample milk production for last 7 days
for ($i = 1; $i <= 7; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $conn->query("INSERT INTO milk_production (animal_id, session, amount_liters, fat_percentage, protein_percentage, recording_date, recorded_by) 
                  VALUES (1, 'Morning', ROUND(15 + RAND() * 5, 2), ROUND(3.5 + RAND() * 0.5, 2), ROUND(3.0 + RAND() * 0.3, 2), '$date', 1)");
    $conn->query("INSERT INTO milk_production (animal_id, session, amount_liters, fat_percentage, protein_percentage, recording_date, recorded_by) 
                  VALUES (1, 'Evening', ROUND(14 + RAND() * 4, 2), ROUND(3.5 + RAND() * 0.5, 2), ROUND(3.0 + RAND() * 0.3, 2), '$date', 1)");
    $conn->query("INSERT INTO milk_production (animal_id, session, amount_liters, fat_percentage, protein_percentage, recording_date, recorded_by) 
                  VALUES (2, 'Morning', ROUND(18 + RAND() * 6, 2), ROUND(3.8 + RAND() * 0.4, 2), ROUND(3.2 + RAND() * 0.3, 2), '$date', 1)");
    $conn->query("INSERT INTO milk_production (animal_id, session, amount_liters, fat_percentage, protein_percentage, recording_date, recorded_by) 
                  VALUES (2, 'Evening', ROUND(17 + RAND() * 5, 2), ROUND(3.8 + RAND() * 0.4, 2), ROUND(3.2 + RAND() * 0.3, 2), '$date', 1)");
}

// Insert sample expenses
$expenses = [
    ['Feed', 2500.00, '2026-03-01', 'Monthly feed purchase'],
    ['Veterinary', 500.00, '2026-03-05', 'Vaccination program'],
    ['Labor', 3000.00, '2026-03-10', 'Staff wages'],
    ['Equipment', 200.00, '2026-03-12', 'Maintenance supplies'],
    ['Feed', 2800.00, '2026-03-15', 'Feed restock'],
];

foreach ($expenses as $exp) {
    $conn->query("INSERT INTO expenses (category, amount, transaction_date, description) 
                  VALUES ('{$exp[0]}', {$exp[1]}, '{$exp[2]}', '{$exp[3]}')");
}

// Insert sample income
$income = [
    ['Milk Sales', 4500.00, '2026-03-01', 'Milk delivery to cooperative'],
    ['Milk Sales', 4200.00, '2026-03-08', 'Milk delivery to cooperative'],
    ['Milk Sales', 4800.00, '2026-03-15', 'Milk delivery to cooperative'],
];

foreach ($income as $inc) {
    $conn->query("INSERT INTO income (category, amount, transaction_date, description) 
                  VALUES ('{$inc[0]}', {$inc[1]}, '{$inc[2]}', '{$inc[3]}')");
}

// ==========================================
// DAIRY SHOP SAMPLE DATA
// ==========================================

// Insert product categories
$categories = [
    ['Fresh Milk', 'Raw and pasteurized fresh milk'],
    ['Yogurt', 'Plain and flavored yogurts'],
    ['Cheese', 'Various cheese products'],
    ['Butter', 'Dairy butter products'],
    ['Ice Cream', 'Frozen dairy desserts'],
    ['Ghee', 'Clarified butter'],
];

foreach ($categories as $cat) {
    $conn->query("INSERT IGNORE INTO product_categories (category_name, category_description) VALUES ('{$cat[0]}', '{$cat[1]}')");
}

// Insert suppliers
$suppliers = [
    ['Mukama Dairy Farm', 'John Mukama', '+256 772 123456', 'john@mukama.com', 'Kampala Road, Kira', 1500, 'Weekly'],
    ['Green Valley Milk', 'Sarah Nanteza', '+256 701 234567', 'sarah@greenvalley.com', 'Mukono', 1450, 'Monthly'],
    ['Best Milk Suppliers', 'Robert Okello', '+256 782 345678', 'robert@bestmilk.com', 'Entebbe Road', 1600, 'Weekly'],
];

foreach ($suppliers as $sup) {
    $conn->query("INSERT IGNORE INTO suppliers (supplier_name, contact_person, phone, email, address, milk_rate_per_liter, payment_terms) 
                  VALUES ('{$sup[0]}', '{$sup[1]}', '{$sup[2]}', '{$sup[3]}', '{$sup[4]}', {$sup[5]}, '{$sup[6]}')");
}

// Insert products
$products = [
    ['Fresh Milk (1L)', 1, 'Liter', 2500, 50],
    ['Fresh Milk (500ml)', 1, 'Pack', 1500, 100],
    ['Yogurt Plain', 2, 'Pack', 2000, 30],
    ['Yogurt Flavored', 2, 'Pack', 2500, 25],
    ['Cheddar Cheese', 3, 'Kilogram', 15000, 10],
    ['Butter (250g)', 4, 'Pack', 8000, 20],
    ['Ghee (500ml)', 6, 'Pack', 12000, 15],
    ['Ice Cream Vanilla', 5, 'Liter', 10000, 20],
];

foreach ($products as $prod) {
    $conn->query("INSERT IGNORE INTO products (product_name, category_id, unit, price_per_unit, stock_quantity, low_stock_threshold) 
                  VALUES ('{$prod[0]}', {$prod[1]}, '{$prod[2]}', {$prod[3]}, {$prod[4]}, 10)");
}

// Insert sample invoices
$conn->query("INSERT IGNORE INTO invoices (invoice_number, customer_name, customer_phone, invoice_date, total_amount, payment_status) 
              VALUES ('INV-20260320-1001', 'James Kato', '0772123456', '2026-03-20', 7000, 'Paid')");
$conn->query("INSERT IGNORE INTO invoices (invoice_number, customer_name, customer_phone, invoice_date, total_amount, payment_status) 
              VALUES ('INV-20260321-1002', 'Mary Nakato', '0782567890', '2026-03-21', 4500, 'Paid')");
$conn->query("INSERT IGNORE INTO invoices (invoice_number, customer_name, customer_phone, invoice_date, total_amount, payment_status) 
              VALUES ('INV-20260322-1003', 'Peter Opiyo', '0792123456', '2026-03-22', 12000, 'Paid')");

echo "Sample data inserted successfully!";
$conn->close();