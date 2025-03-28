<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Kipay Payment Gateway</title>
    
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
            text-align: center;
        }
        
        .error-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
        }
        
        .error-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 40px;
        }
        
        .error-icon {
            font-size: 80px;
            color: #ef4444;
            margin-bottom: 20px;
        }
        
        .error-code {
            font-size: 36px;
            font-weight: 700;
            color: #ef4444;
            margin-bottom: 10px;
        }
        
        .back-button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
            </div>
            
            <div class="error-code">500</div>
            <h1 class="mb-4">Internal Server Error</h1>
            
            <p class="lead">Sorry, something went wrong on our server. We are working to fix the problem as soon as possible.</p>
            
            <?php if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true'): ?>
                <div class="alert alert-danger mt-4 text-start">
                    <h5>Error Details:</h5>
                    <p><strong>Message:</strong> <?php echo htmlspecialchars($e->getMessage()); ?></p>
                    <p><strong>File:</strong> <?php echo htmlspecialchars($e->getFile()); ?></p>
                    <p><strong>Line:</strong> <?php echo $e->getLine(); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="back-button">
                <a href="/" class="btn btn-primary">Go to Homepage</a>
                <button class="btn btn-outline-secondary ms-2" onclick="window.history.back();">Go Back</button>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>