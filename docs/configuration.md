# Kipay Configuration Guide

This guide provides detailed information on configuring the Kipay Payment Gateway to suit your needs.

## Environment Configuration

Kipay uses a `.env` file to manage environment-specific settings. After installation, you should have a `.env` file in the root directory with the following settings:

### Database Settings

```
DB_HOST=localhost
DB_NAME=kipay_db
DB_USER=root
DB_PASS=your_password
```

These settings determine your database connection details. Make sure to use a dedicated database user with appropriate permissions.

### Paystack Configuration

```
PAYSTACK_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxx
PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxx
PAYSTACK_ENVIRONMENT=test
```

- `PAYSTACK_SECRET_KEY`: Your Paystack secret key
- `PAYSTACK_PUBLIC_KEY`: Your Paystack public key
- `PAYSTACK_ENVIRONMENT`: Set to `test` for testing or `live` for production

### Application Settings

```
APP_URL=http://localhost/kipay
APP_DEBUG=true
```

- `APP_URL`: The base URL where Kipay is installed
- `APP_DEBUG`: Set to `true` for development or `false` for production

## Payment Channels

Payment channels represent different payment providers or payment methods that you can offer to your customers. You can configure multiple payment channels with different settings.

### Adding a Payment Channel

1. Log in to the admin dashboard
2. Navigate to **Payment Channels**
3. Click **Add New Channel**
4. Fill in the required information:
   - **Name**: A descriptive name for the channel
   - **Provider**: Select the payment provider (Paystack, Flutterwave, Stripe, or Manual)
   - **Provider Configuration**: Enter the API keys and other settings specific to the provider
   - **Fee Configuration**: Set up transaction fees (optional)
   - **Status**: Active or Inactive

### Provider-Specific Configuration

#### Paystack

- **Public Key**: Your Paystack public key
- **Secret Key**: Your Paystack secret key
- **Test Mode**: Enable or disable test mode
- **Webhook URL**: URL for receiving webhook notifications (automatically generated)

#### Flutterwave

- **Public Key**: Your Flutterwave public key
- **Secret Key**: Your Flutterwave secret key
- **Encryption Key**: Your Flutterwave encryption key
- **Test Mode**: Enable or disable test mode
- **Webhook URL**: URL for receiving webhook notifications

#### Stripe

- **Publishable Key**: Your Stripe publishable key
- **Secret Key**: Your Stripe secret key
- **Test Mode**: Enable or disable test mode
- **Webhook Secret**: Your Stripe webhook signing secret

#### Manual Payment

- **Payment Instructions**: Instructions for manual payments
- **Account Name**: Bank account name (optional)
- **Account Number**: Bank account number (optional)
- **Bank Name**: Bank name (optional)

### Fee Configuration

You can configure transaction fees for each payment channel:

- **Fixed Fee**: Fixed amount charged per transaction
- **Percentage Fee**: Percentage of transaction amount
- **Fee Cap**: Maximum fee amount (optional)

## Webhooks

Webhooks allow Kipay to receive real-time notifications from payment providers about transaction events.

### Configuring Webhooks

#### Paystack Webhooks

