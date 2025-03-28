<?php
/**
 * Kipay Payment Gateway for PHPNuxBill (Benfex Customization)
 * 
 * This file integrates the Kipay payment gateway with PHPNuxBill.
 * 
 * @package Kipay
 * @version 1.0.0
 */

// Load configuration
require_once 'kipay_config.php';

/**
 * Process payment through Kipay
 * 
 * @param array $invoice Invoice data
 * @return array Payment response
 */
function kipay_payment_processing($invoice)
{
    global $config, $kipay_config;
    
    // Check if gateway is enabled
    if (!$kipay_config['active']) {
        return [
            'status' => 'error',
            'message' => 'Kipay Payment Gateway is currently disabled.'
        ];
    }
    
    // Get client data
    $client = ORM::for_table('tbl_customers')->find_one($invoice['userid']);
    
    if (!$client) {
        return [
            'status' => 'error',
            'message' => 'Client not found.'
        ];
    }
    
    // Prepare transaction data
    $amount = number_format($invoice['total'], 2, '.', '');
    $currency = $config['currency_code'];
    $reference = 'INV-' . $invoice['id'];
    $payment_channel_id = $kipay_config['payment_channel_id'];
    
    // Set description
    $description = "Payment for Invoice #{$invoice['id']}";
    
    // Add customer details
    $email = $client['email'];
    $first_name = $client['fullname'];
    $phone = $client['phonenumber'];
    
    // Additional metadata
    $metadata = [
        'invoice_id' => $invoice['id'],
        'client_id' => $invoice['userid'],
        'source' => 'phpnuxbill'
    ];
    
    // Set custom fields if available
    $fields = [];
    
    if (!empty($invoice['description'])) {
        $fields[] = [
            'display_name' => 'Description',
            'variable_name' => 'description',
            'value' => $invoice['description']
        ];
    }
    
    if (!empty($fields)) {
        $metadata['custom_fields'] = $fields;
    }
    
    // Prepare API request data
    $data = [
        'amount' => $amount,
        'email' => $email,
        'payment_channel_id' => $payment_channel_id,
        'reference' => $reference,
        'currency' => $currency,
        'description' => $description,
        'first_name' => $first_name,
        'phone' => $phone,
        'metadata' => $metadata
    ];
    
    // Initialize transaction via API
    $result = kipay_api_request('transactions/initialize', 'POST', $data);
    
    if (isset($result['status']) && $result['status'] === 'success') {
        // Save transaction reference to database
        ORM::for_table('tbl_payment_gateway')
            ->where('invoice_id', $invoice['id'])
            ->find_one()
            ->set('gateway_ref_id', $result['reference'])
            ->save();
        
        // Return success with authorization URL
        return [
            'status' => 'success',
            'redirect_url' => $result['authorization_url'],
            'reference' => $result['reference']
        ];
    } else {
        // Return error message
        return [
            'status' => 'error',
            'message' => $result['message'] ?? 'Failed to initialize transaction. Please try again.'
        ];
    }
}

/**
 * Check payment status via Kipay
 * 
 * @param array $invoice Invoice data
 * @return array Payment status
 */
function kipay_payment_check($invoice)
{
    global $kipay_config;
    
    // Check if gateway is enabled
    if (!$kipay_config['active']) {
        return [
            'status' => 'error',
            'message' => 'Kipay Payment Gateway is currently disabled.'
        ];
    }
    
    // Get transaction reference
    $payment_gateway = ORM::for_table('tbl_payment_gateway')
        ->where('invoice_id', $invoice['id'])
        ->find_one();
    
    if (!$payment_gateway || empty($payment_gateway['gateway_ref_id'])) {
        return [
            'status' => 'error',
            'message' => 'Transaction reference not found.'
        ];
    }
    
    $reference = $payment_gateway['gateway_ref_id'];
    
    // Verify transaction via API
    $result = kipay_api_request('transactions/verify/' . $reference, 'GET');
    
    if (isset($result['status']) && $result['status'] === 'success') {
        $transaction = $result['transaction'];
        
        // Check transaction status
        if ($transaction['status'] === 'completed') {
            return [
                'status' => 'success',
                'message' => 'Payment completed successfully.',
                'paid_amount' => $transaction['amount'],
                'payment_method' => $transaction['payment_method'],
                'transaction_id' => $transaction['reference']
            ];
        } elseif ($transaction['status'] === 'pending') {
            return [
                'status' => 'pending',
                'message' => 'Payment is pending. Please wait a moment.',
                'transaction_id' => $transaction['reference']
            ];
        } else {
            return [
                'status' => 'failed',
                'message' => 'Payment failed or was cancelled.',
                'transaction_id' => $transaction['reference']
            ];
        }
    } else {
        // Return error message
        return [
            'status' => 'error',
            'message' => $result['message'] ?? 'Failed to verify transaction. Please try again.'
        ];
    }
}

/**
 * Make API request to Kipay
 * 
 * @param string $endpoint API endpoint
 * @param string $method HTTP method
 * @param array $data Request data
 * @return array API response
 */
