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
        // Placeholder for Flutterwave webhook handling
        // Would create a FlutterwaveWebhook class similar to PaystackWebhook
        
        $this->logger->info('Flutterwave webhook received, but handler not implemented');
        
        return [
            'success' => false,
            'message' => 'Flutterwave webhook handler not implemented'
        ];
    }
    
    /**
     * Handle Stripe webhook
     * 
     * @return array Result with success status and message
     */
    public function handleStripe(): array
    {
        // Placeholder for Stripe webhook handling
        // Would create a StripeWebhook class similar to PaystackWebhook
        
        $this->logger->info('Stripe webhook received, but handler not implemented');
        
        return [
            'success' => false,
            'message' => 'Stripe webhook handler not implemented'
        ];
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