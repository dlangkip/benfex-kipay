<?php
// Start output buffering
ob_start();
?>

<h1>Payment Channels</h1>
<p class="lead">Payment channels allow you to connect with different payment providers through a single interface, giving your customers more payment options.</p>

<div class="alert alert-primary">
    <i class="fas fa-lightbulb"></i> 
    <strong>Key Benefit:</strong> 
    With multiple payment channels, you can offer more payment options to your customers and have fallback options if one provider experiences issues.
</div>

<h2>Supported Payment Providers</h2>
<p>Kipay currently supports the following payment providers:</p>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Paystack</h5>
                <p class="card-text">A popular payment provider focusing on African markets, particularly Nigeria, Ghana, and Kenya.</p>
                <a href="https://paystack.com" target="_blank" class="btn btn-sm btn-outline-primary">Visit Website</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Flutterwave</h5>
                <p class="card-text">A comprehensive payment solution for businesses in Africa with support for multiple payment methods.</p>
                <a href="https://flutterwave.com" target="_blank" class="btn btn-sm btn-outline-primary">Visit Website</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Stripe</h5>
                <p class="card-text">A global payment processor supporting businesses in over 40 countries with extensive payment options.</p>
                <a href="https://stripe.com" target="_blank" class="btn btn-sm btn-outline-primary">Visit Website</a>
            </div>
        </div>
    </div>
</div>

<h2>Setting Up Payment Channels</h2>
<ol>
    <li>First, sign up with your preferred payment provider(s) and obtain API keys</li>
    <li>In the Kipay admin dashboard, go to <strong>Payment Channels</strong></li>
    <li>Click <strong>Add New Channel</strong></li>
    <li>Fill in the required information for your chosen provider</li>
    <li>Save the channel</li>
</ol>

<h3>Paystack Configuration</h3>
<p>To set up a Paystack payment channel, you'll need the following information:</p>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Field</th>
            <th>Description</th>
            <th>Required</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Public Key</td>
            <td>Your Paystack public key (starts with pk_)</td>
            <td>Yes</td>
        </tr>
        <tr>
            <td>Secret Key</td>
            <td>Your Paystack secret key (starts with sk_)</td>
            <td>Yes</td>
        </tr>
        <tr>
            <td>Test Mode</td>
            <td>Enable if you're using test API keys</td>
            <td>No</td>
        </tr>
        <tr>
            <td>Webhook URL</td>
            <td>URL for receiving webhooks (auto-generated)</td>
            <td>No</td>
        </tr>
    </tbody>
</table>

<p>You can find your Paystack API keys in the Paystack Dashboard under Settings > API Keys & Webhooks.</p>

<h3>Flutterwave Configuration</h3>
<p>To set up a Flutterwave payment channel, you'll need:</p>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Field</th>
            <th>Description</th>
            <th>Required</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Public Key</td>
            <td>Your Flutterwave public key</td>
            <td>Yes</td>
        </tr>
        <tr>
            <td>Secret Key</td>
            <td>Your Flutterwave secret key</td>
            <td>Yes</td>
        </tr>
        <tr>
            <td>Encryption Key</td>
            <td>Your Flutterwave encryption key</td>
            <td>Yes</td>
        </tr>
        <tr>
            <td>Test Mode</td>
            <td>Enable if you're using test API keys</td>
            <td>No</td>
        </tr>
    </tbody>
</table>

<p>You can find your Flutterwave API keys in the Flutterwave Dashboard under Settings > API.</p>

<h3>Stripe Configuration</h3>
<p>To set up a Stripe payment channel, you'll need:</p>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Field</th>
            <th>Description</th>
            <th>Required</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Publishable Key</td>
            <td>Your Stripe publishable key (starts with pk_)</td>
            <td>Yes</td>
        </tr>
        <tr>
            <td>Secret Key</td>
            <td>Your Stripe secret key (starts with sk_)</td>
            <td>Yes</td>
        </tr>
        <tr>
            <td>Webhook Secret</td>
            <td>Your Stripe webhook signing secret</td>
            <td>No</td>
        </tr>
    </tbody>
</table>

<p>You can find your Stripe API keys in the Stripe Dashboard under Developers > API keys.</p>

<h2>Managing Payment Channels</h2>