function kipay_api_request($endpoint, $method = 'GET', $data = [])
{
    global $kipay_config;
    
    // API credentials
    $api_key = $kipay_config['api_key'];
    $api_url = $kipay_config['api_url'];
    
    // Build complete URL
    $url = rtrim($api_url, '/') . '/api/' . ltrim($endpoint, '/');
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-Key: ' . $api_key
    ]);
    
    // Set method and data
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'status' => 'error',
            'message' => 'API request failed: ' . $error,
            'http_code' => $httpCode
        ];
    }
    
    // Close cURL
    curl_close($ch);
    
    // Parse response
    $result = json_decode($response, true);
    
    // Check for valid JSON
    if ($result === null) {
        return [
            'status' => 'error',
            'message' => 'Invalid response from API',
            'http_code' => $httpCode,
            'raw_response' => $response
        ];
    }
    
    return $result;
}

/**
 * Initialize Kipay payment gateway
 * 
 * @return array Gateway configuration
 */
function kipay_init()
{
    global $d, $kipay_config;
    
    // Register payment hook
    _register_payment_hook('kipay', 'kipay_payment_processing', 'kipay_payment_check');
    
    // Add admin menu item
    add_menu_admin('Kipay Settings', U . 'settings/app-settings', 'money', '4', '');
    
    // Get existing configuration
    $kipay_settings = ORM::for_table('tbl_appconfig')
        ->where('setting', 'kipay_settings')
        ->find_one();
    
    // Initialize default configuration
    if (!$kipay_settings) {
        $default_config = [
            'active' => false,
            'api_key' => '',
            'api_url' => 'https://kipay.benfex.net',
            'payment_channel_id' => '',
            'success_url' => U . 'payment-successful',
            'cancel_url' => U . 'payment-cancelled'
        ];
        
        // Save default configuration
        $kipay_settings = ORM::for_table('tbl_appconfig')->create();
        $kipay_settings->setting = 'kipay_settings';
        $kipay_settings->value = json_encode($default_config);
        $kipay_settings->save();
        
        $kipay_config = $default_config;
    } else {
        // Load existing configuration
        $kipay_config = json_decode($kipay_settings['value'], true);
    }
    
    // Add payment gateway
    add_payment_gateway('Kipay', 'kipay');
    
    // Add admin hook for settings page
    hook_add('admin_settings_save', 1, function($data) {
        global $kipay_config;
        
        // Check if Kipay settings were submitted
        if (isset($_POST['kipay_active'])) {
            // Update configuration
            $kipay_config['active'] = isset($_POST['kipay_active']);
            $kipay_config['api_key'] = $_POST['kipay_api_key'];
            $kipay_config['api_url'] = $_POST['kipay_api_url'];
            $kipay_config['payment_channel_id'] = $_POST['kipay_payment_channel_id'];
            $kipay_config['success_url'] = $_POST['kipay_success_url'];
            $kipay_config['cancel_url'] = $_POST['kipay_cancel_url'];
            
            // Save configuration
            $kipay_settings = ORM::for_table('tbl_appconfig')
                ->where('setting', 'kipay_settings')
                ->find_one();
            
            $kipay_settings->value = json_encode($kipay_config);
            $kipay_settings->save();
        }
    });
    
    // Add admin hook for settings page
    hook_add('admin_settings_content', 1, function() {
        global $kipay_config;
        
        // Render settings form
        echo '<div class="panel panel-primary" id="kipay_settings">
            <div class="panel-heading">Kipay Payment Gateway Settings</div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="switch">
                        <input type="checkbox" name="kipay_active" id="kipay_active" value="1" ' . ($kipay_config['active'] ? 'checked' : '') . '>
                        <span class="slider round"></span>
                    </label>
                    <label for="kipay_active">Enable Kipay Payment Gateway</label>
                </div>
                <div class="form-group">
                    <label for="kipay_api_key">API Key</label>
                    <input type="text" class="form-control" id="kipay_api_key" name="kipay_api_key" value="' . $kipay_config['api_key'] . '" required>
                    <p class="help-block">Enter your Kipay API Key</p>
                </div>
                <div class="form-group">
                    <label for="kipay_api_url">API URL</label>
                    <input type="text" class="form-control" id="kipay_api_url" name="kipay_api_url" value="' . $kipay_config['api_url'] . '" required>
                    <p class="help-block">Enter the Kipay API URL</p>
                </div>
                <div class="form-group">
                    <label for="kipay_payment_channel_id">Payment Channel ID</label>
                    <input type="text" class="form-control" id="kipay_payment_channel_id" name="kipay_payment_channel_id" value="' . $kipay_config['payment_channel_id'] . '" required>
                    <p class="help-block">Enter the Kipay Payment Channel ID</p>
                </div>
                <div class="form-group">
                    <label for="kipay_success_url">Success URL</label>
                    <input type="text" class="form-control" id="kipay_success_url" name="kipay_success_url" value="' . $kipay_config['success_url'] . '" required>
                    <p class="help-block">URL to redirect after successful payment</p>
                </div>
                <div class="form-group">
                    <label for="kipay_cancel_url">Cancel URL</label>
                    <input type="text" class="form-control" id="kipay_cancel_url" name="kipay_cancel_url" value="' . $kipay_config['cancel_url'] . '" required>
                    <p class="help-block">URL to redirect after cancelled payment</p>
                </div>
            </div>
        </div>';
    });
    
    return $kipay_config;
}