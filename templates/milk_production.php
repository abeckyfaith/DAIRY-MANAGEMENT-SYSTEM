<?php
require_once __DIR__ . '/../includes/rbac.php';

$title = 'Milk Production';
$page = 'milk_production';
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
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['record_milk'])) {
        $animal_id = $_POST['animal_id'];
        $amount_liters = $_POST['amount_liters'];
        $recording_date = date('Y-m-d');
        $recorded_at = date('Y-m-d H:i:s'); // Capture exact time
        
        // Auto-set default values
        $fat_percentage = 3.5;
        $protein_percentage = 3.2;
        $somatic_cell_count = 200;
        
        // Determine session based on recorded time
        $hour = date('H', strtotime($recorded_at));
        $session = ($hour < 12) ? 'Morning' : 'Evening';
        
        $stmt = $conn->prepare("INSERT INTO milk_production (animal_id, session, amount_liters, fat_percentage, protein_percentage, somatic_cell_count, recording_date, recorded_by, recorded_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdddisiss", $animal_id, $session, $amount_liters, $fat_percentage, $protein_percentage, $somatic_cell_count, $recording_date, $_SESSION['user_id'], $recorded_at);
        
        if ($stmt->execute()) {
            log_activity($_SESSION['user_id'], "Recorded milk production for animal ID: $animal_id");
            $success = "Milk production recorded successfully!";
        } else {
            $error = "Error recording milk production: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$milk_data = [];
$result = $conn->query("SELECT mp.*, a.tag_number, a.breed_id, b.name as breed_name FROM milk_production mp JOIN animals a ON mp.animal_id = a.id LEFT JOIN breeds b ON a.breed_id = b.id ORDER BY mp.recording_date DESC, mp.id DESC LIMIT 50");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Use the session that's stored in database
        $row['display_session'] = $row['session'] ?? 'Morning';
        // No timestamp in DB, show recording date only
        $row['display_time'] = '';
        $milk_data[] = $row;
    }
}

$animals = [];
$result = $conn->query("SELECT id, tag_number FROM animals ORDER BY tag_number");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $animals[] = $row;
    }
}

$today_summary = [];
$result = $conn->query("SELECT 
    SUM(CASE WHEN session = 'Morning' THEN amount_liters ELSE 0 END) as morning_total,
    SUM(CASE WHEN session = 'Evening' OR session = 'Afternoon' THEN amount_liters ELSE 0 END) as evening_total,
    SUM(amount_liters) as daily_total,
    AVG(fat_percentage) as avg_fat,
    AVG(protein_percentage) as avg_protein
    FROM milk_production WHERE DATE(recording_date) = CURDATE()");
if ($result) {
    $today_summary = $result->fetch_assoc();
}

$conn->close();
?>

<div class="welcome-banner manager">
    <div>
        <h1><i class="fas fa-wine-bottle"></i> Milk Production</h1>
        <p>Track and manage milk production records</p>
    </div>
    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#recordMilkModal">
        <i class="fas fa-plus"></i> Record Milk
    </button>
</div>

<?php if ($success): ?>
<div class="alert alert-success fade-in"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger fade-in"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-sun"></i></div>
            <div class="stat-info">
                <span>Morning</span>
                <h3><?php echo number_format($today_summary['morning_total'] ?? 0, 2); ?> L</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-moon"></i></div>
            <div class="stat-info">
                <span>Evening</span>
                <h3><?php echo number_format($today_summary['evening_total'] ?? 0, 2); ?> L</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
                <span>Total Today</span>
                <h3><?php echo number_format($today_summary['daily_total'] ?? 0, 2); ?> L</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-percent"></i></div>
            <div class="stat-info">
                <span>Avg Fat</span>
                <h3><?php echo number_format($today_summary['avg_fat'] ?? 0, 2); ?>%</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Milk Production Records</h5>
        <span class="badge bg-primary"><?php echo count($milk_data); ?> records</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Animal</th>
                        <th>Session</th>
                        <th>Amount (L)</th>
                        <th>Fat %</th>
                        <th>Protein %</th>
                        <th>Somatic Cells</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($milk_data)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No milk production records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($milk_data as $record): ?>
                            <tr>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($record['recording_date'])); ?>
                                    <small class="text-muted"><?php echo $record['display_time']; ?></small>
                                </td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($record['tag_number']); ?></span></td>
                                <td>
                                    <span class="badge <?php echo $record['display_session'] === 'Morning' ? 'bg-warning' : 'bg-secondary'; ?>">
                                        <i class="fas <?php echo $record['display_session'] === 'Morning' ? 'fa-sun' : 'fa-moon'; ?>"></i>
                                        <?php echo $record['display_session']; ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($record['amount_liters'], 2); ?></td>
                                <td><?php echo $record['fat_percentage'] ?? 'N/A'; ?></td>
                                <td><?php echo $record['protein_percentage'] ?? 'N/A'; ?></td>
                                <td><?php echo $record['somatic_cell_count'] ?? 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="recordMilkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Milk Production</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="animal_id" class="form-label">Animal</label>
                        <select class="form-select" id="animal_id" name="animal_id" required>
                            <option value="">Select Animal</option>
                            <?php foreach ($animals as $animal): ?>
                                <option value="<?php echo $animal['id']; ?>"><?php echo htmlspecialchars($animal['tag_number']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Session (Auto-detected)</label>
                        <div class="form-control-plaintext">
                            <span id="session-display" class="badge bg-secondary">
                                <i class="fas <?php echo (date('H') < 12) ? 'fa-sun' : 'fa-moon'; ?>"></i>
                                <?php echo (date('H') < 12) ? 'Morning' : 'Evening'; ?> (<?php echo date('H:i'); ?>)
                            </span>
                        </div>
                        <small class="text-muted">Auto-detected based on current time</small>
                    </div>
                    <div class="mb-3">
                        <label for="amount_liters" class="form-label">Amount (Liters)</label>
                        <input type="number" step="0.01" class="form-control" id="amount_liters" name="amount_liters" min="0" required>
                    </div>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Auto-recorded:</strong> Recording Date (Today), Fat % (3.5), Protein % (3.2), Somatic Cells (200)
                    </div>
                </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="record_milk" class="btn btn-primary">Record Milk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
