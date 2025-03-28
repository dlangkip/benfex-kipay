<?php
namespace Kipay\Controllers;

use Kipay\Core\Gateway;
use Kipay\Core\Transaction;
use Kipay\Core\Customer;
use Kipay\Config\AppConfig;

/**
 * PaymentController Class for Kipay Payment Gateway
 * 
 * This class handles payment-related pages.
 */
class PaymentController
{
    /**
     * @var \Kipay\Core\Gateway
     */
    protected $gateway;
    
    /**
     * @var \Kipay\Core\Transaction
     */
    protected $transaction;
    
    /**
     * @var \Kipay\Core\Customer
     */
    protected $customer;
    
    /**
     * @var \Kipay\Config\AppConfig
     */
    protected $config;
    
    /**
     * PaymentController constructor
     */
    public function __construct()
    {
        $this->gateway = new Gateway();
        $this->transaction = new Transaction();
        $this->customer = new Customer();
        $this->config = new AppConfig();
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
        // Add site settings
        $data['site_name'] = $this->config->get('site_name', 'Kipay Payment Gateway');
        $data['site_url'] = $this->config->get('site_url', '/');
        $data['logo_url'] = $this->config->get('logo_url', '/assets/images/logo.png');
        
        // Extract data to variables
        extract($data);
        
        // Include template file
        $templateFile = KIPAY_PATH . '/src/Templates/' . $template . '.php';
        
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            echo "Template not found: $template";
        }
    }
    
    /**
     * Checkout page
     * 
     * @param string $reference Transaction reference
     * @return void
     */
    public function checkout(string $reference): void
    {
        // Get transaction by reference
        $transaction = $this->transaction->getByReference($reference);
        
        if (!$transaction) {
            header('Location: /payment/failure?error=invalid_reference');
            exit;
        }
        
        // Check if transaction is already completed
        if ($transaction['status'] === 'completed') {
            header('Location: /payment/success?reference=' . $reference);
            exit;
        }
        
        // Check if transaction is already failed
        if ($transaction['status'] === 'failed') {
            header('Location: /payment/failure?reference=' . $reference);
            exit;
        }
        
        // Get customer
        $customer = null;
        if ($transaction['customer_id']) {
            $customer = $this->customer->getById($transaction['customer_id']);
        }
        
        // Get payment channel
        $paymentChannel = $this->gateway->getPaymentChannelConfig($transaction['payment_channel_id']);
        
        if (!$paymentChannel) {
            header('Location: /payment/failure?error=invalid_channel');
            exit;
        }
        
        // Get Paystack configuration for client-side
        $paystackConfig = [];
        if ($paymentChannel['provider'] === 'paystack') {
            $paystackConfig = [
                'public_key' => $paymentChannel['public_key']
            ];
        }
        
        // Get available payment methods
        $paymentMethods = [
            [
                'id' => 'card',
                'name' => 'Credit/Debit Card',
                'icon' => '/assets/images/card.png',
                'description' => 'Pay with your credit or debit card'
            ],
            [
                'id' => 'bank_transfer',
                'name' => 'Bank Transfer',
                'icon' => '/assets/images/bank.png',
                'description' => 'Pay directly from your bank account'
            ],
            [
                'id' => 'ussd',
                'name' => 'USSD',
                'icon' => '/assets/images/ussd.png',
                'description' => 'Pay using USSD code'
            ]
        ];
        
        // Set cancel URL
        $cancelUrl = '/payment/failure?reference=' . $reference;
        
        // Render checkout template
        $this->render('checkout', [
            'transaction' => $transaction,
            'customer' => $customer,
            'paymentChannel' => $paymentChannel,
            'paystackConfig' => $paystackConfig,
            'paymentMethods' => $paymentMethods,
            'cancelUrl' => $cancelUrl
        ]);
    }
    
    /**
     * Verify payment
     * 
     * @param string $reference Transaction reference
     * @return void
     */
    public function verify(string $reference): void
    {
        // Verify transaction
        $result = $this->gateway->verifyTransaction($reference);
        
        if (!$result || $result['transaction']['status'] !== 'completed') {
            // Transaction failed
            header('Location: /payment/failure?reference=' . $reference);
            exit;
        }
        
        // Transaction successful
        header('Location: /payment/success?reference=' . $reference);
        exit;
    }
    
    /**
     * Success page
     * 
     * @return void
     */
    public function success(): void
    {
        // Get reference from query params
        $reference = $_GET['reference'] ?? '';
        
        if (empty($reference)) {
            header('Location: /');
            exit;
        }
        
        // Get transaction
        $transaction = $this->transaction->getByReference($reference);
        
        if (!$transaction || $transaction['status'] !== 'completed') {
            header('Location: /payment/failure?reference=' . $reference);
            exit;
        }
        
        // Get continue URL
        $continueUrl = $_GET['continue_url'] ?? '/';
        
        // Render success template
        $this->render('success', [
            'transaction' => $transaction,
            'continueUrl' => $continueUrl
        ]);
    }
    
    /**
     * Failure page
     * 
     * @return void
     */
    public function failure(): void
    {
        // Get reference from query params
        $reference = $_GET['reference'] ?? '';
        
        // Get error message
        $errorMessage = $_GET['error'] ?? '';
        
        // Get transaction if reference provided
        $transaction = null;
        if (!empty($reference)) {
            $transaction = $this->transaction->getByReference($reference);
        }
        
        // Set retry URL
        $retryUrl = '';
        if ($transaction) {
            $retryUrl = '/payment/checkout/' . $reference;
        }
        
        // Set cancel URL
        $cancelUrl = $_GET['cancel_url'] ?? '/';
        
        // Render failure template
        $this->render('failure', [
            'transaction' => $transaction,
            'errorMessage' => $errorMessage,
            'retryUrl' => $retryUrl,
            'cancelUrl' => $cancelUrl
        ]);
    }
}