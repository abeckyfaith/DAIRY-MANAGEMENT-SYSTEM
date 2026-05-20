<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/rbac.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if (login($username, $password)) {
        $_SESSION['success'] = "Welcome back, " . $_SESSION['full_name'] . "!";
        
        // Redirect to intended page or dashboard
        if (isset($_SESSION['redirect_url'])) {
            $redirect_url = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']);
            header("Location: $redirect_url");
        } else {
            $role_level = get_role_level();
            if ($role_level == 2) { 
                redirect('worker_dashboard'); 
            } elseif ($role_level == 4) {
                redirect('manager_dashboard');
            } else {
                redirect('dashboard');
            }
        }
    } else {
        $_SESSION['error'] = "Invalid username or password. Please try again.";
        redirect('login');
    }
} else {
    redirect('login');
}
?>
