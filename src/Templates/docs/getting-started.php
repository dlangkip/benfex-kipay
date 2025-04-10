<?php
// Start output buffering
ob_start();
?>

<h1>Getting Started</h1>
<p class="lead">This guide will walk you through the steps to set up your Kipay Payment Gateway account and process your first transaction.</p>

<div class="alert alert-primary">
    <i class="fas fa-lightbulb"></i> 
    <strong>Quick Start:</strong> 
    Follow these steps to start accepting payments in minutes!
</div>

<h2>Step 1: Create an Account</h2>
<p>To get started with Kipay, you need to create an account:</p>
<ol>
    <li>Visit the <a href="/admin/login">login page</a> and click on "Sign Up"</li>
    <li>Fill in your details to create an account</li>
    <li>Verify your email address</li>
    <li>Log in to your account</li>
</ol>

<h2>Step 2: Set Up a Payment Channel</h2>
<p>Before you can accept payments, you need to set up at least one payment channel:</p>
<ol>
    <li>Go to <strong>Payment Channels</strong> in the admin dashboard</li>
    <li>Click on <strong>Add New Channel</strong></li>
    <li>Select a payment provider (e.g., Paystack, Flutterwave, Stripe)</li>
    <li>Enter your API keys and other required information</li>
    <li>Save the channel</li>
</ol>

<div class="alert alert-info">
    <strong>Note:</strong> You will need to create an account with your chosen payment provider and obtain API keys from them.
</div>

<h2>Step 3: Get Your API Keys</h2>
<p>To integrate Kipay with your application, you need your API keys:</p>
<ol>
    <li>Go to <strong>Settings</strong> in the admin dashboard</li>
    <li>Navigate to the <strong>API Keys</strong> tab</li>
    <li>Copy your API key (keep it secure!)</li>
</ol>

<h2>Step 4: Initialize a Transaction</h2>
<p>Now you're ready to initialize your first transaction. You can do this via API or use our checkout page:</p>

<h4>Via API:</h4>

<pre class="line-numbers"><code class="language-php">// Sample PHP code to initialize a transaction
$curl = curl_init();

$data = [
    'amount' => 10000, // Amount in cents/kobo
    'email' => 'customer@example.com',
    'payment_channel_id' => 1, // Your payment channel ID
    'description' => 'Payment for Order #12345',
    'currency' => 'KSH'
];

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://kipay.benfex.net/api/transactions/initialize',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: your_api_key_here'
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $result = json_decode($response, true);
    
    // Redirect to payment page
    if ($result['status'] === 'success') {
        header('Location: ' . $result['authorization_url']);
        exit;
    }
}</code></pre>

<h4>Via Checkout Page:</h4>
<p>Alternatively, you can create a simple HTML form that redirects to our checkout page:</p>

<pre class="line-numbers"><code class="language-html">&lt;form action="https://kipay.benfex.net/payment/checkout" method="post"&gt;
    &lt;input type="hidden" name="amount" value="10000"&gt;
    &lt;input type="hidden" name="email" value="customer@example.com"&gt;
    &lt;input type="hidden" name="payment_channel_id" value="1"&gt;
    &lt;input type="hidden" name="description" value="Payment for Order #12345"&gt;
    &lt;input type="hidden" name="currency" value="KSH"&gt;
    &lt;input type="hidden" name="api_key" value="your_api_key_here"&gt;
    
    &lt;button type="submit"&gt;Pay Now&lt;/button&gt;
&lt;/form&gt;</code></pre>

<h2>Step 5: Verify Transaction Status</h2>
<p>After a payment is initiated, you'll want to verify its status. This is done using the reference number:</p>

<pre class="line-numbers"><code class="language-php">// Sample PHP code to verify a transaction
$curl = curl_init();

$reference = 'KIPAY12345678'; // The transaction reference returned during initialization

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://kipay.benfex.net/api/transactions/verify/' . $reference,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => [
        'X-API-Key: your_api_key_here'
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $result = json_decode($response, true);
    
    if ($result['status'] === 'success' && $result['transaction']['status'] === 'completed') {
        // Payment was successful
        // Update your database or perform other actions
        echo "Payment completed successfully!";
    } else {
        // Payment failed or is still pending
        echo "Payment status: " . $result['transaction']['status'];
    }
}</code></pre>

<h2>Step 6: Set Up Webhooks (Optional)</h2>
<p>For real-time payment notifications, you should set up webhooks:</p>
<ol>
    <li>Go to <strong>Settings</strong> in the admin dashboard</li>
    <li>Navigate to the <strong>Webhook Settings</strong> tab</li>
    <li>Copy the webhook URL for your payment provider</li>
    <li>Set up this URL in your payment provider's dashboard</li>
</ol>

<p>Once set up, Kipay will receive real-time updates about transaction statuses, even when your customers don't return to your website after payment.</p>

<div class="alert alert-success mt-4">
    <i class="fas fa-check-circle"></i> 
    <strong>Congratulations!</strong> 
    You've now set up Kipay and processed your first transaction. To learn more about advanced features, check out the rest of our documentation.
</div>

<h2>Next Steps</h2>
<ul>
    <li><a href="/docs/payment-channels">Configure Multiple Payment Channels</a></li>
    <li><a href="/docs/webhooks">Set Up Advanced Webhook Handling</a></li>
    <li><a href="/docs/api">Explore the Complete API Reference</a></li>
</ul>

<?php
// Get the content of the output buffer
$content = ob_get_clean();

// Include the layout template
include KIPAY_PATH . '/src/Templates/docs/layout.php';
?>