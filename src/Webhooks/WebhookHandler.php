<?php
namespace Kipay\Webhooks;

use Kipay\Utils\Logger;

/**
 * WebhookHandler Class for Kipay Payment Gateway
 * 
 * This class routes webhook events to the appropriate handler.
 */
class WebhookHandler
{
    /**
     * @var \Kipay\Utils\Logger
     */
    protected $logger;
    
    /**
     * WebhookHandler constructor
     */
    public function __construct()
    {
        $this->logger = new Logger('webhook_handler');
    }
    
    /**
     * Handle Paystack webhook
     * 
     * @return array Result with success status and message
     */
    public function handlePaystack(): array
    {
        try {
            // Create Paystack webhook handler
            $handler = new PaystackWebhook();
            
            // Handle webhook
            return $handler->handle();
        } catch (\Exception $e) {
            $this->logger->error('Error handling Paystack webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error handling Paystack webhook: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle Flutterwave webhook
     * 
     * @return array Result with success status and message
     */
    public function handleFlutterwave(): array
    {
        try {
            // Get the request payload
            $input = file_get_contents('php://input');
            $event = json_decode($input, true);
            
            // Log the event
            $this->logger->info('Flutterwave webhook received', [
                'event' => $event
            ]);
            
            // For now, we'll just log the event
            // In production, you would implement full verification and processing
            
            // Get the transaction reference if available
            $reference = $event['data']['tx_ref'] ?? '';
            
            if (!empty($reference)) {
                // Get transaction from database
                $transactionModel = new \Kipay\Models\TransactionModel();
                $transaction = $transactionModel->getByReference($reference);
                
                if ($transaction) {
                    // Update transaction status based on Flutterwave status
                    $flutterwaveStatus = $event['data']['status'] ?? '';
                    $kipayStatus = 'pending';
                    
                    if ($flutterwaveStatus === 'successful') {
                        $kipayStatus = 'completed';
                    } elseif (in_array($flutterwaveStatus, ['failed', 'cancelled'])) {
                        $kipayStatus = 'failed';
                    }
                    
                    // Update transaction
                    $transactionCore = new \Kipay\Core\Transaction();
                    $transactionCore->updateStatus($transaction['id'], $kipayStatus, [
                        'provider_reference' => $event['data']['id'] ?? '',
                        'payment_method' => $event['data']['payment_type'] ?? '',
                        'gateway_response' => $input
                    ]);
                }
            }
            
            return [
                'success' => true,
                'message' => 'Flutterwave webhook processed'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error handling Flutterwave webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error handling Flutterwave webhook: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle Stripe webhook
     * 
     * @return array Result with success status and message
     */
    public function handleStripe(): array
    {
        try {
            // Get the request payload
            $input = file_get_contents('php://input');
            $event = json_decode($input, true);
            
            // Log the event
            $this->logger->info('Stripe webhook received', [
                'event' => $event
            ]);
            
            // For now, we'll just log the event
            // In production, you would implement full verification and processing
            
            // Get the transaction reference if available
            $metadata = $event['data']['object']['metadata'] ?? [];
            $reference = $metadata['reference'] ?? '';
            
            if (!empty($reference)) {
                // Get transaction from database
                $transactionModel = new \Kipay\Models\TransactionModel();
                $transaction = $transactionModel->getByReference($reference);
                
                if ($transaction) {
                    // Update transaction status based on Stripe event type
                    $eventType = $event['type'] ?? '';
                    $kipayStatus = 'pending';
                    
                    if ($eventType === 'charge.succeeded') {
                        $kipayStatus = 'completed';
                    } elseif (in_array($eventType, ['charge.failed', 'charge.refunded'])) {
                        $kipayStatus = 'failed';
                    }
                    
                    // Update transaction
                    $transactionCore = new \Kipay\Core\Transaction();
                    $transactionCore->updateStatus($transaction['id'], $kipayStatus, [
                        'provider_reference' => $event['data']['object']['id'] ?? '',
                        'payment_method' => $event['data']['object']['payment_method_details']['type'] ?? '',
                        'gateway_response' => $input
                    ]);
                }
            }
            
            return [
                'success' => true,
                'message' => 'Stripe webhook processed'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error handling Stripe webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error handling Stripe webhook: ' . $e->getMessage()
            ];
        }
    }  
      
    /**
     * Log webhook event to database
     * 
     * @param int $paymentChannelId Payment channel ID
     * @param string $eventType Event type
     * @param array $payload Event payload
     * @return bool True if successful
     */
    public function logWebhookEvent(int $paymentChannelId, string $eventType, array $payload): bool
    {
        try {
            // Create database connection
            $db = new \Kipay\Database\Database();
            
            // Insert webhook event
            $id = $db->insert('webhook_events', [
                'payment_channel_id' => $paymentChannelId,
                'event_type' => $eventType,
                'payload' => json_encode($payload),
                'processed' => false,
                'processing_attempts' => 0
            ]);
            
            return $id !== false;
        } catch (\Exception $e) {
            $this->logger->error('Error logging webhook event', [
                'error' => $e->getMessage(),
                'payment_channel_id' => $paymentChannelId,
                'event_type' => $eventType
            ]);
            
            return false;
        }
    }
    
    /**
     * Mark webhook event as processed
     * 
     * @param int $eventId Webhook event ID
     * @param bool $success Processing success
     * @param string $error Error message if processing failed
     * @return bool True if successful
     */
    public function markWebhookEventProcessed(int $eventId, bool $success, string $error = ''): bool
    {
        try {
            // Create database connection
            $db = new \Kipay\Database\Database();
            
            // Update webhook event
            $updated = $db->update('webhook_events', $eventId, [
                'processed' => $success,
                'processing_attempts' => $db->raw('processing_attempts + 1'),
                'processing_error' => $success ? null : $error,
                'processed_at' => date('Y-m-d H:i:s')
            ]);
            
            return $updated;
        } catch (\Exception $e) {
            $this->logger->error('Error marking webhook event as processed', [
                'error' => $e->getMessage(),
                'event_id' => $eventId
            ]);
            
            return false;
        }
    }
    
    /**
     * Get unprocessed webhook events
     * 
     * @param int $limit Maximum number of events to retrieve
     * @return array Unprocessed webhook events
     */
    public function getUnprocessedEvents(int $limit = 10): array
    {
        try {
            // Create database connection
            $db = new \Kipay\Database\Database();
            
            // Get unprocessed webhook events
            $query = "SELECT * FROM webhook_events 
                WHERE processed = 0 
                AND processing_attempts < 3 
                ORDER BY created_at ASC 
                LIMIT :limit";
            
            $params = ['limit' => $limit];
            
            $result = $db->query($query, $params);
            
            return $result ?: [];
        } catch (\Exception $e) {
            $this->logger->error('Error getting unprocessed webhook events', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }
}