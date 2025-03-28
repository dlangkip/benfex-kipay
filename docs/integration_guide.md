# Kipay Integration Guide

This guide will help you integrate Kipay Payment Gateway with your applications and websites.

## Integration Methods

Kipay offers several methods for integration:

1. **API Integration**: Use the RESTful API for complete control
2. **Payment Links**: Generate payment links for quick integration
3. **Checkout Page**: Redirect customers to the Kipay checkout page
4. **JavaScript Library**: Use the Kipay.js library for client-side integration
5. **Benfex Integration**: Use the pre-built integration with BENFEX

## API Integration

The most flexible integration method is using the Kipay API directly.

### Authentication

To use the API, you need an API key. Include it in the request header:

```
X-API-Key: your_api_key_here
```

You can generate API keys in the admin dashboard under **Settings** > **API Keys**.

### Basic Payment Flow

1. **Initialize Transaction**: Create a new transaction
2. **Redirect to Payment**: Redirect the customer to the payment URL
3. **Verify Transaction**: Verify the payment status
4. **Update Order**: Update your order status based on the payment result

### Example: PHP Integration

```php
<?php
// Initialize transaction
$data = [
    'amount' => 10000,
    'email' => 'customer@example.com',
    'payment_channel_id' => 1,
    'description' => 'Payment for Order #12345',
    'metadata' => [
        'order_id' => '12345'
    ]
];

$ch = curl_init('https://your-kipay-url.com/api/transactions/initialize');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-API-Key: your_api_key_here'
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['status'] === 'success') {
    // Redirect to payment URL
    header('Location: ' . $result['authorization_url']);
    exit;
} else {
    // Handle error
    echo "Error: " . $result['message'];
}
```

### Example: Verifying Transaction

```php
<?php
// Verify transaction
$reference = $_GET['reference'];

$ch = curl_init("https://your-kipay-url.com/api/transactions/verify/{$reference}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: your_api_key_here'
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['status'] === 'success' && $result['transaction']['status'] === 'completed') {
    // Payment successful, update order status
    echo "Payment successful!";
} else {
    // Payment failed or pending
    echo "Payment not completed. Status: " . $result['transaction']['status'];
}
```

## Payment Links

Payment links provide a simple way to accept payments without complex integration.

### Creating a Payment Link

1. Log in to the admin dashboard
2. Navigate to **Payment Links**
3. Click **Create New Link**
4. Fill in the required details:
   - **Amount**: Payment amount
   - **Description**: Payment description
   - **Expiry Date**: Link expiration (optional)
   - **Payment Channel**: Default payment channel
   - **Redirect URL**: URL to redirect after payment (optional)
5. Click **Generate Link**

### Sharing Payment Links

You can share the generated link via:
- Email
- SMS
- Social media
- QR code (generated automatically)

## Checkout Page

The checkout page offers a ready-made payment experience.

### Redirecting to Checkout

```php
<?php
// Parameters for checkout
$params = [
    'amount' => 10000,
    'email' => 'customer@example.com',
    'reference' => 'ORD-12345',
    'description' => 'Payment for Order #12345',
    'callback_url' => 'https://your-website.com/payment-callback'
];

// Build the checkout URL
$checkoutUrl = 'https://your-kipay-url.com/payment/checkout?' . http_build_query($params);

// Redirect to checkout
header('Location: ' . $checkoutUrl);
exit;
```

### Handling the Callback

```php
<?php
// Get the reference from the callback
$reference = $_GET['reference'];

// Verify the transaction
// (Use the verification code from the API example)
```

## JavaScript Library (Kipay.js)

Kipay provides a JavaScript library for client-side integration.

### Including the Library

```html
<!-- Include Kipay.js -->
<script src="https://your-kipay-url.com/assets/js/kipay.js"></script>
```

### Initializing Payment

```html
<button data-kipay-pay
    data-amount="10000"
    data-email="customer@example.com"
    data-reference="ORD-12345"
    data-description="Payment for Order #12345"
    data-first-name="John"
    data-last-name="Doe">
    Pay Now
</button>

<script>
// Initialize Kipay with configuration
Kipay.init({
    publicKey: 'pk_test_xxxxxxxxxxxxxxxxxxxxx',
    currency: 'KSH',
    callbackUrl: 'https://your-website.com/verify-payment',
    cancelUrl: 'https://your-website.com/payment-cancelled'
});

// Alternative: Programmatically initiate payment
document.querySelector('#customPayButton').addEventListener('click', function() {
    Kipay.setTransaction({
        reference: 'ORD-12345',
        amount: 10000,
        email: 'customer@example.com',
        firstName: 'John',
        lastName: 'Doe',
        description: 'Payment for Order #12345'
    }).payWithPaystack();
});

// Listen for payment events
document.addEventListener('kipay:success', function(e) {
    console.log('Payment successful', e.detail);
    // Update UI or redirect
});

document.addEventListener('kipay:cancel', function(e) {
    console.log('Payment cancelled', e.detail);
    // Handle cancellation
});

document.addEventListener('kipay:error', function(e) {
    console.log('Payment error', e.detail);
    // Handle error
});
</script>
```

## Benfex Integration

Kipay includes pre-built integration with BENFEX 

### Installation

