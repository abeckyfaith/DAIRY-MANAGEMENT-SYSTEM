<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dairy Management System - Add Animal</title>
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
        $animal_type = sanitize_input($_POST['animal_type']);
        $breed_id = intval($_POST['breed_id']);
        $birth_date = $_POST['birth_date'] ?: null;
        $gender = $_POST['gender'];
        $weight = floatval($_POST['weight'] ?? 0);
        $notes = sanitize_input($_POST['notes'] ?? '');
        
        // Generate tag number based on animal type
        $prefix = strtoupper(substr($animal_type, 0, 2));
        $result = $conn->query("SELECT COUNT(*) as count FROM animals WHERE animal_type = '$animal_type'");
        $count = $result->fetch_assoc()['count'] + 1;
        $tag_number = $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("INSERT INTO animals (tag_number, animal_type, breed_id, birth_date, gender, weight, status, notes) VALUES (?, ?, ?, ?, ?, ?, 'Active', ?)");
        $stmt->bind_param("ssissds", $tag_number, $animal_type, $breed_id, $birth_date, $gender, $weight, $notes);
        
        if ($stmt->execute()) {
            log_activity("Added new animal: $tag_number");
            $_SESSION['success'] = "Animal added successfully!";
            redirect('animals');
        } else {
            $_SESSION['error'] = "Failed to add animal. Tag number may already exist.";
        }
        
        $stmt->close();
    }
    
    $breeds = $conn->query("SELECT * FROM breeds ORDER BY name");
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
                    <h1 class="h2"><i class="fas fa-plus-circle"></i> Add New Animal</h1>
                    <a href="index.php?page=animals" class="btn btn-secondary">Back to Animals</a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Animal Type *</label>
                                    <select name="animal_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="Cow">Cow</option>
                                        <option value="Goat">Goat</option>
                                        <option value="Sheep">Sheep</option>
                                        <option value="Buffalo">Buffalo</option>
                                        <option value="Calf">Calf</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Breed</label>
                                    <select name="breed_id" class="form-select">
                                        <option value="">Select Breed</option>
                                        <?php while ($breed = $breeds->fetch_assoc()): ?>
                                        <option value="<?php echo $breed['id']; ?>"><?php echo $breed['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender *</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Birth Date</label>
                                    <input type="date" name="birth_date" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Weight (kg)</label>
                                    <input type="number" name="weight" class="form-control" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <input type="text" class="form-control" value="Active" disabled>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary-custom">Add Animal</button>
                            <a href="index.php?page=animals" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>