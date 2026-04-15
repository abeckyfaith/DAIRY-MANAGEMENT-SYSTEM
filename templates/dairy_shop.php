<?php
$title = "Dairy Shop";
$page = "dairy_shop";
require_once __DIR__ . "/partials/header.php";

$pdo = get_pdo_connection();

// Handle sale submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_sale'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (float)$_POST['quantity'];
    $price_per_unit = (float)$_POST['price_per_unit'];
    $total_price = $quantity * $price_per_unit;
    
    try {
        // Check if shop_sales table exists, if not create it
        $pdo->exec("CREATE TABLE IF NOT EXISTS shop_sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            total_sales DECIMAL(10,2) NOT NULL,
            sale_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $stmt = $pdo->prepare("INSERT INTO shop_sales (product_id, quantity, unit_price, total_sales, sale_date, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$product_id, $quantity, $price_per_unit, $total_price, date('Y-m-d')]);
        
        log_activity("Sold product ID $product_id - Qty: $quantity, Total: $total_price");
        
        $_SESSION['success'] = "Sale recorded successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error recording sale: " . $e->getMessage();
    }
    
    redirect('dairy_shop');
}

// Get products for sale
$products = $pdo->query("SELECT * FROM products ORDER BY product_name")->fetchAll();
?>

<!-- Welcome -->
<div class="welcome-banner staff fade-in">
    <div>
        <h2><i class="fas fa-shopping-cart"></i> Dairy Shop</h2>
        <p>Record daily sales</p>
    </div>
    <a href="index.php?page=logout" class="btn btn-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger fade-in"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success fade-in"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="card fade-in">
    <div class="card-header"><i class="fas fa-plus-circle"></i> Record Sale</div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <input type="hidden" name="record_sale" value="1">
            <div class="col-md-4">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price_per_unit']; ?>">
                        <?php echo htmlspecialchars($product['product_name']); ?> - UG Shillings <?php echo number_format($product['price_per_unit'], 0); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" step="0.01" min="0.01" value="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Price per Unit</label>
                <input type="number" name="price_per_unit" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Total</label>
                <input type="text" class="form-control" id="total_display" disabled value="0">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Record Sale</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelector('select[name="product_id"]').addEventListener('change', function() {
    const price = this.options[this.selectedIndex]?.dataset.price || 0;
    document.querySelector('input[name="price_per_unit"]').value = price;
    calculateTotal();
});

document.querySelector('input[name="quantity"]').addEventListener('input', calculateTotal);
document.querySelector('input[name="price_per_unit"]').addEventListener('input', calculateTotal);

function calculateTotal() {
    const qty = parseFloat(document.querySelector('input[name="quantity"]').value) || 0;
    const price = parseFloat(document.querySelector('input[name="price_per_unit"]').value) || 0;
    document.getElementById('total_display').value = (qty * price).toFixed(0);
}
</script>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
