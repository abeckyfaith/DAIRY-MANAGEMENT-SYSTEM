<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Add animal_type column if not exists
$conn->query("ALTER TABLE animals ADD COLUMN animal_type VARCHAR(20) DEFAULT 'Cow' AFTER tag_number");

// Update existing animals to have proper tag numbers with prefixes
$conn->query("UPDATE animals SET tag_number = CONCAT('CW-', LPAD(id, 3, '0')) WHERE animal_type = 'Cow' OR animal_type = '' OR animal_type IS NULL");

echo "Database updated!";
$conn->close();