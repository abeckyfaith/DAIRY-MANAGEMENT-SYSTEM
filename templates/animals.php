<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/rbac.php';

require_login();

// BLOCK NON-ADMIN - Only Admin can access
if (get_role_level() < 5) {
    $_SESSION['error'] = "You don't have permission to access this page.";
    header("Location: index.php?page=dashboard");
    exit;
}

$title = "Animals";
$page = "animals";
require_once __DIR__ . "/partials/header.php";

$conn = get_db_connection();
$role = get_user_role();
$role_class = 'admin';

$breeds = $conn->query("SELECT * FROM breeds ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_animal'])) {
    $tag_number = sanitize_input($_POST['tag_number']);
    $breed_id = intval($_POST['breed_id']);
    $birth_date = sanitize_input($_POST['birth_date']);
    $gender = sanitize_input($_POST['gender']);
    $weight = floatval($_POST['weight']);
    $status = 'Active';
    
    $stmt = $conn->prepare("INSERT INTO animals (tag_number, breed_id, birth_date, gender, weight, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissds", $tag_number, $breed_id, $birth_date, $gender, $weight, $status);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Animal added successfully!";
    } else {
        $_SESSION['error'] = "Error adding animal: " . $conn->error;
    }
    $stmt->close();
    header("Location: index.php?page=animals");
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM animals WHERE id = $id");
    header("Location: index.php?page=animals");
    exit;
}

$animals = $conn->query("SELECT a.*, b.name as breed_name FROM animals a LEFT JOIN breeds b ON a.breed_id = b.id ORDER BY a.id DESC");
$conn->close();
?>

<!-- Welcome -->
<div class="welcome-banner <?php echo $role_class; ?> fade-in">
    <div>
        <h2><i class="fas fa-paw"></i> Animal Management</h2>
        <p>Manage your farm animals</p>
    </div>
    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addAnimalModal">
        <i class="fas fa-plus"></i> Add Animal
    </button>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success fade-in"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger fade-in"><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><i class="fas fa-list"></i> All Animals</div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Tag Number</th>
                    <th>Breed</th>
                    <th>Gender</th>
                    <th>Birth Date</th>
                    <th>Weight (kg)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($animals && $animals->num_rows > 0): ?>
                    <?php while ($animal = $animals->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $animal['tag_number']; ?></strong></td>
                        <td><?php echo $animal['breed_name'] ?? 'N/A'; ?></td>
                        <td><span class="badge bg-<?php echo $animal['gender'] === 'Male' ? 'primary' : 'success'; ?>"><?php echo $animal['gender']; ?></span></td>
                        <td><?php echo $animal['birth_date'] ? date('d M Y', strtotime($animal['birth_date'])) : 'N/A'; ?></td>
                        <td><?php echo $animal['weight']; ?></td>
                        <td><span class="badge bg-success"><?php echo $animal['status']; ?></span></td>
                        <td>
                            <a href="index.php?page=animals&delete=<?php echo $animal['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this animal?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No animals found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Animal Modal -->
<div class="modal fade" id="addAnimalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Animal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tag Number *</label>
                        <input type="text" name="tag_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Breed</label>
                        <select name="breed_id" class="form-select">
                            <option value="">Select Breed</option>
                            <?php while ($breed = $breeds->fetch_assoc()): ?>
                            <option value="<?php echo $breed['id']; ?>"><?php echo $breed['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-select" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" name="weight" class="form-control" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_animal" class="btn btn-primary">Add Animal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
