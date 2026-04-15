<?php
require_once __DIR__ . '/../includes/rbac.php';

$title = 'Reports';
$page = 'reports';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_report'])) {
        $report_type = $_POST['report_type'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        
        $_SESSION['report_params'] = [
            'type' => $report_type,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        
        header("Location: index.php?page=reports");
        exit;
    }
}

$report_params = $_SESSION['report_params'] ?? [
    'type' => 'daily',
    'start_date' => date('Y-m-d', strtotime('-7 days')),
    'end_date' => date('Y-m-d')
];

$report_data = [];
$summary_data = [];

switch ($report_params['type']) {
    case 'daily':
        $result = $conn->query("SELECT 
            DATE(recording_date) as date,
            SUM(amount_liters) as total_milk,
            COUNT(DISTINCT animal_id) as active_animals,
            AVG(fat_percentage) as avg_fat,
            AVG(protein_percentage) as avg_protein
            FROM milk_production 
            WHERE DATE(recording_date) BETWEEN '{$report_params['start_date']}' AND '{$report_params['end_date']}'
            GROUP BY DATE(recording_date)
            ORDER BY DATE(recording_date)");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
        break;
        
    case 'animal':
        $result = $conn->query("SELECT 
            a.tag_number,
            a.breed_id,
            b.name as breed_name,
            COUNT(mp.id) as milk_records,
            SUM(mp.amount_liters) as total_milk,
            AVG(mp.fat_percentage) as avg_fat,
            AVG(mp.protein_percentage) as avg_protein
            FROM animals a 
            LEFT JOIN milk_production mp ON a.id = mp.animal_id 
            LEFT JOIN breeds b ON a.breed_id = b.id
            WHERE a.status = 'Active'
            GROUP BY a.id
            ORDER BY total_milk DESC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
        break;
        
    case 'health':
        $result = $conn->query("SELECT 
            DATE(recording_date) as date,
            COUNT(*) as health_checks,
            AVG(temperature) as avg_temp,
            COUNT(CASE WHEN condition_score < 3 THEN 1 END) as poor_condition
            FROM health_checks 
            WHERE DATE(recording_date) BETWEEN '{$report_params['start_date']}' AND '{$report_params['end_date']}'
            GROUP BY DATE(recording_date)
            ORDER BY DATE(recording_date)");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
        break;
        
    case 'financial':
        $result = $conn->query("SELECT 
            DATE(transaction_date) as date,
            SUM(CASE WHEN category = 'Milk Sales' THEN amount ELSE 0 END) as milk_sales,
            SUM(CASE WHEN category = 'Animal Sales' THEN amount ELSE 0 END) as animal_sales,
            SUM(CASE WHEN category IN ('Feed', 'Veterinary', 'Labor', 'Equipment') THEN amount ELSE 0 END) as expenses
            FROM (
                SELECT transaction_date, category, amount FROM income 
                UNION ALL 
                SELECT transaction_date, category, -amount as amount FROM expenses
            ) as combined
            WHERE DATE(transaction_date) BETWEEN '{$report_params['start_date']}' AND '{$report_params['end_date']}'
            GROUP BY DATE(transaction_date)
            ORDER BY DATE(transaction_date)");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
        break;
}

$summary_result = $conn->query("SELECT 
    COUNT(*) as total_animals,
    SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_animals,
    (SELECT COUNT(*) FROM milk_production WHERE DATE(recording_date) = CURDATE()) as today_milk_records,
    (SELECT SUM(amount_liters) FROM milk_production WHERE DATE(recording_date) = CURDATE()) as today_milk_total
    FROM animals");
if ($summary_result) {
    $summary_data = $summary_result->fetch_assoc();
}

$conn->close();
?>

<div class="welcome-banner manager">
    <div>
        <h1><i class="fas fa-chart-pie"></i> Reports & Analytics</h1>
        <p>View and analyze dairy farm data</p>
    </div>
    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#reportModal">
        <i class="fas fa-chart-line"></i> Generate Report
    </button>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-paw"></i></div>
            <div class="stat-info">
                <span>Total Animals</span>
                <h3><?php echo $summary_data['total_animals'] ?? 0; ?></h3>
                <small class="text-muted"><?php echo $summary_data['active_animals'] ?? 0; ?> active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-wine-bottle"></i></div>
            <div class="stat-info">
                <span>Today's Milk</span>
                <h3><?php echo number_format($summary_data['today_milk_total'] ?? 0, 2); ?> L</h3>
                <small class="text-muted"><?php echo $summary_data['today_milk_records'] ?? 0; ?> records</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar"></i></div>
            <div class="stat-info">
                <span>Report Period</span>
                <h4><?php echo date('M j, Y', strtotime($report_params['start_date'])); ?> - <?php echo date('M j, Y', strtotime($report_params['end_date'])); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
            <div class="stat-info">
                <span>Report Type</span>
                <h4><?php echo ucfirst($report_params['type']); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <h4 class="mb-4 card-header">Report Data</h4>
    <?php if (empty($report_data)): ?>
        <div class="text-center py-5">
            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
            <h5>No data available for selected period</h5>
            <p class="text-muted">Try adjusting the date range or report type.</p>
        </div>
    <?php else: ?>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <?php
                            switch ($report_params['type']) {
                                case 'daily':
                                    echo '<th>Date</th><th>Total Milk (L)</th><th>Active Animals</th><th>Avg Fat %</th><th>Avg Protein %</th>';
                                    break;
                                case 'animal':
                                    echo '<th>Tag Number</th><th>Breed</th><th>Milk Records</th><th>Total Milk (L)</th><th>Avg Fat %</th><th>Avg Protein %</th>';
                                    break;
                                case 'health':
                                    echo '<th>Date</th><th>Health Checks</th><th>Avg Temp (°C)</th><th>Poor Condition</th>';
                                    break;
                                case 'financial':
                                    echo '<th>Date</th><th>Milk Sales</th><th>Animal Sales</th><th>Expenses</th><th>Net Income</th>';
                                    break;
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($report_data as $row) {
                            echo '<tr>';
                            switch ($report_params['type']) {
                                case 'daily':
                                    echo '<td>' . date('M j, Y', strtotime($row['date'])) . '</td>';
                                    echo '<td>' . number_format($row['total_milk'], 2) . '</td>';
                                    echo '<td>' . $row['active_animals'] . '</td>';
                                    echo '<td>' . number_format($row['avg_fat'], 2) . '</td>';
                                    echo '<td>' . number_format($row['avg_protein'], 2) . '</td>';
                                    break;
                                case 'animal':
                                    echo '<td>' . htmlspecialchars($row['tag_number']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['breed_name'] ?? 'Unknown') . '</td>';
                                    echo '<td>' . $row['milk_records'] . '</td>';
                                    echo '<td>' . number_format($row['total_milk'], 2) . '</td>';
                                    echo '<td>' . number_format($row['avg_fat'], 2) . '</td>';
                                    echo '<td>' . number_format($row['avg_protein'], 2) . '</td>';
                                    break;
                                case 'health':
                                    echo '<td>' . date('M j, Y', strtotime($row['date'])) . '</td>';
                                    echo '<td>' . $row['health_checks'] . '</td>';
                                    echo '<td>' . number_format($row['avg_temp'], 1) . '</td>';
                                    echo '<td>' . $row['poor_condition'] . '</td>';
                                    break;
                                case 'financial':
                                    echo '<td>' . date('M j, Y', strtotime($row['date'])) . '</td>';
                                    echo '<td>$' . number_format($row['milk_sales'], 2) . '</td>';
                                    echo '<td>$' . number_format($row['animal_sales'], 2) . '</td>';
                                    echo '<td>$' . number_format($row['expenses'], 2) . '</td>';
                                    echo '<td>$' . number_format($row['milk_sales'] + $row['animal_sales'] - $row['expenses'], 2) . '</td>';
                                    break;
                            }
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select class="form-select" id="report_type" name="report_type" required>
                            <option value="daily">Daily Production Summary</option>
                            <option value="animal">Animal Performance</option>
                            <option value="health">Health Statistics</option>
                            <option value="financial">Financial Overview</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required value="<?php echo $report_params['start_date']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required value="<?php echo $report_params['end_date']; ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="generate_report" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
