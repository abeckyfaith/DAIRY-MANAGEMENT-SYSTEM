<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-cow me-2"></i> <?php echo APP_NAME; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (can_access_page('dashboard')): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard">Dashboard</a></li>
                <?php endif; ?>

                <?php if (can_access_page('animals')): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=animals">Animals</a></li>
                <?php endif; ?>

                <?php if (can_access_page('milk_production')): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=milk_production">Milk Production</a></li>
                <?php endif; ?>

                <?php if (can_access_page('health')): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=health">Health</a></li>
                <?php endif; ?>

                <?php if (can_access_page('finance')): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=finance">Financials</a></li>
                <?php endif; ?>

                <?php if (can_access_page('inventory')): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=inventory">Inventory</a></li>
                <?php endif; ?>

                <?php if (can_access_page('dairy_shop')): ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=dairy_shop">Dairy Shop</a></li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item me-3">
                    <span class="role-badge">
                        ROLE: <?php echo strtoupper(get_user_role()); ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="index.php?page=profile">
                        <i class="fas fa-user-circle me-2"></i>
                        <strong><?php echo $_SESSION['username']; ?></strong>
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link text-danger" href="index.php?page=logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="index.php?page=login">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container pb-5">
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
