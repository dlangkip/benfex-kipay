<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_name ?? 'Kipay Payment Gateway'); ?> - Secure Payment Gateway</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="/assets/css/styles.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    
    <style>
        .hero-section {
            background-color: #f8fafc;
            padding: 100px 0;
        }
        
        .feature-card {
            border-radius: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            height: 100%;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 40px;
            margin-bottom: 20px;
            color: #3490dc;
        }
        
        .cta-section {
            background-color: #3490dc;
            color: white;
            padding: 80px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="<?php echo htmlspecialchars($logo_url ?? '/assets/images/logo.png'); ?>" alt="<?php echo htmlspecialchars($site_name ?? 'Kipay'); ?>" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="/admin/login">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold mb-4">Accept Payments Anywhere, Anytime</h1>
                    <p class="lead mb-4">Kipay is a secure and flexible payment gateway that allows you to accept payments from your customers through multiple channels.</p>
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="/admin/login" class="btn btn-primary btn-lg px-4">Get Started</a>
                        <a href="#features" class="btn btn-outline-secondary btn-lg px-4">Learn More</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <img src="/assets/images/hero-image.svg" alt="Payment Gateway" class="img-fluid">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Powerful Features</h2>
                <p class="lead">Everything you need to manage payments effectively</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3>Multiple Payment Channels</h3>
                        <p>Accept payments via cards, bank transfers, and other methods through multiple providers.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3>RESTful API</h3>
                        <p>Integrate with any application using our clean and well-documented API.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Comprehensive Dashboard</h3>
                        <p>Track and manage all your transactions with our intuitive dashboard.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h3>Real-time Notifications</h3>
                        <p>Receive instant notifications for payments and other important events.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Secure Transactions</h3>
                        <p>Industry-standard security measures to protect your payments and data.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Customer Management</h3>
                        <p>Keep track of your customers and their payment history.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Pricing Section -->
    <section id="pricing" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Simple, Transparent Pricing</h2>
                <p class="lead">No hidden fees, just pay for what you use</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header py-3">
                            <h4 class="my-0 fw-normal">Basic</h4>
                        </div>
                        <div class="card-body">
                            <h1 class="card-title">1.5% <small class="text-muted fw-light">+ KSH10</small></h1>
                            <p class="lead">per successful transaction</p>
                            <ul class="list-unstyled mt-3 mb-4">
                                <li><i class="fas fa-check text-success me-2"></i> All payment methods</li>
                                <li><i class="fas fa-check text-success me-2"></i> Dashboard access</li>
                                <li><i class="fas fa-check text-success me-2"></i> API integration</li>
                                <li><i class="fas fa-check text-success me-2"></i> Email support</li>
                            </ul>
                            <a href="/admin/login" class="w-100 btn btn-lg btn-outline-primary">Get Started</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm border-primary">
                        <div class="card-header py-3 bg-primary text-white">
                            <h4 class="my-0 fw-normal">Business</h4>
                        </div>
                        <div class="card-body">
                            <h1 class="card-title">1.2% <small class="text-muted fw-light">+ KSH10</small></h1>
                            <p class="lead">per successful transaction</p>
                            <ul class="list-unstyled mt-3 mb-4">
                                <li><i class="fas fa-check text-success me-2"></i> All payment methods</li>
                                <li><i class="fas fa-check text-success me-2"></i> Advanced dashboard</li>
                                <li><i class="fas fa-check text-success me-2"></i> Priority API integration</li>
                                <li><i class="fas fa-check text-success me-2"></i> Priority email & phone support</li>
                                <li><i class="fas fa-check text-success me-2"></i> Transaction reports</li>
                            </ul>
                            <a href="/admin/login" class="w-100 btn btn-lg btn-primary">Get Started</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header py-3">
                            <h4 class="my-0 fw-normal">Enterprise</h4>
                        </div>
                        <div class="card-body">
                            <h1 class="card-title">Custom</h1>
                            <p class="lead">tailored to your needs</p>
                            <ul class="list-unstyled mt-3 mb-4">
                                <li><i class="fas fa-check text-success me-2"></i> All Business features</li>
                                <li><i class="fas fa-check text-success me-2"></i> Custom integration</li>
                                <li><i class="fas fa-check text-success me-2"></i> Dedicated account manager</li>
                                <li><i class="fas fa-check text-success me-2"></i> 24/7 support</li>
                                <li><i class="fas fa-check text-success me-2"></i> Advanced analytics</li>
                            </ul>
                            <a href="#contact" class="w-100 btn btn-lg btn-outline-primary">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to get started?</h2>
            <p class="lead mb-4">Create your account now and start accepting payments in minutes.</p>
            <a href="/admin/login" class="btn btn-light btn-lg px-5">Sign Up for Free</a>
        </div>
    </section>
    
    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="fw-bold mb-4">Get in Touch</h2>
                    <p class="mb-4">Have questions or need help? Contact us and we'll get back to you as soon as possible.</p>
                    
                    <form action="/contact" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                
                <div class="col-md-5 offset-md-1">
                    <div class="mt-5 mt-md-0">
                        <h4 class="mb-4">Contact Information</h4>
                        <p><i class="fas fa-envelope me-2 text-primary"></i> info@kipay.com</p>
                        <p><i class="fas fa-phone me-2 text-primary"></i> +254 700 760 386</p>
                        <p><i class="fas fa-map-marker-alt me-2 text-primary"></i> Nairobi, Kenya</p>
                        
                        <h4 class="mt-5 mb-4">Follow Us</h4>
                        <div class="d-flex">
                            <a href="#" class="me-3 fs-4"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="me-3 fs-4"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="me-3 fs-4"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="me-3 fs-4"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <img src="<?php echo htmlspecialchars($logo_url ?? '/assets/images/logo.png'); ?>" alt="<?php echo htmlspecialchars($site_name ?? 'Kipay'); ?>" height="40" class="mb-3">
                    <p class="mb-3">A secure and flexible payment gateway for businesses of all sizes.</p>
                    <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name ?? 'Kipay Payment Gateway'); ?>. All rights reserved.</p>
                </div>
                
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5>Company</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Careers</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Blog</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Press</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5>Product</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#features" class="text-white text-decoration-none">Features</a></li>
                        <li class="mb-2"><a href="#pricing" class="text-white text-decoration-none">Pricing</a></li>
                        <li class="mb-2"><a href="/docs/api_reference.md" class="text-white text-decoration-none">API</a></li>
                        <li class="mb-2"><a href="/docs" class="text-white text-decoration-none">Documentation</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5>Resources</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="#contact" class="text-white text-decoration-none">Support</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Community</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Terms of Service</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Compliance</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Security</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>