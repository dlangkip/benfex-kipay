<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Kipay Admin</title>
    
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
        
        .forgot-password-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        
        .forgot-password-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .forgot-password-header {
            background: #3490dc;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .forgot-password-body {
            padding: 30px;
        }
        
        .logo {
            height: 50px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <div class="forgot-password-card">
            <div class="forgot-password-header">
                <img src="/assets/images/logo.png" alt="Kipay" class="logo">
                <h2>Forgot Password</h2>
            </div>
            
            <div class="forgot-password-body">
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
                
                <p class="mb-4">Enter your email address below and we'll send you a link to reset your password.</p>
                
                <form method="POST" action="/admin/forgot-password">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Send Reset Link</button>
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
</body>
</html>