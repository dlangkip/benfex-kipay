<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - Kipay Payment Gateway</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/styles.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    
    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .payment-container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .payment-header {
            background: #ef4444;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .payment-body {
            padding: 40px;
            text-align: center;
        }
        
        .payment-summary {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .btn-primary {
            background: #3490dc;
            border-color: #3490dc;
            padding: 12px 25px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: #2779bd;
            border-color: #2779bd;
        }
        
        .logo {
            height: 40px;
            margin-bottom: 10px;
        }
        
        .error-icon {
            font-size: 80px;
            color: #ef4444;
            margin-bottom: 20px;
        }
        
        .payment-footer {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #718096;
        }
        
        .error-details {
            background-color: #fef2f2;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container payment-container">
        <div class="payment-header">
            <img src="/assets/images/logo.png" alt="Kipay" class="logo">
            <h2>Payment Failed</h2>
        </div>
        
        <div class="payment-body">
            <div class="error-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-exclamation-circle-fill" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                </svg>
            </div>
            
            <h3>Payment Unsuccessful</h3>
            <p class="lead mb-4">Your transaction could not be completed. Please try again or use a different payment method.</p>
            
            <div class="payment-summary">
                <div class="row">
                    <div class="col-md-6 text-start">
                        <p class="mb-1"><strong>Reference:</strong> <?php echo htmlspecialchars($transaction['reference'] ?? ''); ?></p>
                        <p class="mb-0"><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($transaction['created_at'] ?? 'now')); ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h4 class="mb-1">Amount</h4>
                        <h3 class="mb-0"><?php echo htmlspecialchars($transaction['currency'] ?? 'KSH'); ?> <?php echo number_format($transaction['amount'] ?? 0, 2); ?></h3>
                    </div>
                </div>
                
                <?php if (!empty($errorMessage)): ?>
                <div class="error-details mt-3">
                    <p class="mb-0"><strong>Error Details:</strong> <?php echo htmlspecialchars($errorMessage); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-4">
                <a href="<?php echo htmlspecialchars($retryUrl ?? '/payment/checkout/' . ($transaction['reference'] ?? '')); ?>" class="btn btn-primary">Try Again</a>
                <a href="<?php echo htmlspecialchars($cancelUrl ?? '/'); ?>" class="btn btn-outline-secondary ms-2">Cancel Payment</a>
            </div>
            
            <div class="mt-4">
                <p>Having trouble? Contact customer support:</p>
                <p><a href="mailto:support@kipay.com">support@kipay.com</a> | <a href="tel:+254700 760 386">++254 700 760 386</a></p>
            </div>
        </div>
        
        <div class="payment-footer">
            <p class="mb-0">Powered by Kipay Payment Gateway &copy; <?php echo date('Y'); ?></p>
            <div class="mt-2">
                <img src="/assets/images/secure-payment.png" alt="Secure Payment" height="30">
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>