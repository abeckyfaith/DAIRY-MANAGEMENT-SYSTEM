<?php
require_once __DIR__ . '/../includes/rbac.php';

$title = 'Reproduction';
$page = 'reproduction';
$role_class = 'manager';

if (get_role_level() < 3) {
    $_SESSION['error'] = "Access denied. Staff+ only.";
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_insemination'])) {
        $animal_id = intval($_POST['animal_id']);
        $insemination_date = sanitize_input($_POST['insemination_date']);
        $type = sanitize_input($_POST['type']);
        $sire_details = sanitize_input($_POST['sire_details']);
        
        $stmt = $conn->prepare("INSERT INTO inseminations (animal_id, insemination_date, type, sire_details, performed_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $animal_id, $insemination_date, $type, $sire_details, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $insemination_id = $conn->insert_id;
            
            $preg_stmt = $conn->prepare("INSERT INTO pregnancies (animal_id, insemination_id, confirmation_date, expected_calving_date) VALUES (?, ?, ?, DATE_ADD(?, INTERVAL 280 DAY))");
            $preg_stmt->bind_param("iiss", $animal_id, $insemination_id, $insemination_date, $insemination_date);
            $preg_stmt->execute();
            $preg_stmt->close();
            
            log_activity($_SESSION['user_id'], "Recorded insemination for animal ID: $animal_id");
            $success = "Insemination recorded successfully!";
        }
        $stmt->close();
    }
}

$inseminations = $conn->query("SELECT i.*, a.tag_number FROM inseminations i JOIN animals a ON i.animal_id = a.id ORDER BY i.insemination_date DESC");
$pregnancies = $conn->query("SELECT p.*, a.tag_number FROM pregnancies p JOIN animals a ON p.animal_id = a.id WHERE p.status = 'Confirmed' ORDER BY p.expected_calving_date");
$conn->close();
?>

<div class="welcome-banner manager">
    <div>
        <h1><i class="fas fa-venus-mars"></i> Reproduction & Breeding</h1>
        <p>Track inseminations and pregnancies</p>
    </div>
    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addInseminationModal">
        <i class="fas fa-plus"></i> Record Insemination
    </button>
</div>

<?php if ($success): ?>
<div class="alert alert-success fade-in">
    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-baby"></i> Active Pregnancies
    </div>
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Animal Tag</th>
                    <th>Confirm Date</th>
                    <th>Expected Calving</th>
                    <th>Days Remaining</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pregnancies && $pregnancies->num_rows > 0): ?>
                    <?php while ($preg = $pregnancies->fetch_assoc()): 
                        $days_remaining = (strtotime($preg['expected_calving_date']) - time()) / 86400;
                    ?>
                    <tr>
                        <td><strong><?php echo $preg['tag_number']; ?></strong></td>
                        <td><?php echo date('d M Y', strtotime($preg['confirmation_date'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($preg['expected_calving_date'])); ?></td>
                        <td><?php echo round($days_remaining); ?> days</td>
                        <td><span class="badge bg-success"><?php echo $preg['status']; ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No active pregnancies</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-history"></i> Insemination History
    </div>
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Animal Tag</th>
                    <th>Type</th>
                    <th>Sire Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($inseminations && $inseminations->num_rows > 0): ?>
                    <?php while ($ins = $inseminations->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($ins['insemination_date'])); ?></td>
                        <td><strong><?php echo $ins['tag_number']; ?></strong></td>
                        <td><?php echo $ins['type']; ?></td>
                        <td><?php echo $ins['sire_details'] ?? 'N/A'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No insemination records</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addInseminationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Record Insemination</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Animal</label>
                        <?php 
                        $conn = get_db_connection();
                        $animals = $conn->query("SELECT id, tag_number FROM animals WHERE gender = 'Female' AND status = 'Active'");
                        ?>
                        <select name="animal_id" class="form-select" required>
                            <option value="">Select Animal</option>
                            <?php while ($animal = $animals->fetch_assoc()): ?>
                            <option value="<?php echo $animal['id']; ?>"><?php echo $animal['tag_number']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Insemination Date</label>
                        <input type="date" name="insemination_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="AI">Artificial Insemination</option>
                            <option value="Natural">Natural Service</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sire Details</label>
                        <input type="text" name="sire_details" class="form-control" placeholder="Bull/AI Source details">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_insemination" class="btn btn-primary">Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . "/partials/footer.php"; ?>
