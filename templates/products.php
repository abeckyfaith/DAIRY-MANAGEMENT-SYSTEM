<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/activity_log.php';

require_login();
$user = get_current_user_info();
$role = get_user_role();
$conn = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $product_name = sanitize_input($_POST['product_name']);
        $category_id = intval($_POST['category_id']);
        $unit = sanitize_input($_POST['unit']);
        $price = floatval($_POST['price']);
        $stock = floatval($_POST['stock']);
        $low_stock = floatval($_POST['low_stock']);
        $description = sanitize_input($_POST['description']);
        
        $stmt = $conn->prepare("INSERT INTO products (product_name, category_id, unit, price_per_unit, stock_quantity, low_stock_threshold, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisddds", $product_name, $category_id, $unit, $price, $stock, $low_stock, $description);
        
        if ($stmt->execute()) {
            log_activity($user['id'], "Added new product: $product_name");
            $success = "Product added successfully!";
        } else {
            $error = "Error adding product: " . $conn->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['update_stock'])) {
        $product_id = intval($_POST['product_id']);
        $new_stock = floatval($_POST['new_stock']);
        $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
        $stmt->bind_param("di", $new_stock, $product_id);
        if ($stmt->execute()) {
            log_activity($user['id'], "Updated stock for product ID: $product_id");
            $success = "Stock updated successfully!";
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_product'])) {
        $id = intval($_POST['product_id']);
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            log_activity($user['id'], "Deleted product ID: $id");
            $success = "Product deleted successfully!";
        }
        $stmt->close();
    }
}

$products = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN product_categories c ON p.category_id = c.id ORDER BY p.product_name");
$categories = $conn->query("SELECT * FROM product_categories ORDER BY category_name");
$low_stock_products = $conn->query("SELECT * FROM products WHERE stock_quantity <= low_stock_threshold");
$total_stock = $conn->query("SELECT SUM(stock_quantity) as total FROM products")->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo APP_NAME; ?> - Products</title>
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
                <li class="nav-item"><a class="nav-link active" href="index.php?page=products"><i class="fas fa-box"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=suppliers"><i class="fas fa-truck"></i> Suppliers</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=sales"><i class="fas fa-cash-register"></i> Sales</a></li>
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
                <h1><i class="fas fa-boxes"></i> Product Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>

            <?php if (isset($success)): ?><div class="alert alert-success fade-in"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if (isset($error)): ?><div class="alert alert-danger fade-in"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>
            <?php if ($low_stock_products && $low_stock_products->num_rows > 0): ?>
            <div class="alert alert-danger fade-in"><i class="fas fa-exclamation-triangle"></i> <strong>Low Stock Alert:</strong> <?php echo $low_stock_products->num_rows; ?> product(s) are below threshold!</div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="fas fa-box"></i></div>
                    <div class="stat-info"><h3><?php echo $products ? $products->num_rows : 0; ?></h3><p>Total Products</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon accent"><i class="fas fa-warehouse"></i></div>
                    <div class="stat-info"><h3><?php echo number_format($total_stock['total'] ?? 0, 0); ?></h3><p>Total Stock</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon secondary"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-info"><h3><?php echo $low_stock_products ? $low_stock_products->num_rows : 0; ?></h3><p>Low Stock Items</p></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-list"></i> All Products</div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead><tr><th>ID</th><th>Product Name</th><th>Category</th><th>Unit</th><th>Price/Unit</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if ($products && $products->num_rows > 0): ?>
                                <?php while ($prod = $products->fetch_assoc()): 
                                    $is_low_stock = $prod['stock_quantity'] <= $prod['low_stock_threshold']; ?>
                                <tr>
                                    <td><?php echo $prod['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($prod['product_name']); ?></strong></td>
                                    <td><span class="badge bg-secondary"><?php echo $prod['category_name'] ?? 'Uncategorized'; ?></span></td>
                                    <td><?php echo $prod['unit']; ?></td>
                                    <td><strong>UG Shillings <?php echo number_format($prod['price_per_unit'], 0); ?></strong></td>
                                    <td><span class="<?php echo $is_low_stock ? 'text-danger fw-bold' : ''; ?>"><?php echo number_format($prod['stock_quantity'], 1); ?></span></td>
                                    <td>
                                        <?php if ($prod['stock_quantity'] <= 0): ?><span class="badge bg-danger">Out of Stock</span>
                                        <?php elseif ($is_low_stock): ?><span class="badge bg-warning">Low Stock</span>
                                        <?php else: ?><span class="badge bg-success">In Stock</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#stockModal<?php echo $prod['id']; ?>"><i class="fas fa-edit"></i></button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                                                <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                                <button type="submit" name="delete_product" class="btn btn-sm btn-outline-secondary"><i class="fas fa-trash text-danger"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <div class="modal fade" id="stockModal<?php echo $prod['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header"><h5 class="modal-title">Update Stock - <?php echo htmlspecialchars($prod['product_name']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <div class="mb-3"><label class="form-label">Current: <?php echo $prod['stock_quantity']; ?> <?php echo $prod['unit']; ?></label></div>
                                                    <div class="mb-3"><label class="form-label">New Stock</label><input type="number" name="new_stock" class="form-control" value="<?php echo $prod['stock_quantity']; ?>" step="0.01" required></div>
                                                    <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="update_stock" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-box-open fa-2x mb-2"></i><p>No products found.</p></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="product_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">Select Category</option>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit *</label>
                                <select name="unit" class="form-select" required>
                                    <option value="Liter">Liter</option><option value="Kilogram">Kilogram</option>
                                    <option value="Piece">Piece</option><option value="Pack">Pack</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price per Unit (UG Shillings) *</label>
                                <input type="number" name="price" class="form-control" required step="0.01">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Initial Stock</label>
                                <input type="number" name="stock" class="form-control" value="0" step="0.01">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Low Stock Threshold</label>
                                <input type="number" name="low_stock" class="form-control" value="10" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>