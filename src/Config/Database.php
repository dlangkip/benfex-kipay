<?php
namespace Kipay\Config;

/**
 * Database Configuration Class for Kipay Payment Gateway
 * 
 * This class handles database configuration.
 */
class Database
{
    /**
     * @var string Database host
     */
    public $host;
    
    /**
     * @var string Database name
     */
    public $database;
    
    /**
     * @var string Database username
     */
    public $username;
    
    /**
     * @var string Database password
     */
    public $password;
    
    /**
     * @var string Database charset
     */
    public $charset = 'utf8mb4';
    
    /**
     * Database constructor
     */
    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->database = $_ENV['DB_NAME'] ?? 'kipay_db';
        $this->username = $_ENV['DB_USER'] ?? 'benfex';
        $this->password = $_ENV['DB_PASS'] ?? 'Benfex@2025';
    }
}