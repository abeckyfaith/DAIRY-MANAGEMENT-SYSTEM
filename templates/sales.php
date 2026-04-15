<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/activity_log.php';

require_login();
$user = get_current_user_info();
$role = get_user_role();
$conn = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_invoice'])) {
    $customer_name = sanitize_input($_POST['customer_name']);
    $customer_phone = sanitize_input($_POST['customer_phone']);
    $items = json_decode($_POST['items_json'], true);
    
    if (empty($items)) {
        $error = "No items in cart!";
    } else {
        $invoice_number = "INV-" . date("Ymd") . "-" . rand(1000, 9999);
        $total_amount = 0;
        
        foreach ($items as $item) { $total_amount += $item['quantity'] * $item['price']; }
        
        $stmt = $conn->prepare("INSERT INTO invoices (invoice_number, customer_name, customer_phone, invoice_date, total_amount) VALUES (?, ?, ?, CURDATE(), ?)");
        $stmt->bind_param("sssd", $invoice_number, $customer_name, $customer_phone, $total_amount);
        
        if ($stmt->execute()) {
            $invoice_id = $conn->insert_id;
            foreach ($items as $item) {
                $item_total = $item['quantity'] * $item['price'];
                $item_stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
                $item_stmt->bind_param("ididd", $invoice_id, $item['product_id'], $item['quantity'], $item['price'], $item_total);
                $item_stmt->execute();
                $item_stmt->close();
                
                $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $update_stock->bind_param("di", $item['quantity'], $item['product_id']);
                $update_stock->execute();
                $update_stock->close();
            }
            log_activity($user['id'], "Created invoice: $invoice_number for UG Shillings " . number_format($total_amount));
            $success = "Invoice created! Invoice #: $invoice_number";
        }
        $stmt->close();
    }
}

