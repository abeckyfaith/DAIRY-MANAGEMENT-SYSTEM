<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check if connection successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create manager user (role_id 4 for farm manager)
$manager_hash = password_hash('manager123', PASSWORD_DEFAULT);
$manager_result = $conn->query("INSERT IGNORE INTO users (username, password, full_name, role_id) VALUES ('manager', '$manager_hash', 'Farm Manager', 4)");

// Create worker user (role_id 3 for worker)
$worker_hash = password_hash('worker123', PASSWORD_DEFAULT);
$worker_result = $conn->query("INSERT IGNORE INTO users (username, password, full_name, role_id) VALUES ('worker', '$worker_hash', 'Farm Worker', 3)");

if ($manager_result && $worker_result) {
    echo "Manager and Worker users created successfully!";
    echo "<br>";
    echo "Manager login: manager / manager123";
    echo "<br>";
    echo "Worker login: worker / worker123";
} else {
    echo "Error creating users: " . $conn->error;
}

$conn->close();
?>