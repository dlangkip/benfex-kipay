<?php
/**
 * Main entry point for the Kipay application.
 */

// Autoload dependencies
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Initialize the application
use Kipay\App\Application;

// Set error reporting based on environment
$isDebug = isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true';
if ($isDebug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
}

// Create and run the application
$app = new Application();
$app->run();