<?php
$current_page = $_GET['page'] ?? 'dashboard';
?>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
    <button class="sidebar-close" onclick="closeSidebar()">×</button>
    <div class="sidebar-header">
        <svg viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="25" cy="28" rx="15" ry="12" fill="white" stroke="white" stroke-width="2"/>
            <ellipse cx="12" cy="18" rx="5" ry="3" fill="white" transform="rotate(-20 12 18)"/>
            <ellipse cx="38" cy="18" rx="5" ry="3" fill="white" transform="rotate(20 38 18)"/>
            <path d="M13 15 Q10 5 16 10" stroke="white" stroke-width="2" fill="none"/>
            <path d="M37 15 Q40 5 34 10" stroke="white" stroke-width="2" fill="none"/>
            <circle cx="20" cy="25" r="3" fill="#764ba2" opacity="0.5"/>
            <circle cx="32" cy="29" r="2" fill="#764ba2" opacity="0.5"/>
            <circle cx="19" cy="24" r="2" fill="white"/>
            <circle cx="31" cy="24" r="2" fill="white"/>
            <circle cx="19.5" cy="24.5" r="1" fill="#333"/>
            <circle cx="31.5" cy="24.5" r="1" fill="#333"/>
            <ellipse cx="25" cy="33" rx="6" ry="4" fill="#f8b4d9"/>
            <circle cx="23" cy="33" r="1" fill="#764ba2"/>
            <circle cx="27" cy="33" r="1" fill="#764ba2"/>
        </svg>
        <h3>Dairy MS</h3>
    </div>
    <nav class="sidebar-nav">
        <?php if (can_access_page('dashboard')): ?>
        <a href="index.php?page=dashboard" class="nav-item <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <?php endif; ?>

        <?php if (can_access_page('animals')): ?>
        <a href="index.php?page=animals" class="nav-item <?php echo in_array($current_page, ['animals', 'add_animal']) ? 'active' : ''; ?>">
            <i class="fas fa-cow"></i> Animals
        </a>
        <?php endif; ?>

        <?php if (can_access_page('milk_production')): ?>
        <a href="index.php?page=milk_production" class="nav-item <?php echo in_array($current_page, ['milk_production', 'record_milk']) ? 'active' : ''; ?>">
            <i class="fas fa-glass-water"></i> Milk Records
        </a>
        <?php endif; ?>

        <?php if (can_access_page('health')): ?>
        <a href="index.php?page=health" class="nav-item <?php echo in_array($current_page, ['health', 'health_check']) ? 'active' : ''; ?>">
            <i class="fas fa-stethoscope"></i> Health
        </a>
        <?php endif; ?>

        <?php if (can_access_page('dairy_shop')): ?>
        <a href="index.php?page=dairy_shop" class="nav-item <?php echo $current_page === 'dairy_shop' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-basket"></i> Dairy Shop
        </a>
        <?php endif; ?>

        <?php if (can_access_page('finance')): ?>
        <a href="index.php?page=finance" class="nav-item <?php echo in_array($current_page, ['finance', 'add_income', 'add_expense']) ? 'active' : ''; ?>">
            <i class="fas fa-wallet"></i> Finance
        </a>
        <?php endif; ?>

        <?php if (can_access_page('inventory')): ?>
        <a href="index.php?page=inventory" class="nav-item <?php echo in_array($current_page, ['inventory', 'feed', 'add_feed']) ? 'active' : ''; ?>">
            <i class="fas fa-cubes"></i> Inventory
        </a>
        <?php endif; ?>

        <?php if (can_access_page('staff')): ?>
        <a href="index.php?page=staff" class="nav-item <?php echo $current_page === 'staff' ? 'active' : ''; ?>">
            <i class="fas fa-users-cog"></i> Users
        </a>
        <?php endif; ?>

        <a href="index.php?page=logout" class="nav-item mt-auto">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</aside>
