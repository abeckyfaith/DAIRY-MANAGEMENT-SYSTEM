<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (isset($title) ? $title . " - " : "") . APP_NAME; ?></title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">
<?php if (isset($_SESSION['user_id'])): ?>
    <!-- Fading Cow Background -->
    <div class="bg-cow-container">
        <svg class="bg-cow bg-cow-1" viewBox="0 0 200 200" fill="var(--primary-dark)"><ellipse cx="100" cy="110" rx="60" ry="50"/><ellipse cx="40" cy="70" rx="20" ry="12" transform="rotate(-30 40 70)"/><ellipse cx="160" cy="70" rx="20" ry="12" transform="rotate(30 160 70)"/><path d="M50 60 Q35 25 55 40" stroke="currentColor" stroke-width="8" fill="none" stroke-linecap="round"/><path d="M150 60 Q165 25 145 40" stroke="currentColor" stroke-width="8" fill="none" stroke-linecap="round"/><circle cx="75" cy="95" r="12" fill="rgba(102,126,234,0.5)"/><circle cx="130" cy="115" r="8" fill="rgba(102,126,234,0.5)"/><circle cx="80" cy="90" r="8" fill="#fff"/><circle cx="130" cy="90" r="8" fill="#fff"/><circle cx="82" cy="92" r="4" fill="#333"/><circle cx="132" cy="92" r="4" fill="#333"/><ellipse cx="100" cy="130" rx="24" ry="16" fill="#f8b4d9"/><circle cx="92" cy="130" r="4" fill="var(--primary-dark)"/><circle cx="108" cy="130" r="4" fill="var(--primary-dark)"/></svg>
        <svg class="bg-cow bg-cow-2" viewBox="0 0 200 200" fill="var(--primary)"><ellipse cx="100" cy="110" rx="60" ry="50"/><ellipse cx="40" cy="70" rx="20" ry="12" transform="rotate(-30 40 70)"/><ellipse cx="160" cy="70" rx="20" ry="12" transform="rotate(30 160 70)"/><path d="M50 60 Q35 25 55 40" stroke="currentColor" stroke-width="8" fill="none" stroke-linecap="round"/><path d="M150 60 Q165 25 145 40" stroke="currentColor" stroke-width="8" fill="none" stroke-linecap="round"/><circle cx="75" cy="95" r="12" fill="rgba(118,75,162,0.5)"/><circle cx="130" cy="115" r="8" fill="rgba(118,75,162,0.5)"/><circle cx="80" cy="90" r="8" fill="#fff"/><circle cx="130" cy="90" r="8" fill="#fff"/><circle cx="82" cy="92" r="4" fill="#333"/><circle cx="132" cy="92" r="4" fill="#333"/><ellipse cx="100" cy="130" rx="24" ry="16" fill="#f8b4d9"/><circle cx="92" cy="130" r="4" fill="var(--primary)"/><circle cx="108" cy="130" r="4" fill="var(--primary)"/></svg>
    </div>

    <?php require_once "templates/partials/sidebar.php"; ?>

    <main class="main-content">
        <header class="topbar">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="h5 mb-0 fw-bold"><?php echo $title ?? 'Dashboard'; ?></h1>
            
            <div class="topbar-right ms-auto d-flex align-items-center">
                <div class="dropdown">
                    <button class="btn btn-link text-dark text-decoration-none dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fs-4 me-2 text-primary"></i>
                        <span class="d-none d-sm-inline fw-semibold"><?php echo $_SESSION['username'] ?? 'User'; ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <li><a class="dropdown-item py-2" href="index.php?page=profile"><i class="fas fa-user me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item py-2" href="index.php?page=settings"><i class="fas fa-cog me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="index.php?page=logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="container-fluid p-4">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
<?php endif; ?>
