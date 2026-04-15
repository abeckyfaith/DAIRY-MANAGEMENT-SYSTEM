<?php
$title = "Dashboard";
require_once "includes/auth.php";
require_once "includes/rbac.php";
require_once "templates/partials/header.php";

$pdo = get_pdo_connection();
$role_level = get_role_level();
$user = get_current_user_info();
$role = get_user_role();

// Determine color based on role for the banner
$role_class = 'staff';
if ($role_level == 2) $role_class = 'worker';
elseif ($role_level == 3) $role_class = 'staff';
elseif ($role_level >= 5) $role_class = 'admin';

?>

<div class="welcome-banner <?php echo $role_class; ?> fade-in">
    <div>
        <h2 class="mb-3 text-white">Welcome, <?php echo htmlspecialchars($user['full_name'] ?? $_SESSION['username']); ?></h2>
        <div class="d-flex align-items-center">
            <span class="banner-badge">
                <?php echo strtoupper($role); ?> DASHBOARD
            </span>
            <span class="opacity-75 text-white">- Dairy Management System</span>
        </div>
    </div>
</div>

<div class="quick-actions-card mb-5 fade-in">
    <div class="quick-actions-header">
        Quick Actions
    </div>
    <div class="quick-actions-body">
        <?php if (can_access_page('dairy_shop')): ?>
        <a href="index.php?page=dairy_shop" class="quick-action-item">
            <div class="icon-box">
                <i class="fas fa-shopping-basket"></i>
            </div>
            <span>Dairy Shop</span>
        </a>
        <?php endif; ?>

        <?php if (can_access_page('milk_production')): ?>
        <a href="index.php?page=milk_production" class="quick-action-item">
            <div class="icon-box">
                <i class="fas fa-glass-water"></i>
            </div>
            <span>Milk Production</span>
        </a>
        <?php endif; ?>

        <?php if (can_access_page('health')): ?>
        <a href="index.php?page=health" class="quick-action-item">
            <div class="icon-box">
                <i class="fas fa-stethoscope"></i>
            </div>
            <span>Health</span>
        </a>
        <?php endif; ?>

        <?php if (can_access_page('finance')): ?>
        <a href="index.php?page=finance" class="quick-action-item">
            <div class="icon-box">
                <i class="fas fa-wallet"></i>
            </div>
            <span>Financials</span>
        </a>
        <?php endif; ?>

        <?php if (can_access_page('inventory')): ?>
        <a href="index.php?page=inventory" class="quick-action-item">
            <div class="icon-box">
                <i class="fas fa-cubes"></i>
            </div>
            <span>Inventory</span>
        </a>
        <?php endif; ?>

        <?php if (can_access_page('animals')): ?>
        <a href="index.php?page=animals" class="quick-action-item">
            <div class="icon-box">
                <i class="fas fa-cow"></i>
            </div>
            <span>Animals</span>
        </a>
        <?php endif; ?>
        
        <?php if (can_access_page('staff')): ?>
        <a href="index.php?page=staff" class="quick-action-item">
            <div class="icon-box">
                <i class="fas fa-users-cog"></i>
            </div>
            <span>Staff Management</span>
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (can_manage_users() || can_view_reports()): ?>
<div class="row fade-in">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 text-secondary fw-bold">Recent System Activities</h5>
            </div>
            <div class="card-body p-0">
                <?php
                $activities = $pdo->query("SELECT al.*, u.full_name, r.name as role FROM activity_log al LEFT JOIN users u ON al.user_id = u.id LEFT JOIN roles r ON u.role_id = r.id ORDER BY al.created_at DESC LIMIT 5")->fetchAll();
                ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">User</th>
                                <th>Role</th>
                                <th>Activity</th>
                                <th class="text-end pe-4">Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td class="ps-4">
                                    <strong><?php echo htmlspecialchars($activity['full_name'] ?? 'System'); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary-light text-primary rounded-pill px-3">
                                        <?php echo htmlspecialchars($activity['role'] ?? 'System'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                <td class="text-end pe-4 text-muted">
                                    <?php echo date('d M Y, H:i', strtotime($activity['created_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once "templates/partials/footer.php"; ?>
