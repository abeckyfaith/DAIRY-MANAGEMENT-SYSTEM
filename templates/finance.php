<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/rbac.php';

require_login();

// BLOCK STAFF - Only Manager+ can access
if (get_role_level() < 4) {
    $_SESSION['error'] = "You don't have permission to access this page.";
    if (get_role_level() == 2) {
        header("Location: index.php?page=dairy_shop");
    } else {
        header("Location: index.php?page=dashboard");
    }
    exit;
}

$title = "Finance";
$page = "finance";
require_once __DIR__ . "/partials/header.php";

$conn = get_db_connection();
$role = get_user_role();
$role_class = ($role == 'Farm Manager') ? 'manager' : 'admin';
    
$total_income = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM income")->fetch_row()[0];
$total_expenses = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM expenses")->fetch_row()[0];
$net_profit = $total_income - $total_expenses;
$this_month_income = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM income WHERE transaction_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetch_row()[0];
$this_month_expenses = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE transaction_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetch_row()[0];
?>

<!-- Welcome -->
<div class="welcome-banner <?php echo $role_class; ?> fade-in">
    <div>
        <h2><i class="fas fa-wallet"></i> Financial Management</h2>
        <p>Track income and expenses</p>
    </div>
    <div>
        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
            <i class="fas fa-plus"></i> Add Income
        </button>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="fas fa-minus"></i> Add Expense
        </button>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #d4edda; color: #28a745;"><i class="fas fa-arrow-up"></i></div>
            <div class="stat-info">
                <h3>UG Shillings <?php echo number_format($total_income, 0); ?></h3>
                <p>Total Income</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #f8d7da; color: #dc3545;"><i class="fas fa-arrow-down"></i></div>
            <div class="stat-info">
                <h3>UG Shillings <?php echo number_format($total_expenses, 0); ?></h3>
                <p>Total Expenses</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: <?php echo $net_profit >= 0 ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $net_profit >= 0 ? '#28a745' : '#dc3545'; ?>"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
                <h3>UG Shillings <?php echo number_format($net_profit, 0); ?></h3>
                <p>Net Profit</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #cce5ff; color: #007ce8;"><i class="fas fa-calendar"></i></div>
            <div class="stat-info">
                <h3>UG Shillings <?php echo number_format($this_month_income - $this_month_expenses, 0); ?></h3>
                <p>This Month</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-arrow-up"></i> Recent Income</div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM income ORDER BY transaction_date DESC LIMIT 10");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('d/m/Y', strtotime($row['transaction_date'])) . "</td>";
                            echo "<td><span class='badge bg-success'>{$row['category']}</span></td>";
                            echo "<td>UG Shillings " . number_format($row['amount'], 0) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-arrow-down"></i> Recent Expenses</div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM expenses ORDER BY transaction_date DESC LIMIT 10");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('d/m/Y', strtotime($row['transaction_date'])) . "</td>";
                            echo "<td><span class='badge bg-danger'>{$row['category']}</span></td>";
                            echo "<td>UG Shillings " . number_format($row['amount'], 0) . "</td>";
                            echo "</tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Income Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Income</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=add_income">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="Milk Sales">Milk Sales</option>
                            <option value="Animal Sales">Animal Sales</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Income</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=add_expense">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="Feed">Feed</option>
                            <option value="Veterinary">Veterinary</option>
                            <option value="Labor">Labor</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Add Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
