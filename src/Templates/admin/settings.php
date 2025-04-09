<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Kipay Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="/assets/css/admin.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Navigation -->
    <?php include KIPAY_PATH . '/src/Templates/admin/partials/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include KIPAY_PATH . '/src/Templates/admin/partials/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
                </div>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['api_keys'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <h5><i class="fas fa-key"></i> New API Credentials Generated</h5>
                        <p class="mb-1"><strong>API Key:</strong> <?php echo htmlspecialchars($_SESSION['api_keys']['api_key']); ?></p>
                        <p><strong>API Secret:</strong> <?php echo htmlspecialchars($_SESSION['api_keys']['api_secret']); ?></p>
                        <p class="text-danger mb-0"><strong>Important:</strong> Please copy these credentials now. The API Secret will not be shown again.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php unset($_SESSION['api_keys']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Settings Tabs -->
                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                            <i class="fas fa-cog"></i> General
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab" aria-controls="api" aria-selected="false">
                            <i class="fas fa-key"></i> API Keys
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">
                            <i class="fas fa-credit-card"></i> Payment
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="notification-tab" data-bs-toggle="tab" data-bs-target="#notification" type="button" role="tab" aria-controls="notification" aria-selected="false">
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
                                <form action="/admin/settings/update" method="post" id="generalSettingsForm">
                                    <input type="hidden" name="form_type" value="general_settings">
                                    
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label">Site Name</label>
                                        <input type="text" class="form-control" id="site_name" name="setting_site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Kipay Payment Gateway'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="site_url" class="form-label">Site URL</label>
                                        <input type="url" class="form-control" id="site_url" name="setting_site_url" value="<?php echo htmlspecialchars($settings['site_url'] ?? 'https://kipay.benfex.net'); ?>">
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="company_name" class="form-label">Company Name</label>
                                            <input type="text" class="form-control" id="company_name" name="setting_company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? 'Benfex'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="company_email" class="form-label">Company Email</label>
                                            <input type="email" class="form-control" id="company_email" name="setting_company_email" value="<?php echo htmlspecialchars($settings['company_email'] ?? 'info@benfex.com'); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="logo_url" class="form-label">Logo URL</label>
                                        <input type="text" class="form-control" id="logo_url" name="setting_logo_url" value="<?php echo htmlspecialchars($settings['logo_url'] ?? '/assets/images/logo.png'); ?>">
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="theme_color" class="form-label">Theme Color</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" id="theme_color" name="setting_theme_color" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#3490dc'); ?>">
                                                <input type="text" class="form-control" id="theme_color_hex" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#3490dc'); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="currency" class="form-label">Default Currency</label>
                                            <select class="form-select" id="currency" name="setting_currency">
                                                <option value="KSH" <?php echo ($settings['currency'] ?? 'KSH') === 'KSH' ? 'selected' : ''; ?>>Kenyan Shilling (KSH)</option>
                                                <option value="NGN" <?php echo ($settings['currency'] ?? 'KSH') === 'NGN' ? 'selected' : ''; ?>>Nigerian Naira (NGN)</option>
                                                <option value="USD" <?php echo ($settings['currency'] ?? 'KSH') === 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                                                <option value="GHS" <?php echo ($settings['currency'] ?? 'KSH') === 'GHS' ? 'selected' : ''; ?>>Ghanaian Cedi (GHS)</option>
                                                <option value="ZAR" <?php echo ($settings['currency'] ?? 'KSH') === 'ZAR' ? 'selected' : ''; ?>>South African Rand (ZAR)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select class="form-select" id="timezone" name="setting_timezone">
                                            <option value="Africa/Nairobi" <?php echo ($settings['timezone'] ?? 'Africa/Nairobi') === 'Africa/Nairobi' ? 'selected' : ''; ?>>Africa/Nairobi (EAT)</option>
                                            <option value="Africa/Lagos" <?php echo ($settings['timezone'] ?? 'Africa/Nairobi') === 'Africa/Lagos' ? 'selected' : ''; ?>>Africa/Lagos (WAT)</option>
                                            <option value="Europe/London" <?php echo ($settings['timezone'] ?? 'Africa/Nairobi') === 'Europe/London' ? 'selected' : ''; ?>>Europe/London (GMT)</option>
                                            <option value="America/New_York" <?php echo ($settings['timezone'] ?? 'Africa/Nairobi') === 'America/New_York' ? 'selected' : ''; ?>>America/New_York (EST)</option>
                                            <option value="UTC" <?php echo ($settings['timezone'] ?? 'Africa/Nairobi') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- API Keys -->
                    <div class="tab-pane fade" id="api" role="tabpanel" aria-labelledby="api-tab">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">API Keys</h6>
                                <form action="/admin/settings/update" method="post">
                                    <input type="hidden" name="form_type" value="api_keys">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sync-alt"></i> Regenerate Keys
                                    </button>
                                </form>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> Your API keys provide full access to your account. Never share them publicly.
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">API Key</label>
                                    <div class="api-key-container input-group">
                                        <input type="text" class="form-control api-key-value" value="<?php echo htmlspecialchars($api_key ?? 'No API key available'); ?>" readonly>
                                        <button class="btn btn-outline-secondary api-key-copy" type="button" title="Copy to clipboard">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Use this key to authenticate API requests.</div>
                                </div>
                                
                                <h6 class="font-weight-bold mt-4">API Documentation</h6>
                                <p>Learn how to integrate with our API by checking the documentation:</p>
                                <a href="/docs/api" class="btn btn-outline-primary" target="_blank">
                                    <i class="fas fa-book"></i> View API Documentation
                                </a>
                                
                                <h6 class="font-weight-bold mt-4">API Endpoints</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Endpoint</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><code>/api/transactions/initialize</code></td>
                                                <td>Initialize a new transaction</td>
                                            </tr>
                                            <tr>
                                                <td><code>/api/transactions/verify/{reference}</code></td>
                                                <td>Verify a transaction status</td>
                                            </tr>
                                            <tr>
                                                <td><code>/api/transactions/list</code></td>
                                                <td>List all transactions</td>
                                            </tr>
                                            <tr>
                                                <td><code>/api/payment-channels/list</code></td>
                                                <td>List all payment channels</td>
                                            </tr>
                                            <tr>
                                                <td><code>/api/customers/list</code></td>
                                                <td>List all customers</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Webhook Settings</h6>
                            </div>
                            <div class="card-body">
                                <p>Configure your payment provider webhooks to point to these URLs:</p>
                                
                                <div class="mb-3">
                                    <label class="form-label">Paystack Webhook URL</label>
                                    <div class="api-key-container input-group">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['site_url'] ?? 'https://kipay.benfex.net'); ?>/webhook/paystack" readonly>
                                        <button class="btn btn-outline-secondary api-key-copy" type="button" title="Copy to clipboard">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Flutterwave Webhook URL</label>
                                    <div class="api-key-container input-group">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['site_url'] ?? 'https://kipay.benfex.net'); ?>/webhook/flutterwave" readonly>
                                        <button class="btn btn-outline-secondary api-key-copy" type="button" title="Copy to clipboard">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Stripe Webhook URL</label>
                                    <div class="api-key-container input-group">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['site_url'] ?? 'https://kipay.benfex.net'); ?>/webhook/stripe" readonly>
                                        <button class="btn btn-outline-secondary api-key-copy" type="button" title="Copy to clipboard">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Settings -->
                    <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Payment Settings</h6>
                            </div>
                            <div class="card-body">
                                <form action="/admin/settings/update" method="post">
                                    <input type="hidden" name="form_type" value="payment_settings">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="global_transaction_fee" class="form-label">Global Transaction Fee (%)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="global_transaction_fee" name="setting_global_transaction_fee" step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($settings['global_transaction_fee'] ?? '0'); ?>">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">Applied to all transactions unless overridden by payment channel.</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="fixed_transaction_fee" class="form-label">Fixed Transaction Fee</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><?php echo htmlspecialchars($settings['currency'] ?? 'KSH'); ?></span>
                                                <input type="number" class="form-control" id="fixed_transaction_fee" name="setting_fixed_transaction_fee" step="0.01" min="0" value="<?php echo htmlspecialchars($settings['fixed_transaction_fee'] ?? '0'); ?>">
                                            </div>
                                            <div class="form-text">Applied in addition to percentage fee.</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="fee_cap" class="form-label">Fee Cap (Maximum Fee)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><?php echo htmlspecialchars($settings['currency'] ?? 'KSH'); ?></span>
                                            <input type="number" class="form-control" id="fee_cap" name="setting_fee_cap" step="0.01" min="0" value="<?php echo htmlspecialchars($settings['fee_cap'] ?? '0'); ?>">
                                        </div>
                                        <div class="form-text">Set to 0 for no cap.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="show_fees_to_customer" name="setting_show_fees_to_customer" value="1" <?php echo ($settings['show_fees_to_customer'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="show_fees_to_customer">
                                                Show fees to customer during checkout
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="customer_pays_fees" name="setting_customer_pays_fees" value="1" <?php echo ($settings['customer_pays_fees'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="customer_pays_fees">
                                                Customer pays transaction fees
                                            </label>
                                        </div>
                                        <div class="form-text">If enabled, fees will be added to the customer's total. If disabled, fees will be deducted from the received amount.</div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="mb-3">
                                        <label for="success_redirect_url" class="form-label">Success Redirect URL</label>
                                        <input type="url" class="form-control" id="success_redirect_url" name="setting_success_redirect_url" value="<?php echo htmlspecialchars($settings['success_redirect_url'] ?? '/payment/success'); ?>">
                                        <div class="form-text">Default URL to redirect after successful payment.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="failure_redirect_url" class="form-label">Failure Redirect URL</label>
                                        <input type="url" class="form-control" id="failure_redirect_url" name="setting_failure_redirect_url" value="<?php echo htmlspecialchars($settings['failure_redirect_url'] ?? '/payment/failure'); ?>">
                                        <div class="form-text">Default URL to redirect after failed payment.</div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Payment Page Customization</h6>
                            </div>
                            <div class="card-body">
                                <form action="/admin/settings/update" method="post">
                                    <input type="hidden" name="form_type" value="payment_page_settings">
                                    
                                    <div class="mb-3">
                                        <label for="checkout_page_title" class="form-label">Checkout Page Title</label>
                                        <input type="text" class="form-control" id="checkout_page_title" name="setting_checkout_page_title" value="<?php echo htmlspecialchars($settings['checkout_page_title'] ?? 'Secure Checkout'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="checkout_description" class="form-label">Checkout Page Description</label>
                                        <textarea class="form-control" id="checkout_description" name="setting_checkout_description" rows="2"><?php echo htmlspecialchars($settings['checkout_description'] ?? 'Complete your payment securely.'); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="checkout_logo" class="form-label">Checkout Page Logo URL</label>
                                        <input type="url" class="form-control" id="checkout_logo" name="setting_checkout_logo" value="<?php echo htmlspecialchars($settings['checkout_logo'] ?? $settings['logo_url'] ?? '/assets/images/logo.png'); ?>">
                                        <div class="form-text">Leave blank to use the default site logo.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="show_payment_methods_icons" name="setting_show_payment_methods_icons" value="1" <?php echo ($settings['show_payment_methods_icons'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="show_payment_methods_icons">
                                                Show payment method icons
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notification Settings -->
                    <div class="tab-pane fade" id="notification" role="tabpanel" aria-labelledby="notification-tab">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Email Notification Settings</h6>
                            </div>
                            <div class="card-body">
                                <form action="/admin/settings/update" method="post">
                                    <input type="hidden" name="form_type" value="notification_settings">
                                    
                                    <h6 class="mb-3 font-weight-bold">Administrator Notifications</h6>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_admin_new_transaction" name="setting_notify_admin_new_transaction" value="1" <?php echo ($settings['notify_admin_new_transaction'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_admin_new_transaction">
                                                New transaction notifications
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_admin_successful_transaction" name="setting_notify_admin_successful_transaction" value="1" <?php echo ($settings['notify_admin_successful_transaction'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_admin_successful_transaction">
                                                Successful transaction notifications
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_admin_failed_transaction" name="setting_notify_admin_failed_transaction" value="1" <?php echo ($settings['notify_admin_failed_transaction'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_admin_failed_transaction">
                                                Failed transaction notifications
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="admin_notification_emails" class="form-label">Admin Notification Emails</label>
                                        <input type="text" class="form-control" id="admin_notification_emails" name="setting_admin_notification_emails" value="<?php echo htmlspecialchars($settings['admin_notification_emails'] ?? ''); ?>" placeholder="email@example.com, another@example.com">
                                        <div class="form-text">Comma-separated list of email addresses to receive admin notifications.</div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <h6 class="mb-3 font-weight-bold">Customer Notifications</h6>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_customer_transaction_receipt" name="setting_notify_customer_transaction_receipt" value="1" <?php echo ($settings['notify_customer_transaction_receipt'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_customer_transaction_receipt">
                                                Send transaction receipts to customers
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_customer_payment_failure" name="setting_notify_customer_payment_failure" value="1" <?php echo ($settings['notify_customer_payment_failure'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_customer_payment_failure">
                                                Notify customers of payment failures
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <h6 class="mb-3 font-weight-bold">Email Settings</h6>
                                    
                                    <div class="mb-3">
                                        <label for="mail_from_name" class="form-label">Sender Name</label>
                                        <input type="text" class="form-control" id="mail_from_name" name="setting_mail_from_name" value="<?php echo htmlspecialchars($settings['mail_from_name'] ?? 'Kipay Payment Gateway'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mail_from_email" class="form-label">Sender Email</label>
                                        <input type="email" class="form-control" id="mail_from_email" name="setting_mail_from_email" value="<?php echo htmlspecialchars($settings['mail_from_email'] ?? 'noreply@kipay.com'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                                            <i class="fas fa-paper-plane"></i> Send Test Email
                                        </a>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
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
                        <div class="mb-3">
                            <label for="test_subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="test_subject" name="subject" value="Kipay Test Email" required>
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
    
    <!-- Footer -->
    <?php include KIPAY_PATH . '/src/Templates/admin/partials/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/admin.js"></script>
    
    <script>
        $(document).ready(function() {
            // Update hex input when color picker changes
            $('#theme_color').on('input', function() {
                $('#theme_color_hex').val($(this).val());
            });
            
            // Copy to clipboard functionality
            $('.api-key-copy').click(function() {
                const inputElement = $(this).closest('.input-group').find('input');
                inputElement.select();
                document.execCommand('copy');
                
                // Show success feedback
                const originalHtml = $(this).html();
                $(this).html('<i class="fas fa-check"></i>');
                $(this).addClass('btn-success').removeClass('btn-outline-secondary');
                
                setTimeout(() => {
                    $(this).html(originalHtml);
                    $(this).addClass('btn-outline-secondary').removeClass('btn-success');
                }, 2000);
            });
            
            // Tab persistence across page reloads
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                localStorage.setItem('activeSettingsTab', $(e.target).attr('href'));
            });
            
            // Check if there's a saved tab and activate it
            const activeTab = localStorage.getItem('activeSettingsTab');
            if (activeTab) {
                const tabPane = document.querySelector(activeTab);
                if (tabPane) {
                    const tab = new bootstrap.Tab(document.querySelector(`[data-bs-target="${activeTab}"]`));
                    tab.show();
                }
            }
        });
    </script>
</body>
</html>