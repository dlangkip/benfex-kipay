<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo htmlspecialchars($site_name); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <!-- Prism.js for code highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.28.0/themes/prism.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.28.0/plugins/line-numbers/prism-line-numbers.min.css">
    
    <!-- Custom CSS -->
    <link href="/assets/css/docs.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        
        .docs-container {
            display: flex;
            flex: 1;
        }
        
        .docs-sidebar {
            width: 260px;
            background-color: #f8f9fa;
            border-right: 1px solid #e9ecef;
            padding: 2rem 1rem;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .docs-content {
            flex: 1;
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .docs-nav .nav-link {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            color: #495057;
        }
        
        .docs-nav .nav-link:hover {
            background-color: #e9ecef;
        }
        
        .docs-nav .nav-link.active {
            background-color: #3490dc;
            color: white;
        }
        
        .docs-nav .nav-header {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            padding-left: 1rem;
        }
        
        pre[class*="language-"] {
            border-radius: 0.25rem;
            margin: 1.5rem 0;
        }
        
        code[class*="language-"] {
            font-size: 0.85rem;
        }
        
        .api-endpoint {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .api-endpoint .method {
            font-weight: bold;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-right: 0.5rem;
        }
        
        .api-endpoint .method.get {
            background-color: #61affe;
        }
        
        .api-endpoint .method.post {
            background-color: #49cc90;
        }
        
        .api-endpoint .method.put {
            background-color: #fca130;
        }
        
        .api-endpoint .method.delete {
            background-color: #f93e3e;
        }
        
        .api-endpoint .path {
            font-family: monospace;
            font-size: 1rem;
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
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/docs">Documentation</a>
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
    
    <div class="docs-container">
        <!-- Sidebar -->
        <aside class="docs-sidebar d-none d-lg-block">
            <nav class="docs-nav">
                <div class="nav-header">Introduction</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'home' ? 'active' : ''; ?>" href="/docs">
                            Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'getting-started' ? 'active' : ''; ?>" href="/docs/getting-started">
                            Getting Started
                        </a>
                    </li>
                </ul>
                
                <div class="nav-header">Guides</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'payment-channels' ? 'active' : ''; ?>" href="/docs/payment-channels">
                            Payment Channels
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'webhooks' ? 'active' : ''; ?>" href="/docs/webhooks">
                            Webhooks
                        </a>
                    </li>
                </ul>
                
                <div class="nav-header">API Reference</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'api' ? 'active' : ''; ?>" href="/docs/api">
                            API Overview
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Content -->
        <main class="docs-content">
            <?php echo $content ?? ''; ?>
        </main>
    </div>
    
    <!-- Footer -->
    <footer class="bg-light py-4 border-top">
        <div class="container text-center">
            <p class="mb-0">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.
            </p>
            <p class="text-muted mb-0">
                <small>Powered by Kipay Payment Gateway</small>
            </p>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Prism.js for code highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.28.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.28.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.28.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
</body>
</html>