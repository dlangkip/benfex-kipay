<?php
namespace Kipay\Controllers;

use Kipay\Core\Transaction;
use Kipay\Core\PaymentChannel;
use Kipay\Core\Customer;
use Kipay\Config\AppConfig;
use Kipay\Models\UserModel;
use Kipay\Utils\Response;

/**
 * AdminController Class for Kipay Payment Gateway
 * 
 * This class handles all admin panel routes and functionality.
 */
class AdminController
{
    /**
     * @var \Kipay\Core\Transaction
     */
    protected $transaction;
    
    /**
     * @var \Kipay\Core\PaymentChannel
     */
    protected $paymentChannel;
    
    /**
     * @var \Kipay\Core\Customer
     */
    protected $customer;
    
    /**
     * @var \Kipay\Config\AppConfig
     */
    protected $config;
    
    /**
     * @var \Kipay\Models\UserModel
     */
    protected $userModel;
    
    /**
     * @var \Kipay\Utils\Response
     */
    protected $response;
    
    /**
     * @var array User data from session
     */
    protected $user;
    
    /**
     * AdminController constructor
     */
    public function __construct()
    {
        $this->transaction = new Transaction();
        $this->paymentChannel = new PaymentChannel();
        $this->customer = new Customer();
        $this->config = new AppConfig();
        $this->userModel = new UserModel();
        $this->response = new Response();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['user']) && !$this->isAuthRoute()) {
            header('Location: /admin/login');
            exit;
        }
        
        // Set user data from session
        $this->user = $_SESSION['user'] ?? null;
    }
    
    /**
     * Check if current route is an auth route (login, etc.)
     * 
     * @return bool True if auth route
     */
    protected function isAuthRoute(): bool
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($requestUri, '/admin/login') !== false;
    }
    
    /**
     * Render a template with data
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return void
     */
    protected function render(string $template, array $data = []): void
    {
        // Add user data to template data
        $data['user'] = $this->user;
        
        // Add site settings
        $data['site_name'] = $this->config->get('site_name', 'Kipay Payment Gateway');
        $data['site_url'] = $this->config->get('site_url', '/');
        $data['logo_url'] = $this->config->get('logo_url', '/assets/images/logo.png');
        
        // Extract data to variables
        extract($data);
        
        // Include template file
        $templateFile = KIPAY_PATH . '/src/Templates/admin/' . $template . '.php';
        
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            echo "Template not found: $template";
        }
    }
    
    /**
     * Dashboard page
     * 
     * @return void
     */
    public function dashboard(): void
    {
        // Get transaction summary
        $summary = $this->transaction->getSummaryByUserId($this->user['id']);
        
        // Get recent transactions
        $recentTransactions = $this->transaction->getRecentByUserId($this->user['id'], 5);
        
        // Get chart data
        $chartData = $this->transaction->getChartDataByUserId($this->user['id'], 'week');
        
        // Get payment channels
        $paymentChannels = $this->paymentChannel->getByUserId($this->user['id']);
        
        // Render dashboard template
        $this->render('dashboard', [
            'page_title' => 'Dashboard',
            'summary' => $summary,
            'recent_transactions' => $recentTransactions,
            'chart_data' => $chartData,
            'payment_channels' => $paymentChannels
        ]);
    }
    
    /**
     * Transactions page
     * 
     * @return void
     */
    public function transactions(): void
    {
        // Get query parameters
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        
        // Get filters
        $filters = [];
        $filterFields = ['status', 'payment_method', 'currency', 'date_from', 'date_to', 'amount_min', 'amount_max', 'search'];
        
        foreach ($filterFields as $field) {
            if (isset($_GET[$field]) && $_GET[$field] !== '') {
                $filters[$field] = $_GET[$field];
            }
        }
        
        // Get transactions
        $transactions = $this->transaction->getByUserId($this->user['id'], $filters, $page, $limit);
        
        // Get payment channels for filter dropdown
        $paymentChannels = $this->paymentChannel->getByUserId($this->user['id']);
        
        // Render transactions template
        $this->render('transactions', [
            'page_title' => 'Transactions',
            'transactions' => $transactions,
            'filters' => $filters,
            'payment_channels' => $paymentChannels
        ]);
    }

    /**
     * View transaction details
     * 
     * @param int $id Transaction ID
     * @return void
     */
    public function viewTransaction(int $id = 0): void
    {
        // Get transaction
        $transaction = $this->transaction->getById($id, true);
        
        if (!$transaction) {
            $_SESSION['error_message'] = 'Transaction not found';
            header('Location: /admin/transactions');
            exit;
        }
        
        // Check if transaction belongs to this user
        if ($transaction['user_id'] != $this->user['id']) {
            $_SESSION['error_message'] = 'You do not have permission to view this transaction';
            header('Location: /admin/transactions');
            exit;
        }
        
        // Get customer if available
        $customer = null;
        if (!empty($transaction['customer_id'])) {
            $customer = $this->customer->getById($transaction['customer_id']);
        }
        
        // Get payment channel
        $paymentChannel = $this->paymentChannel->getById($transaction['payment_channel_id']);
        
        // Render transaction details template
        $this->render('transaction_details', [
            'page_title' => 'Transaction Details',
            'transaction' => $transaction,
            'customer' => $customer,
            'payment_channel' => $paymentChannel
        ]);
    } 
    
    /**
     * Verify a transaction status
     * 
     * @param int $id Transaction ID
     * @return void
     */
    public function verifyTransaction(int $id = 0): void
    {
        // Get transaction
        $transaction = $this->transaction->getById($id);
        
        if (!$transaction) {
            $_SESSION['error_message'] = 'Transaction not found';
            header('Location: /admin/transactions');
            exit;
        }
        
        // Check if transaction belongs to this user
        if ($transaction['user_id'] != $this->user['id']) {
            $_SESSION['error_message'] = 'You do not have permission to verify this transaction';
            header('Location: /admin/transactions');
            exit;
        }
        
        // Verify transaction
        $result = $this->gateway->verifyTransaction($transaction['reference']);
        
        if (!$result) {
            $_SESSION['error_message'] = 'Failed to verify transaction';
            header('Location: /admin/transactions/view/' . $id);
            exit;
        }
        
        // Set success message
        $_SESSION['success_message'] = 'Transaction verified successfully. Status: ' . ucfirst($result['transaction']['status']);
        
        // Redirect to transaction details
        header('Location: /admin/transactions/view/' . $id);
        exit;
    }
    /**
     * Payment channels page
     * 
     * @return void
     */
    public function paymentChannels(): void
    {
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePaymentChannelForm();
        }
        
        // Get payment channels
        $paymentChannels = $this->paymentChannel->getByUserId($this->user['id']);
        
        // Get supported providers
        $providers = $this->paymentChannel->getSupportedProviders();
        
        // Render payment channels template
        $this->render('payment_channels', [
            'page_title' => 'Payment Channels',
            'payment_channels' => $paymentChannels,
            'providers' => $providers
        ]);
    }
    
    /**
     * Handle payment channel form submission
     * 
     * @return void
     */
    protected function handlePaymentChannelForm(): void
    {
        // Get form data
        $data = $_POST;
        
        // Debug log
        error_log("Payment channel form data: " . json_encode($data));
        
        // Validate form data
        if (empty($data['name']) || empty($data['provider'])) {
            $_SESSION['error_message'] = 'Name and provider are required';
            error_log("Payment channel validation failed: missing name or provider");
            return;
        }
        
        // Prepare data
        $channelData = [
            'user_id' => $this->user['id'],
            'name' => $data['name'],
            'provider' => $data['provider'],
            'is_active' => isset($data['is_active']),
            'is_default' => isset($data['is_default'])
        ];
        
        // Extract config data
        $config = [];
        
        // The form is submitting config[public_key] and config[secret_key], but they're not being used
        if (isset($data['config']) && is_array($data['config'])) {
            error_log("Found config array in form data: " . json_encode($data['config']));
            $config = $data['config'];
        }
        
        // Also add test_mode if provided
        if (isset($data['test_mode'])) {
            $config['test_mode'] = $data['test_mode'];
        }
        
        // Force valid test keys for demonstration
        // This will override any empty values with test values
        if ($data['provider'] === 'paystack') {
            if (empty($config['public_key'])) {
                $config['public_key'] = 'pk_test_744c2bad7a229ffe6e89320992e96ed34e38bfb0';
            }
            
            if (empty($config['secret_key'])) {
                $config['secret_key'] = 'sk_test_365eca4512d4529836da46cf061e02c08f776c0a';
            }
        }
        
        // Add config to channel data
        $channelData['config'] = $config;
        error_log("Final config data: " . json_encode($config));
        
        // Add fees config if provided
        if (isset($data['fixed_fee']) || isset($data['percentage_fee'])) {
            $channelData['fees_config'] = [
                'fixed_fee' => !empty($data['fixed_fee']) ? $data['fixed_fee'] : 0,
                'percentage_fee' => !empty($data['percentage_fee']) ? $data['percentage_fee'] : 0,
                'cap' => !empty($data['fee_cap']) ? $data['fee_cap'] : null
            ];
        } else {
            // Set default fees for demonstration
            $channelData['fees_config'] = [
                'fixed_fee' => 10,
                'percentage_fee' => 1.5,
                'cap' => 1000
            ];
        }
        
        error_log("Final channel data: " . json_encode($channelData));
        
        // Create or update payment channel
        if (isset($data['id']) && $data['id']) {
            // Update existing channel
            $updated = $this->paymentChannel->update((int)$data['id'], $channelData);
            
            if ($updated) {
                $_SESSION['success_message'] = 'Payment channel updated successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to update payment channel';
                error_log("Failed to update payment channel with data: " . json_encode($channelData));
            }
        } else {
            // Create new channel
            $channel = $this->paymentChannel->create($channelData);
            
            if ($channel) {
                $_SESSION['success_message'] = 'Payment channel created successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to create payment channel';
                error_log("Failed to create payment channel with data: " . json_encode($channelData));
            }
        }
        
        // Redirect to payment channels page
        header('Location: /admin/payment-channels');
        exit;
    }
        
    /**
     * Edit payment channel
     * 
     * @param int $id Payment Channel ID
     * @return void
     */
    public function editPaymentChannel(int $id = 0): void
    {
        // Get payment channel
        $channel = $this->paymentChannel->getById($id, true);
        
        if (!$channel) {
            $_SESSION['error_message'] = 'Payment channel not found';
            header('Location: /admin/payment-channels');
            exit;
        }
        
        // Check if channel belongs to this user
        if ($channel['user_id'] != $this->user['id']) {
            $_SESSION['error_message'] = 'You do not have permission to edit this payment channel';
            header('Location: /admin/payment-channels');
            exit;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePaymentChannelForm();
            return;
        }
        
        // Get supported providers
        $providers = $this->paymentChannel->getSupportedProviders();
        
        // Render edit payment channel template
        $this->render('edit_payment_channel', [
            'page_title' => 'Edit Payment Channel',
            'channel' => $channel,
            'providers' => $providers
        ]);
    }   
    
    /**
     * Set a payment channel as default
     * 
     * @param int $id Payment Channel ID
     * @return void
     */
    public function setDefaultPaymentChannel(int $id = 0): void
    {
        // Get payment channel
        $channel = $this->paymentChannel->getById($id);
        
        if (!$channel) {
            $_SESSION['error_message'] = 'Payment channel not found';
            header('Location: /admin/payment-channels');
            exit;
        }
        
        // Check if channel belongs to this user
        if ($channel['user_id'] != $this->user['id']) {
            $_SESSION['error_message'] = 'You do not have permission to modify this payment channel';
            header('Location: /admin/payment-channels');
            exit;
        }
        
        // Set as default
        $success = $this->paymentChannel->setAsDefault($id);
        
        if ($success) {
            $_SESSION['success_message'] = 'Payment channel has been set as default';
        } else {
            $_SESSION['error_message'] = 'Failed to set payment channel as default';
        }
        
        header('Location: /admin/payment-channels');
        exit;
    }    

    /**
     * Delete a payment channel
     * 
     * @param int $id Payment Channel ID
     * @return void
     */
    public function deletePaymentChannel(int $id = 0): void
    {
        // Get payment channel
        $channel = $this->paymentChannel->getById($id);
        
        if (!$channel) {
            $_SESSION['error_message'] = 'Payment channel not found';
            header('Location: /admin/payment-channels');
            exit;
        }
        
        // Check if channel belongs to this user
        if ($channel['user_id'] != $this->user['id']) {
            $_SESSION['error_message'] = 'You do not have permission to delete this payment channel';
            header('Location: /admin/payment-channels');
            exit;
        }
        
        // Delete the payment channel
        $success = $this->paymentChannel->delete($id);
        
        if ($success) {
            $_SESSION['success_message'] = 'Payment channel has been deleted';
        } else {
            $_SESSION['error_message'] = 'Failed to delete payment channel. It may have transactions associated with it.';
        }
        
        header('Location: /admin/payment-channels');
        exit;
    }

    /**
     * Customers page
     * 
     * @return void
     */
    public function customers(): void
    {
        // Get query parameters
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        
        // Get filters
        $filters = [];
        $filterFields = ['country', 'date_from', 'date_to', 'search'];
        
        foreach ($filterFields as $field) {
            if (isset($_GET[$field]) && $_GET[$field] !== '') {
                $filters[$field] = $_GET[$field];
            }
        }
        
        // Get customers
        $customers = $this->customer->getByUserId($this->user['id'], $filters, $page, $limit);
        
        // Render customers template
        $this->render('customers', [
            'page_title' => 'Customers',
            'customers' => $customers,
            'filters' => $filters
        ]);
    }

    /**
     * View customer details
     * 
     * @param int $id Customer ID
     * @return void
     */
    public function viewCustomer(int $id = 0): void
    {
        // Get customer
        $customer = $this->customer->getById($id);
        
        if (!$customer) {
            $_SESSION['error_message'] = 'Customer not found';
            header('Location: /admin/customers');
            exit;
        }
        
        // Check if customer belongs to this user
        if ($customer['user_id'] != $this->user['id']) {
            $_SESSION['error_message'] = 'You do not have permission to view this customer';
            header('Location: /admin/customers');
            exit;
        }
        
        // Get customer transactions
        $transactions = $this->customer->getTransactions($id, [], 1, 10);
        
        // Render customer details template
        $this->render('customer_details', [
            'page_title' => 'Customer Details',
            'customer' => $customer,
            'transactions' => $transactions
        ]);
    }

    /**
     * Create a new customer
     * 
     * @return void
     */
    public function createCustomer(): void
    {
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // If not a POST request, redirect back to customers page
            header('Location: /admin/customers');
            exit;
        }
        
        // Get and validate form data
        $data = $_POST;
        
        // Validate required fields
        if (empty($data['email'])) {
            $_SESSION['error_message'] = 'Email is required';
            header('Location: /admin/customers');
            exit;
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = 'Invalid email format';
            header('Location: /admin/customers');
            exit;
        }
        
        // Prepare customer data
        $customerData = [
            'user_id' => $this->user['id'],
            'email' => $data['email'],
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'phone' => $data['phone'] ?? '',
            'country' => $data['country'] ?? ''
        ];
        
        // Optional fields
        $optionalFields = ['address', 'city', 'state', 'postal_code'];
        foreach ($optionalFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $customerData[$field] = $data[$field];
            }
        }
        
        // Create the customer
        $customer = $this->customer->create($customerData);
        
        if ($customer) {
            $_SESSION['success_message'] = 'Customer created successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to create customer';
        }
        
        // Redirect back to customers page
        header('Location: /admin/customers');
        exit;
    }

        /**
         * Update a customer
         * 
         * @param int $id Customer ID
         * @return void
         */
        public function updateCustomer(int $id = 0): void
        {
            // Validate ID
            if ($id <= 0) {
                $_SESSION['error_message'] = 'Invalid customer ID';
                header('Location: /admin/customers');
                exit;
            }
            
            // Get existing customer
            $customer = $this->customer->getById($id);
            
            if (!$customer) {
                $_SESSION['error_message'] = 'Customer not found';
                header('Location: /admin/customers');
                exit;
            }
            
            // Check if customer belongs to this user
            if ($customer['user_id'] != $this->user['id']) {
                $_SESSION['error_message'] = 'You do not have permission to update this customer';
                header('Location: /admin/customers');
                exit;
            }
            
            // Check if form is submitted
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                // If not a POST request, render the edit form
                $this->render('edit_customer', [
                    'page_title' => 'Edit Customer',
                    'customer' => $customer
                ]);
                return;
            }
            
            // Get and validate form data
            $data = $_POST;
            
            // Validate required fields
            if (empty($data['email'])) {
                $_SESSION['error_message'] = 'Email is required';
                header('Location: /admin/customers/update/' . $id);
                exit;
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_message'] = 'Invalid email format';
                header('Location: /admin/customers/update/' . $id);
                exit;
            }
            
            // Prepare customer data for update
            $customerData = [
                'email' => $data['email'],
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'phone' => $data['phone'] ?? '',
                'country' => $data['country'] ?? ''
            ];
            
            // Optional fields
            $optionalFields = ['address', 'city', 'state', 'postal_code'];
            foreach ($optionalFields as $field) {
                if (isset($data[$field])) {
                    $customerData[$field] = $data[$field];
                }
            }
            
            // Update the customer
            $updated = $this->customer->update($id, $customerData);
            
            if ($updated) {
                $_SESSION['success_message'] = 'Customer updated successfully';
                header('Location: /admin/customers');
            } else {
                $_SESSION['error_message'] = 'Failed to update customer';
                header('Location: /admin/customers/update/' . $id);
            }
            exit;
        }

        /**
         * Delete a customer
         * 
         * @param int $id Customer ID
         * @return void
         */
        public function deleteCustomer(int $id = 0): void
        {
            // Validate ID
            if ($id <= 0) {
                $_SESSION['error_message'] = 'Invalid customer ID';
                header('Location: /admin/customers');
                exit;
            }
            
            // Get existing customer
            $customer = $this->customer->getById($id);
            
            if (!$customer) {
                $_SESSION['error_message'] = 'Customer not found';
                header('Location: /admin/customers');
                exit;
            }
            
            // Check if customer belongs to this user
            if ($customer['user_id'] != $this->user['id']) {
                $_SESSION['error_message'] = 'You do not have permission to delete this customer';
                header('Location: /admin/customers');
                exit;
            }
            
            // Delete the customer
            $deleted = $this->customer->delete($id);
            
            if ($deleted) {
                $_SESSION['success_message'] = 'Customer deleted successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to delete customer. The customer may have transactions associated with it.';
            }
            
            // Redirect back to customers page
            header('Location: /admin/customers');
            exit;
        }    
        
    /**
     * Settings page
     * 
     * @return void
     */
    public function settings(): void
    {
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSettingsForm();
        }
        
        // Get all settings
        $settings = [
            'site_name' => $this->config->get('site_name'),
            'site_url' => $this->config->get('site_url'),
            'company_name' => $this->config->get('company_name'),
            'company_email' => $this->config->get('company_email'),
            'logo_url' => $this->config->get('logo_url'),
            'theme_color' => $this->config->get('theme_color'),
            'currency' => $this->config->get('currency')
        ];
        
        // Get API keys
        $apiKey = $this->userModel->getApiKeyByUserId($this->user['id']);
        
        // Render settings template
        $this->render('settings', [
            'page_title' => 'Settings',
            'settings' => $settings,
            'api_key' => $apiKey
        ]);
    }
    
    /**
     * Handle settings form submission
     * 
     * @return void
     */
    protected function handleSettingsForm(): void
    {
        // Get form data
        $data = $_POST;
        
        // Determine form type
        $formType = $data['form_type'] ?? '';
        
        switch ($formType) {
            case 'general_settings':
                // Update general settings
                foreach ($data as $key => $value) {
                    if (strpos($key, 'setting_') === 0) {
                        $settingKey = substr($key, 8);
                        $this->config->saveSetting($settingKey, $value);
                    }
                }
                
                $_SESSION['success_message'] = 'Settings updated successfully';
                break;
                
            case 'api_keys':
                // Regenerate API keys
                $keys = $this->userModel->regenerateApiCredentials($this->user['id']);
                
                if ($keys) {
                    $_SESSION['api_keys'] = $keys;
                    $_SESSION['success_message'] = 'API keys regenerated successfully';
                } else {
                    $_SESSION['error_message'] = 'Failed to regenerate API keys';
                }
                break;
        }
        
        // Redirect to settings page
        header('Location: /admin/settings');
        exit;
    }

    /**
     * Handle settings update
     * 
     * @return void
     */
    public function updateSettings(): void
    {
        // Check if the request is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = 'Invalid request method';
            header('Location: /admin/settings');
            exit;
        }
        
        // Get form data
        $data = $_POST;
        
        // Determine form type
        $formType = $data['form_type'] ?? '';
        
        switch ($formType) {
            case 'general_settings':
                // Update general settings
                foreach ($data as $key => $value) {
                    if (strpos($key, 'setting_') === 0) {
                        $settingKey = substr($key, 8);
                        $this->config->saveSetting($settingKey, $value);
                    }
                }
                
                $_SESSION['success_message'] = 'Settings updated successfully';
                break;
                
            case 'api_keys':
                // Regenerate API keys
                $keys = $this->userModel->regenerateApiCredentials($this->user['id']);
                
                if ($keys) {
                    $_SESSION['api_keys'] = $keys;
                    $_SESSION['success_message'] = 'API keys regenerated successfully';
                } else {
                    $_SESSION['error_message'] = 'Failed to regenerate API keys';
                }
                break;
                
            case 'payment_settings':
                // Handle payment settings
                foreach ($data as $key => $value) {
                    if (strpos($key, 'setting_') === 0) {
                        $settingKey = substr($key, 8);
                        $this->config->saveSetting($settingKey, $value);
                    }
                }
                
                $_SESSION['success_message'] = 'Payment settings updated successfully';
                break;
                
            case 'notification_settings':
                // Handle notification settings
                foreach ($data as $key => $value) {
                    if (strpos($key, 'setting_') === 0) {
                        $settingKey = substr($key, 8);
                        $this->config->saveSetting($settingKey, $value);
                    }
                }
                
                $_SESSION['success_message'] = 'Notification settings updated successfully';
                break;
                
            case 'payment_page_settings':
                // Handle payment page settings
                foreach ($data as $key => $value) {
                    if (strpos($key, 'setting_') === 0) {
                        $settingKey = substr($key, 8);
                        $this->config->saveSetting($settingKey, $value);
                    }
                }
                
                $_SESSION['success_message'] = 'Payment page settings updated successfully';
                break;
                
            default:
                $_SESSION['error_message'] = 'Unknown settings type';
                break;
        }
        
        // Redirect back to the settings page
        header('Location: /admin/settings');
        exit;
    } 
    
    /**
     * Send test email
     * 
     * @return void
     */
    public function sendTestEmail(): void
    {
        // Check if request is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = 'Invalid request method';
            header('Location: /admin/settings');
            exit;
        }
        
        // Get form data
        $email = $_POST['email'] ?? '';
        $subject = $_POST['subject'] ?? 'Kipay Test Email';
        
        if (empty($email)) {
            $_SESSION['error_message'] = 'Email address is required';
            header('Location: /admin/settings');
            exit;
        }
        
        // Get mail settings
        $fromName = $this->config->get('mail_from_name', 'Kipay Payment Gateway');
        $fromEmail = $this->config->get('mail_from_email', 'noreply@kipay.com');
        
        // Set mail headers
        $headers = [
            'From' => "$fromName <$fromEmail>",
            'Reply-To' => $fromEmail,
            'X-Mailer' => 'Kipay Payment Gateway',
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
        
        // Prepare header string
        $headerString = '';
        foreach ($headers as $name => $value) {
            $headerString .= "$name: $value\r\n";
        }
        
        // Prepare mail content
        $content = '
        <html>
        <head>
            <title>' . htmlspecialchars($subject) . '</title>
        </head>
        <body>
            <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
                <div style="background-color: #3490dc; padding: 20px; color: white; text-align: center; border-radius: 5px 5px 0 0;">
                    <h2>' . htmlspecialchars($subject) . '</h2>
                </div>
                <div style="background-color: #ffffff; padding: 20px; border-radius: 0 0 5px 5px; border: 1px solid #e4e4e4;">
                    <p>This is a test email from your Kipay Payment Gateway.</p>
                    <p>If you received this email, your email settings are configured correctly.</p>
                    <p>Time sent: ' . date('Y-m-d H:i:s') . '</p>
                    <p>Sent to: ' . htmlspecialchars($email) . '</p>
                    <hr>
                    <p style="font-size: 12px; color: #666; text-align: center;">
                        &copy; ' . date('Y') . ' Kipay Payment Gateway. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        // Send the email
        $success = mail($email, $subject, $content, $headerString);
        
        if ($success) {
            $_SESSION['success_message'] = 'Test email sent successfully to ' . $email;
        } else {
            $_SESSION['error_message'] = 'Failed to send test email. Please check your mail server configuration.';
        }
        
        // Redirect back to settings page
        header('Location: /admin/settings');
        exit;
    }
    /**
     * Profile page
     * 
     * @return void
     */
    public function profile(): void
    {
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProfileForm();
        }
        
        // Get user data
        $userData = $this->userModel->getById($this->user['id']);
        
        // Render profile template
        $this->render('profile', [
            'page_title' => 'Profile',
            'user_data' => $userData
        ]);
    }
    
    /**
     * Handle profile form submission
     * 
     * @return void
     */
    protected function handleProfileForm(): void
    {
        // Get form data
        $data = $_POST;
        
        // Validate form data
        if (empty($data['email'])) {
            $_SESSION['error_message'] = 'Email is required';
            return;
        }
        
        // Prepare user data
        $userData = [
            'email' => $data['email'],
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? ''
        ];
        
        // Update password if provided
        if (!empty($data['password']) && !empty($data['password_confirm'])) {
            if ($data['password'] !== $data['password_confirm']) {
                $_SESSION['error_message'] = 'Passwords do not match';
                return;
            }
            
            $userData['password'] = $data['password'];
        }
        
        // Update user
        $updated = $this->userModel->update($this->user['id'], $userData);
        
        if ($updated) {
            // Update session data
            $_SESSION['user'] = $this->userModel->getById($this->user['id']);
            
            $_SESSION['success_message'] = 'Profile updated successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to update profile';
        }
        
        // Redirect to profile page
        header('Location: /admin/profile');
        exit;
    }
}

