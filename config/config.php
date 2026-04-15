<?php
// Simple .env loader
if (file_exists(__DIR__ . "/../.env")) {
    $lines = file(__DIR__ . "/../.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), "#") === 0) continue;
        list($name, $value) = explode("=", $line, 2);
        define(trim($name), trim($value));
    }
}

// Fallback to defaults if not defined
if (!defined("DB_HOST")) define("DB_HOST", "localhost");
if (!defined("DB_USER")) define("DB_USER", "root");
if (!defined("DB_PASS")) define("DB_PASS", "");
if (!defined("DB_NAME")) define("DB_NAME", "dairy_management");
if (!defined("APP_NAME")) define("APP_NAME", "Dairy Management System");
if (!defined("BASE_URL")) define("BASE_URL", "http://localhost/dairy_management/");
if (!defined("CURRENCY")) define("CURRENCY", "UG Shillings ");

// Error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
