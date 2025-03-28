<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Kipay Payment Gateway</title>
    
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
            background: #10b981;
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
        
        .success-icon {
            font-size: 80px;
            color: #10b981;
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
    </style>
</head>
<body>
    <div class="container payment-container">
        <div class="payment-header">
            <img src="/assets/images/logo.png" alt="Kipay" class="logo">
            <h2>Payment Successful</h2>
        </div>
        
        <div class="payment-body">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            </div>
            
            <h3>Thank You for Your Payment</h3>
            <p class="lead mb-4">Your transaction has been completed successfully.</p>
            
            <div class="payment-summary">
                <div class="row">
                    <div class="col-md-6 text-start">
                        <p class="mb-1"><strong>Reference:</strong> <?php echo htmlspecialchars($transaction['reference'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($transaction['created_at'] ?? 'now')); ?></p>
                        <p class="mb-0"><strong>Payment Method:</strong> <?php echo htmlspecialchars($transaction['payment_method'] ?? 'Card'); ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h4 class="mb-1">Amount Paid</h4>
                        <h3 class="mb-0"><?php echo htmlspecialchars($transaction['currency'] ?? 'KSH'); ?> <?php echo number_format($transaction['amount'] ?? 0, 2); ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="<?php echo htmlspecialchars($continueUrl ?? '/'); ?>" class="btn btn-primary">Continue to Website</a>
                
                <?php if (isset($transaction['reference'])): ?>
                <a href="/payment/receipt/<?php echo htmlspecialchars($transaction['reference']); ?>" class="btn btn-outline-secondary ms-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                    </svg>
                    Print Receipt
                </a>
                <?php endif; ?>
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