<?php
require_once __DIR__ . '/../includes/rbac.php';

$title = 'Staff Management';
$page = 'staff';
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
    if (isset($_POST['add_staff'])) {
        $username = sanitize_input($_POST['username']);
        $password = $_POST['password'];
        $full_name = sanitize_input($_POST['full_name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        
        if (empty($username) || empty($password) || empty($full_name)) {
            $error = "Username, password and full name are required!";
        } else {
            $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
            if ($check->num_rows > 0) {
                $error = "Username already exists!";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, phone, role_id) VALUES (?, ?, ?, ?, ?, 3)");
                $stmt->bind_param("sssss", $username, $hash, $full_name, $email, $phone);
                
                if ($stmt->execute()) {
                    log_activity($_SESSION['user_id'], "Added new staff: $username");
                    $success = "Staff member added successfully!";
                } else {
                    $error = "Error adding staff: " . $conn->error;
                }
                $stmt->close();
            }
        }
    }
    
    if (isset($_POST['delete_staff'])) {
        $staff_id = intval($_POST['staff_id']);
        if ($staff_id == $_SESSION['user_id']) {
            $error = "You cannot delete your own account!";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role_id = 3");
            $stmt->bind_param("i", $staff_id);
            if ($stmt->execute()) {
                log_activity($_SESSION['user_id'], "Deleted staff ID: $staff_id");
                $success = "Staff member deleted successfully!";
            } else {
                $error = "Error deleting staff: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

$staff_list = $conn->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.role_id = 3 ORDER BY u.full_name");
$conn->close();
?>

<div class="welcome-banner admin">
    <div>
        <h1><i class="fas fa-users-cog"></i> Staff Management</h1>
        <p>Manage staff accounts and permissions</p>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success fade-in"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger fade-in"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user-plus"></i> Add New Staff</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <button type="submit" name="add_staff" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Add Staff
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user-tie"></i> Staff List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($staff_list && $staff_list->num_rows > 0): ?>
                                <?php while ($staff = $staff_list->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $staff['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($staff['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($staff['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($staff['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($staff['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($staff['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                            <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
                                            <button type="submit" name="delete_staff" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No staff members found. Add your first staff member.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
