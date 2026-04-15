<?php
// Role-based access control functions

// Get role level (higher = more access)
function get_role_level() {
    if (!is_logged_in()) return 0;

    $role = get_user_role();
    if (!$role) return 0;

    switch (strtolower($role)) {
        case 'owner': 
        case 'admin':
            return 5;
        case 'farm manager':
        case 'manager':
            return 4;
        case 'staff':
        case 'veterinarian':
            return 3;
        case 'worker':
            return 2;
        default:
            return 0;
    }
}

// Check if user can access a specific page
function can_access_page($page) {
    $role_level = get_role_level();

    // Public pages (no login required)
    $public_pages = ['login', 'login_process', 'logout'];
    if (in_array($page, $public_pages)) return true;

    // Page access requirements (minimum role level needed)
    $page_access = [
        'dashboard' => 3,
        'dairy_shop' => 2,
        'milk_production' => 3,
        'record_milk' => 3,
        'health' => 3,
        'health_check' => 3,
        'reproduction' => 3,
        'add_insemination' => 3,
        'profile' => 2,
        
        // Admin/Owner only (Level 5)
        'finance' => 5,
        'add_income' => 5,
        'add_expense' => 5,
        'inventory' => 5,
        'add_equipment' => 5,
        'reports' => 5,
        'sales_report' => 5,
        'categories' => 5,
        'suppliers' => 5,
        'products' => 5,
        'sales' => 5,
        'animals' => 5,
        'add_animal' => 5,
        'staff' => 5,
        'settings' => 5,
        'feed' => 5,
        'add_feed' => 5,
        'activity_log' => 5,
    ];

    $required_level = $page_access[$page] ?? 1;
    
    // Explicitly restrict finance and inventory for Staff (Level 3) and below
    if ($role_level <= 3 && in_array($page, ['finance', 'inventory', 'add_income', 'add_expense', 'add_equipment', 'reports', 'sales_report', 'categories', 'suppliers', 'products', 'sales'])) {
        return false;
    }

    return $role_level >= $required_level;
}

// Redirect if no access to page
function require_page_access($page) {
    if (!can_access_page($page)) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        
        $role_level = get_role_level();
        if ($role_level == 2) {
            if ($page === 'dairy_shop') return; // Avoid loop
            redirect('dairy_shop');
        } elseif ($role_level >= 3) {
            if ($page === 'dashboard') return; // Avoid loop
            redirect('dashboard');
        } else {
            redirect('logout');
        }
        exit;
    }
}

// Helper functions for specific permissions
function can_delete() { return get_role_level() >= 5; }
function can_view_reports() { return get_role_level() >= 5; }
function can_access_finance() { return get_role_level() >= 5; }
function can_access_inventory() { return get_role_level() >= 5; }
function can_manage_users() { return get_role_level() >= 5; }
?>
