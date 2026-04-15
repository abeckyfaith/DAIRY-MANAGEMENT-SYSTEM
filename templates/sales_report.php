<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

require_login();
$user = get_current_user_info();
$role = get_user_role();
$conn = get_db_connection();

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$sales_report = $conn->query("SELECT DATE(invoice_date) as sale_date, COUNT(*) as total_orders, SUM(total_amount) as total_sales FROM invoices WHERE invoice_date BETWEEN '$start_date' AND '$end_date' GROUP BY DATE(invoice_date) ORDER BY sale_date DESC");

$summary = $conn->query("SELECT COUNT(*) as total_orders, SUM(total_amount) as total_sales, AVG(total_amount) as avg_order FROM invoices WHERE invoice_date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc();

$top_products = $conn->query("SELECT p.product_name, SUM(ii.quantity) as total_qty, SUM(ii.total_price) as total_sales FROM invoice_items ii JOIN products p ON ii.product_id = p.id JOIN invoices i ON ii.invoice_id = i.id WHERE i.invoice_date BETWEEN '$start_date' AND '$end_date' GROUP BY p.product_name ORDER BY total_sales DESC LIMIT 10");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo APP_NAME; ?> - Sales Report</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <nav class="sidebar<?php echo $role === 'Staff' ? ' staff' : ''; ?>">
            <div class="sidebar-brand"><i class="fas fa-mug-hot"></i><span>Dairy Manager</span></div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=animals"><i class="fas fa-paw"></i> Animals</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=milk_production"><i class="fas fa-wine-bottle"></i> Milk</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=health"><i class="fas fa-heart-pulse"></i> Health</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=feed"><i class="fas fa-leaf"></i> Feed</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=reproduction"><i class="fas fa-venus"></i> Breeding</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=finance"><i class="fas fa-wallet"></i> Finance</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=inventory"><i class="fas fa-boxes-stacked"></i> Inventory</a></li>
                <li class="nav-item mt-2"><span class="nav-link text-muted small fw-bold text-uppercase">Dairy Shop</span></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=categories"><i class="fas fa-tags"></i> Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=products"><i class="fas fa-box"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=suppliers"><i class="fas fa-truck"></i> Suppliers</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=sales"><i class="fas fa-cash-register"></i> Sales</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?page=sales_report"><i class="fas fa-chart-bar"></i> Sales Report</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=profile"><i class="fas fa-user-cog"></i> Profile</a></li>
                <?php if (is_admin()): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=staff"><i class="fas fa-users-cog"></i> Staff</a></li>
                <?php endif; ?>
                <li class="nav-item mt-3"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-chart-bar"></i> Sales Report</h1>
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="page" value="sales_report">
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                </form>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-icon primary"><i class="fas fa-shopping-bag"></i></div><div class="stat-info"><h3><?php echo $summary['total_orders'] ?? 0; ?></h3><p>Total Orders</p></div></div>
                <div class="stat-card"><div class="stat-icon accent"><i class="fas fa-money-bill-wave"></i></div><div class="stat-info"><h3>UG Shillings <?php echo number_format($summary['total_sales'] ?? 0, 0); ?></h3><p>Total Sales</p></div></div>
                <div class="stat-card"><div class="stat-icon secondary"><i class="fas fa-calculator"></i></div><div class="stat-info"><h3>UG Shillings <?php echo number_format($summary['avg_order'] ?? 0, 0); ?></h3><p>Average Order</p></div></div>
            </div>

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><i class="fas fa-chart-line"></i> Daily Sales</div>
                        <div class="card-body">
                            <table class="table">
                                <thead><tr><th>Date</th><th>Orders</th><th>Sales</th></tr></thead>
                                <tbody>
                                    <?php if ($sales_report && $sales_report->num_rows > 0): ?>
                                        <?php while ($row = $sales_report->fetch_assoc()): ?>
                                        <tr><td><?php echo date('d M Y', strtotime($row['sale_date'])); ?></td><td><span class="badge bg-primary"><?php echo $row['total_orders']; ?></span></td><td><strong>UG Shillings <?php echo number_format($row['total_sales'], 0); ?></strong></td></tr>
                                        <?php endwhile; ?>
                                    <?php else: ?><tr><td colspan="3" class="text-center text-muted">No sales in this period</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><i class="fas fa-star"></i> Top Products</div>
                        <div class="card-body p-0">
                            <table class="table">
                                <thead><tr><th>Product</th><th>Qty</th><th>Sales</th></tr></thead>
                                <tbody>
                                    <?php if ($top_products && $top_products->num_rows > 0): ?>
                                        <?php while ($prod = $top_products->fetch_assoc()): ?>
                                        <tr><td><?php echo $prod['product_name']; ?></td><td><?php echo $prod['total_qty']; ?></td><td><strong>UG Shillings <?php echo number_format($prod['total_sales'], 0); ?></strong></td></tr>
                                        <?php endwhile; ?>
                                    <?php else: ?><tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header"><i class="fas fa-file-export"></i> Export Options</div>
                <div class="card-body">
                    <button class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>