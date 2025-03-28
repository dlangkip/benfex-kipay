<?php
/**
 * Main entry point for the Kipay application.
 * This file is included by public/index.php.
 */

// Autoload dependencies
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the application
use Kipay\App\Application;

$app = new Application();
$app->run();