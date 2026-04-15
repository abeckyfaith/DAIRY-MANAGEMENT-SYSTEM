<?php
require_once "config/config.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
require_once "includes/rbac.php";

$page = isset($_GET["page"]) ? $_GET["page"] : "";

// If no page specified, go to dashboard (which will redirect to login if needed)
if (empty($page)) {
    redirect('dashboard');
}

// Route to appropriate page
if ($page === "login") {
    require_once "templates/login.php";
} elseif ($page === "login_process") {
    require_once "login_process.php";
} elseif ($page === "logout") {
    require_once "logout.php";
} else {
    // For all other pages, require login
    require_login();

    // Check if user has access to this page
    if (!can_access_page($page)) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        
        $role_level = get_role_level();
        if ($role_level == 2) {
            if ($page === 'dairy_shop') die("Access Loop: Worker cannot access Dairy Shop");
            redirect('dairy_shop');
        } elseif ($role_level >= 3) {
            if ($page === 'dashboard') die("Access Loop: User cannot access Dashboard");
            redirect('dashboard');
        } else {
            redirect('logout');
        }
    }

    // Map pages to templates
    $templates = [
        'dashboard' => 'templates/dashboard.php',
        'animals' => 'templates/animals.php',
        'add_animal' => 'templates/add_animal.php',
        'milk_production' => 'templates/milk_production.php',
        'record_milk' => 'templates/record_milk.php',
        'health' => 'templates/health.php',
        'health_check' => 'templates/health_check.php',
        'feed' => 'templates/feed.php',
        'add_feed' => 'templates/add_feed.php',
        'reports' => 'templates/reports.php',
        'settings' => 'templates/settings.php',
        'activity_log' => 'templates/activity_log.php',
        'reproduction' => 'templates/reproduction.php',
        'finance' => 'templates/finance.php',
        'inventory' => 'templates/inventory.php',
        'add_insemination' => 'templates/add_insemination.php',
        'add_income' => 'templates/add_income.php',
        'add_expense' => 'templates/add_expense.php',
        'add_equipment' => 'templates/add_equipment.php',
        'categories' => 'templates/categories.php',
        'suppliers' => 'templates/suppliers.php',
        'products' => 'templates/products.php',
        'sales' => 'templates/sales.php',
        'sales_report' => 'templates/sales_report.php',
        'staff' => 'templates/staff.php',
        'profile' => 'templates/profile.php',
        'dairy_shop' => 'templates/dairy_shop.php'
    ];

    if (isset($templates[$page]) && file_exists($templates[$page])) {
        require_once $templates[$page];
    } else {
        // Handle 404 - redirect to home based on role
        if (get_role_level() == 2) {
            redirect('dairy_shop');
        } else {
            redirect('dashboard');
        }
    }
}
?>
