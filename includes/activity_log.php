<?php
// Activity log initialization using PDO
require_once __DIR__ . "/database.php";

function create_activity_log_table() {
    $pdo = get_pdo_connection();
    $sql = "CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        activity VARCHAR(500) NOT NULL,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);
}

// Initialize table
create_activity_log_table();
?>
