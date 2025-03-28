<?php
namespace Kipay\Core;

use Kipay\Config\AppConfig;
use Kipay\Models\PaymentChannelModel;
use Kipay\Models\TransactionModel;
use Kipay\Utils\Logger;
use Kipay\Utils\Response;
use Yabacon\Paystack;

/**
 * Main Gateway Class for Kipay Payment Processor
 * 
 * This class serves as the primary interface for interacting with 
 * the payment processing functionality of Kipay.
 */
class Gateway
{
    /**
     * @var \Kipay\Config\AppConfig
     */
    protected $config;
    
    /**
     * @var \Kipay\Models\PaymentChannelModel
     */
    protected $paymentChannelModel;
    
    /**
     * @var \Kipay\Models\TransactionModel
     */
    protected $transactionModel;
    
    /**
     * @var \Kipay\Utils\Logger
     */
    protected $logger;
    
    /**
     * @var array Current payment channel configuration
     */
    protected $paymentChannel;
    
    /**
     * @var \Yabacon\Paystack|null Paystack API client instance
     */
    protected $paystackClient = null;
    
    /**
     * Gateway constructor
     */
    public function __construct()
    {
        $this->config = new AppConfig();
        $this->paymentChannelModel = new PaymentChannelModel();
        $this->transactionModel = new TransactionModel();
        $this->logger = new Logger('gateway');
    }
    
