<?php
/**
 * Kipay Payment Gateway
 * 
 * Public entry point for the application.
 */

// Define application path
define('KIPAY_PATH', dirname(__DIR__));

// Autoload dependencies
require_once KIPAY_PATH . '/vendor/autoload.php';

// Load environment variables
if (file_exists(KIPAY_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(KIPAY_PATH);
    $dotenv->load();
}

// Initialize the application using the singleton pattern
use Kipay\App\Application;

try {
    // Get the application instance instead of creating a new instance
    $app = Application::getInstance();
    $app->run();
} catch (Exception $e) {
    // Log the error
    error_log("Application error: " . $e->getMessage());
    
    // Show error page
    http_response_code(500);
    echo "Internal Server Error: Please check the application logs.";
}