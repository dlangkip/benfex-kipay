-- Kipay Payment Gateway Database Schema

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS kipay_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE kipay_db;

-- Users table (for admin/merchant access)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role ENUM('admin', 'merchant', 'developer') NOT NULL DEFAULT 'merchant',
    api_key VARCHAR(64) UNIQUE,
    api_secret VARCHAR(64),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Payment channels table
CREATE TABLE IF NOT EXISTS payment_channels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    provider ENUM('paystack', 'flutterwave', 'stripe', 'manual') NOT NULL DEFAULT 'paystack',
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    config JSON NOT NULL,
    fees_config JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    country VARCHAR(50),
    postal_code VARCHAR(20),
    metadata JSON,
    paystack_customer_code VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, email)
) ENGINE=InnoDB;

-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_id INT NULL,
    payment_channel_id INT NOT NULL,
    reference VARCHAR(100) NOT NULL UNIQUE,
    provider_reference VARCHAR(100),
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'KSH',
    description TEXT,
    status ENUM('pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50),
    fee DECIMAL(10, 2) DEFAULT 0.00,
    metadata JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    gateway_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (payment_channel_id) REFERENCES payment_channels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Transaction logs table
CREATE TABLE IF NOT EXISTS transaction_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    message TEXT,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Webhook events table
CREATE TABLE IF NOT EXISTS webhook_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_channel_id INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    processed BOOLEAN DEFAULT FALSE,
    processing_attempts INT DEFAULT 0,
    processing_error TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (payment_channel_id) REFERENCES payment_channels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- API logs table
CREATE TABLE IF NOT EXISTS api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    request_data JSON,
    response_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status_code INT,
    execution_time FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, setting_key)
) ENGINE=InnoDB;

-- Insert default admin user (Change password after installation)
INSERT INTO users (username, email, password, first_name, last_name, role)
VALUES ('admin', 'admin@benfex.net', '$2y$10$u7vhg8UKP2ZtU5LcI.3qb.cIlT5.by76AvF6uFJX8YwH4OgQYb.mW', 'Admin', 'User', 'admin');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, is_public) VALUES 
('site_name', 'Kipay Payment Gateway', 1),
('site_url', 'https://kipay.benfex.net', 1),
('company_name', 'Benfex', 1),
('company_email', 'info@benfex.com', 1),
('logo_url', '/assets/images/logo.png', 1),
('theme_color', '#3490dc', 1),
('currency', 'KSH', 1),
('payment_success_url', '/payment/success', 1),
('payment_failure_url', '/payment/failure', 1);