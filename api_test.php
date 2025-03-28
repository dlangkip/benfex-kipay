<?php
/**
 * Kipay Payment Flow Test Script
 * 
 * This script tests the complete payment flow, including transaction creation,
 * payment UI, and verification.
 * 
 * Usage: Open in browser - http://your-domain.com/payment_test.php
 */

// Configuration - Change these values
$apiKey = '123456789';
$apiUrl = 'https://kipay.benfex.net'; // Replace with your Kipay URL

// Test parameters
$amount = 100; // Amount in lowest currency unit (e.g., kobo for NGN)
$email = 'customer@example.com';
$currency = 'KSH';
$description = 'Test Payment';

// Start a session to store transaction data
session_start();

// Function to make API requests
function makeApiRequest($endpoint, $method = 'GET', $data = null, $apiKey = null) {
    global $apiUrl;
    
    $ch = curl_init($apiUrl . $endpoint);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $headers = ['Accept: application/json'];
    
    if ($apiKey) {
        $headers[] = 'X-API-Key: ' . $apiKey;
    }
    
    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }
        
        if ($data) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($jsonData);
        }
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response ? json_decode($response, true) : null,
        'error' => $error
    ];
}

// Function to get payment channels
function getPaymentChannels($apiKey) {
    $result = makeApiRequest('/api/payment-channels/list?active_only=true', 'GET', null, $apiKey);
    
    if ($result['http_code'] === 200 && isset($result['response']['channels']) && !empty($result['response']['channels'])) {
        return $result['response']['channels'];
    }
    
    return [];
}

// Function to initialize transaction
function initializeTransaction($apiKey, $amount, $email, $channelId, $currency, $description) {
    $transactionData = [
        'amount' => $amount,
        'email' => $email,
        'payment_channel_id' => $channelId,
        'description' => $description,
        'currency' => $currency
    ];
    
    $result = makeApiRequest('/api/transactions/initialize', 'POST', $transactionData, $apiKey);
    
    if ($result['http_code'] === 200 && isset($result['response']['reference'])) {
        return $result['response'];
    }
    
    return null;
}

// Function to verify transaction
function verifyTransaction($apiKey, $reference) {
    $result = makeApiRequest("/api/transactions/verify/$reference", 'GET', null, $apiKey);
    
    if ($result['http_code'] === 200) {
        return $result['response'];
    }
    
    return null;
}

// Handle form submission
$error = null;
$success = null;
$transactionData = null;
$verificationResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check action
    if (isset($_POST['action'])) {
        // Initialize Payment
        if ($_POST['action'] === 'initialize') {
            // Get first available payment channel
            $channels = getPaymentChannels($apiKey);
            
            if (!empty($channels)) {
                $channelId = $channels[0]['id'];
                $transactionData = initializeTransaction($apiKey, $amount, $email, $channelId, $currency, $description);
                
                if ($transactionData) {
                    // Store transaction reference in session
                    $_SESSION['transaction_reference'] = $transactionData['reference'];
                    
                    // Redirect to payment page
                    header('Location: ' . $transactionData['authorization_url']);
                    exit;
                } else {
                    $error = "Failed to initialize transaction. Please check your API key and payment channel.";
                }
            } else {
                $error = "No active payment channels found. Please configure a payment channel first.";
            }
        }
        // Verify Payment
        elseif ($_POST['action'] === 'verify') {
            if (isset($_SESSION['transaction_reference'])) {
                $reference = $_SESSION['transaction_reference'];
                $verificationResult = verifyTransaction($apiKey, $reference);
                
                if ($verificationResult && isset($verificationResult['transaction'])) {
                    $success = "Transaction verified: " . $verificationResult['transaction']['status'];
                } else {
                    $error = "Failed to verify transaction.";
                }
            } else {
                $error = "No transaction reference found. Please initialize a transaction first.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipay Payment Flow Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fc;
            padding: 40px 0;
        }
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .step-container {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .step-header {
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: #3490dc;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 10px;
        }
        .transaction-info {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        .result-container {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container test-container">
        <h1 class="mb-4">Kipay Payment Flow Test</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- Step 1: Transaction Initialization -->
        <div class="step-container">
            <div class="step-header">
                <span class="step-number">1</span>
                <h3 class="mb-0">Initialize Transaction</h3>
            </div>
            <p>This step creates a new transaction and redirects to the payment page.</p>
            
            <form method="post" action="">
                <input type="hidden" name="action" value="initialize">
                
                <div class="mb-3">
                    <label class="form-label">Amount:</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($amount); ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Currency:</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($currency); ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description:</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($description); ?>" readonly>
                </div>
                
                <button type="submit" class="btn btn-primary">Initialize Payment</button>
            </form>
            
            <?php if (isset($_SESSION['transaction_reference'])): ?>
                <div class="transaction-info">
                    <p class="mb-0"><strong>Transaction Reference:</strong> <?php echo htmlspecialchars($_SESSION['transaction_reference']); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Step 2: Verify Transaction -->
        <div class="step-container">
            <div class="step-header">
                <span class="step-number">2</span>
                <h3 class="mb-0">Verify Transaction</h3>
            </div>
            <p>After completing payment (or attempting payment), verify the transaction status.</p>
            
            <?php if (isset($_SESSION['transaction_reference'])): ?>
                <form method="post" action="">
                    <input type="hidden" name="action" value="verify">
                    <button type="submit" class="btn btn-success">Verify Payment</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info">Please initialize a transaction first.</div>
            <?php endif; ?>
            
            <?php if ($verificationResult): ?>
                <div class="transaction-info">
                    <h4>Verification Result:</h4>
                    <p><strong>Reference:</strong> <?php echo htmlspecialchars($verificationResult['transaction']['reference']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($verificationResult['transaction']['status']); ?></p>
                    <p><strong>Amount:</strong> <?php echo htmlspecialchars($verificationResult['transaction']['currency'] . ' ' . number_format($verificationResult['transaction']['amount'], 2)); ?></p>
                    <?php if (isset($verificationResult['transaction']['payment_method'])): ?>
                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($verificationResult['transaction']['payment_method']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Test Information -->
        <div class="result-container">
            <h3>Test Cards</h3>
            <p>Use these test cards when testing with Paystack:</p>
            <ul>
                <li><strong>Success:</strong> 4084 0840 8408 4081, CVV: 408, Expiry: any future date</li>
                <li><strong>Failed:</strong> 4084 0840 8408 4082, CVV: 408, Expiry: any future date</li>
                <li><strong>Requires OTP:</strong> 4084 0840 8408 4083, CVV: 408, Expiry: any future date, OTP: 123456</li>
            </ul>
            
            <h3>Testing Notes</h3>
            <ul>
                <li>Make sure your API key and payment channel are correctly configured.</li>
                <li>Test both successful and failed payment scenarios.</li>
                <li>Verify that the payment status is correctly updated in your system.</li>
                <li>Check webhook functionality separately by using the webhook test script.</li>
            </ul>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>