<h3>Setting a Default Channel</h3>
<p>You can set one payment channel as the default, which will be used when no specific channel is selected for a transaction:</p>
<ol>
    <li>Go to <strong>Payment Channels</strong> in the admin dashboard</li>
    <li>Find the channel you want to set as default</li>
    <li>Click the <strong>Set as Default</strong> button</li>
</ol>

<h3>Activating/Deactivating Channels</h3>
<p>You can activate or deactivate payment channels as needed:</p>
<ol>
    <li>Go to <strong>Payment Channels</strong> in the admin dashboard</li>
    <li>Find the channel you want to activate/deactivate</li>
    <li>Toggle the <strong>Active</strong> switch</li>
    <li>Save your changes</li>
</ol>

<h3>Deleting Channels</h3>
<p>You can delete a payment channel if it's no longer needed:</p>
<ol>
    <li>Go to <strong>Payment Channels</strong> in the admin dashboard</li>
    <li>Find the channel you want to delete</li>
    <li>Click the <strong>Delete</strong> button</li>
    <li>Confirm the deletion</li>
</ol>

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i> 
    <strong>Important:</strong> 
    You cannot delete a payment channel that has transactions associated with it. You should deactivate it instead.
</div>

<h2>Fee Configuration</h2>
<p>You can configure transaction fees for each payment channel:</p>

<h3>Fee Types</h3>
<ul>
    <li><strong>Fixed Fee:</strong> A fixed amount charged per transaction</li>
    <li><strong>Percentage Fee:</strong> A percentage of the transaction amount</li>
    <li><strong>Fee Cap:</strong> The maximum fee that can be charged for a transaction</li>
</ul>

<p>You can configure who pays the fees:</p>
<ul>
    <li><strong>Customer pays fees:</strong> Fees are added to the customer's total</li>
    <li><strong>Merchant pays fees:</strong> Fees are deducted from the received amount</li>
</ul>

<h2>Using Payment Channels in the API</h2>
<p>When initializing a transaction via the API, you can specify which payment channel to use:</p>

<pre class="line-numbers"><code class="language-json">{
  "amount": 10000,
  "email": "customer@example.com",
  "payment_channel_id": 1,  // Specify the channel ID here
  "description": "Payment for Order #12345",
  "currency": "KSH"
}</code></pre>

<p>If you don't specify a payment channel, the default channel will be used.</p>

<h2>Channel-Specific Features</h2>

<h3>Paystack</h3>
<ul>
    <li><strong>Card Payments:</strong> Accept payments via credit/debit cards</li>
    <li><strong>Bank Transfers:</strong> Receive payments via bank transfers</li>
    <li><strong>USSD:</strong> Accept payments via USSD</li>
    <li><strong>QR Codes:</strong> Generate QR codes for payments</li>
    <li><strong>Recurring Billing:</strong> Set up subscription payments</li>
</ul>

<h3>Flutterwave</h3>
<ul>
    <li><strong>Card Payments:</strong> Accept payments via credit/debit cards</li>
    <li><strong>Mobile Money:</strong> Accept payments via mobile money services</li>
    <li><strong>Bank Transfers:</strong> Receive payments via bank transfers</li>
    <li><strong>USSD:</strong> Accept payments via USSD</li>
    <li><strong>Mpesa:</strong> Accept payments via Mpesa</li>
</ul>

<h3>Stripe</h3>
<ul>
    <li><strong>Card Payments:</strong> Accept payments via credit/debit cards</li>
    <li><strong>Bank Transfers:</strong> Receive payments via bank transfers</li>
    <li><strong>Wallets:</strong> Accept payments via Apple Pay, Google Pay, etc.</li>
    <li><strong>Recurring Billing:</strong> Set up subscription payments</li>
</ul>

<h2>Best Practices</h2>
<ul>
    <li><strong>Use Multiple Channels:</strong> Set up multiple payment channels to provide more options to your customers</li>
    <li><strong>Test Thoroughly:</strong> Always test your payment channels in test mode before going live</li>
    <li><strong>Monitor Transactions:</strong> Regularly check your transaction logs to ensure everything is working correctly</li>
    <li><strong>Keep API Keys Secure:</strong> Never share your API keys or include them in client-side code</li>
    <li><strong>Set Up Webhooks:</strong> Configure webhooks to receive real-time updates about transaction statuses</li>
</ul>

<?php
// Get the content of the output buffer
$content = ob_get_clean();

// Include the layout template
include KIPAY_PATH . '/src/Templates/docs/layout.php';
?>