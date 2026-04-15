<?php
require_once __DIR__ . '/../includes/rbac.php';

$title = 'Profile';
$page = 'profile';
$role_class = 'manager';

if (get_role_level() < 4) {
    $_SESSION['error'] = "Access denied. Manager+ only.";
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("sssi", $full_name, $email, $phone, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        log_activity($_SESSION['user_id'], "Updated profile");
        $success = "Profile updated successfully!";
    } else {
        $error = "Error updating profile.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } else {
        $check = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $check->bind_param("i", $_SESSION['user_id']);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();
        
        if (password_verify($current_password, $result['password'])) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed, $_SESSION['user_id']);
            
            if ($update->execute()) {
                log_activity($_SESSION['user_id'], "Changed password");
                $success = "Password changed successfully!";
            }
            $update->close();
        } else {
            $error = "Current password is incorrect!";
        }
        $check->close();
    }
}

$user_data = $conn->query("SELECT * FROM users WHERE id = " . $_SESSION['user_id'])->fetch_assoc();
$conn->close();
?>

<div class="welcome-banner manager">
    <div>
        <h1><i class="fas fa-user-cog"></i> Profile</h1>
        <p>Manage your profile and account settings</p>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success fade-in"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger fade-in"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-user"></i> Profile Information</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                        <small class="text-muted">Username cannot be changed</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-lock"></i> Change Password</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Current Password *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password *</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle"></i> Account Details</div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><td><strong>User ID:</strong></td><td><?php echo $_SESSION['user_id']; ?></td></tr>
                    <tr><td><strong>Username:</strong></td><td><?php echo htmlspecialchars($user_data['username']); ?></td></tr>
                    <tr><td><strong>Role:</strong></td><td><span class="badge bg-primary"><?php echo htmlspecialchars($user['role_name']); ?></span></td></tr>
                    <tr><td><strong>Created:</strong></td><td><?php echo date('d M Y H:i', strtotime($user_data['created_at'])); ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
