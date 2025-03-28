<?php
/**
 * Kipay Payment Gateway IPN Handler for Benfex
 * 
 * This file handles Instant Payment Notifications (IPNs) from Kipay to Benfex.
 * 
 * @package Kipay
 * @version 1.0.0
 */

// Include system core files
require_once '../../system/config.php';
require_once '../../system/vendor/autoload.php';
require_once '../../system/controllers/auth.php';

// Include Kipay configuration
require_once 'kipay_config.php';

// Set response type to JSON
header('Content-Type: application/json');

// Get the raw POST data
$rawPostData = file_get_contents('php://input');

// Log the raw request for debugging
error_log("Kipay IPN Raw Request: " . $rawPostData);

// Verify the signature if provided
$signature = $_SERVER['HTTP_X_KIPAY_SIGNATURE'] ?? '';
$verified = false;

if (!empty($signature) && !empty($kipay_config['api_secret'])) {
    $expectedSignature = hash_hmac('sha256', $rawPostData, $kipay_config['api_secret']);
    $verified = hash_equals($expectedSignature, $signature);
}

// Parse the JSON data
$postData = json_decode($rawPostData, true);

// Check if gateway is enabled and data is valid
if (!$kipay_config['active'] || empty($postData) || !$verified) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request or gateway is disabled.'
    ]);
    error_log("Kipay IPN Error: " . ($verified ? 'Invalid data' : 'Invalid signature'));
    exit;
}

// Process the IPN data
try {
    // Extract event type and data
    $eventType = $postData['event'] ?? '';
    $data = $postData['data'] ?? [];
    
    // Check if event type is valid
    if (empty($eventType) || empty($data)) {
        throw new Exception("Invalid event data");
    }
    
    // Get transaction reference
    $reference = $data['reference'] ?? '';
    
    if (empty($reference)) {
        throw new Exception("Missing transaction reference");
    }
    
    // Find the payment record
    $payment = ORM::for_table('tbl_payment_gateway')
        ->where('gateway_ref_id', $reference)
        ->find_one();
    
    if (!$payment) {
        throw new Exception("Payment record not found for reference: " . $reference);
    }
    
    // Get the invoice
    $invoice = ORM::for_table('tbl_invoices')
        ->find_one($payment['invoice_id']);
    
    if (!$invoice) {
        throw new Exception("Invoice not found for payment");
    }
    
    // Process based on event type
    switch ($eventType) {
        case 'charge.success':
            // Handle successful payment
            processSuccessfulPayment($payment, $invoice, $data);
            break;
            
        case 'charge.failed':
            // Handle failed payment
            processFailedPayment($payment, $invoice, $data);
            break;
            
        case 'charge.pending':
            // Handle pending payment
            processPendingPayment($payment, $invoice, $data);
            break;
            
        default:
            // Log unhandled event type
            error_log("Kipay IPN: Unhandled event type: " . $eventType);
            break;
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'IPN processed successfully'
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Kipay IPN Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error processing IPN: ' . $e->getMessage()
    ]);
}

/**
 * Process a successful payment
 * 
 * @param object $payment Payment record
 * @param object $invoice Invoice record
 * @param array $data Payment data
 * @return void
 */
function processSuccessfulPayment($payment, $invoice, $data)
{
    // Update payment status
    $payment->payment_status = 'Successful';
    $payment->pg_response_data = json_encode($data);
    $payment->save();
    
    // Update invoice status if not already paid
    if ($invoice['status'] != 'Paid') {
        $invoice->status = 'Paid';
        $invoice->save();
        
        // Add payment transaction
        $trx = ORM::for_table('tbl_transactions')->create();
        $trx->invoice = $invoice['id'];
        $trx->customer = $invoice['userid'];
        $trx->amount = $data['amount'] ?? $invoice['total'];
        $trx->date = date('Y-m-d H:i:s');
        $trx->method = 'Kipay';
        $trx->description = 'Payment for Invoice #' . $invoice['id'] . ' via Kipay';
        $trx->refid = $data['reference'];
        $trx->save();
        
        // If invoice is for service, activate the service
        if ($invoice['type'] == 'Service') {
            // Get the plan
            $plan = ORM::for_table('tbl_plans')
                ->find_one($invoice['plan_id']);
            
            if ($plan) {
                // Get the customer
                $customer = ORM::for_table('tbl_customers')
                    ->find_one($invoice['userid']);
                
                if ($customer) {
                    // Update customer service
                    $customer->service_id = $plan['id'];
                    $customer->service_expiry = Service::get_expiry_by_plan($plan['id']);
                    $customer->service_status = 'Active';
                    $customer->save();
                    
                    // Log the activity
                    _log('Payment Successful: Invoice #' . $invoice['id'] . ' - $' . $invoice['total'] . ' via Kipay [ ' . $data['reference'] . ' ]', 'Customer', $customer['id']);
                }
            }
        }
    }
}

/**
 * Process a failed payment
 * 
 * @param object $payment Payment record
 * @param object $invoice Invoice record
 * @param array $data Payment data
 * @return void
 */
function processFailedPayment($payment, $invoice, $data)
{
    // Update payment status
    $payment->payment_status = 'Failed';
    $payment->pg_response_data = json_encode($data);
    $payment->save();
    
    // Log the activity
    _log('Payment Failed: Invoice #' . $invoice['id'] . ' - $' . $invoice['total'] . ' via Kipay [ ' . $data['reference'] . ' ]', 'System', 0);
}

/**
 * Process a pending payment
 * 
 * @param object $payment Payment record
 * @param object $invoice Invoice record
 * @param array $data Payment data
 * @return void
 */
function processPendingPayment($payment, $invoice, $data)
{
    // Update payment status
    $payment->payment_status = 'Pending';
    $payment->pg_response_data = json_encode($data);
    $payment->save();
    
    // Log the activity
    _log('Payment Pending: Invoice #' . $invoice['id'] . ' - $' . $invoice['total'] . ' via Kipay [ ' . $data['reference'] . ' ]', 'System', 0);
}