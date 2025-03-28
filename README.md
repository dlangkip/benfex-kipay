# Kipay Payment Gateway

Kipay is a flexible, extensible PHP-based payment gateway built on top of Paystack's API. It provides complete control over the payment flow while leveraging Paystack's established payment infrastructure in Africa.

## Features

- **Custom Payment Flow**: Complete control over the payment experience and user interface
- **Multiple Payment Channels**: Support for multiple payment providers and accounts
- **RESTful API**: Clean and well-documented API for easy integration with any application
- **Merchant Dashboard**: Comprehensive dashboard for managing transactions, payment channels, and customers
- **BENFEX Integration**: Ready-to-use integration with BENFEX
- **Transaction Management**: Search, filter, export, and analyze transactions
- **Customer Management**: Track customer information and payment history
- **Webhook Support**: Receive real-time updates from payment providers
- **Security**: Secure API authentication and data encryption

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Apache/Nginx web server
- mod_rewrite enabled
- PDO PHP Extension
- OpenSSL PHP Extension
- JSON PHP Extension
- cURL PHP Extension
- Mbstring PHP Extension

## Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/yourusername/kipay.git
   cd kipay
   ```

2. **Install dependencies:**

   ```bash
   composer install
   ```

3. **Copy the environment file:**

   ```bash
   cp .env.example .env
   ```

4. **Configure your environment variables:**

   Edit the `.env` file and set your database credentials, Paystack API keys, and other settings.

5. **Create the database:**

   ```sql
   CREATE DATABASE kipay_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

6. **Run the database migrations:**

   ```bash
   php src/Database/Migrations/create_tables.sql
   ```

   Or import the SQL file via phpMyAdmin or another MySQL client.

7. **Set proper permissions:**

   ```bash
   chmod -R 755 .
   chmod -R 777 logs
   ```

8. **Configure your web server:**

   Set the document root to the `public` directory.

9. **Access the dashboard:**

   Open your browser and navigate to `http://yourdomain.com/admin`
   
   Default login:
   - Username: admin
   - Password: admin123

   (Remember to change the default password after first login)

## Configuration

### Payment Channels

You can add multiple payment channels with different configurations:

1. Log in to the admin dashboard
2. Go to "Payment Channels"
3. Click "Add New Channel"
4. Select the provider (e.g., Paystack)
5. Enter the required configuration
6. Save the channel

### Webhook Setup

For real-time updates, configure webhooks in your payment provider:

1. Log in to your Paystack dashboard
2. Go to Settings > API Keys & Webhooks
3. Add a webhook endpoint: `https://yourdomain.com/webhook/paystack`
4. Select the events you want to receive

## API Usage

### Authentication

All API requests require an API key for authentication. You can get your API key from the dashboard under Settings > API Keys.

Include the API key in the header of all requests:

```
X-API-Key: your_api_key_here
```

### Initialize a Transaction

```bash
curl -X POST \
  https://yourdomain.com/api/transactions/initialize \
  -H 'Content-Type: application/json' \
  -H 'X-API-Key: your_api_key_here' \
  -d '{
    "amount": 10000,
    "email": "customer@example.com",
    "payment_channel_id": 1,
    "description": "Payment for Order #12345",
    "currency": "KSH",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+2348012345678"
  }'
```

### Verify a Transaction

```bash
curl -X GET \
  https://yourdomain.com/api/transactions/verify/KIPAY12345678 \
  -H 'X-API-Key: your_api_key_here'
```

## BENFEX Integration

To integrate Kipay with BENFEX:

1. Copy the files from `integrations/benfex` to your benfex `system/plugins/kipay` directory
2. Add the following line to your `system/autoload.php`:

   ```php
   require_once 'plugins/kipay/kipay_gateway.php';
   ```

3. Initialize the plugin by adding this line to your `system/hooks.php`:

   ```php
   $kipay_config = kipay_init();
   ```

4. Configure the gateway in BENFEX admin panel under Settings > Kipay Settings

## Extending Kipay

### Adding a New Payment Provider

1. Add the provider to the supported providers list in `src/Core/PaymentChannel.php`
2. Create a new method in `src/Core/Gateway.php` to initialize the provider client
3. Create a new method in `src/Core/Gateway.php` to initialize transactions with the provider
4. Create a new method in `src/Core/Gateway.php` to verify transactions with the provider
5. Add a webhook handler in `src/Webhooks/WebhookHandler.php`

### Creating Custom Plugins

The modular structure of Kipay makes it easy to create custom plugins:

1. Create a new directory in `integrations/your_plugin_name`
2. Implement the necessary files to integrate with the target application
3. Use the Kipay API to interact with the payment gateway

## Security

- All API requests are authenticated using API keys
- Passwords are securely hashed using bcrypt
- Sensitive data is encrypted
- API logs are maintained for auditing
- CSRF protection for web forms
- Input validation and sanitization

## License

This software is released under the MIT License. See LICENSE file for details.

## Support

For support, bug reports, or feature requests, please create an issue on GitHub or contact support@kipay.com.

## Credits

Kipay Payment Gateway is built on top of the following technologies:

- [Paystack PHP Library](https://github.com/yabacon/paystack-php)
- [Monolog](https://github.com/Seldaek/monolog)
- [Dotenv](https://github.com/vlucas/phpdotenv)