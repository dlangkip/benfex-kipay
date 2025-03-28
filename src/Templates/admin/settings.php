<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Kipay Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="/assets/css/admin.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>Kipay Admin</h3>
                <img src="/assets/images/logo.png" alt="Kipay" class="logo">
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="/admin"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li>
                    <a href="/admin/transactions"><i class="fas fa-exchange-alt"></i> Transactions</a>
                </li>
                <li>
                    <a href="/admin/payment-channels"><i class="fas fa-credit-card"></i> Payment Channels</a>
                </li>
                <li>
                    <a href="/admin/customers"><i class="fas fa-users"></i> Customers</a>
                </li>
                <li class="active">
                    <a href="/admin/settings"><i class="fas fa-cog"></i> Settings</a>
                </li>
                <li>
                    <a href="/admin/profile"><i class="fas fa-user"></i> Profile</a>
                </li>
                <li>
                    <a href="/admin/logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <p>Kipay Payment Gateway<br>Version 1.0.0</p>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username'] ?? 'Admin'); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="/admin/profile"><i class="fas fa-user-cog"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="/admin/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Settings Content -->
            <div class="container-fluid">
                <h1 class="mt-4 mb-4">Settings</h1>
                
                <!-- Settings Tabs -->
                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                            <i class="fas fa-sliders-h"></i> General
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab" aria-controls="api" aria-selected="false">
                            <i class="fas fa-key"></i> API Keys
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab" aria-controls="email" aria-selected="false">
                            <i class="fas fa-envelope"></i> Email
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">
                            <i class="fas fa-bell"></i> Notifications
                        </button>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="settingsTabContent">
                    <!-- General Settings -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                            </div>
                            <div class="card-body">
                                <form action="/admin/settings/update" method="post" class="needs-validation" novalidate>
                                    <input type="hidden" name="section" value="general">
                                    
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label">Site Name</label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Kipay Payment Gateway'); ?>" required>
                                        <div class="invalid-feedback">
                                            Please provide a site name.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="site_url" class="form-label">Site URL</label>
                                        <input type="url" class="form-control" id="site_url" name="site_url" value="<?php echo htmlspecialchars($settings['site_url'] ?? 'http://localhost/kipay'); ?>" required>
                                        <div class="invalid-feedback">
                                            Please provide a valid URL.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? 'Benfex'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="company_email" class="form-label">Company Email</label>
                                        <input type="email" class="form-control" id="company_email" name="company_email" value="<?php echo htmlspecialchars($settings['company_email'] ?? 'info@benfex.com'); ?>">
                                        <div class="invalid-feedback">
                                            Please provide a valid email.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="logo_url" class="form-label">Logo URL</label>
                                        <input type="text" class="form-control" id="logo_url" name="logo_url" value="<?php echo htmlspecialchars($settings['logo_url'] ?? '/assets/images/logo.png'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="theme_color" class="form-label">Theme Color</label>
                                        <input type="color" class="form-control form-control-color" id="theme_color" name="theme_color" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#3490dc'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="currency" class="form-label">Default Currency</label>
                                        <select class="form-select" id="currency" name="currency">
                                            <option value="NGN" <?php echo ($settings['currency'] ?? 'NGN') === 'NGN' ? 'selected' : ''; ?>>Nigerian Naira (NGN)</option>
                                            <option value="USD" <?php echo ($settings['currency'] ?? 'NGN') === 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                                            <option value="GHS" <?php echo ($settings['currency'] ?? 'NGN') === 'GHS' ? 'selected' : ''; ?>>Ghanaian Cedi (GHS)</option>
                                            <option value="KES" <?php echo ($settings['currency'] ?? 'NGN') === 'KES' ? 'selected' : ''; ?>>Kenyan Shilling (KES)</option>
                                            <option value="ZAR" <?php echo ($settings['currency'] ?? 'NGN') === 'ZAR' ? 'selected' : ''; ?>>South African Rand (ZAR)</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- API Keys Settings -->
                    <div class="tab-pane fade" id="api" role="tabpanel" aria-labelledby="api-tab">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">API Keys</h6>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateKeyModal">
                                    <i class="fas fa-plus"></i> Generate New Key
                                </button>
                            </div>
                            <div class="card-body">
                                <?php if (isset($apiKeys) && !empty($apiKeys)) : ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Description</th>
                                                    <th>API Key</th>
                                                    <th>Created</th>
                                                    <th>Last Used</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($apiKeys as $key) : ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($key['description'] ?? 'API Key'); ?></td>
                                                        <td>
                                                            <div class="api-key-container">
                                                                <span class="api-key-value"><?php echo htmlspecialchars($key['api_key']); ?></span>
                                                                <i class="fas fa-copy api-key-copy" title="Copy to clipboard"></i>
                                                            </div>
                                                        </td>
                                                        <td><?php echo date('M d, Y', strtotime($key['created_at'])); ?></td>
                                                        <td><?php echo $key['last_used_at'] ? date('M d, Y H:i', strtotime($key['last_used_at'])) : 'Never'; ?></td>
                                                        <td>
                                                            <a href="/admin/settings/revoke-key/<?php echo $key['id']; ?>" class="btn btn-sm btn-danger delete-btn" data-confirm="Are you sure you want to revoke this API key? This action cannot be undone.">
                                                                <i class="fas fa-trash"></i> Revoke
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else : ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No API keys found. Click the "Generate New Key" button to create one.
                                    </div>
                                <?php endif; ?>
                                
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> API keys provide full access to your account. Keep them secure and never share them publicly.
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Webhook Settings</h6>
                            </div>
                            <div class="card-body">
                                <p>Use these webhook URLs in your payment provider's dashboard:</p>
                                
                                <div class="mb-3">
                                    <label class="form-label">Paystack Webhook URL</label>
                                    <div class="api-key-container">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['site_url'] ?? 'http://localhost/kipay'); ?>/webhook/paystack" readonly>
                                        <i class="fas fa-copy api-key-copy" title="Copy to clipboard"></i>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Flutterwave Webhook URL</label>
                                    <div class="api-key-container">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['site_url'] ?? 'http://localhost/kipay'); ?>/webhook/flutterwave" readonly>
                                        <i class="fas fa-copy api-key-copy" title="Copy to clipboard"></i>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Stripe Webhook URL</label>
                                    <div class="api-key-container">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['site_url'] ?? 'http://localhost/kipay'); ?>/webhook/stripe" readonly>
                                        <i class="fas fa-copy api-key-copy" title="Copy to clipboard"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Settings -->
                    <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Email Settings</h6>
                            </div>
                            <div class="card-body">
                                <form action="/admin/settings/update" method="post" class="needs-validation" novalidate>
                                    <input type="hidden" name="section" value="email">
                                    
                                    <div class="mb-3">
                                        <label for="mail_driver" class="form-label">Mail Driver</label>
                                        <select class="form-select" id="mail_driver" name="mail_driver">
                                            <option value="smtp" <?php echo ($settings['mail_driver'] ?? 'smtp') === 'smtp' ? 'selected' : ''; ?>>SMTP</option>
                                            <option value="sendmail" <?php echo ($settings['mail_driver'] ?? 'smtp') === 'sendmail' ? 'selected' : ''; ?>>Sendmail</option>
                                            <option value="mailgun" <?php echo ($settings['mail_driver'] ?? 'smtp') === 'mailgun' ? 'selected' : ''; ?>>Mailgun</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mail_host" class="form-label">SMTP Host</label>
                                        <input type="text" class="form-control" id="mail_host" name="mail_host" value="<?php echo htmlspecialchars($settings['mail_host'] ?? 'smtp.example.com'); ?>">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="mail_port" class="form-label">SMTP Port</label>
                                            <input type="number" class="form-control" id="mail_port" name="mail_port" value="<?php echo htmlspecialchars($settings['mail_port'] ?? '587'); ?>">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="mail_encryption" class="form-label">Encryption</label>
                                            <select class="form-select" id="mail_encryption" name="mail_encryption">
                                                <option value="tls" <?php echo ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                                <option value="ssl" <?php echo ($settings['mail_encryption'] ?? 'tls') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                                <option value="none" <?php echo ($settings['mail_encryption'] ?? 'tls') === 'none' ? 'selected' : ''; ?>>None</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mail_username" class="form-label">SMTP Username</label>
                                        <input type="text" class="form-control" id="mail_username" name="mail_username" value="<?php echo htmlspecialchars($settings['mail_username'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mail_password" class="form-label">SMTP Password</label>
                                        <input type="password" class="form-control" id="mail_password" name="mail_password" value="<?php echo htmlspecialchars($settings['mail_password'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mail_from_address" class="form-label">From Email Address</label>
                                        <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" value="<?php echo htmlspecialchars($settings['mail_from_address'] ?? 'no-reply@kipay.com'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mail_from_name" class="form-label">From Name</label>
                                        <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" value="<?php echo htmlspecialchars($settings['mail_from_name'] ?? 'Kipay Payment Gateway'); ?>">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-primary ms-2" id="testEmailBtn">
                                        <i class="fas fa-paper-plane"></i> Send Test Email
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notification Settings -->
                    <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Notification Settings</h6>
                            </div>
                            <div class="card-body">
                                <form action="/admin/settings/update" method="post">
                                    <input type="hidden" name="section" value="notifications">
                                    
                                    <h6 class="mb-3">Email Notifications</h6>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_admin_new_transaction" name="notify_admin_new_transaction" value="1" <?php echo ($settings['notify_admin_new_transaction'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_admin_new_transaction">
                                                Notify admin on new transaction
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_admin_failed_transaction" name="notify_admin_failed_transaction" value="1" <?php echo ($settings['notify_admin_failed_transaction'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_admin_failed_transaction">
                                                Notify admin on failed transaction
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_customer_transaction_receipt" name="notify_customer_transaction_receipt" value="1" <?php echo ($settings['notify_customer_transaction_receipt'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_customer_transaction_receipt">
                                                Send receipt to customer on successful payment
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_customer_transaction_failed" name="notify_customer_transaction_failed" value="1" <?php echo ($settings['notify_customer_transaction_failed'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_customer_transaction_failed">
                                                Notify customer on failed payment
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <h6 class="mb-3 mt-4">Admin Notification Recipients</h6>
                                    
                                    <div class="mb-3">
                                        <label for="admin_notification_emails" class="form-label">Email Addresses (comma separated)</label>
                                        <input type="text" class="form-control" id="admin_notification_emails" name="admin_notification_emails" value="<?php echo htmlspecialchars($settings['admin_notification_emails'] ?? ''); ?>" placeholder="admin@example.com, support@example.com">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate API Key Modal -->
    <div class="modal fade" id="generateKeyModal" tabindex="-1" aria-labelledby="generateKeyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="generateKeyModalLabel">Generate API Key</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/admin/settings/generate-key" method="post" id="apiKeyForm">
                        <div class="mb-3">
                            <label for="key_description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="key_description" name="description" placeholder="e.g., Website Integration" required>
                            <div class="form-text">This helps you identify what the key is used for.</div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> The API Secret will only be shown once. Make sure to copy it immediately.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="apiKeyForm" class="btn btn-primary">Generate Key</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Email Modal -->
    <div class="modal fade" id="testEmailModal" tabindex="-1" aria-labelledby="testEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="testEmailModalLabel">Send Test Email</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/admin/settings/send-test-email" method="post" id="testEmailForm">
                        <div class="mb-3">
                            <label for="test_email" class="form-label">Recipient Email</label>
                            <input type="email" class="form-control" id="test_email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="testEmailForm" class="btn btn-primary">Send Test Email</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/admin.js"></script>
    
    <script>
        // Test email button event
        document.getElementById('testEmailBtn').addEventListener('click', function() {
            const testEmailModal = new bootstrap.Modal(document.getElementById('testEmailModal'));
            testEmailModal.show();
        });
    </script>
</body>
</html>