1. Copy the files from `/integrations/benfex/` to your Benfex plugins directory
2. Add the following line to your Benfex `system/autoload.php` file:

   ```php
   require_once 'plugins/kipay/kipay_gateway.php';
   ```

3. Configure the gateway in the Benfex admin panel

### Configuration

Configure the gateway in Benfex:

1. Log in to the Benfex admin panel
2. Navigate to **Settings** > **Payment Gateways**
3. Enable and configure Kipay:
   - **API Key**: Your Kipay API key
   - **API URL**: URL of your Kipay installation
   - **Payment Channel ID**: ID of the payment channel to use

## Webhooks Integration

Webhooks allow your application to receive real-time updates about payments.

### Configuring Your Endpoint

1. Create an endpoint on your server to receive webhook notifications
2. Configure this URL in your payment provider's dashboard

### Example: PHP Webhook Handler

```php
<?php
// Get the payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_KIPAY_SIGNATURE'] ?? '';

// Verify signature
$expectedSignature = hash_hmac('sha256', $payload, 'your_webhook_secret');
if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit;
}

// Parse the payload
$data = json_decode($payload, true);

// Process based on event type
switch ($data['event']) {
    case 'charge.success':
        // Handle successful payment
        $reference = $data['data']['reference'];
        $amount = $data['data']['amount'] / 100; // Convert from kobo to naira
        
        // Update your order status
        updateOrderStatus($reference, 'paid', $amount);
        break;
        
    case 'charge.failed':
        // Handle failed payment
        $reference = $data['data']['reference'];
        
        // Update your order status
        updateOrderStatus($reference, 'failed');
        break;
    
    // Handle other event types
}

// Return success response
http_response_code(200);
echo json_encode(['status' => 'success']);

// Example function to update order status
function updateOrderStatus($reference, $status, $amount = null) {
    // Your code to update the order in your database
}
```

## Mobile Integration

### Android Integration (Kotlin)

```kotlin
// Initialize payment
fun initiatePayment() {
    val client = OkHttpClient()
    
    val json = JSONObject()
    json.put("amount", 10000)
    json.put("email", "customer@example.com")
    json.put("payment_channel_id", 1)
    json.put("description", "Payment from Android app")
    
    val requestBody = json.toString().toRequestBody("application/json".toMediaType())
    
    val request = Request.Builder()
        .url("https://your-kipay-url.com/api/transactions/initialize")
        .addHeader("X-API-Key", "your_api_key_here")
        .post(requestBody)
        .build()
    
    client.newCall(request).enqueue(object : Callback {
        override fun onFailure(call: Call, e: IOException) {
            // Handle error
        }
        
        override fun onResponse(call: Call, response: Response) {
            val responseBody = response.body?.string()
            val jsonResponse = JSONObject(responseBody)
            
            if (jsonResponse.getString("status") == "success") {
                val authUrl = jsonResponse.getString("authorization_url")
                
                // Open the authorization URL in browser
                val intent = Intent(Intent.ACTION_VIEW, Uri.parse(authUrl))
                startActivity(intent)
            }
        }
    })
}
```

### iOS Integration (Swift)

```swift
// Initialize payment
func initiatePayment() {
    let url = URL(string: "https://your-kipay-url.com/api/transactions/initialize")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.addValue("application/json", forHTTPHeaderField: "Content-Type")
    request.addValue("your_api_key_here", forHTTPHeaderField: "X-API-Key")
    
    let parameters: [String: Any] = [
        "amount": 10000,
        "email": "customer@example.com",
        "payment_channel_id": 1,
        "description": "Payment from iOS app"
    ]
    
    request.httpBody = try? JSONSerialization.data(withJSONObject: parameters)
    
    let task = URLSession.shared.dataTask(with: request) { data, response, error in
        guard let data = data, error == nil else {
            // Handle error
            return
        }
        
        if let jsonResponse = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
           let status = jsonResponse["status"] as? String,
           status == "success",
           let authUrl = jsonResponse["authorization_url"] as? String {
            
            // Open the authorization URL in browser
            DispatchQueue.main.async {
                if let url = URL(string: authUrl) {
                    UIApplication.shared.open(url)
                }
            }
        }
    }
    
    task.resume()
}
```

## Integration Best Practices

### Security

1. **Keep API keys secure**: Never expose your API keys in client-side code
2. **Use HTTPS**: Always use secure connections for API calls
3. **Verify signatures**: Always validate webhook signatures
4. **Verify transactions**: Always verify transaction status server-side

### Error Handling

1. **Handle network errors**: Implement proper error handling for API calls
2. **Retry mechanism**: Implement retries for transient errors
3. **Logging**: Log all payment events for debugging

### Testing

1. **Use test mode**: Use test API keys during development
2. **Test cards**: Use test cards provided by payment providers
3. **Edge cases**: Test various error scenarios and edge cases

### UX Considerations

1. **Loading states**: Show appropriate loading indicators during payment processing
2. **Clear messaging**: Provide clear success and error messages
3. **Responsive design**: Ensure payment forms work on mobile devices
4. **Timeout handling**: Handle payment timeout scenarios gracefully

## Additional Resources

- [API Reference](api_reference.md): Detailed documentation of all API endpoints
- [Configuration Guide](configuration.md): Guide to configuring Kipay
- [Installation Guide](installation.md): Guide to installing Kipay

For support, please contact support@kipay.com.