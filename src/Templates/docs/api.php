<?php
// Start output buffering
ob_start();
?>

<h1>API Reference</h1>
<p class="lead">The Kipay Payment Gateway provides a comprehensive API for integrating payment processing into your applications.</p>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> 
    <strong>Authentication:</strong> 
    All API requests require an API key for authentication. You can get your API key from the <a href="/admin/settings" class="alert-link">Dashboard Settings</a>.
</div>

<h2>Authentication</h2>
<p>All API requests must include your API key in the <code>X-API-Key</code> header:</p>

<pre class="line-numbers"><code class="language-bash">curl -X POST \
  https://kipay.benfex.net/api/transactions/initialize \
  -H 'Content-Type: application/json' \
  -H 'X-API-Key: your_api_key_here' \
  -d '{
    "amount": 10000,
    "email": "customer@example.com",
    "payment_channel_id": 1
  }'</code></pre>

<h2>Endpoints</h2>

<h3>Transactions</h3>

<div class="api-endpoint">
    <h4>
        <span class="method post">POST</span>
        <span class="path">/api/transactions/initialize</span>
    </h4>
    <p>Initialize a new transaction and get a payment URL</p>
    
    <h5>Request Body</h5>
    <pre class="line-numbers"><code class="language-json">{
  "amount": 10000,
  "email": "customer@example.com",
  "payment_channel_id": 1,
  "description": "Payment for Order #12345",
  "currency": "KSH",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+2348012345678"
}</code></pre>

    <h5>Response</h5>
    <pre class="line-numbers"><code class="language-json">{
  "status": "success",
  "message": "Success",
  "transaction": {
    "id": 1,
    "reference": "KIPAY12345678",
    "amount": 10000,
    "status": "pending",
    "created_at": "2025-04-10T12:34:56.000Z"
  },
  "authorization_url": "https://kipay.benfex.net/payment/checkout/KIPAY12345678",
  "access_code": "ACCESS_CODE",
  "reference": "KIPAY12345678"
}</code></pre>
</div>

<div class="api-endpoint">
    <h4>
        <span class="method get">GET</span>
        <span class="path">/api/transactions/verify/{reference}</span>
    </h4>
    <p>Verify a transaction's status</p>
    
    <h5>Parameters</h5>
    <ul>
        <li><code>reference</code> - Transaction reference</li>
    </ul>
    
    <h5>Response</h5>
    <pre class="line-numbers"><code class="language-json">{
  "status": "success",
  "transaction": {
    "id": 1,
    "reference": "KIPAY12345678",
    "amount": 10000,
    "status": "completed",
    "payment_method": "card",
    "created_at": "2025-04-10T12:34:56.000Z"
  },
  "status": "completed"
}</code></pre>
</div>

<div class="api-endpoint">
    <h4>
        <span class="method get">GET</span>
        <span class="path">/api/transactions/list</span>
    </h4>
    <p>List all transactions</p>
    
    <h5>Query Parameters</h5>
    <ul>
        <li><code>page</code> - Page number (default: 1)</li>
        <li><code>limit</code> - Items per page (default: 20)</li>
        <li><code>status</code> - Filter by status (pending, completed, failed)</li>
        <li><code>date_from</code> - Filter by start date (YYYY-MM-DD)</li>
        <li><code>date_to</code> - Filter by end date (YYYY-MM-DD)</li>
    </ul>
    
    <h5>Response</h5>
    <pre class="line-numbers"><code class="language-json">{
  "status": "success",
  "data": [
    {
      "id": 1,
      "reference": "KIPAY12345678",
      "amount": 10000,
      "status": "completed",
      "created_at": "2025-04-10T12:34:56.000Z"
    }
  ],
  "total": 1,
  "page": 1,
  "limit": 20,
  "pages": 1
}</code></pre>
</div>

<h3>Payment Channels</h3>

<div class="api-endpoint">
    <h4>
        <span class="method post">POST</span>
        <span class="path">/api/payment-channels/create</span>
    </h4>
    <p>Create a new payment channel</p>
    
    <h5>Request Body</h5>
    <pre class="line-numbers"><code class="language-json">{
  "name": "My Paystack Channel",
  "provider": "paystack",
  "config": {
    "public_key": "pk_test_xxxxxxxxxxxxxxxxxxxxxxxx",
    "secret_key": "sk_test_xxxxxxxxxxxxxxxxxxxxxxxx"
  }
}</code></pre>

    <h5>Response</h5>
    <pre class="line-numbers"><code class="language-json">{
  "status": "success",
  "message": "Payment channel created successfully",
  "channel": {
    "id": 1,
    "name": "My Paystack Channel",
    "provider": "paystack",
    "is_active": true,
    "is_default": true,
    "created_at": "2025-04-10T12:34:56.000Z"
  }
}</code></pre>
</div>

<div class="api-endpoint">
    <h4>
        <span class="method get">GET</span>
        <span class="path">/api/payment-channels/list</span>
    </h4>
    <p>List all payment channels</p>
    
    <h5>Query Parameters</h5>
    <ul>
        <li><code>active_only</code> - Filter to active channels only (default: false)</li>
    </ul>
    
    <h5>Response</h5>
    <pre class="line-numbers"><code class="language-json">{
  "status": "success",
  "channels": [
    {
      "id": 1,
      "name": "My Paystack Channel",
      "provider": "paystack",
      "is_active": true,
      "is_default": true,
      "created_at": "2025-04-10T12:34:56.000Z"
    }
  ]
}</code></pre>
</div>

<h3>Customers</h3>

<div class="api-endpoint">
    <h4>
        <span class="method post">POST</span>
        <span class="path">/api/customers/create</span>
    </h4>
    <p>Create a new customer</p>
    
    <h5>Request Body</h5>
    <pre class="line-numbers"><code class="language-json">{
  "email": "customer@example.com",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+2348012345678"
}</code></pre>

    <h5>Response</h5>
    <pre class="line-numbers"><code class="language-json">{
  "status": "success",
  "message": "Customer created successfully",
  "customer": {
    "id": 1,
    "email": "customer@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+2348012345678",
    "created_at": "2025-04-10T12:34:56.000Z"
  }
}</code></pre>
</div>

<div class="api-endpoint">
    <h4>
        <span class="method get">GET</span>
        <span class="path">/api/customers/list</span>
    </h4>
    <p>List all customers</p>
    
    <h5>Query Parameters</h5>
    <ul>
        <li><code>page</code> - Page number (default: 1)</li>
        <li><code>limit</code> - Items per page (default: 20)</li>
        <li><code>search</code> - Search by name, email, or phone</li>
    </ul>
    
    <h5>Response</h5>
    <pre class="line-numbers"><code class="language-json">{
  "status": "success",
  "data": [
    {
      "id": 1,
      "email": "customer@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "phone": "+2348012345678",
      "created_at": "2025-04-10T12:34:56.000Z"
    }
  ],
  "total": 1,
  "page": 1,
  "limit": 20,
  "pages": 1
}</code></pre>
</div>

<h2>Error Handling</h2>
<p>The API uses standard HTTP status codes to indicate the success or failure of a request. In case of an error, the response will include an error message:</p>

<pre class="line-numbers"><code class="language-json">{
  "status": "error",
  "message": "Invalid API key"
}</code></pre>

<h2>Rate Limiting</h2>
<p>API requests are rate limited to 100 requests per minute per API key. If you exceed this limit, you will receive a 429 Too Many Requests response.</p>

<?php
// Get the content of the output buffer
$content = ob_get_clean();

// Include the layout template
include KIPAY_PATH . '/src/Templates/docs/layout.php';
?>