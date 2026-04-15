<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/activity_log.php';

require_login();
$user = get_current_user_info();
$role = get_user_role();
$conn = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_supplier'])) {
        $supplier_name = sanitize_input($_POST['supplier_name']);
        $contact_person = sanitize_input($_POST['contact_person']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $milk_rate = floatval($_POST['milk_rate']);
        $payment_terms = sanitize_input($_POST['payment_terms']);
        
        $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, milk_rate_per_liter, payment_terms) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssds", $supplier_name, $contact_person, $phone, $email, $address, $milk_rate, $payment_terms);
        
        if ($stmt->execute()) {
            log_activity($user['id'], "Added new supplier: $supplier_name");
            $success = "Supplier added successfully!";
        } else {
            $error = "Error adding supplier: " . $conn->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_supplier'])) {
        $id = intval($_POST['supplier_id']);
        $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            log_activity($user['id'], "Deleted supplier ID: $id");
            $success = "Supplier deleted successfully!";
        }
        $stmt->close();
    }
    
    if (isset($_POST['toggle_status'])) {
        $id = intval($_POST['supplier_id']);
        $status = sanitize_input($_POST['new_status']);
        $stmt = $conn->prepare("UPDATE suppliers SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            log_activity($user['id'], "Updated supplier status to $status");
            $success = "Supplier status updated!";
        }
        $stmt->close();
    }
}

$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY supplier_name");
$active_count = $conn->query("SELECT COUNT(*) as cnt FROM suppliers WHERE status = 'Active'")->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo APP_NAME; ?> - Suppliers</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <nav class="sidebar<?php echo $role === 'Staff' ? ' staff' : ''; ?>">
            <div class="sidebar-brand">
                <i class="fas fa-mug-hot"></i>
                <span>Dairy Manager</span>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=animals"><i class="fas fa-paw"></i> Animals</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=milk_production"><i class="fas fa-wine-bottle"></i> Milk</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=health"><i class="fas fa-heart-pulse"></i> Health</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=feed"><i class="fas fa-leaf"></i> Feed</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=reproduction"><i class="fas fa-venus"></i> Breeding</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=finance"><i class="fas fa-wallet"></i> Finance</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=inventory"><i class="fas fa-boxes-stacked"></i> Inventory</a></li>
                <li class="nav-item mt-2"><span class="nav-link text-muted small fw-bold text-uppercase">Dairy Shop</span></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=categories"><i class="fas fa-tags"></i> Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=products"><i class="fas fa-box"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?page=suppliers"><i class="fas fa-truck"></i> Suppliers</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=sales"><i class="fas fa-cash-register"></i> Sales</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=sales_report"><i class="fas fa-chart-bar"></i> Sales Report</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=profile"><i class="fas fa-user-cog"></i> Profile</a></li>
                <?php if (is_admin()): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=staff"><i class="fas fa-users-cog"></i> Staff</a></li>
                <?php endif; ?>
                <li class="nav-item mt-3"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-truck"></i> Milk Suppliers</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                    <i class="fas fa-plus"></i> Add Supplier
                </button>
            </div>

            <?php if (isset($success)): ?>
            <div class="alert alert-success fade-in"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
            <div class="alert alert-danger fade-in"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="fas fa-truck"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $suppliers ? $suppliers->num_rows : 0; ?></h3>
                        <p>Total Suppliers</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon accent"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $active_count['cnt']; ?></h3>
                        <p>Active Suppliers</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-list"></i> All Suppliers</div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Supplier Name</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Milk Rate/Ltr</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($suppliers && $suppliers->num_rows > 0): ?>
                                <?php while ($sup = $suppliers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $sup['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($sup['supplier_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($sup['contact_person'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($sup['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($sup['email'] ?? 'N/A'); ?></td>
                                    <td><strong>UG Shillings <?php echo number_format($sup['milk_rate_per_liter'], 0); ?></strong></td>
                                    <td><span class="badge bg-<?php echo $sup['status'] === 'Active' ? 'success' : 'secondary'; ?>"><?php echo $sup['status']; ?></span></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="supplier_id" value="<?php echo $sup['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $sup['status'] === 'Active' ? 'Inactive' : 'Active'; ?>">
                                                <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-secondary"><i class="fas fa-toggle-<?php echo $sup['status'] === 'Active' ? 'on text-success' : 'off'; ?>"></i></button>
                                            </form>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this supplier?');">
                                                <input type="hidden" name="supplier_id" value="<?php echo $sup['id']; ?>">
                                                <button type="submit" name="delete_supplier" class="btn btn-sm btn-outline-secondary"><i class="fas fa-trash text-danger"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-truck-loading fa-2x mb-2"></i><p>No suppliers found.</p></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="addSupplierModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-truck-loading"></i> Add New Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Supplier Name *</label>
                                <input type="text" name="supplier_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Milk Rate (UG Shillings/Liter) *</label>
                                <input type="number" name="milk_rate" class="form-control" required step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Terms</label>
                                <input type="text" name="payment_terms" class="form-control" placeholder="e.g., Weekly, Monthly">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_supplier" class="btn btn-primary">Add Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>