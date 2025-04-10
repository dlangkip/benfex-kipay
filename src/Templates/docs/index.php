<?php
// Start output buffering
ob_start();
?>

<h1>Kipay Payment Gateway Documentation</h1>
<p class="lead">Welcome to the Kipay Payment Gateway documentation. Here you'll find everything you need to integrate and use our payment solution.</p>

<div class="alert alert-primary">
    <i class="fas fa-info-circle"></i> 
    <strong>Getting Started:</strong> 
    New to Kipay? Check out our <a href="/docs/getting-started" class="alert-link">Getting Started Guide</a> to set up your account and make your first transaction.
</div>

<hr class="my-4">

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-bolt text-primary"></i> 
                    Quick Start
                </h5>
                <p class="card-text">Get up and running with Kipay in minutes. Learn how to create payment channels, generate API keys, and process your first transaction.</p>
                <a href="/docs/getting-started" class="btn btn-primary">Get Started →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-code text-primary"></i> 
                    API Reference
                </h5>
                <p class="card-text">Comprehensive documentation of the Kipay API endpoints, request parameters, and response formats.</p>
                <a href="/docs/api" class="btn btn-primary">Explore API →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-credit-card text-primary"></i> 
                    Payment Channels
                </h5>
                <p class="card-text">Learn how to set up and manage different payment channels including Paystack, Flutterwave, and Stripe.</p>
                <a href="/docs/payment-channels" class="btn btn-primary">Learn More →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-bell text-primary"></i> 
                    Webhooks
                </h5>
                <p class="card-text">Understand how to use webhooks to receive real-time notifications about transaction events.</p>
                <a href="/docs/webhooks" class="btn btn-primary">Learn More →</a>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

<h2>Key Features</h2>
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="d-flex">
            <div class="me-3">
                <i class="fas fa-shield-alt text-primary fa-2x"></i>
            </div>
            <div>
                <h5>Secure Payments</h5>
                <p class="mb-0">Industry-standard security measures to protect payment data.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="d-flex">
            <div class="me-3">
                <i class="fas fa-globe text-primary fa-2x"></i>
            </div>
            <div>
                <h5>Multiple Providers</h5>
                <p class="mb-0">Connect with various payment providers from a single interface.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="d-flex">
            <div class="me-3">
                <i class="fas fa-chart-line text-primary fa-2x"></i>
            </div>
            <div>
                <h5>Comprehensive Dashboard</h5>
                <p class="mb-0">Track and analyze all your transactions in real-time.</p>
            </div>
        </div>
    </div>
</div>

<h2>Technical Resources</h2>
<ul>
    <li><a href="/docs/api">API Reference</a> - Complete documentation of API endpoints</li>
    <li><a href="/docs/webhooks">Webhooks Guide</a> - Setting up and using webhooks</li>
    <li><a href="/docs/payment-channels">Payment Channels Configuration</a> - Configure different payment providers</li>
</ul>

<?php
// Get the content of the output buffer
$content = ob_get_clean();

// Include the layout template
include KIPAY_PATH . '/src/Templates/docs/layout.php';
?>