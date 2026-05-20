<?php
$title = "Worker Dashboard";
require_once "includes/auth.php";
require_once "includes/rbac.php";
require_once "templates/partials/header.php";

$pdo = get_pdo_connection();
$role_level = get_role_level();
$user = get_current_user_info();
$role = get_user_role();

// Require worker or higher access
if ($role_level < 2) {
    redirect('login');
}
?>
<div class="welcome-banner worker fade-in">
    <div>
        <h2 class="mb-3 text-white">Welcome, <?php echo htmlspecialchars($user['full_name'] ?? $_SESSION['username']); ?></h2>
        <div class="d-flex align-items-center">
            <span class="banner-badge">WORKER DASHBOARD</span>
            <span class="opacity-75 text-white">- Product Management Focus</span>
        </div>
    </div>
</div>

<div class="row fade-in">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 text-secondary fw-bold">Product Stock Management</h5>
            </div>
            <div class="card-body p-0">
                <div class="d-grid gap-2">
                    <a href="index.php?page=products" class="btn btn-outline-primary w-100">
                        <i class="fas fa-boxes me-2"></i> View All Products
                    </a>
                    <a href="index.php?page=inventory" class="btn btn-outline-primary w-100">
                        <i class="fas fa-warehouse me-2"></i> Inventory Overview
                    </a>
                    <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#quickStockUpdate">
                        <i class="fas fa-edit me-2"></i> Quick Stock Update
                    </button>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        As a worker, you can view and update product stock levels.
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 text-secondary fw-bold">Today's Tasks</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="index.php?page=record_milk" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><i class="fas fa-glass-cheers me-2"></i>Record Milk Production</h6>
                            <small class="text-muted">Log daily milk yields</small>
                        </div>
                    </a>
                    <a href="index.php?page=feed" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><i class="fas fa-leaf me-2"></i>Feed Management</h6>
                            <small class="text-muted">Check feed inventory</small>
                        </div>
                    </a>
                    <a href="index.php?page=health_check" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><i class="fas fa-stethoscope me-2"></i>Animal Health Checks</h6>
                            <small class="text-muted">Monitor animal wellbeing</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stock Update Modal -->
<div class="modal fade" id="quickStockUpdate" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Quick Stock Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="stockUpdateForm">
                    <div class="mb-3">
                        <label class="form-label">Select Product</label>
                        <select class="form-select" id="productSelect" required>
                            <option value="">-- Select Product --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="number" class="form-control" id="currentStock" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Stock Level</label>
                        <input type="number" class="form-control" id="newStock" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" id="productUnit" readonly>
                    </div>
                    <div id="formMessage"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="stockUpdateForm" class="btn btn-primary">Update Stock</button>
            </div>
        </div>
    </div>
</div>

<?php require_once "templates/partials/footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load products for the dropdown
    fetch('get_products.php')
        .then(response => response.json())
        .then(products => {
            const productSelect = document.getElementById('productSelect');
            products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.product_name} (${product.unit})`;
                productSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading products:', error);
            document.getElementById('formMessage').innerHTML = '<div class="alert alert-danger">Error loading products</div>';
        });

    // Handle product selection
    document.getElementById('productSelect').addEventListener('change', function() {
        const productId = this.value;
        if (productId) {
            // In a real app, you'd fetch product details via AJAX
            // For now, we'll simulate with static data or make an actual AJAX call
            document.getElementById('currentStock').value = 'Loading...';
            document.getElementById('productUnit').value = 'Loading...';
            
            // Simulate AJAX call to get product details
            setTimeout(() => {
                // This would normally come from an API endpoint
                document.getElementById('currentStock').value = '25.5';
                document.getElementById('productUnit').value = 'Liters';
            }, 500);
        } else {
            document.getElementById('currentStock').value = '';
            document.getElementById('productUnit').value = '';
        }
    });

    // Handle form submission
    document.getElementById('stockUpdateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const productId = document.getElementById('productSelect').value;
        const newStock = document.getElementById('newStock').value;
        
        if (!productId || !newStock) {
            document.getElementById('formMessage').innerHTML = '<div class="alert alert-danger">Please fill all fields</div>';
            return;
        }
        
        // Show loading state
        document.getElementById('formMessage').innerHTML = '<div class="alert alert-info">Updating stock...</div>';
        
        // Simulate AJAX request
        setTimeout(() => {
            document.getElementById('formMessage').innerHTML = '<div class="alert alert-success">Stock updated successfully!</div>';
            
            // Reset form
            this.reset();
            document.getElementById('currentStock').value = '';
            document.getElementById('productUnit').value = '';
            
            // Hide message after 3 seconds
            setTimeout(() => {
                document.getElementById('formMessage').innerHTML = '';
            }, 3000);
        }, 1000);
    });
});
</script>