    /**
     * Initialize a payment channel
     * 
     * @param int $channelId Payment channel ID
     * @return bool True if initialization was successful
     */
    public function initializePaymentChannel(int $channelId): bool
    {
        try {
            $channel = $this->paymentChannelModel->getById($channelId);
            
            if (!$channel) {
                $this->logger->error("Payment channel not found", ['channel_id' => $channelId]);
                return false;
            }
            
            if (!$channel['is_active']) {
                $this->logger->error("Payment channel is not active", ['channel_id' => $channelId]);
                return false;
            }
            
            $this->paymentChannel = $channel;
            
            // Initialize the appropriate provider client
            switch ($channel['provider']) {
                case 'paystack':
                    return $this->initializePaystackClient($channel);
                // Add other providers as needed
                default:
                    $this->logger->error("Unsupported payment provider", ['provider' => $channel['provider']]);
                    return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Error initializing payment channel", [
                'channel_id' => $channelId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Initialize the Paystack API client
     * 
     * @param array $channel Payment channel data
     * @return bool True if initialization was successful
     */
    protected function initializePaystackClient(array $channel): bool
    {
        try {
            $config = json_decode($channel['config'], true);
            
            if (!isset($config['secret_key']) || empty($config['secret_key'])) {
                $this->logger->error("Paystack secret key not configured", ['channel_id' => $channel['id']]);
                return false;
            }
            
            $this->paystackClient = new Paystack($config['secret_key']);
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error initializing Paystack client", [
                'channel_id' => $channel['id'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Initialize a transaction for payment processing
     * 
     * @param array $transactionData Transaction data
     * @return array|bool Transaction data or false on failure
     */
    public function initializeTransaction(array $transactionData)
    {
        try {
            // Validate required fields
            $requiredFields = ['amount', 'email', 'payment_channel_id', 'user_id'];
            foreach ($requiredFields as $field) {
                if (!isset($transactionData[$field]) || empty($transactionData[$field])) {
                    $this->logger->error("Missing required field for transaction", ['field' => $field]);
                    return false;
                }
            }
            
            // Initialize the payment channel
            if (!$this->initializePaymentChannel($transactionData['payment_channel_id'])) {
                return false;
            }
            
            // Generate a unique reference
            $transactionData['reference'] = $this->generateReference();
            
            // Use the appropriate provider to initialize the transaction
            switch ($this->paymentChannel['provider']) {
                case 'paystack':
                    return $this->initializePaystackTransaction($transactionData);
                // Add other providers as needed
                default:
                    $this->logger->error("Unsupported payment provider", ['provider' => $this->paymentChannel['provider']]);
                    return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Error initializing transaction", [
                'error' => $e->getMessage(),
                'transaction_data' => $transactionData
            ]);
            return false;
        }
    }
    
    /**
     * Initialize a transaction with Paystack
     * 
     * @param array $transactionData Transaction data
     * @return array|bool Transaction data or false on failure
     */
    protected function initializePaystackTransaction(array $transactionData)
    {
        try {
            // Calculate the amount in kobo (Paystack uses kobo for KSH)
            $amount = $transactionData['amount'] * 100;
            
            // Prepare Paystack transaction data
            $paystackData = [
                'amount' => $amount,
                'email' => $transactionData['email'],
                'reference' => $transactionData['reference'],
                'callback_url' => $this->config->get('app_url') . '/payment/verify/' . $transactionData['reference'],
                'metadata' => [
                    'custom_fields' => [
                        [
                            'display_name' => 'Payment For',
                            'variable_name' => 'payment_for',
                            'value' => $transactionData['description'] ?? 'Payment'
                        ],
                        [
                            'display_name' => 'Generated By',
                            'variable_name' => 'generated_by',
                            'value' => 'Kipay Payment Gateway'
                        ]
                    ]
                ]
            ];
            
            // Add optional fields
            if (isset($transactionData['currency'])) {
                $paystackData['currency'] = $transactionData['currency'];
            }
            
            if (isset($transactionData['metadata']) && is_array($transactionData['metadata'])) {
                $paystackData['metadata'] = array_merge($paystackData['metadata'], $transactionData['metadata']);
            }
            
            // Initialize transaction on Paystack
            $response = $this->paystackClient->transaction->initialize($paystackData);
            
            if ($response->status) {
                // Save transaction to database
                $transactionData['status'] = 'pending';
                $transactionData['gateway_response'] = json_encode($response->data);
                
                $transaction = $this->transactionModel->create($transactionData);
                
                if (!$transaction) {
                    $this->logger->error("Failed to save transaction to database", [
                        'transaction_data' => $transactionData
                    ]);
                    return false;
                }
                
                // Return the combined data
                return [
                    'transaction' => $transaction,
                    'paystack' => $response->data
                ];
            } else {
                $this->logger->error("Paystack transaction initialization failed", [
                    'error' => $response->message,
                    'transaction_data' => $transactionData
                ]);
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Error initializing Paystack transaction", [
                'error' => $e->getMessage(),
                'transaction_data' => $transactionData
            ]);
            return false;
        }
    }
    
    /**
     * Verify a transaction
     * 
     * @param string $reference Transaction reference
     * @return array|bool Transaction data or false on failure
     */
    public function verifyTransaction(string $reference)
    {
        try {
            // Get transaction from database
            $transaction = $this->transactionModel->getByReference($reference);
            
            if (!$transaction) {
                $this->logger->error("Transaction not found", ['reference' => $reference]);
                return false;
            }
            
            // Initialize the payment channel
            if (!$this->initializePaymentChannel($transaction['payment_channel_id'])) {
                return false;
            }
            
            // Use the appropriate provider to verify the transaction
            switch ($this->paymentChannel['provider']) {
                case 'paystack':
                    return $this->verifyPaystackTransaction($transaction);
                // Add other providers as needed
                default:
                    $this->logger->error("Unsupported payment provider", ['provider' => $this->paymentChannel['provider']]);
                    return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Error verifying transaction", [
                'error' => $e->getMessage(),
                'reference' => $reference
            ]);
            return false;
        }
    }
    
    /**
     * Verify a transaction with Paystack
     * 
     * @param array $transaction Transaction data
     * @return array|bool Updated transaction data or false on failure
     */
    protected function verifyPaystackTransaction(array $transaction)
    {
        try {
            // Verify transaction on Paystack
            $response = $this->paystackClient->transaction->verify([
                'reference' => $transaction['reference']
            ]);
            
            if ($response->status) {
                $paystackData = $response->data;
                
                // Determine the transaction status
                $status = 'pending';
                if ($paystackData->status === 'success') {
                    $status = 'completed';
                } elseif (in_array($paystackData->status, ['failed', 'abandoned'])) {
                    $status = 'failed';
                }
                
                // Update transaction in database
                $updateData = [
                    'status' => $status,
                    'provider_reference' => $paystackData->id,
                    'payment_method' => $paystackData->channel,
                    'gateway_response' => json_encode($paystackData)
                ];
                
                $updated = $this->transactionModel->update($transaction['id'], $updateData);
                
                if (!$updated) {
                    $this->logger->error("Failed to update transaction in database", [
                        'transaction_id' => $transaction['id'],
                        'update_data' => $updateData
                    ]);
                    return false;
                }
                
                // Get the updated transaction
                $updatedTransaction = $this->transactionModel->getById($transaction['id']);
                
                // Return the combined data
                return [
                    'transaction' => $updatedTransaction,
                    'paystack' => $paystackData
                ];
            } else {
                $this->logger->error("Paystack transaction verification failed", [
                    'error' => $response->message,
                    'reference' => $transaction['reference']
                ]);
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Error verifying Paystack transaction", [
                'error' => $e->getMessage(),
                'reference' => $transaction['reference']
            ]);
            return false;
        }
    }
    
    /**
     * Generate a unique transaction reference
     * 
     * @return string Unique reference
     */
    protected function generateReference(): string
    {
        $prefix = 'KIPAY';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        
        return $prefix . $timestamp . $random;
    }
    
    /**
     * Get a list of all active payment channels for a user
     * 
     * @param int $userId User ID
     * @return array List of payment channels
     */
    public function getActivePaymentChannels(int $userId): array
    {
        return $this->paymentChannelModel->getActiveByUserId($userId);
    }
    
    /**
     * Get payment channel configuration for frontend
     * 
     * @param int $channelId Payment channel ID
     * @return array|bool Channel configuration or false on failure
     */
    public function getPaymentChannelConfig(int $channelId)
    {
        try {
            $channel = $this->paymentChannelModel->getById($channelId);
            
            if (!$channel || !$channel['is_active']) {
                return false;
            }
            
            $config = json_decode($channel['config'], true);
            
            // Return only the public configuration
            $publicConfig = [
                'id' => $channel['id'],
                'name' => $channel['name'],
                'provider' => $channel['provider']
            ];
            
            // Add provider-specific public configuration
            switch ($channel['provider']) {
                case 'paystack':
                    $publicConfig['public_key'] = $config['public_key'] ?? null;
                    break;
                // Add other providers as needed
            }
            
            return $publicConfig;
        } catch (\Exception $e) {
            $this->logger->error("Error getting payment channel configuration", [
                'channel_id' => $channelId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}