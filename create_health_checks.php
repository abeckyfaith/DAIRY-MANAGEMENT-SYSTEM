<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Create health_checks table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS health_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    temperature DECIMAL(4,1),
    heart_rate INT,
    respiratory_rate INT,
    condition_score VARCHAR(20),
    notes TEXT,
    recorded_by INT,
    recording_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
)");

echo "health_checks table created!";
$conn->close();