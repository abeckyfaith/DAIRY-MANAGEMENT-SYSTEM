<?php
require_once __DIR__ . '/../includes/rbac.php';

$title = 'Health Management';
$page = 'health';
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
    if (isset($_POST['record_health'])) {
        $animal_id = $_POST['animal_id'];
        $temperature = $_POST['temperature'];
        $heart_rate = $_POST['heart_rate'];
        $respiratory_rate = $_POST['respiratory_rate'];
        $condition_score = $_POST['condition_score'];
        $notes = sanitize_input($_POST['notes']);
        $recording_date = $_POST['recording_date'];
        
        $stmt = $conn->prepare("INSERT INTO health_checks (animal_id, temperature, heart_rate, respiratory_rate, condition_score, notes, recorded_by, recording_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idddisss", $animal_id, $temperature, $heart_rate, $respiratory_rate, $condition_score, $notes, $_SESSION['user_id'], $recording_date);
        
        if ($stmt->execute()) {
            log_activity($_SESSION['user_id'], "Recorded health check for animal ID: $animal_id");
            $success = "Health check recorded successfully!";
        } else {
            $error = "Error recording health check: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$health_data = [];
$result = $conn->query("SELECT hc.*, a.tag_number, a.breed_id, b.name as breed_name FROM health_checks hc JOIN animals a ON hc.animal_id = a.id LEFT JOIN breeds b ON a.breed_id = b.id ORDER BY hc.recording_date DESC LIMIT 50");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $health_data[] = $row;
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
    COUNT(*) as total_checks,
    AVG(temperature) as avg_temp,
    AVG(heart_rate) as avg_heart_rate,
    AVG(respiratory_rate) as avg_respiratory_rate,
    COUNT(CASE WHEN condition_score < 3 THEN 1 END) as poor_condition
    FROM health_checks WHERE DATE(recording_date) = CURDATE()");
if ($result) {
    $today_summary = $result->fetch_assoc();
}

$conn->close();
?>

<div class="welcome-banner manager">
    <div>
        <h1><i class="fas fa-heart-pulse"></i> Health Management</h1>
        <p>Track animal health checks and records</p>
    </div>
    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#recordHealthModal">
        <i class="fas fa-plus"></i> Record Health Check
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
            <div class="stat-icon"><i class="fas fa-stethoscope"></i></div>
            <div class="stat-info">
                <span>Total Checks</span>
                <h3><?php echo $today_summary['total_checks'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-thermometer-half"></i></div>
            <div class="stat-info">
                <span>Avg Temp (°C)</span>
                <h3><?php echo number_format($today_summary['avg_temp'] ?? 0, 1); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-heart"></i></div>
            <div class="stat-info">
                <span>Avg Heart Rate</span>
                <h3><?php echo number_format($today_summary['avg_heart_rate'] ?? 0, 0); ?> bpm</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <span>Poor Condition</span>
                <h3><?php echo $today_summary['poor_condition'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Health Check Records</h5>
        <span class="badge bg-primary"><?php echo count($health_data); ?> records</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Animal</th>
                        <th>Temperature (°C)</th>
                        <th>Heart Rate (bpm)</th>
                        <th>Respiratory Rate</th>
                        <th>Condition Score</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($health_data)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No health check records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($health_data as $record): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($record['recording_date'])); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($record['tag_number']); ?></span></td>
                                <td><?php echo $record['temperature'] ?? 'N/A'; ?></td>
                                <td><?php echo $record['heart_rate'] ?? 'N/A'; ?></td>
                                <td><?php echo $record['respiratory_rate'] ?? 'N/A'; ?></td>
                                <td>
                                    <span class="badge <?php
                                    if ($record['condition_score'] >= 4) echo 'bg-success';
                                    elseif ($record['condition_score'] >= 3) echo 'bg-warning';
                                    else echo 'bg-danger';
                                    ?>">
                                        <?php echo $record['condition_score'] ?? 'N/A'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php
                                    if ($record['temperature'] > 39.5 || $record['temperature'] < 37.5) echo 'bg-danger';
                                    elseif ($record['heart_rate'] > 80 || $record['heart_rate'] < 50) echo 'bg-warning';
                                    else echo 'bg-success';
                                    ?>">
                                        <?php
                                        if ($record['temperature'] > 39.5 || $record['temperature'] < 37.5) echo 'Fever';
                                        elseif ($record['heart_rate'] > 80 || $record['heart_rate'] < 50) echo 'Abnormal';
                                        else echo 'Normal';
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="recordHealthModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Health Check</h5>
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
                        <label for="temperature" class="form-label">Temperature (°C)</label>
                        <input type="number" step="0.1" class="form-control" id="temperature" name="temperature" min="35" max="42" required>
                    </div>
                    <div class="mb-3">
                        <label for="heart_rate" class="form-label">Heart Rate (bpm)</label>
                        <input type="number" class="form-control" id="heart_rate" name="heart_rate" min="40" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="respiratory_rate" class="form-label">Respiratory Rate</label>
                        <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate" min="10" max="60" required>
                    </div>
                    <div class="mb-3">
                        <label for="condition_score" class="form-label">Body Condition Score</label>
                        <select class="form-select" id="condition_score" name="condition_score" required>
                            <option value="">Select Score</option>
                            <option value="1">1 - Poor</option>
                            <option value="2">2 - Fair</option>
                            <option value="3">3 - Good</option>
                            <option value="4">4 - Very Good</option>
                            <option value="5">5 - Excellent</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any observations or concerns..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="recording_date" class="form-label">Recording Date</label>
                        <input type="date" class="form-control" id="recording_date" name="recording_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="record_health" class="btn btn-primary">Record Health</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
