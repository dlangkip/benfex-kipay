<?php
namespace Kipay\Utils;

/**
 * Security Class for Kipay Payment Gateway
 * 
 * This class handles security-related functions.
 */
class Security
{
    /**
     * Hash a password
     * 
     * @param string $password Plain password
     * @return string Hashed password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify a password against a hash
     * 
     * @param string $password Plain password
     * @param string $hash Password hash
     * @return bool True if password matches
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate a random API key
     * 
     * @return string API key
     */
    public function generateApiKey(): string
    {
        return bin2hex(random_bytes(24));
    }
    
    /**
     * Generate a random API secret
     * 
     * @return string API secret
     */
    public function generateApiSecret(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Generate a random token
     * 
     * @param int $length Token length
     * @return string Random token
     */
    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Generate a secure hash
     * 
     * @param string $data Data to hash
     * @param string $salt Salt for hashing
     * @return string Secure hash
     */
    public function hash(string $data, string $salt = ''): string
    {
        return hash_hmac('sha256', $data, $salt ?: $_ENV['APP_KEY'] ?? 'kipay');
    }
    
    /**
     * Encrypt data
     * 
     * @param string $data Data to encrypt
     * @param string $key Encryption key (optional)
     * @return string Encrypted data
     */
    public function encrypt(string $data, string $key = ''): string
    {
        // Use provided key or app key
        $encryptionKey = $key ?: $_ENV['APP_KEY'] ?? 'kipay';
        
        // Generate an initialization vector
        $iv = random_bytes(16);
        
        // Encrypt the data
        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            $encryptionKey,
            0,
            $iv
        );
        
        // Combine IV and encrypted data
        $result = base64_encode($iv . $encrypted);
        
        return $result;
    }
    
    /**
     * Decrypt data
     * 
     * @param string $data Encrypted data
     * @param string $key Encryption key (optional)
     * @return string|bool Decrypted data or false on failure
     */
    public function decrypt(string $data, string $key = '')
    {
        // Use provided key or app key
        $encryptionKey = $key ?: $_ENV['APP_KEY'] ?? 'kipay';
        
        // Decode the data
        $data = base64_decode($data);
        
        // Extract IV and encrypted data
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        // Decrypt the data
        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $encryptionKey,
            0,
            $iv
        );
        
        return $decrypted;
    }
    
    /**
     * Generate a CSRF token
     * 
     * @return string CSRF token
     */
    public function generateCsrfToken(): string
    {
        // Generate a random token
        $token = $this->generateToken();
        
        // Store token in session
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }
    
    /**
     * Verify a CSRF token
     * 
     * @param string $token CSRF token to verify
     * @return bool True if token is valid
     */
    public function verifyCsrfToken(string $token): bool
    {
        // Check if token exists in session
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Compare tokens using constant-time comparison
        $result = hash_equals($_SESSION['csrf_token'], $token);
        
        // Regenerate token after verification
        $this->generateCsrfToken();
        
        return $result;
    }
    
    /**
     * Sanitize input data
     * 
     * @param mixed $data Input data
     * @return mixed Sanitized data
     */
    public function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
            return $data;
        }
        
        if (is_string($data)) {
            // Trim whitespace
            $data = trim($data);
            
            // Remove control characters
            $data = preg_replace('/[\x00-\x1F\x7F]/', '', $data);
            
            // HTML encode
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        
        return $data;
    }
    
    /**
     * Validate email address
     * 
     * @param string $email Email address
     * @return bool True if valid
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate URL
     * 
     * @param string $url URL
     * @return bool True if valid
     */
    public function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}