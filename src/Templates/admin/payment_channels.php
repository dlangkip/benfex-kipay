<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Channels - Kipay Admin</title>
    
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
                <li class="active">
                    <a href="/admin/payment-channels"><i class="fas fa-credit-card"></i> Payment Channels</a>
                </li>
                <li>
                    <a href="/admin/customers"><i class="fas fa-users"></i> Customers</a>
                </li>
                <li>
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

            <!-- Payment Channels Content -->
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mt-4">Payment Channels</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChannelModal">
                        <i class="fas fa-plus"></i> Add New Channel
                    </button>
                </div>
                
                <!-- Payment Channels Grid -->
                <div class="row">
                    <?php if (isset($channels) && !empty($channels)) : ?>
                        <?php foreach ($channels as $channel) : ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card shadow payment-channel-card">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold">
                                            <span class="channel-status <?php echo $channel['is_active'] ? 'active' : 'inactive'; ?>"></span>
                                            <?php echo htmlspecialchars($channel['name']); ?>
                                            <?php if ($channel['is_default']) : ?>
                                                <span class="badge bg-primary ms-2">Default</span>
                                            <?php endif; ?>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-2">
                                            <strong>Provider:</strong> 
                                            <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($channel['provider'])); ?></span>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Status:</strong> 
                                            <?php if ($channel['is_active']) : ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else : ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Created:</strong> 
                                            <?php echo date('M d, Y', strtotime($channel['created_at'])); ?>
                                        </p>
                                        
                                        <hr>
                                        
                                        <div class="d-flex justify-content-between">
                                            <a href="/admin/payment-channels/edit/<?php echo $channel['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            
                                            <?php if (!$channel['is_default']) : ?>
                                                <a href="/admin/payment-channels/set-default/<?php echo $channel['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-check"></i> Set as Default
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="/admin/payment-channels/delete/<?php echo $channel['id']; ?>" class="btn btn-sm btn-danger delete-btn" data-confirm="Are you sure you want to delete this payment channel?">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No payment channels found. Click the "Add New Channel" button to create one.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Channel Modal -->
    <div class="modal fade" id="addChannelModal" tabindex="-1" aria-labelledby="addChannelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addChannelModalLabel">Add New Payment Channel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/admin/payment-channels/create" method="post" id="paymentChannelForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Channel Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">
                                Please provide a channel name.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="provider" class="form-label">Payment Provider</label>
                            <select class="form-select" id="provider" name="provider" required>
                                <option value="">Select Provider</option>
                                <?php if (isset($providers) && !empty($providers)) : ?>
                                    <?php foreach ($providers as $provider) : ?>
                                        <option value="<?php echo htmlspecialchars($provider['id']); ?>"><?php echo htmlspecialchars($provider['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">
                                Please select a payment provider.
                            </div>
                        </div>
                        
                        <!-- Paystack Configuration -->
                        <div class="provider-config" id="paystack-config" style="display: none;">
                            <h6 class="mt-4 mb-3">Paystack Configuration</h6>
                            
                            <div class="mb-3">
                                <label for="paystack_public_key" class="form-label">Public Key</label>
                                <input type="text" class="form-control" id="paystack_public_key" name="config[public_key]">
                                <div class="invalid-feedback">
                                    Please provide your Paystack public key.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="paystack_secret_key" class="form-label">Secret Key</label>
                                <input type="text" class="form-control" id="paystack_secret_key" name="config[secret_key]">
                                <div class="invalid-feedback">
                                    Please provide your Paystack secret key.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="paystack_test_mode" name="config[test_mode]">
                                    <label class="form-check-label" for="paystack_test_mode">
                                        Test Mode
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Flutterwave Configuration -->
                        <div class="provider-config" id="flutterwave-config" style="display: none;">
                            <h6 class="mt-4 mb-3">Flutterwave Configuration</h6>
                            
                            <div class="mb-3">
                                <label for="flutterwave_public_key" class="form-label">Public Key</label>
                                <input type="text" class="form-control" id="flutterwave_public_key" name="config[public_key]">
                                <div class="invalid-feedback">
                                    Please provide your Flutterwave public key.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="flutterwave_secret_key" class="form-label">Secret Key</label>
                                <input type="text" class="form-control" id="flutterwave_secret_key" name="config[secret_key]">
                                <div class="invalid-feedback">
                                    Please provide your Flutterwave secret key.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="flutterwave_encryption_key" class="form-label">Encryption Key</label>
                                <input type="text" class="form-control" id="flutterwave_encryption_key" name="config[encryption_key]">
                                <div class="invalid-feedback">
                                    Please provide your Flutterwave encryption key.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="flutterwave_test_mode" name="config[test_mode]">
                                    <label class="form-check-label" for="flutterwave_test_mode">
                                        Test Mode
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Manual Configuration -->
                        <div class="provider-config" id="manual-config" style="display: none;">
                            <h6 class="mt-4 mb-3">Manual Payment Configuration</h6>
                            
                            <div class="mb-3">
                                <label for="manual_payment_instructions" class="form-label">Payment Instructions</label>
                                <textarea class="form-control" id="manual_payment_instructions" name="config[payment_instructions]" rows="3"></textarea>
                                <div class="invalid-feedback">
                                    Please provide payment instructions.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="manual_account_name" class="form-label">Account Name</label>
                                <input type="text" class="form-control" id="manual_account_name" name="config[account_name]">
                            </div>
                            
                            <div class="mb-3">
                                <label for="manual_account_number" class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="manual_account_number" name="config[account_number]">
                            </div>
                            
                            <div class="mb-3">
                                <label for="manual_bank_name" class="form-label">Bank Name</label>
                                <input type="text" class="form-control" id="manual_bank_name" name="config[bank_name]">
                            </div>
                        </div>
                        
                        <!-- Fee Configuration -->
                        <h6 class="mt-4 mb-3">Fee Configuration</h6>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fixed_fee" class="form-label">Fixed Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">NGN</span>
                                    <input type="number" class="form-control" id="fixed_fee" name="fees_config[fixed_fee]" step="0.01" min="0">
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="percentage_fee" class="form-label">Percentage Fee</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="percentage_fee" name="fees_config[percentage_fee]" step="0.01" min="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="fee_cap" class="form-label">Maximum Fee (Cap)</label>
                                <div class="input-group">
                                    <span class="input-group-text">NGN</span>
                                    <input type="number" class="form-control" id="fee_cap" name="fees_config[cap]" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="is_default" name="is_default">
                                <label class="form-check-label" for="is_default">
                                    Set as Default
                                </label>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Payment Channel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/admin.js"></script>
</body>
</html>