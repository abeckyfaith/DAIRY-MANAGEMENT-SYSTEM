<?php
require_once __DIR__ . '/../includes/rbac.php';

$title = 'Feed Inventory';
$page = 'feed';
$role_class = 'admin';

if (get_role_level() < 5) {
    $_SESSION['error'] = "Access denied. Admin only.";
    header("Location: index.php?page=dashboard");
    exit;
}

require_once __DIR__ . "/partials/header.php";

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/activity_log.php';

$conn = get_db_connection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_feed'])) {
        $feed_name = sanitize_input($_POST['feed_name']);
        $quantity_kg = $_POST['quantity_kg'];
        $unit_cost = $_POST['unit_cost'];
        $supplier = sanitize_input($_POST['supplier']);
        
        $stmt = $conn->prepare("INSERT INTO feed_inventory (feed_name, quantity_kg, unit_cost, supplier) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdds", $feed_name, $quantity_kg, $unit_cost, $supplier);
        
        if ($stmt->execute()) {
            log_activity($_SESSION['user_id'], "Added new feed: $feed_name");
            $success = "Feed added successfully!";
        } else {
            $error = "Error adding feed: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$feed_data = [];
$result = $conn->query("SELECT * FROM feed_inventory ORDER BY feed_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $feed_data[] = $row;
    }
}

$summary = [];
$result = $conn->query("SELECT 
    SUM(quantity_kg) as total_quantity,
    SUM(quantity_kg * unit_cost) as total_value,
    COUNT(*) as feed_types
    FROM feed_inventory");
if ($result) {
    $summary = $result->fetch_assoc();
}

$conn->close();
?>

<div class="welcome-banner admin">
    <div>
        <h1><i class="fas fa-leaf"></i> Feed Inventory</h1>
        <p>Manage feed stock and suppliers</p>
    </div>
    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addFeedModal">
        <i class="fas fa-plus"></i> Add Feed
    </button>
</div>

<?php if ($success): ?>
<div class="alert alert-success fade-in"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger fade-in"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="stat-info">
                <span>Total Feed Types</span>
                <h3><?php echo $summary['feed_types'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-weight-hanging"></i></div>
            <div class="stat-info">
                <span>Total Quantity</span>
                <h3><?php echo number_format($summary['total_quantity'] ?? 0, 2); ?> kg</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-info">
                <span>Total Value</span>
                <h3>UG <?php echo number_format($summary['total_value'] ?? 0, 0); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Feed Inventory</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Feed Name</th>
                        <th>Quantity (kg)</th>
                        <th>Unit Cost</th>
                        <th>Total Value</th>
                        <th>Supplier</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($feed_data)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No feed inventory records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($feed_data as $feed): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($feed['feed_name']); ?></td>
                                <td><?php echo number_format($feed['quantity_kg']); ?></td>
                                <td>UG <?php echo number_format($feed['unit_cost'], 0); ?></td>
                                <td>UG <?php echo number_format($feed['quantity_kg'] * $feed['unit_cost'], 0); ?></td>
                                <td><?php echo htmlspecialchars($feed['supplier'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addFeedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Feed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="feed_name" class="form-label">Feed Name</label>
                        <input type="text" class="form-control" id="feed_name" name="feed_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity_kg" class="form-label">Quantity (kg)</label>
                        <input type="number" step="0.01" class="form-control" id="quantity_kg" name="quantity_kg" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="unit_cost" class="form-label">Unit Cost</label>
                        <input type="number" step="0.01" class="form-control" id="unit_cost" name="unit_cost" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="supplier" class="form-label">Supplier</label>
                        <input type="text" class="form-control" id="supplier" name="supplier">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_feed" class="btn btn-primary">Add Feed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
