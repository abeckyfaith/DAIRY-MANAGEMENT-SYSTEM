<?php
$current_page = $_GET['page'] ?? 'dashboard';
?>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
    <button class="sidebar-close" onclick="closeSidebar()">×</button>
<div class="sidebar-header">
    <img src="assets/images/animal (1).png" alt="Cow Logo" class="sidebar-logo">
    <h3>Dairy MS</h3>
</div>
    <nav class="sidebar-nav">
        <?php if (can_access_page('dashboard')): ?>
        <a href="index.php?page=dashboard" class="nav-item <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <?php endif; ?>
        
        <?php if (can_access_page('worker_dashboard')): ?>
        <a href="index.php?page=worker_dashboard" class="nav-item <?php echo $current_page === 'worker_dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-user-hard-hat"></i> Worker Panel
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

        <?php if (can_access_page('feed')): ?>
        <a href="index.php?page=feed" class="nav-item <?php echo in_array($current_page, ['feed', 'add_feed']) ? 'active' : ''; ?>">
            <i class="fas fa-leaf"></i> Feed
        </a>
        <?php endif; ?>

        <?php if (can_access_page('finance')): ?>
        <a href="index.php?page=finance" class="nav-item <?php echo in_array($current_page, ['finance', 'add_income', 'add_expense']) ? 'active' : ''; ?>">
            <i class="fas fa-wallet"></i> Finance
        </a>
        <?php endif; ?>

        <?php if (can_access_page('inventory')): ?>
        <a href="index.php?page=inventory" class="nav-item <?php echo $current_page === 'inventory' ? 'active' : ''; ?>">
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
