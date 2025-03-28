<?php
namespace Kipay\Webhooks;

use Kipay\Config\PaystackConfig;
use Kipay\Core\Gateway;
use Kipay\Core\Transaction;
use Kipay\Models\PaymentChannelModel;
use Kipay\Utils\Logger;

/**
 * PaystackWebhook Class for Kipay Payment Gateway
 * 
 * This class handles webhook events from Paystack.
 */
class PaystackWebhook
{
    /**
     * @var \Kipay\Config\PaystackConfig
     */
    protected $config;
    
    /**
     * @var \Kipay\Core\Gateway
     */
    protected $gateway;
    
    /**
     * @var \Kipay\Core\Transaction
     */
    protected $transaction;
    
    /**
     * @var \Kipay\Utils\Logger
     */
    protected $logger;
    
    /**
     * @var \Kipay\Models\PaymentChannelModel
     */
    protected $paymentChannelModel;
    
    /**
     * PaystackWebhook constructor
     */
    public function __construct()
    {
        $this->config = new PaystackConfig();
        $this->gateway = new Gateway();
        $this->transaction = new Transaction();
        $this->logger = new Logger('paystack_webhook');
        $this->paymentChannelModel = new PaymentChannelModel();
    }
    
    /**
     * Handle Paystack webhook
     * 
     * @return array Result with success status and message
     */
    public function handle(): array
    {
        try {
            // Get the input data
            $input = file_get_contents('php://input');
            
            // Verify webhook signature
            if (!$this->verifySignature($input)) {
                $this->logger->warning('Invalid webhook signature');
                return [
                    'success' => false,
                    'message' => 'Invalid signature'
                ];
            }
            
            // Parse the JSON data
            $event = json_decode($input, true);
            
            // Verify the event data
            if (!isset($event['event']) || !isset($event['data'])) {
                $this->logger->warning('Invalid webhook data', ['event' => $event]);
                return [
                    'success' => false,
                    'message' => 'Invalid event data'
                ];
            }
            
            // Log the event
            $this->logger->info('Webhook event received', [
                'event' => $event['event'],
                'data' => [
                    'id' => $event['data']['id'] ?? null,
                    'reference' => $event['data']['reference'] ?? null
                ]
            ]);
            
            // Process the event
            $result = $this->processEvent($event['event'], $event['data']);
            
            if (!$result['success']) {
                $this->logger->warning('Event processing failed', [
                    'event' => $event['event'],
                    'message' => $result['message']
                ]);
            } else {
                $this->logger->info('Event processed successfully', [
                    'event' => $event['event'],
                    'message' => $result['message']
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error processing webhook: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify webhook signature
     * 
     * @param string $payload Webhook payload
     * @return bool True if signature is valid
     */
    protected function verifySignature(string $payload): bool
    {
        // Get signature from header
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
        
        if (empty($signature)) {
            return false;
        }
        
        // Get Paystack secret key
        $secretKey = $this->config->secretKey;
        
        if (empty($secretKey)) {
            return false;
        }
        
        // Calculate expected signature
        $expectedSignature = hash_hmac('sha512', $payload, $secretKey);
        
        // Compare signatures
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Process webhook event
     * 
     * @param string $eventType Event type
     * @param array $data Event data
     * @return array Result with success status and message
     */
    protected function processEvent(string $eventType, array $data): array
    {
        switch ($eventType) {
            case 'charge.success':
                return $this->processChargeSuccess($data);
                
            case 'transfer.success':
                return $this->processTransferSuccess($data);
                
            case 'transfer.failed':
                return $this->processTransferFailed($data);
                
            case 'charge.failed':
                return $this->processChargeFailed($data);
                
            case 'subscription.create':
                return $this->processSubscriptionCreate($data);
                
            case 'subscription.disable':
                return $this->processSubscriptionDisable($data);
                
            case 'invoice.create':
                return $this->processInvoiceCreate($data);
                
            case 'invoice.payment_failed':
                return $this->processInvoicePaymentFailed($data);
                
            default:
                // Log unhandled event type
                $this->logger->info('Unhandled event type', [
                    'event_type' => $eventType,
                    'data' => $data
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Unhandled event type: ' . $eventType
                ];
        }
    }
    
    /**
     * Process charge.success event
     * 
     * @param array $data Event data
     * @return array Result with success status and message
     */
    protected function processChargeSuccess(array $data): array
    {
        try {
            // Get transaction reference
            $reference = $data['reference'] ?? '';
            
            if (empty($reference)) {
                return [
                    'success' => false,
                    'message' => 'Missing transaction reference'
                ];
            }
            
            // Get transaction
            $transaction = $this->transaction->getByReference($reference);
            
            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found: ' . $reference
                ];
            }
            
            // Check if transaction is already completed
            if ($transaction['status'] === 'completed') {
                return [
                    'success' => true,
                    'message' => 'Transaction already completed'
                ];
            }
            
            // Update transaction status
            $updated = $this->transaction->updateStatus($transaction['id'], 'completed', [
                'provider_reference' => $data['id'],
                'payment_method' => $data['channel'],
                'gateway_response' => json_encode($data)
            ]);
            
            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Failed to update transaction status'
                ];
            }
            
            // Create transaction log
            $this->transaction->createLog(
                $transaction['id'],
                'completed',
                'Transaction completed via webhook',
                $data
            );
            
            return [
                'success' => true,
                'message' => 'Transaction marked as completed: ' . $reference
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error processing charge.success', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return [
                'success' => false,
                'message' => 'Error processing charge.success: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process charge.failed event
     * 
     * @param array $data Event data
     * @return array Result with success status and message
     */
    protected function processChargeFailed(array $data): array
    {
        try {
            // Get transaction reference
            $reference = $data['reference'] ?? '';
            
            if (empty($reference)) {
                return [
                    'success' => false,
                    'message' => 'Missing transaction reference'
                ];
            }
            
            // Get transaction
            $transaction = $this->transaction->getByReference($reference);
            
            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found: ' . $reference
                ];
            }
            
            // Check if transaction is already failed or completed
            if (in_array($transaction['status'], ['failed', 'completed'])) {
                return [
                    'success' => true,
                    'message' => 'Transaction already processed: ' . $transaction['status']
                ];
            }
            
            // Update transaction status
            $updated = $this->transaction->updateStatus($transaction['id'], 'failed', [
                'provider_reference' => $data['id'],
                'payment_method' => $data['channel'],
                'gateway_response' => json_encode($data)
            ]);
            
            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Failed to update transaction status'
                ];
            }
            
            // Create transaction log
            $this->transaction->createLog(
                $transaction['id'],
                'failed',
                'Transaction failed via webhook',
                $data
            );
            
            return [
                'success' => true,
                'message' => 'Transaction marked as failed: ' . $reference
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error processing charge.failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return [
                'success' => false,
                'message' => 'Error processing charge.failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process transfer.success event
     * 
     * @param array $data Event data
     * @return array Result with success status and message
     */
    protected function processTransferSuccess(array $data): array
    {
        // Log transfer success
        $this->logger->info('Transfer successful', [
            'reference' => $data['reference'],
            'amount' => $data['amount'],
            'recipient' => $data['recipient']
        ]);
        
        return [
            'success' => true,
            'message' => 'Transfer success logged'
        ];
    }
    
    /**
     * Process transfer.failed event
     * 
     * @param array $data Event data
     * @return array Result with success status and message
     */
    protected function processTransferFailed(array $data): array
    {
        // Log transfer failure
        $this->logger->error('Transfer failed', [
            'reference' => $data['reference'],
            'amount' => $data['amount'],
            'recipient' => $data['recipient'],
            'reason' => $data['reason'] ?? 'Unknown reason'
        ]);
        
        return [
            'success' => true,
            'message' => 'Transfer failure logged'
        ];
    }
    
    /**
     * Process subscription.create event
     * 
     * @param array $data Event data
     * @return array Result with success status and message
     */
    protected function processSubscriptionCreate(array $data): array
    {
        // Log subscription creation
        $this->logger->info('Subscription created', [
            'reference' => $data['reference'],
            'customer' => $data['customer'],
            'plan' => $data['plan'],
            'status' => $data['status']
        ]);
        
        return [
            'success' => true,
            'message' => 'Subscription creation logged'
        ];
    }
    
    /**
     * Process subscription.disable event
     * 
     * @param array $data Event data
     * @return array Result with success status and message
     */
    protected function processSubscriptionDisable(array $data): array
    {
        // Log subscription disabling
        $this->logger->info('Subscription disabled', [
            'reference' => $data['reference'],
            'customer' => $data['customer'],
            'plan' => $data['plan']
        ]);
        
        return [
            'success' => true,
            'message' => 'Subscription disable logged'
        ];
    }
    
    /**
     * Process invoice.create event
     * 
     * @param array $data Event data
     * @return array Result with success status and message
     */
    protected function processInvoiceCreate(array $data): array
    {
        // Log invoice creation
        $this->logger->info('Invoice created', [
            'reference' => $data['reference'],
            'customer' => $data['customer'],
            'amount' => $data['amount']
        ]);
        
        return [
            'success' => true,
            'message' => 'Invoice creation logged'
        ];
    }
    
    /**
     * Process invoice.payment_failed event
     * 
     * @param array $data Event data
     * @return array Result with success status and message
     */
    protected function processInvoicePaymentFailed(array $data): array
    {
        // Log invoice payment failure
        $this->logger->error('Invoice payment failed', [
            'reference' => $data['reference'],
            'customer' => $data['customer'],
            'amount' => $data['amount'],
            'reason' => $data['reason'] ?? 'Unknown reason'
        ]);
        
        return [
            'success' => true,
            'message' => 'Invoice payment failure logged'
        ];
    }
}