$products = $conn->query("SELECT * FROM products WHERE stock_quantity > 0 ORDER BY product_name");
$invoices = $conn->query("SELECT * FROM invoices ORDER BY created_at DESC LIMIT 50");
$sales_today = $conn->query("SELECT SUM(total_amount) as total, COUNT(*) as count FROM invoices WHERE DATE(invoice_date) = CURDATE()")->fetch_assoc();
$sales_week = $conn->query("SELECT SUM(total_amount) as total, COUNT(*) as count FROM invoices WHERE WEEK(invoice_date) = WEEK(CURDATE())")->fetch_assoc();
$sales_month = $conn->query("SELECT SUM(total_amount) as total, COUNT(*) as count FROM invoices WHERE MONTH(invoice_date) = MONTH(CURDATE())")->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo APP_NAME; ?> - Sales</title>
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
                <li class="nav-item"><a class="nav-link active" href="index.php?page=sales"><i class="fas fa-cash-register"></i> Sales</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=sales_report"><i class="fas fa-chart-bar"></i> Sales Report</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=profile"><i class="fas fa-user-cog"></i> Profile</a></li>
                <?php if (is_admin()): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=staff"><i class="fas fa-users-cog"></i> Staff</a></li>
                <?php endif; ?>
                <li class="nav-item mt-3"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-cash-register"></i> Sales & Invoices</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSaleModal"><i class="fas fa-plus"></i> New Sale</button>
            </div>

            <?php if (isset($success)): ?><div class="alert alert-success fade-in"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if (isset($error)): ?><div class="alert alert-danger fade-in"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-icon primary"><i class="fas fa-calendar-day"></i></div><div class="stat-info"><h3>UG Shillings <?php echo number_format($sales_today['total'] ?? 0, 0); ?></h3><p>Today (<?php echo $sales_today['count'] ?? 0; ?>)</p></div></div>
                <div class="stat-card"><div class="stat-icon accent"><i class="fas fa-calendar-week"></i></div><div class="stat-info"><h3>UG Shillings <?php echo number_format($sales_week['total'] ?? 0, 0); ?></h3><p>This Week (<?php echo $sales_week['count'] ?? 0; ?>)</p></div></div>
                <div class="stat-card"><div class="stat-icon secondary"><i class="fas fa-calendar-alt"></i></div><div class="stat-info"><h3>UG Shillings <?php echo number_format($sales_month['total'] ?? 0, 0); ?></h3><p>This Month (<?php echo $sales_month['count'] ?? 0; ?>)</p></div></div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-file-invoice"></i> Recent Invoices</div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead><tr><th>Invoice #</th><th>Customer</th><th>Phone</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if ($invoices && $invoices->num_rows > 0): ?>
                                <?php while ($inv = $invoices->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo $inv['invoice_number']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($inv['customer_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($inv['customer_phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d M Y', strtotime($inv['invoice_date'])); ?></td>
                                    <td><strong>UG Shillings <?php echo number_format($inv['total_amount'], 0); ?></strong></td>
                                    <td><span class="badge bg-<?php echo $inv['payment_status'] === 'Paid' ? 'success' : 'warning'; ?>"><?php echo $inv['payment_status']; ?></span></td>
                                    <td><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i></button></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-receipt fa-2x mb-2"></i><p>No invoices yet.</p></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="newSaleModal" tabindex="-1" style="z-index: 1055;">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-shopping-cart"></i> New Sale</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h6 class="mb-3"><i class="fas fa-boxes"></i> Products</h6>
                            <div style="max-height: 400px; overflow-y: auto;">
                                <?php while ($prod = $products->fetch_assoc()): ?>
                                <div onclick="addToCart(<?php echo htmlspecialchars(json_encode($prod)); ?>)" style="cursor:pointer;padding:8px;border:1px solid #eee;border-radius:5px;margin-bottom:5px;">
                                    <strong><?php echo $prod['product_name']; ?></strong>
                                    <div class="small text-muted">UG Shillings <?php echo number_format($prod['price_per_unit']); ?>/<?php echo $prod['unit']; ?></div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h6 class="mb-3"><i class="fas fa-shopping-basket"></i> Cart</h6>
                            <div id="cart-items" style="min-height: 150px; max-height: 250px; overflow-y: auto;">
                                <p class="text-muted text-center">Click products to add</p>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2"><strong>Total:</strong><strong id="cart-total">UG Shillings 0</strong></div>
                            <form method="POST" id="saleForm">
                                <input type="hidden" name="items_json" id="items_json">
                                <input type="text" name="customer_name" class="form-control mb-2" placeholder="Customer Name (Optional)">
                                <input type="tel" name="customer_phone" class="form-control mb-2" placeholder="Phone (Optional)">
                                <button type="submit" name="create_invoice" class="btn btn-primary w-100" onclick="submitSale()"><i class="fas fa-check"></i> Complete Sale</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let cart = [];
    function addToCart(product) {
        const existing = cart.find(item => item.product_id === product.id);
        if (existing) { if (existing.quantity < product.stock_quantity) existing.quantity++; }
        else { cart.push({product_id: product.id, name: product.product_name, price: product.price_per_unit, unit: product.unit, quantity: 1, max_stock: product.stock_quantity}); }
        renderCart();
    }
    function updateQuantity(index, change) {
        cart[index].quantity += change;
        if (cart[index].quantity < 1) cart.splice(index, 1);
        if (cart[index].quantity > cart[index].max_stock) cart[index].quantity = cart[index].max_stock;
        renderCart();
    }
    function renderCart() {
        const container = document.getElementById('cart-items');
        const totalEl = document.getElementById('cart-total');
        if (cart.length === 0) { container.innerHTML = '<p class="text-muted text-center">Click products to add</p>'; totalEl.textContent = 'UG Shillings 0'; return; }
        let html = ''; let total = 0;
        cart.forEach((item, index) => { const itemTotal = item.price * item.quantity; total += itemTotal;
            html += `<div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded"><div><strong>${item.name}</strong><div class="small">UG Shillings ${item.price} x ${item.quantity} ${item.unit}</div></div><div class="d-flex align-items-center gap-2"><button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, -1)">-</button><span>${item.quantity}</span><button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, 1)">+</button></div></div>`;
        });
        container.innerHTML = html;
        totalEl.textContent = 'UG Shillings ' + total.toLocaleString();
    }
    function submitSale() { document.getElementById('items_json').value = JSON.stringify(cart); }
    </script>
</body>
</html>