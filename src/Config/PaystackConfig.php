<?php
namespace Kipay\Config;

/**
 * Paystack Configuration Class for Kipay Payment Gateway
 * 
 * This class handles Paystack configuration.
 */
class PaystackConfig
{
    /**
     * @var string Paystack secret key
     */
    public $secretKey;
    
    /**
     * @var string Paystack public key
     */
    public $publicKey;
    
    /**
     * @var string Paystack environment (test or live)
     */
    public $environment;
    
    /**
     * @var string Paystack base URL
     */
    public $baseUrl = 'https://api.paystack.co';
    
    /**
     * PaystackConfig constructor
     */
    public function __construct()
    {
        $this->secretKey = $_ENV['PAYSTACK_SECRET_KEY'] ?? '';
        $this->publicKey = $_ENV['PAYSTACK_PUBLIC_KEY'] ?? '';
        $this->environment = $_ENV['PAYSTACK_ENVIRONMENT'] ?? 'test';
    }
    
    /**
     * Check if configuration is valid
     * 
     * @return bool True if valid
     */
    public function isValid(): bool
    {
        return !empty($this->secretKey) && !empty($this->publicKey);
    }
    
    /**
     * Get the appropriate key based on environment
     * 
     * @param string $type Key type (secret or public)
     * @return string API key
     */
    public function getKey(string $type = 'secret'): string
    {
        if ($type === 'public') {
            return $this->publicKey;
        }
        
        return $this->secretKey;
    }
}