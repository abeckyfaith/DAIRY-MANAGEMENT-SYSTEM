<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Create staff user
$staff_hash = password_hash('staff123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (username, password, full_name, role_id) VALUES ('staff', '$staff_hash', 'John Staff', 2)");

// Create worker user
$worker_hash = password_hash('worker123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (username, password, full_name, role_id) VALUES ('worker', '$worker_hash', 'Mike Worker', 3)");

echo "Users created!";
$conn->close();