<?php
namespace Kipay\Utils;

/**
 * Request Class for Kipay Payment Gateway
 * 
 * This class handles HTTP request data and methods.
 */
class Request
{
    /**
     * @var string Request method (GET, POST, etc.)
     */
    public $method;
    
    /**
     * @var array Request query parameters
     */
    protected $queryParams;
    
    /**
     * @var array Request body data
     */
    protected $bodyData;
    
    /**
     * @var array Request files
     */
    protected $files;
    
    /**
     * @var array Request headers
     */
    protected $headers;
    
    /**
     * Request constructor
     */
    public function __construct()
    {
        // Get request method
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Parse query parameters
        $this->queryParams = $_GET ?? [];
        
        // Parse request body
        $this->parseBody();
        
        // Parse files
        $this->files = $_FILES ?? [];
        
        // Parse headers
        $this->parseHeaders();
    }
    
    /**
     * Parse request body based on content type
     * 
     * @return void
     */
    protected function parseBody(): void
    {
        $contentType = $this->getContentType();
        
        // Initialize body data
        $this->bodyData = [];
        
        // Handle different content types
        if ($contentType === 'application/json') {
            // Get raw input
            $input = file_get_contents('php://input');
            
            if (!empty($input)) {
                $this->bodyData = json_decode($input, true) ?? [];
            }
        } elseif ($contentType === 'application/x-www-form-urlencoded') {
            $this->bodyData = $_POST ?? [];
        } elseif (strpos($contentType, 'multipart/form-data') !== false) {
            $this->bodyData = $_POST ?? [];
        }
    }
    
    /**
     * Parse request headers
     * 
     * @return void
     */
    protected function parseHeaders(): void
    {
        $this->headers = [];
        
        // If getallheaders() function exists, use it
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            
            // Convert to all uppercase keys for consistency
            foreach ($headers as $name => $value) {
                $name = strtoupper(str_replace('-', '_', $name));
                $this->headers[$name] = $value;
            }
        } else {
            // Fallback method
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) === 'HTTP_') {
                    $name = substr($name, 5);
                    $this->headers[$name] = $value;
                }
            }
        }
    }
    
    /**
     * Get request content type
     * 
     * @return string Content type
     */
    public function getContentType(): string
    {
        // Get content type header
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        // Extract main content type
        if (strpos($contentType, ';') !== false) {
            $contentType = trim(strstr($contentType, ';', true));
        }
        
        return strtolower($contentType);
    }
    
    /**
     * Get query parameter
     * 
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter not found
     * @return mixed Parameter value
     */
    public function getQueryParam(string $name, $default = null)
    {
        return $this->queryParams[$name] ?? $default;
    }
    
    /**
     * Get all query parameters
     * 
     * @return array Query parameters
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
    
    /**
     * Get body parameter
     * 
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter not found
     * @return mixed Parameter value
     */
    public function getBodyParam(string $name, $default = null)
    {
        return $this->bodyData[$name] ?? $default;
    }
    
    /**
     * Get all body parameters
     * 
     * @return array Body parameters
     */
    public function getBodyParams(): array
    {
        return $this->bodyData;
    }
    
    /**
     * Get request body as JSON
     * 
     * @return array JSON data
     */
    public function getJson(): array
    {
        return $this->bodyData;
    }
    
    /**
     * Get uploaded file
     * 
     * @param string $name File name
     * @return array|null File data or null if not found
     */
    public function getFile(string $name)
    {
        return $this->files[$name] ?? null;
    }
    
    /**
     * Get all uploaded files
     * 
     * @return array Uploaded files
     */
    public function getFiles(): array
    {
        return $this->files;
    }
    
    /**
     * Get header value
     * 
     * @param string $name Header name
     * @return string|null Header value or null if not found
     */
    public function getHeader(string $name)
    {
        $name = strtoupper(str_replace('-', '_', $name));
        
        // Check for standard header
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        
        // Check for HTTP_ prefixed header
        $httpName = 'HTTP_' . $name;
        if (isset($this->headers[$httpName])) {
            return $this->headers[$httpName];
        }
        
        // Check in $_SERVER directly
        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }
        
        if (isset($_SERVER[$httpName])) {
            return $_SERVER[$httpName];
        }
        
        return null;
    }
    
    /**
     * Get all headers
     * 
     * @return array Headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    public function getIpAddress(): string
    {
        // Check for proxy
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        return $ip;
    }
    
    /**
     * Get user agent
     * 
     * @return string User agent
     */
    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    /**
     * Get request URI
     * 
     * @return string Request URI
     */
    public function getUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }
    
    /**
     * Get request path
     * 
     * @return string Request path
     */
    public function getPath(): string
    {
        $uri = $this->getUri();
        
        // Remove query string if present
        if (strpos($uri, '?') !== false) {
            $uri = strstr($uri, '?', true);
        }
        
        return $uri;
    }
    
    /**
     * Get all request data
     * 
     * @return array All request data
     */
    public function getAll(): array
    {
        return [
            'method' => $this->method,
            'query' => $this->queryParams,
            'body' => $this->bodyData,
            'files' => $this->files,
            'headers' => $this->headers
        ];
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool True if AJAX request
     */
    public function isAjax(): bool
    {
        return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
    }
    
    /**
     * Check if request is JSON
     * 
     * @return bool True if JSON request
     */
    public function isJson(): bool
    {
        return $this->getContentType() === 'application/json';
    }
}