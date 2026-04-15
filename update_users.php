<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Create Farm Manager user
$manager_hash = password_hash('manager123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (username, password, full_name, role_id) VALUES ('manager', '$manager_hash', 'Sarah Manager', 2)");

// Remove worker if exists
$conn->query("DELETE FROM users WHERE username = 'worker'");

echo "Users updated!";
$conn->close();