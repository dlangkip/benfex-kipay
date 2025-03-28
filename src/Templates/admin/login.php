<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kipay Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/styles.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    
    <style>
        body {
            background-color: #f7f9fc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: #3490dc;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .logo {
            height: 50px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="/assets/images/logo.png" alt="Kipay" class="logo">
                <h2>Admin Login</h2>
            </div>
            
            <div class="login-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['debug_reset_url'])): ?>
                    <div class="alert alert-info">
                        <strong>Debug:</strong> Reset URL: <a href="<?php echo htmlspecialchars($_SESSION['debug_reset_url']); ?>"><?php echo htmlspecialchars($_SESSION['debug_reset_url']); ?></a>
                        <?php unset($_SESSION['debug_reset_url']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="/admin/login">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Log In</button>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <a href="/admin/forgot-password">Forgot Password?</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="mt-3 text-center">
            <p class="text-muted">&copy; <?php echo date('Y'); ?> Kipay Payment Gateway</p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>