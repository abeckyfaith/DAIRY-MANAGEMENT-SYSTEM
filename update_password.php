<?php
require_once 'config/config.php';

$new_hash = password_hash('admin123', PASSWORD_DEFAULT);
echo "New hash: $new_hash\n";

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $new_hash);
$stmt->execute();

$result = $conn->query("SELECT password FROM users WHERE username = 'admin'");
$row = $result->fetch_assoc();
echo "Stored: " . $row['password'] . "\n";
echo "Verify: " . (password_verify('admin123', $row['password']) ? 'TRUE' : 'FALSE') . "\n";
$conn->close();