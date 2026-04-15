<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dairy Manager</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">

<div class="login-card fade-in">
    <div class="login-header">
        <div class="login-logo-box">
            <i class="fas fa-mug-hot"></i> <!-- Cup icon as per image 1 -->
        </div>
        <h1>Dairy Manager</h1>
        <p>Sign in to your account</p>
    </div>
    
    <div class="login-body">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger mb-4">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?page=login_process" method="POST">
            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control border-end-0" id="password" name="password" placeholder="Enter password" required>
                    <span class="input-group-text bg-white border-start-0" id="password-toggle">
                        <i class="fas fa-eye text-muted" style="cursor: pointer;"></i>
                    </span>
                </div>
            </div>
            
            <div class="mb-4 d-flex align-items-center">
                <input type="checkbox" class="form-check-input me-2" id="remember" name="remember">
                <label for="remember" class="form-check-label text-muted" style="font-size: 0.9rem;">Remember me</label>
            </div>
            
            <button type="submit" class="btn btn-signin">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="mt-4 p-3 bg-light rounded-3" style="font-size: 0.85rem;">
            <p class="mb-1 text-muted"><strong>Default Credentials:</strong></p>
            <div class="text-secondary">
                <div class="d-flex justify-content-between">
                    <span>Admin:</span> <span>admin / admin123</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Staff:</span> <span>staff / staff123</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('password-toggle').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>

</body>
</html>
