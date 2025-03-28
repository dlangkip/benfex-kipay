<?php
namespace Kipay\Utils;

/**
 * Response Class for Kipay Payment Gateway
 * 
 * This class handles HTTP responses.
 */
class Response
{
    /**
     * @var int HTTP status code
     */
    protected $statusCode = 200;
    
    /**
     * @var array Response data
     */
    protected $data = [];
    
    /**
     * @var string Response content type
     */
    protected $contentType = 'application/json';
    
    /**
     * @var array HTTP headers
     */
    protected $headers = [];
    
    /**
     * Response constructor
     */
    public function __construct()
    {
        // Set default headers
        $this->setHeader('Access-Control-Allow-Origin', '*');
        $this->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $this->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key');
    }
    
    /**
     * Set HTTP status code
     * 
     * @param int $code Status code
     * @return self
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }
    
    /**
     * Get current status code
     * 
     * @return int Status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    /**
     * Set response content type
     * 
     * @param string $contentType Content type
     * @return self
     */
    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;
        $this->setHeader('Content-Type', $contentType);
        return $this;
    }
    
    /**
     * Set response header
     * 
     * @param string $name Header name
     * @param string $value Header value
     * @return self
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Get response data
     * 
     * @return array Response data
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Send JSON response
     * 
     * @param mixed $data Response data
     * @param int $code HTTP status code
     * @return void
     */
    public function json($data, int $code = 200): void
    {
        $this->data = $data;
        $this->setStatusCode($code);
        $this->setContentType('application/json');
        $this->send();
    }
    
    /**
     * Send success response
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $code HTTP status code
     * @return void
     */
    public function success($data = null, string $message = 'Success', int $code = 200): void
    {
        $response = [
            'status' => 'success',
            'message' => $message
        ];
        
        if ($data !== null) {
            if (is_array($data) && isset($data['message'])) {
                // If data already contains a message, use it
                $response['message'] = $data['message'];
                unset($data['message']);
            }
            
            // Merge data with response
            if (is_array($data)) {
                $response = array_merge($response, $data);
            } else {
                $response['data'] = $data;
            }
        }
        
        $this->json($response, $code);
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param mixed $errors Detailed errors
     * @param int $code HTTP status code
     * @return void
     */
    public function error(string $message, $errors = null, int $code = 400): void
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        $this->json($response, $code);
    }
    
    /**
     * Send 400 Bad Request response
     * 
     * @param string $message Error message
     * @param mixed $errors Detailed errors
     * @return void
     */
    public function badRequest(string $message = 'Bad Request', $errors = null): void
    {
        $this->error($message, $errors, 400);
    }
    
    /**
     * Send 401 Unauthorized response
     * 
     * @param string $message Error message
     * @return void
     */
    public function unauthorized(string $message = 'Unauthorized'): void
    {
        $this->error($message, null, 401);
    }
    
    /**
     * Send 403 Forbidden response
     * 
     * @param string $message Error message
     * @return void
     */
    public function forbidden(string $message = 'Forbidden'): void
    {
        $this->error($message, null, 403);
    }
    
    /**
     * Send 404 Not Found response
     * 
     * @param string $message Error message
     * @return void
     */
    public function notFound(string $message = 'Not Found'): void
    {
        $this->error($message, null, 404);
    }
    
    /**
     * Send 405 Method Not Allowed response
     * 
     * @param array $allowedMethods Allowed methods
     * @param string $message Error message
     * @return void
     */
    public function methodNotAllowed(array $allowedMethods, string $message = 'Method Not Allowed'): void
    {
        $this->setHeader('Allow', implode(', ', $allowedMethods));
        $this->error($message, null, 405);
    }
    
    /**
     * Send 422 Unprocessable Entity response
     * 
     * @param mixed $errors Validation errors
     * @param string $message Error message
     * @return void
     */
    public function validationError($errors, string $message = 'Validation Error'): void
    {
        $this->error($message, $errors, 422);
    }
    
    /**
     * Send 500 Internal Server Error response
     * 
     * @param string $message Error message
     * @return void
     */
    public function serverError(string $message = 'Internal Server Error'): void
    {
        $this->error($message, null, 500);
    }
    
    /**
     * Send 201 Created response
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @return void
     */
    public function created($data = null, string $message = 'Resource Created Successfully'): void
    {
        $this->success($data, $message, 201);
    }
    
    /**
     * Send 204 No Content response
     * 
     * @return void
     */
    public function noContent(): void
    {
        $this->setStatusCode(204);
        $this->send();
    }
    
    /**
     * Send HTTP response
     * 
     * @return void
     */
    public function send(): void
    {
        // Set status code
        http_response_code($this->statusCode);
        
        // Set headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        // If not a 204 response, send response body
        if ($this->statusCode !== 204) {
            if ($this->contentType === 'application/json') {
                echo json_encode($this->data);
            } else {
                echo $this->data;
            }
        }
        
        // End script execution
        exit;
    }
    
    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     * @param int $code HTTP status code
     * @return void
     */
    public function redirect(string $url, int $code = 302): void
    {
        $this->setStatusCode($code);
        $this->setHeader('Location', $url);
        $this->send();
    }
}