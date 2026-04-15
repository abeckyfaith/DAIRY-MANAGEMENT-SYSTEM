<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dairy Management System - Health Check</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .main-content { background: #f8f9fa; min-height: 100vh; }
        .nav-link { color: rgba(255,255,255,0.8); border-radius: 8px; margin: 5px 10px; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.2); color: white; }
    </style>
</head>
<body>
    <?php
    require_once 'config/config.php';
    require_once 'includes/functions.php';
    require_once 'includes/auth.php';
    
    require_login();
    $user = get_current_user_info();
    $conn = get_db_connection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $animal_id = intval($_POST['animal_id']);
        $treatment_date = $_POST['treatment_date'];
        $diagnosis = sanitize_input($_POST['diagnosis']);
        $medication = sanitize_input($_POST['medication']);
        $dosage = sanitize_input($_POST['dosage']);
        $withdrawal_period_days = intval($_POST['withdrawal_period_days'] ?? null);
        
        $stmt = $conn->prepare("INSERT INTO treatments (animal_id, treatment_date, diagnosis, medication, dosage, withdrawal_period_days) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $animal_id, $treatment_date, $diagnosis, $medication, $dosage, $withdrawal_period_days);
        
        if ($stmt->execute()) {
            log_activity("Recorded health check for animal ID: $animal_id");
            $_SESSION['success'] = "Health check recorded successfully!";
            redirect('health');
        } else {
            $_SESSION['error'] = "Failed to record health check.";
        }
        
        $stmt->close();
    }
    
    $animals = $conn->query("SELECT id, tag_number FROM animals WHERE status = 'Active' ORDER BY tag_number");
    $conn->close();
    ?>
    
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                        <h5 class="text-white">Dairy Management</h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=animals"><i class="fas fa-cow"></i> Animals</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=milk_production"><i class="fas fa-glass-whiskey"></i> Milk Production</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=health"><i class="fas fa-heartbeat"></i> Health</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=feed"><i class="fas fa-utensils"></i> Feed</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=reproduction"><i class="fas fa-baby"></i> Reproduction</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=finance"><i class="fas fa-dollar-sign"></i> Finance</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=inventory"><i class="fas fa-boxes"></i> Inventory</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?page=reports"><i class="fas fa-chart-bar"></i> Reports</a></li>
                        <li class="nav-item mt-3"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-plus-circle"></i> Health Check / Treatment</h1>
                    <a href="index.php?page=health" class="btn btn-secondary btn-sm">Back to Health</a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Animal *</label>
                                    <select name="animal_id" class="form-select" required>
                                        <option value="">Select Animal</option>
                                        <?php while ($animal = $animals->fetch_assoc()): ?>
                                        <option value="<?php echo $animal['id']; ?>"><?php echo $animal['tag_number']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Treatment Date *</label>
                                    <input type="date" name="treatment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Diagnosis *</label>
                                    <input type="text" name="diagnosis" class="form-control" required placeholder="Disease or condition">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Medication</label>
                                    <input type="text" name="medication" class="form-control" placeholder="Medicine name">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dosage</label>
                                    <input type="text" name="dosage" class="form-control" placeholder="e.g., 50ml twice daily">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Withdrawal Period (days)</label>
                                    <input type="number" name="withdrawal_period_days" class="form-control" min="0" placeholder="Milk/meat withdrawal">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary-custom">Record Treatment</button>
                            <a href="index.php?page=health" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>