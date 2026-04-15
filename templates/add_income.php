<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = sanitize_input($_POST['category']);
    $amount = floatval($_POST['amount']);
    $transaction_date = $_POST['transaction_date'];
    $description = sanitize_input($_POST['description'] ?? '');
    
    $conn = get_db_connection();
    $stmt = $conn->prepare("INSERT INTO income (category, amount, transaction_date, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdds", $category, $amount, $transaction_date, $description);
    
    if ($stmt->execute()) {
        log_activity("Added income: $category - $$amount");
        $_SESSION['success'] = "Income added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add income.";
    }
    
    $stmt->close();
    $conn->close();
}

redirect('finance');
?>