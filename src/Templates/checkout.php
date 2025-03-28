<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipay Payment Gateway - Checkout</title>
    
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
            background: #3490dc;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .payment-body {
            padding: 30px;
        }
        
        .payment-summary {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .payment-methods {
            margin-bottom: 30px;
        }
        
        .payment-method-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .payment-method-item:hover {
            border-color: #3490dc;
            background: #f8fafc;
        }
        
        .payment-method-item.active {
            border-color: #3490dc;
            background: #ebf4ff;
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
        
        .form-control {
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
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
            <h2>Secure Checkout</h2>
        </div>
        
        <div class="payment-body">
            <div class="payment-summary mb-4">
                <div class="row">
                    <div class="col-md-8">
                        <h4>Payment Summary</h4>
                        <p class="mb-1"><?php echo htmlspecialchars($transaction['description'] ?? 'Payment'); ?></p>
                        <p class="mb-0 text-muted">Reference: <?php echo htmlspecialchars($transaction['reference'] ?? ''); ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <h3 class="mb-0"><?php echo htmlspecialchars($transaction['currency'] ?? 'KSH'); ?> <?php echo number_format($transaction['amount'] ?? 0, 2); ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="payment-methods">
                <h4 class="mb-3">Select Payment Method</h4>
                
                <?php if (isset($paymentMethods) && !empty($paymentMethods)): ?>
                    <?php foreach ($paymentMethods as $method): ?>
                        <div class="payment-method-item" data-method="<?php echo htmlspecialchars($method['id']); ?>">
                            <div class="row align-items-center">
                                <div class="col-md-1">
                                    <input type="radio" name="payment_method" value="<?php echo htmlspecialchars($method['id']); ?>" id="method_<?php echo htmlspecialchars($method['id']); ?>">
                                </div>
                                <div class="col-md-2">
                                    <img src="<?php echo htmlspecialchars($method['icon']); ?>" alt="<?php echo htmlspecialchars($method['name']); ?>" height="40">
                                </div>
                                <div class="col-md-9">
                                    <label for="method_<?php echo htmlspecialchars($method['id']); ?>" class="mb-0 fw-bold"><?php echo htmlspecialchars($method['name']); ?></label>
                                    <?php if (!empty($method['description'])): ?>
                                        <p class="mb-0 small text-muted"><?php echo htmlspecialchars($method['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">No payment methods available. Please contact the merchant.</div>
                <?php endif; ?>
            </div>
            
            <div class="customer-info mb-4">
                <h4 class="mb-3">Customer Information</h4>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" readonly>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" value="<?php echo htmlspecialchars($customer['first_name'] ?? ''); ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" value="<?php echo htmlspecialchars($customer['last_name'] ?? ''); ?>" readonly>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="button" id="payButton" class="btn btn-primary btn-lg">Pay <?php echo htmlspecialchars($transaction['currency'] ?? 'KSH'); ?> <?php echo number_format($transaction['amount'] ?? 0, 2); ?></button>
                <a href="<?php echo htmlspecialchars($cancelUrl ?? '/'); ?>" class="btn btn-link">Cancel Payment</a>
            </div>
        </div>
        
        <div class="payment-footer">
            <p class="mb-0">Powered by Kipay Payment Gateway &copy; <?php echo date('Y'); ?></p>
            <div class="mt-2">
                <img src="/assets/images/secure-payment.png" alt="Secure Payment" height="30">
            </div>
        </div>
    </div>
    
    <!-- PaystackJS -->
    <script src="https://js.paystack.co/v1/inline.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/kipay.js"></script>
    
    <script>
        $(document).ready(function() {
            // Select payment method
            $('.payment-method-item').click(function() {
                $('.payment-method-item').removeClass('active');
                $(this).addClass('active');
                
                // Select the radio button
                const methodId = $(this).data('method');
                $(`#method_${methodId}`).prop('checked', true);
            });
            
            // Handle payment button click
            $('#payButton').click(function() {
                const selectedMethod = $('input[name="payment_method"]:checked').val();
                
                if (!selectedMethod) {
                    alert('Please select a payment method');
                    return;
                }
                
                // Initialize payment based on selected method
                initializePayment(selectedMethod);
            });
            
            // Initialize payment based on method
            function initializePayment(methodId) {
                // Get transaction information
                const transaction = <?php echo json_encode($transaction ?? []); ?>;
                const paystackConfig = <?php echo json_encode($paystackConfig ?? []); ?>;
                
                if (!transaction || !paystackConfig) {
                    alert('Transaction information not available');
                    return;
                }
                
                // Initialize Paystack payment
                PaystackPopup.setup({
                    key: paystackConfig.public_key,
                    email: transaction.email,
                    amount: transaction.amount * 100, // Convert to kobo
                    currency: transaction.currency,
                    ref: transaction.reference,
                    callback: function(response) {
                        // Redirect to success URL with reference
                        window.location.href = `/payment/verify/${response.reference}`;
                    },
                    onClose: function() {
                        // Payment window closed
                        console.log('Payment window closed');
                    }
                });
            }
        });
    </script>
</body>
</html>