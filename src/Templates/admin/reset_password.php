<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Kipay Admin</title>
    
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
        
        .reset-password-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        
        .reset-password-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .reset-password-header {
            background: #3490dc;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .reset-password-body {
            padding: 30px;
        }
        
        .logo {
            height: 50px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-password-card">
            <div class="reset-password-header">
                <img src="/assets/images/logo.png" alt="Kipay" class="logo">
                <h2>Reset Password</h2>
            </div>
            
            <div class="reset-password-body">
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
                
                <p class="mb-4">Please create a new password for your account.</p>
                
                <form method="POST" action="/admin/reset-password?token=<?php echo htmlspecialchars($token); ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Password must be at least 8 characters long.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                        <a href="/admin/login" class="btn btn-link">Back to Login</a>
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
    
    <script>
        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.onchange = validatePassword;
        confirmPassword.onkeyup = validatePassword;
    </script>
</body>
</html>