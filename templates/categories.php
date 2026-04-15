<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/activity_log.php';

require_login();
$user = get_current_user_info();
$conn = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $category_name = sanitize_input($_POST['category_name']);
        $category_description = sanitize_input($_POST['category_description']);
        
        $stmt = $conn->prepare("INSERT INTO product_categories (category_name, category_description) VALUES (?, ?)");
        $stmt->bind_param("ss", $category_name, $category_description);
        
        if ($stmt->execute()) {
            log_activity($user['id'], "Added new product category: $category_name");
            $success = "Category added successfully!";
        } else {
            $error = "Error adding category: " . $conn->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_category'])) {
        $id = intval($_POST['category_id']);
        $stmt = $conn->prepare("DELETE FROM product_categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            log_activity($user['id'], "Deleted product category ID: $id");
            $success = "Category deleted successfully!";
        }
        $stmt->close();
    }
}

$categories = $conn->query("SELECT * FROM product_categories ORDER BY category_name");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo APP_NAME; ?> - Product Categories</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <nav class="sidebar<?php echo $role === 'Staff' ? ' staff' : ''; ?>">
            <div class="sidebar-brand">
                <i class="fas fa-mug-hot"></i>
                <span>Dairy Manager</span>
            </div>
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
                <li class="nav-item"><a class="nav-link active" href="index.php?page=categories"><i class="fas fa-tags"></i> Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=products"><i class="fas fa-box"></i> Products</a></li>
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
                <h1><i class="fas fa-tags"></i> Product Categories</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>

            <?php if (isset($success)): ?>
            <div class="alert alert-success fade-in">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger fade-in">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> All Categories
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($categories->num_rows > 0): ?>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $cat['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($cat['category_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($cat['category_description'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d M Y', strtotime($cat['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                            <button type="submit" name="delete_category" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-trash text-danger"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-folder-open fa-2x mb-2"></i>
                                        <p>No categories found. Add your first category!</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="category_name" class="form-control" required placeholder="e.g., Milk, Cheese, Yogurt">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="category_description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>