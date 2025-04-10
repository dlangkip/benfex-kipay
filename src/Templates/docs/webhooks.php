<?php
// Start output buffering
ob_start();
?>

<h1>Webhooks</h1>
<p class="lead">Webhooks allow your application to receive real-time updates about transaction events without needing to poll the API.</p>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> 
    <strong>What are Webhooks?</strong> 
    Webhooks are HTTP callbacks that are triggered by specific events in the payment process. When an event occurs, Kipay sends an HTTP POST payload to the webhook's configured URL.
</div>

<h2>Setting Up Webhooks</h2>
<p>To set up webhooks for your payment providers:</p>
<ol>
    <li>Go to <strong>Settings</strong> in the Kipay admin dashboard</li>
    <li>Navigate to the <strong>API Keys</strong> tab and scroll down to the <strong>Webhook Settings</strong> section</li>
    <li>Copy the webhook URL for your payment provider (e.g., Paystack, Flutterwave, or Stripe)</li>
    <li>Log in to your payment provider's dashboard and add this URL as a webhook endpoint</li>
    <li>Configure the events you want to receive (usually all payment events)</li>
</ol>

<h2>Webhook URLs</h2>
<p>Kipay provides different webhook URLs for each supported payment provider:</p>
<ul>
    <li><strong>Paystack:</strong> <code>https://kipay.benfex.net/webhook/paystack</code></li>
    <li><strong>Flutterwave:</strong> <code>https://kipay.benfex.net/webhook/flutterwave</code></li>
    <li><strong>Stripe:</strong> <code>https://kipay.benfex.net/webhook/stripe</code></li>
</ul>

<h2>Handling Webhooks in Your Application</h2>
<p>Kipay automatically processes webhook events from payment providers and updates transaction statuses accordingly. However, you might want to perform additional actions when events occur.</p>

<p>You can set up your own webhook endpoint to receive forwarded events from Kipay:</p>

<ol>
    <li>Create a webhook endpoint URL in your application</li>
    <li>Go to <strong>Settings</strong> > <strong>Notifications</strong> in the Kipay admin dashboard</li>
    <li>Enter your webhook URL and select the events you want to receive</li>
    <li>Save your settings</li>
</ol>

<h2>Webhook Events</h2>
<p>Kipay processes the following webhook events:</p>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Event</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code>charge.success</code></td>
            <td>When a payment is successful</td>
        </tr>
        <tr>
            <td><code>charge.failed</code></td>
            <td>When a payment fails</td>
        </tr>
        <tr>
            <td><code>transfer.success</code></td>
            <td>When a transfer is successful</td>
        </tr>
        <tr>
            <td><code>transfer.failed</code></td>
            <td>When a transfer fails</td>
        </tr>
        <tr>
            <td><code>subscription.create</code></td>
            <td>When a subscription is created</td>
        </tr>
        <tr>
            <td><code>subscription.disable</code></td>
            <td>When a subscription is disabled</td>
        </tr>
        <tr>
            <td><code>invoice.create</code></td>
            <td>When an invoice is created</td>
        </tr>
        <tr>
            <td><code>invoice.payment_failed</code></td>
            <td>When an invoice payment fails</td>
        </tr>
    </tbody>
</table>

<h2>Webhook Payload Format</h2>
<p>The payload format varies depending on the payment provider, but typically includes the following information:</p>

<h3>Paystack Webhook Example</h3>
<pre class="line-numbers"><code class="language-json">{
  "event": "charge.success",
  "data": {
    "id": 123456789,
    "reference": "KIPAY12345678",
    "amount": 10000,
    "status": "success",
    "channel": "card",
    "currency": "KSH",
    "paid_at": "2025-04-10T12:34:56.000Z",
    "customer": {
      "email": "customer@example.com",
      "name": "John Doe"
    }
  }
}</code></pre>

<h3>Flutterwave Webhook Example</h3>
<pre class="line-numbers"><code class="language-json">{
  "event": "charge.completed",
  "data": {
    "id": 123456789,
    "tx_ref": "KIPAY12345678",
    "amount": 10000,
    "status": "successful",
    "payment_type": "card",
    "currency": "KSH",
    "customer": {
      "email": "customer@example.com",
      "name": "John Doe"
    }
  }
}</code></pre>

<h3>Stripe Webhook Example</h3>
<pre class="line-numbers"><code class="language-json">{
  "id": "evt_123456789",
  "type": "charge.succeeded",
  "data": {
    "object": {
      "id": "ch_123456789",
      "amount": 10000,
      "currency": "ksh",
      "status": "succeeded",
      "payment_method_details": {
        "type": "card"
      },
      "metadata": {
        "reference": "KIPAY12345678"
      }
    }
  }
}</code></pre>

<h2>Verifying Webhook Signatures</h2>
<p>To ensure that webhook events are coming from the actual payment provider, Kipay verifies webhook signatures. Each provider has its own signature verification method:</p>

<h3>Paystack</h3>
<p>Paystack signs webhook payloads with the <code>X-Paystack-Signature</code> header.</p>

<h3>Flutterwave</h3>
<p>Flutterwave includes a <code>verificationHash</code> in the webhook payload.</p>

<h3>Stripe</h3>
<p>Stripe signs webhook payloads with the <code>Stripe-Signature</code> header.</p>

<h2>Testing Webhooks</h2>
<p>You can test webhooks locally using tools like ngrok:</p>

<ol>
    <li>Download and install <a href="https://ngrok.com/download" target="_blank">ngrok</a></li>
    <li>Start your local development server (e.g., <code>php -S localhost:8000</code>)</li>
    <li>Start ngrok: <code>ngrok http 8000</code></li>
    <li>Copy the ngrok URL (e.g., <code>https://1234abcd.ngrok.io</code>)</li>
    <li>Update your webhook URL in the payment provider's dashboard to point to your ngrok URL (e.g., <code>https://1234abcd.ngrok.io/webhook/paystack</code>)</li>
    <li>Make a test payment to trigger the webhook</li>
</ol>

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i> 
    <strong>Important:</strong> 
    Always implement proper error handling for webhooks. If your webhook endpoint fails to process an event, the payment provider might retry the webhook multiple times.
</div>

<h2>Troubleshooting Webhooks</h2>
<p>If you're having issues with webhooks, check the following:</p>

<ol>
    <li>Verify that the webhook URL is correctly configured in the payment provider's dashboard</li>
    <li>Check the Kipay logs for webhook events in the admin dashboard</li>
    <li>Ensure your server is accessible from the internet</li>
    <li>Check for any firewall or security settings that might be blocking webhook requests</li>
    <li>Verify that your webhook endpoint is returning a 200 OK response, even if it encounters an error</li>
</ol>

<?php
// Get the content of the output buffer
$content = ob_get_clean();

// Include the layout template
include KIPAY_PATH . '/src/Templates/docs/layout.php';
?>