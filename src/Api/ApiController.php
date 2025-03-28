<?php
namespace Kipay\Api;

use Kipay\Config\AppConfig;
use Kipay\Models\UserModel;
use Kipay\Utils\Logger;
use Kipay\Utils\Request;
use Kipay\Utils\Response;
use Kipay\Utils\Security;

/**
 * ApiController Class for Kipay Payment Gateway
 * 
 * This is the base controller for all API endpoints.
 */
class ApiController
{
    /**
     * @var \Kipay\Utils\Request
     */
    protected $request;
    
    /**
     * @var \Kipay\Utils\Response
     */
    protected $response;
    
    /**
     * @var \Kipay\Config\AppConfig
     */
    protected $config;
    
    /**
     * @var \Kipay\Utils\Logger
     */
    protected $logger;
    
    /**
     * @var \Kipay\Models\UserModel
     */
    protected $userModel;
    
    /**
     * @var \Kipay\Utils\Security
     */
    protected $security;
    
    /**
     * @var array Authenticated user data
     */
    protected $user = null;
    
    /**
     * ApiController constructor
     */
    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->config = new AppConfig();
        $this->logger = new Logger('api');
        $this->userModel = new UserModel();
        $this->security = new Security();
    }
    
    /**
     * Validate API authentication
     * 
     * @return bool True if authenticated
     */
    protected function validateAuth(): bool
    {
        // Check for API key in header or query parameter
        $apiKey = $this->request->getHeader('X-API-Key') ?? $this->request->getQueryParam('api_key');
        
        if (empty($apiKey)) {
            $this->response->unauthorized('API key is required');
            return false;
        }
        
        // Get user by API key
        $user = $this->userModel->getByApiKey($apiKey);
        
        if (!$user) {
            $this->logger->warning('Invalid API key used', [
                'api_key' => $apiKey,
                'ip' => $this->request->getIpAddress()
            ]);
            
            $this->response->unauthorized('Invalid API key');
            return false;
        }
        
        // Check if user is active
        if (!$user['is_active']) {
            $this->logger->warning('Inactive user attempted API access', [
                'user_id' => $user['id'],
                'ip' => $this->request->getIpAddress()
            ]);
            
            $this->response->forbidden('Your account is inactive');
            return false;
        }
        
        // Store authenticated user
        $this->user = $user;
        
        return true;
    }
    
    /**
     * Log API request
     * 
     * @param string $endpoint API endpoint
     * @param int $statusCode HTTP status code
     * @param array $requestData Request data
     * @param array $responseData Response data
     * @param float $executionTime Execution time in seconds
     * @return void
     */
    protected function logApiRequest(
        string $endpoint,
        int $statusCode,
        array $requestData,
        array $responseData,
        float $executionTime
    ): void {
        try {
            $logData = [
                'user_id' => $this->user ? $this->user['id'] : null,
                'endpoint' => $endpoint,
                'method' => $this->request->method,
                'request_data' => json_encode($requestData),
                'response_data' => json_encode($responseData),
                'ip_address' => $this->request->getIpAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'status_code' => $statusCode,
                'execution_time' => $executionTime
            ];
            
            // Log to database
            $this->userModel->logApiRequest($logData);
        } catch (\Exception $e) {
            $this->logger->error('Failed to log API request', [
                'error' => $e->getMessage()
            ]);
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
    public static function route(string $controller, string $method, array $params = []): void
    {
        try {
            // Check if controller exists
            $controllerClass = "\\Kipay\\Api\\{$controller}";
            
            if (!class_exists($controllerClass)) {
                $response = new Response();
                $response->notFound('API endpoint not found');
                return;
            }
            
            // Check if method exists
            if (!method_exists($controllerClass, $method)) {
                $response = new Response();
                $response->notFound('API method not found');
                return;
            }
            
            // Start measuring execution time
            $startTime = microtime(true);
            
            // Create controller instance
            $controllerInstance = new $controllerClass();
            
            // Call method with parameters
            call_user_func_array([$controllerInstance, $method], $params);
            
            // Calculate execution time
            $executionTime = microtime(true) - $startTime;
            
            // Log API request if user is authenticated
            if (property_exists($controllerInstance, 'user') && $controllerInstance->user) {
                $endpoint = "{$controller}/{$method}";
                $requestData = $controllerInstance->request->getAll();
                $responseData = $controllerInstance->response->getData();
                $statusCode = $controllerInstance->response->getStatusCode();
                
                $controllerInstance->logApiRequest(
                    $endpoint,
                    $statusCode,
                    $requestData,
                    $responseData,
                    $executionTime
                );
            }
        } catch (\Exception $e) {
            $logger = new Logger('api');
            $logger->error('API route error', [
                'controller' => $controller,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            $response = new Response();
            $response->serverError('Internal server error');
        }
    }
    
    /**
     * Handle OPTIONS request for CORS
     * 
     * @return void
     */
    public function options(): void
    {
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        header('Access-Control-Max-Age: 86400'); // 24 hours
        
        // Return 200 OK with no content
        http_response_code(200);
        exit;
    }
}