1. Log in to your [Paystack Dashboard](https://dashboard.paystack.com/#/settings/developers)
2. Navigate to **Settings** > **API Keys & Webhooks**
3. Add a webhook endpoint: `https://your-domain.com/webhook/paystack`
4. Select events to receive (recommended: all events)

#### Flutterwave Webhooks

1. Log in to your [Flutterwave Dashboard](https://dashboard.flutterwave.com/dashboard/settings/webhooks)
2. Navigate to **Settings** > **Webhooks**
3. Add a webhook URL: `https://your-domain.com/webhook/flutterwave`
4. Enter your webhook secret hash
5. Select events to receive

#### Stripe Webhooks

1. Log in to your [Stripe Dashboard](https://dashboard.stripe.com/webhooks)
2. Navigate to **Developers** > **Webhooks**
3. Add an endpoint: `https://your-domain.com/webhook/stripe`
4. Select events to listen for (recommended: payment_intent events)
5. Make note of the signing secret

### Testing Webhooks

You can test webhook functionality using tools like [Hookdeck](https://hookdeck.com/) or [ngrok](https://ngrok.com/) to create a temporary public URL for your local development environment.

## System Settings

You can configure various system settings through the admin dashboard:

1. Log in to the admin dashboard
2. Navigate to **Settings**
3. Modify the following settings as needed:

### General Settings

- **Site Name**: Name of your payment gateway
- **Site URL**: URL of your payment gateway
- **Company Name**: Your company name
- **Company Email**: Contact email address
- **Logo URL**: Path to your logo image
- **Theme Color**: Primary color for the UI
- **Default Currency**: Default currency for transactions

### Email Settings

- **SMTP Host**: SMTP server hostname
- **SMTP Port**: SMTP server port
- **SMTP Username**: SMTP username
- **SMTP Password**: SMTP password
- **SMTP Encryption**: SSL, TLS, or none
- **From Email**: Email address for sent emails
- **From Name**: Name for sent emails

### Notification Settings

- **Admin Notifications**: Enable/disable email notifications for admin
- **Customer Notifications**: Enable/disable email notifications for customers
- **Notification Events**: Configure which events trigger notifications

## API Integration

### API Keys

To integrate Kipay with your applications, you need to generate API keys:

1. Log in to the admin dashboard
2. Navigate to **Settings** > **API Keys**
3. Click **Generate New Key**
4. Make note of the API Key and API Secret (the secret will only be shown once)

### API Authentication

Include your API key in the request header:

```
X-API-Key: your_api_key_here
```

### Testing the API

You can test API endpoints using tools like [Postman](https://www.postman.com/) or [cURL](https://curl.se/).

Example cURL request:

```bash
curl -X POST \
  https://your-domain.com/api/transactions/initialize \
  -H 'Content-Type: application/json' \
  -H 'X-API-Key: your_api_key_here' \
  -d '{
    "amount": 10000,
    "email": "customer@example.com",
    "payment_channel_id": 1,
    "description": "Payment for Order #12345"
  }'
```

## Security Configuration

### SSL Certificate

Always use HTTPS in production. To set up an SSL certificate:

1. Obtain an SSL certificate (Let's Encrypt, commercial CA, etc.)
2. Install the certificate on your web server
3. Configure your web server to force HTTPS

### File Permissions

Set appropriate file permissions to secure your installation:

```bash
# Set directories to 755
find /path/to/kipay -type d -exec chmod 755 {} \;

# Set files to 644
find /path/to/kipay -type f -exec chmod 644 {} \;

# Make specific directories writable
chmod -R 777 /path/to/kipay/logs
```

### Firewall Configuration

Consider implementing a firewall to restrict access to sensitive resources:

```bash
# Example UFW configuration
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## Merchant Portal Customization

### Customizing the Logo

1. Upload your logo to the `/public/assets/images/` directory
2. Update the logo URL in the system settings

### Customizing Colors

1. Navigate to **Settings** > **General Settings**
2. Update the **Theme Color** setting

### Customizing Templates

To make more extensive customizations:

1. Modify the template files in `/src/Templates/`
2. Update the CSS in `/public/assets/css/styles.css`

### Adding Custom JavaScript

Add custom JavaScript code to `/public/assets/js/` and include it in your templates.

## Integration with Benfex (PHPNuxBill)

Kipay includes pre-built integration with Benfex, a customized version of PHPNuxBill:

### Installation Steps

1. Copy the files from `/integrations/benfex/` to your Benfex installation directory
2. Add the following line to your Benfex `system/autoload.php` file:

   ```php
   require_once 'plugins/kipay/kipay_gateway.php';
   ```

3. Configure the gateway in the Benfex admin panel:
   - Navigate to **Settings** > **Payment Gateways**
   - Enable and configure Kipay

### Configuration Options

- **API Key**: Your Kipay API key
- **API URL**: URL of your Kipay installation
- **Payment Channel ID**: ID of the payment channel to use
- **Success URL**: URL to redirect after successful payment
- **Cancel URL**: URL to redirect after cancelled payment

## Troubleshooting

### Logging

Kipay logs events to the `/logs/` directory. Check these logs for error information:

- `app.log`: General application logs
- `gateway.log`: Payment gateway logs
- `transaction.log`: Transaction-specific logs
- `webhook.log`: Webhook event logs

### Debug Mode

To enable detailed debugging:

1. Set `APP_DEBUG=true` in your `.env` file
2. Check the logs for detailed error information

Remember to disable debug mode in production environments to prevent sensitive information from being exposed.

### Common Issues

#### Payment Failures

If payments are failing:

1. Check that your API keys are correct
2. Verify that the payment channel is active
3. Check for any webhook errors
4. Ensure SSL is properly configured

#### API Connection Issues

If API connections fail:

1. Verify that your API key is correct
2. Check that your server's IP is whitelisted (if required)
3. Verify that there are no firewall issues

#### Database Errors

For database-related issues:

1. Check that your database credentials are correct
2. Verify that the database user has the necessary permissions
3. Check for any connection timeouts or resource limitations