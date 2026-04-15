<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $purchase_date = $_POST['purchase_date'] ?? null;
    $status = sanitize_input($_POST['status']);
    $next_maintenance = $_POST['next_maintenance'] ?? null;
    
    $conn = get_db_connection();
    $stmt = $conn->prepare("INSERT INTO equipment (name, purchase_date, status, next_maintenance) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $purchase_date, $status, $next_maintenance);
    
    if ($stmt->execute()) {
        log_activity("Added equipment: $name");
        $_SESSION['success'] = "Equipment added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add equipment.";
    }
    
    $stmt->close();
    $conn->close();
}

redirect('inventory');
?>