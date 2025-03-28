<?php
/**
 * Kipay Payment Gateway
 * 
 * Public entry point for the application.
 * 
 * @package Kipay
 * @version 1.0.0
 */

// If this file is accessed directly, redirect to main index.php
if (!defined('KIPAY_PATH')) {
    define('KIPAY_PATH', dirname(__DIR__));
    require_once KIPAY_PATH . '/index.php';
    exit;
}

use Kipay\Api\ApiController;
use Kipay\Utils\Response;

// Parse the request URI to determine the route
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$basePath = dirname($_SERVER['SCRIPT_NAME']);

// Remove the base path from the request URI
if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Remove query string
if (strpos($requestUri, '?') !== false) {
    $requestUri = strstr($requestUri, '?', true);
}

// Add a leading slash if missing
if (substr($requestUri, 0, 1) !== '/') {
    $requestUri = '/' . $requestUri;
}

// Remove trailing slash except for root
if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
    $requestUri = rtrim($requestUri, '/');
}

// Default route
$route = [
    'controller' => 'HomeController',
    'method' => 'index',
    'params' => []
];

// Handle OPTIONS requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
    header('Access-Control-Max-Age: 86400'); // 24 hours
    exit;
}

// Define routes
$routes = [
    // API Routes
    '/api/transactions/initialize' => ['controller' => 'TransactionApi', 'method' => 'initialize'],
    '/api/transactions/verify/([a-zA-Z0-9_-]+)' => ['controller' => 'TransactionApi', 'method' => 'verify', 'params' => ['reference']],
    '/api/transactions/get/([a-zA-Z0-9_-]+)' => ['controller' => 'TransactionApi', 'method' => 'get', 'params' => ['reference']],
    '/api/transactions/list' => ['controller' => 'TransactionApi', 'method' => 'list'],
    '/api/transactions/summary' => ['controller' => 'TransactionApi', 'method' => 'summary'],
    '/api/transactions/export' => ['controller' => 'TransactionApi', 'method' => 'export'],
    '/api/transactions/chart' => ['controller' => 'TransactionApi', 'method' => 'chart'],
    '/api/transactions/recent' => ['controller' => 'TransactionApi', 'method' => 'recent'],
    
    '/api/payment-channels/create' => ['controller' => 'PaymentChannelApi', 'method' => 'create'],
    '/api/payment-channels/update/([0-9]+)' => ['controller' => 'PaymentChannelApi', 'method' => 'update', 'params' => ['id']],
    '/api/payment-channels/delete/([0-9]+)' => ['controller' => 'PaymentChannelApi', 'method' => 'delete', 'params' => ['id']],
    '/api/payment-channels/get/([0-9]+)' => ['controller' => 'PaymentChannelApi', 'method' => 'get', 'params' => ['id']],
    '/api/payment-channels/list' => ['controller' => 'PaymentChannelApi', 'method' => 'list'],
    '/api/payment-channels/config/([0-9]+)' => ['controller' => 'PaymentChannelApi', 'method' => 'getPublicConfig', 'params' => ['id']],
    '/api/payment-channels/set-default/([0-9]+)' => ['controller' => 'PaymentChannelApi', 'method' => 'setDefault', 'params' => ['id']],
    '/api/payment-channels/providers' => ['controller' => 'PaymentChannelApi', 'method' => 'getProviders'],
    '/api/payment-channels/provider-requirements/([a-zA-Z0-9_-]+)' => ['controller' => 'PaymentChannelApi', 'method' => 'getProviderRequirements', 'params' => ['provider']],
    
    '/api/customers/create' => ['controller' => 'CustomerApi', 'method' => 'create'],
    '/api/customers/update/([0-9]+)' => ['controller' => 'CustomerApi', 'method' => 'update', 'params' => ['id']],
    '/api/customers/delete/([0-9]+)' => ['controller' => 'CustomerApi', 'method' => 'delete', 'params' => ['id']],
    '/api/customers/get/([0-9]+)' => ['controller' => 'CustomerApi', 'method' => 'get', 'params' => ['id']],
    '/api/customers/list' => ['controller' => 'CustomerApi', 'method' => 'list'],
    '/api/customers/search' => ['controller' => 'CustomerApi', 'method' => 'search'],
    '/api/customers/transactions/([0-9]+)' => ['controller' => 'CustomerApi', 'method' => 'getTransactions', 'params' => ['id']],
    
    // Webhook Routes
    '/webhook/paystack' => ['controller' => 'WebhookHandler', 'method' => 'handlePaystack'],
    
    // Frontend Routes
    '/payment/checkout/([a-zA-Z0-9_-]+)' => ['controller' => 'PaymentController', 'method' => 'checkout', 'params' => ['reference']],
    '/payment/verify/([a-zA-Z0-9_-]+)' => ['controller' => 'PaymentController', 'method' => 'verify', 'params' => ['reference']],
    '/payment/success' => ['controller' => 'PaymentController', 'method' => 'success'],
    '/payment/failure' => ['controller' => 'PaymentController', 'method' => 'failure'],
    
    // Admin Routes
    '/admin' => ['controller' => 'AdminController', 'method' => 'dashboard'],
    '/admin/login' => ['controller' => 'AuthController', 'method' => 'login'],
    '/admin/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    '/admin/transactions' => ['controller' => 'AdminController', 'method' => 'transactions'],
    '/admin/payment-channels' => ['controller' => 'AdminController', 'method' => 'paymentChannels'],
    '/admin/customers' => ['controller' => 'AdminController', 'method' => 'customers'],
    '/admin/settings' => ['controller' => 'AdminController', 'method' => 'settings'],
    '/admin/profile' => ['controller' => 'AdminController', 'method' => 'profile'],
    
    // Default Route
    '/' => ['controller' => 'HomeController', 'method' => 'index']
];

// Match request URI to route
$matchedRoute = false;

foreach ($routes as $pattern => $routeConfig) {
    $pattern = str_replace('/', '\/', $pattern);
    if (preg_match('/^' . $pattern . '$/', $requestUri, $matches)) {
        $route = $routeConfig;
        array_shift($matches); // Remove the full match
        
        // Add captured parameters
        if (isset($route['params']) && is_array($route['params'])) {
            $params = [];
            foreach ($route['params'] as $index => $name) {
                $params[] = $matches[$index] ?? null;
            }
            $route['params'] = $params;
        } else {
            $route['params'] = [];
        }
        
        $matchedRoute = true;
        break;
    }
}

// Handle API routes
if ($matchedRoute && strpos($requestUri, '/api/') === 0) {
    // Route the API request
    ApiController::route($route['controller'], $route['method'], $route['params']);
} elseif ($matchedRoute) {
    // Route the web request
    $controllerClass = "\\Kipay\\Controllers\\{$route['controller']}";
    
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        
        if (method_exists($controller, $route['method'])) {
            call_user_func_array([$controller, $route['method']], $route['params']);
        } else {
            // Debugging: Log missing method
            error_log("Method '{$route['method']}' not found in controller '{$controllerClass}'");
            $response = new Response();
            $response->notFound('Method not found');
        }
    } else {
        // Debugging: Log missing controller
        error_log("Controller class '{$controllerClass}' not found");
        $response = new Response();
        $response->notFound('Controller not found');
    }
} else {
    // Debugging: Log unmatched route
    error_log("No route matched for URI '{$requestUri}'");
    $response = new Response();
    $response->notFound('Route not found');
}