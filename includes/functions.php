<?php
// Generic functions for the application
require_once __DIR__ . '/database.php';

// Database connection (Legacy MySQLi)
function get_db_connection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    return $conn;
}

// Redirect utility
function redirect($page) {
    header('Location: index.php?page=' . $page);
    exit();
}

// Format date
function format_date($date) {
    return date('d/m/Y', strtotime($date));
}

// Sanitize user input
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
