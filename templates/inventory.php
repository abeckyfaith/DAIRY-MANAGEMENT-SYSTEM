<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/rbac.php';

require_login();

// BLOCK STAFF - Only Manager+ can access
if (get_role_level() < 4) {
    $_SESSION['error'] = "You don't have permission to access this page.";
    if (get_role_level() == 2) {
        header("Location: index.php?page=dairy_shop");
    } else {
        header("Location: index.php?page=dashboard");
    }
    exit;
}

$title = "Inventory";
$page = "inventory";
require_once __DIR__ . "/partials/header.php";

$conn = get_db_connection();
$role = get_user_role();
$role_class = ($role == 'Farm Manager') ? 'manager' : 'admin';
    
$total_feed = $conn->query("SELECT COALESCE(SUM(quantity_kg), 0) FROM feed_inventory")->fetch_row()[0];
$feed_value = $conn->query("SELECT COALESCE(SUM(quantity_kg * unit_cost), 0) FROM feed_inventory")->fetch_row()[0];
$total_equipment = $conn->query("SELECT COUNT(*) FROM equipment")->fetch_row()[0];
$equipment_needs_maintenance = $conn->query("SELECT COUNT(*) FROM equipment WHERE next_maintenance <= CURDATE() OR next_maintenance IS NULL")->fetch_row()[0];
?>

<!-- Welcome -->
<div class="welcome-banner <?php echo $role_class; ?> fade-in">
    <div>
        <h2><i class="fas fa-boxes-stacked"></i> Inventory Management</h2>
        <p>Track feed, equipment and supplies</p>
    </div>
    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
        <i class="fas fa-plus"></i> Add Equipment
    </button>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-leaf"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($total_feed); ?> kg</h3>
                <p>Total Feed Stock</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-money-bill"></i></div>
            <div class="stat-info">
                <h3>UG Shillings <?php echo number_format($feed_value, 0); ?></h3>
                <p>Feed Value</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-tools"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_equipment; ?></h3>
                <p>Total Equipment</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: <?php echo $equipment_needs_maintenance > 0 ? '#f8d7da' : '#d4edda'; ?>; color: <?php echo $equipment_needs_maintenance > 0 ? '#dc3545' : '#28a745'; ?>">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $equipment_needs_maintenance; ?></h3>
                <p>Needs Maintenance</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-tools"></i> Equipment List</div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Next Maintenance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM equipment ORDER BY name");
                        while ($row = $result->fetch_assoc()) {
                            $status_class = $row['status'] === 'Functional' ? 'success' : ($row['status'] === 'Under Repair' ? 'warning' : ($row['status'] === 'Broken' ? 'danger' : 'secondary'));
                            echo "<tr>";
                            echo "<td>" . $row['name'] . "</td>";
                            echo "<td><span class='badge bg-$status_class'>{$row['status']}</span></td>";
                            echo "<td>" . ($row['next_maintenance'] ? date('d/m/Y', strtotime($row['next_maintenance'])) : '-') . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-leaf"></i> Feed Inventory</div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Feed Name</th>
                            <th>Qty (kg)</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM feed_inventory ORDER BY feed_name");
                        while ($row = $result->fetch_assoc()) {
                            $total_value = $row['quantity_kg'] * $row['unit_cost'];
                            echo "<tr>";
                            echo "<td>" . $row['feed_name'] . "</td>";
                            echo "<td>" . number_format($row['quantity_kg']) . "</td>";
                            echo "<td>UG Shillings " . number_format($total_value, 0) . "</td>";
                            echo "</tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=add_equipment">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Equipment Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Functional">Functional</option>
                            <option value="Under Repair">Under Repair</option>
                            <option value="Broken">Broken</option>
                            <option value="Retired">Retired</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Maintenance Date</label>
                        <input type="date" name="next_maintenance" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Equipment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
