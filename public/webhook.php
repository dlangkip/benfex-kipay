<?php
/**
 * Kipay Payment Gateway
 * 
 * Webhook handler for payment providers.
 * 
 * @package Kipay
 * @version 1.0.0
 */

// Define base path if not defined
if (!defined('KIPAY_PATH')) {
    define('KIPAY_PATH', dirname(__DIR__));
    
    // Load composer autoloader
    require_once KIPAY_PATH . '/vendor/autoload.php';
    
    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(KIPAY_PATH);
    $dotenv->load();
}

use Kipay\Config\AppConfig;
use Kipay\Utils\Logger;
use Kipay\Webhooks\WebhookHandler;

// Get the webhook provider from the URL
$requestPath = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestPath, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// The last part of the path is the provider
$provider = end($pathParts);

// Create logger
$logger = new Logger('webhook');

// Log webhook request
$logger->info('Webhook request received', [
    'provider' => $provider,
    'method' => $_SERVER['REQUEST_METHOD'],
    'ip' => $_SERVER['REMOTE_ADDR']
]);

// Create webhook handler
$webhookHandler = new WebhookHandler();

// Handle webhook based on provider
try {
    switch ($provider) {
        case 'paystack':
            $result = $webhookHandler->handlePaystack();
            break;
            
        case 'flutterwave':
            $result = $webhookHandler->handleFlutterwave();
            break;
            
        case 'stripe':
            $result = $webhookHandler->handleStripe();
            break;
            
        default:
            // Unknown provider
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Unknown webhook provider'
            ]);
            exit;
    }
    
    // Return result
    if ($result['success']) {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => $result['message']
        ]);
    } else {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $result['message']
        ]);
    }
} catch (Exception $e) {
    // Log error
    $logger->error('Webhook error', [
        'provider' => $provider,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Return error
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}