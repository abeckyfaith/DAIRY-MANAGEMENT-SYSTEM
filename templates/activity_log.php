<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dairy Management System - Activity Log</title>
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
    
    $page = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    $result = $conn->query("SELECT al.*, u.full_name FROM activity_log al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT $limit OFFSET $offset");
    $count_result = $conn->query("SELECT COUNT(*) as total FROM activity_log");
    $total = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total / $limit);
    
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
                    <h1 class="h2"><i class="fas fa-history"></i> Activity Log</h1>
                    <a href="index.php?page=dashboard" class="btn btn-secondary btn-sm">Back to Dashboard</a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo $row['full_name'] ?? 'System'; ?></td>
                                    <td><?php echo htmlspecialchars($row['activity']); ?></td>
                                    <td><?php echo $row['ip_address'] ?? '-'; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        
                        <?php if ($total_pages > 1): ?>
                        <nav>
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=activity_log&page_num=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>