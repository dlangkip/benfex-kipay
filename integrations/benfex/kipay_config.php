<?php
/**
 * Kipay Payment Gateway Configuration for BENFEX
 * 
 * This file contains the configuration for the Kipay payment gateway in BENFEX.
 * 
 * @package Kipay
 * @version 1.0.0
 */

// Initialize default configuration
$kipay_config = [
    'active' => false,
    'api_key' => '',
    'api_url' => 'https://kipay.benfex.net',
    'payment_channel_id' => '',
    'success_url' => U . 'payment-successful',
    'cancel_url' => U . 'payment-cancelled'
];

// Load configuration from database if available
$kipay_settings = ORM::for_table('tbl_appconfig')
    ->where('setting', 'kipay_settings')
    ->find_one();

if ($kipay_settings) {
    // Parse configuration
    $loaded_config = json_decode($kipay_settings['value'], true);
    
    if (is_array($loaded_config)) {
        $kipay_config = array_merge($kipay_config, $loaded_config);
    }
}