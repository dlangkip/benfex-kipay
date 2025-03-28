<?php
namespace Kipay\App;

use Kipay\Config\AppConfig;
use Kipay\Utils\Logger;
use Kipay\Utils\Request;
use Kipay\Utils\Response;
use Kipay\Controllers\AuthController;

/**
 * Application Class for Kipay Payment Gateway
 * 
 * This class serves as the entry point for the application
 * and handles bootstrapping, routing, and error handling.
 */
class Application
{
    /**
     * @var \Kipay\App\Application Singleton instance
     */
    private static $instance = null;
    
    /**
     * @var \Kipay\Config\AppConfig
     */
    protected $config;
    
    /**
     * @var \Kipay\Utils\Logger
     */
    protected $logger;
    
    /**
     * @var \Kipay\Utils\Request
     */
    protected $request;
    
    /**
     * @var \Kipay\Utils\Response
     */
    protected $response;
    
    /**
     * @var array Route definitions
     */
    protected $routes;
    
    /**
     * Get singleton instance
     * 
     * @return \Kipay\App\Application
     */
    public static function getInstance(): Application
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        // Initialize components
        $this->config = new AppConfig();
        $this->logger = new Logger('app');
        $this->request = new Request();
        $this->response = new Response();
        
        // Define routes
        $this->defineRoutes();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check for remember me authentication
        $this->checkRememberMe();
    }
    
    /**
     * Define application routes
     * 
     * @return void
     */
    protected function defineRoutes(): void
    {
        $this->routes = [
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
    }
    
    /**
     * Check for remember me cookie
     * 
     * @return void
     */
    protected function checkRememberMe(): void
    {
        // Only check if user is not already logged in
        if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
            $authController = new AuthController();
            $authController->checkRememberMe();
        }
    }
    
    /**
     * Run the application
     * 
     * @return void
     */
    public function run(): void
    {
        try {
            $this->processRequest();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Process the incoming request
     * 
     * @return void
     */
    protected function processRequest(): void
    {
        // Get the request URI
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (strpos($requestUri, '?') !== false) {
            $requestUri = strstr($requestUri, '?', true);
        }
        
        // Handle OPTIONS request for CORS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
            header('Access-Control-Max-Age: 86400'); // 24 hours
            exit;
        }
        
        // Match request to route
        $matchedRoute = false;
        $routeParams = [];
        $routeConfig = null;
        
        foreach ($this->routes as $pattern => $config) {
            $pattern = str_replace('/', '\/', $pattern);
            if (preg_match('/^' . $pattern . '$/', $requestUri, $matches)) {
                $routeConfig = $config;
                array_shift($matches); // Remove the full match
                
                // Add captured parameters
                $routeParams = $matches;
                
                $matchedRoute = true;
                break;
            }
        }
        
        if ($matchedRoute) {
            $this->handleRoute($routeConfig, $routeParams);
        } else {
            // Route not found
            $this->response->notFound('Page not found');
        }
    }
    
    /**
     * Handle matched route
     * 
     * @param array $routeConfig Route configuration
     * @param array $params Route parameters
     * @return void
     */
    protected function handleRoute(array $routeConfig, array $params): void
    {
        $controller = $routeConfig['controller'];
        $method = $routeConfig['method'];
        
        // Map parameters to named keys if defined
        if (isset($routeConfig['params']) && is_array($routeConfig['params'])) {
            $namedParams = [];
            foreach ($routeConfig['params'] as $index => $name) {
                $namedParams[] = $params[$index] ?? null;
            }
            $params = $namedParams;
        }
        
        // Handle API routes
        if (strpos($controller, 'Api') !== false) {
            $this->handleApiRoute($controller, $method, $params);
        } else {
            // Handle web routes
            $this->handleWebRoute($controller, $method, $params);
        }
    }
    
    /**
     * Handle API route
     * 
     * @param string $controller Controller name
     * @param string $method Method name
     * @param array $params Method parameters
     * @return void
     */
    protected function handleApiRoute(string $controller, string $method, array $params): void
    {
        $namespace = '\\Kipay\\Api\\';
        $controllerClass = $namespace . $controller;
        
        if (!class_exists($controllerClass)) {
            $this->response->notFound('API endpoint not found');
            return;
        }
        
        // Check if method exists
        if (!method_exists($controllerClass, $method)) {
            $this->response->notFound('API method not found');
            return;
        }
        
        // Create controller instance
        $controllerInstance = new $controllerClass();
        
        // Call method with parameters
        call_user_func_array([$controllerInstance, $method], $params);
    }
    
    /**
     * Handle web route
     * 
     * @param string $controller Controller name
     * @param string $method Method name
     * @param array $params Method parameters
     * @return void
     */
    protected function handleWebRoute(string $controller, string $method, array $params): void
    {
        $namespace = '\\Kipay\\Controllers\\';
        $controllerClass = $namespace . $controller;
        
        if (!class_exists($controllerClass)) {
            $this->response->notFound('Page not found');
            return;
        }
        
        // Check if method exists
        if (!method_exists($controllerClass, $method)) {
            $this->response->notFound('Page not found');
            return;
        }
        
        // Create controller instance
        $controllerInstance = new $controllerClass();
        
        // Call method with parameters
        call_user_func_array([$controllerInstance, $method], $params);
    }
    
    /**
     * Handle uncaught exceptions
     * 
     * @param \Exception $e Exception
     * @return void
     */
    protected function handleException(\Exception $e): void
    {
        // Log the exception
        $this->logger->error('Uncaught exception: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Check if it's an API request
        $isApiRequest = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
        
        if ($isApiRequest) {
            // Send JSON error response
            $this->response->serverError('Internal server error');
        } else {
            // Show error page
            include KIPAY_PATH . '/src/Templates/error.php';
        }
    }
}