<?php
/**
 * Kipay Payment Gateway Callback Handler for BENFEX
 * 
 * This file handles payment callbacks from Kipay to BENFEX.
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

// Check if gateway is enabled
if (!$kipay_config['active']) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Kipay Payment Gateway is currently disabled.'
    ]);
    exit;
}

// Get transaction reference from query parameters
$reference = isset($_GET['reference']) ? $_GET['reference'] : '';

// Check if reference is provided
if (empty($reference)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Transaction reference is required.'
    ]);
    exit;
}

// Find the payment record
$payment = ORM::for_table('tbl_payment_gateway')
    ->where('gateway_ref_id', $reference)
    ->find_one();

if (!$payment) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'Payment record not found.'
    ]);
    exit;
}

// Get the invoice
$invoice = ORM::for_table('tbl_invoices')
    ->find_one($payment['invoice_id']);

if (!$invoice) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invoice not found.'
    ]);
    exit;
}

// Verify transaction with Kipay
$result = kipay_api_request('transactions/verify/' . $reference, 'GET');

if (isset($result['status']) && $result['status'] === 'success') {
    $transaction = $result['transaction'];
    
    // Check transaction status
    if ($transaction['status'] === 'completed') {
        // Update payment status
        $payment->payment_status = 'Successful';
        $payment->pg_response_data = json_encode($transaction);
        $payment->save();
        
        // Update invoice status
        $invoice->status = 'Paid';
        $invoice->save();
        
        // Add payment transaction
        $trx = ORM::for_table('tbl_transactions')->create();
        $trx->invoice = $invoice['id'];
        $trx->customer = $invoice['userid'];
        $trx->amount = $invoice['total'];
        $trx->date = date('Y-m-d H:i:s');
        $trx->method = 'Kipay';
        $trx->description = 'Payment for Invoice #' . $invoice['id'] . ' via Kipay';
        $trx->refid = $transaction['reference'];
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
                    _log('Payment Successful: Invoice #' . $invoice['id'] . ' - $' . $invoice['total'] . ' via Kipay [ ' . $transaction['reference'] . ' ]', 'Customer', $customer['id']);
                }
            }
        }
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Payment successful.',
            'invoice_id' => $invoice['id'],
            'reference' => $transaction['reference']
        ]);
        exit;
    } elseif ($transaction['status'] === 'pending') {
        // Update payment status
        $payment->payment_status = 'Pending';
        $payment->pg_response_data = json_encode($transaction);
        $payment->save();
        
        // Return pending response
        echo json_encode([
            'status' => 'pending',
            'message' => 'Payment is pending.',
            'invoice_id' => $invoice['id'],
            'reference' => $transaction['reference']
        ]);
        exit;
    } else {
        // Update payment status
        $payment->payment_status = 'Failed';
        $payment->pg_response_data = json_encode($transaction);
        $payment->save();
        
        // Return failed response
        echo json_encode([
            'status' => 'failed',
            'message' => 'Payment failed or was cancelled.',
            'invoice_id' => $invoice['id'],
            'reference' => $transaction['reference']
        ]);
        exit;
    }
} else {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $result['message'] ?? 'Failed to verify transaction.',
        'invoice_id' => $invoice['id'],
        'reference' => $reference
    ]);
    exit;
}