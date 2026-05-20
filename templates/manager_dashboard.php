<?php
$title = "Manager Dashboard";
require_once "includes/auth.php";
require_once "includes/rbac.php";
require_once "templates/partials/header.php";

$pdo = get_pdo_connection();
$role_level = get_role_level();
$user = get_current_user_info();
$role = get_user_role();

// Require manager or higher access
require_role('manager');
?>
<div class="welcome-banner manager fade-in">
    <div>
        <h2 class="mb-3 text-white">Welcome, <?php echo htmlspecialchars($user['full_name'] ?? $_SESSION['username']); ?></h2>
        <div class="d-flex align-items-center">
            <span class="banner-badge">MANAGER DASHBOARD</span>
            <span class="opacity-75 text-white">- Dairy Management System</span>
        </div>
    </div>
</div>

<div class="row fade-in">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 text-secondary fw-bold">Quick Overview</h5>
            </div>
            <div class="card-body p-0">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary"><i class="fas fa-tachometer-alt"></i></div>
                        <div class="stat-info"><h3 id="totalAnimals">-</h3><p>Total Animals</p></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon success"><i class="fas fa-glass-cheers"></i></div>
                        <div class="stat-info"><h3 id="todayMilk">-</h3><p>Today's Milk (L)</p></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon warning"><i class="fas fa-calendar-alt"></i></div>
                        <div class="stat-info"><h3 id="expectedCalvings">-</h3><p>Expected Calvings</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 text-secondary fw-bold">Today's Activities</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="index.php?page=record_milk" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Record Milk Production</h6>
                            <small class="text-muted">Morning &amp; Evening</small>
                        </div>
                    </a>
                    <a href="index.php?page=health_check" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Health Checks</h6>
                            <small class="text-muted">Veterinary visits</small>
                        </div>
                    </a>
                    <a href="index.php?page=feed" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Feed Management</h6>
                            <small class="text-muted">Inventory &amp; rations</small>
                        </div>
                    </a>
                    <a href="index.php?page=reproduction" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Breeding</h6>
                            <small class="text-muted">Inseminations &amp; pregnancies</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 text-secondary fw-bold">Quick Actions</h5>
            </div>
            <div class="card-body p-0">
                <div class="d-grid gap-2">
                    <a href="index.php?page=animals" class="btn btn-outline-primary">
                        <i class="fas fa-cow me-2"></i> Manage Animals
                    </a>
                    <a href="index.php?page=finance" class="btn btn-outline-primary">
                        <i class="fas fa-wallet me-2"></i> Financial Overview
                    </a>
                    <a href="index.php?page=inventory" class="btn btn-outline-primary">
                        <i class="fas fa-boxes me-2"></i> Inventory Management
                    </a>
                    <a href="index.php?page=dairy_shop" class="btn btn-outline-primary">
                        <i class="fas fa-store me-2"></i> Dairy Shop
                    </a>
                    <a href="index.php?page=settings" class="btn btn-outline-primary">
                        <i class="fas fa-cog me-2"></i> System Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4 fade-in">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 text-secondary fw-bold">Recent Farm Activities</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">User</th>
                                <th>Activity</th>
                                <th class="text-end pe-4">Date/Time</th>
                            </tr>
                        </thead>
                        <tbody id="recentActivities">
                            <!-- Activities will be loaded here via AJAX or PHP -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 text-secondary fw-bold">Alerts &amp; Notifications</h5>
            </div>
            <div class="card-body p-0">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No critical alerts at the moment.
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Last updated: <span id="lastUpdated">just now</span>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "templates/partials/footer.php"; ?>