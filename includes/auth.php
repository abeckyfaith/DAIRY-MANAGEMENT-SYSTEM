<?php
// Authentication and authorization functions using PDO
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/database.php";

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION["user_id"]);
}

// Check if user has specific role
function has_role($role_name) {
    if (!is_logged_in()) return false;

    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare("SELECT r.name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $row = $stmt->fetch();

    return $row && $row["name"] === $role_name;
}

// Check if user is admin (Owner role)
function is_admin() {
    return has_role("Owner");
}

// Get current user role name
function get_user_role() {
    if (!is_logged_in()) return null;

    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare("SELECT r.name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $row = $stmt->fetch();

    return $row ? $row["name"] : null;
}

// Require login
function require_login() {
    if (!is_logged_in()) {
        $_SESSION["redirect_url"] = $_SERVER["REQUEST_URI"];
        redirect("login");
    }
}

// Require specific role
function require_role($role_name) {
    require_login();
    if (!has_role($role_name)) {
        $_SESSION["error"] = "Access denied. You need \"$role_name\" role to access this page.";
        redirect("dashboard");
    }
}

// Login function
function login($username, $password) {
    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare("SELECT id, username, password, full_name, role_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["role_id"] = $user["role_id"];

        // Log successful login
        log_activity("User logged in: {$user["username"]}");

        return true;
    }

    return false;
}

// Logout function
function logout() {
    if (isset($_SESSION["username"])) {
        log_activity("User logged out: {$_SESSION["username"]}");
    }
    session_destroy();
    redirect("login");
}

// Get current user info
function get_current_user_info() {
    if (!is_logged_in()) return null;

    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    return $stmt->fetch();
}

// Log user activity
function log_activity($message) {
    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity, ip_address, created_at) VALUES (?, ?, ?, NOW())");
    $ip_address = $_SERVER["REMOTE_ADDR"];
    $stmt->execute([$_SESSION["user_id"] ?? null, $message, $ip_address]);
}

// CSRF Protection
function generate_csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function verify_csrf_token($token) {
    return isset($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $token);
}

function csrf_input() {
    return "<input type='hidden' name='csrf_token' value='" . generate_csrf_token() . "'>";
}
?>
