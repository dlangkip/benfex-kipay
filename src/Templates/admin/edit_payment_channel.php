<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Payment Channel - Kipay Admin</title>
    
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
                    <h1 class="h2">Edit Payment Channel</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/admin/payment-channels" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Payment Channels
                        </a>
                    </div>
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
                
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Payment Channel Details</h6>
                    </div>
                    <div class="card-body">
                        <form action="/admin/payment-channels" method="post" id="paymentChannelForm" class="needs-validation" novalidate>
                            <input type="hidden" name="id" value="<?php echo $channel['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Channel Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($channel['name']); ?>" required>
                                <div class="invalid-feedback">
                                    Please provide a channel name.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="provider" class="form-label">Payment Provider</label>
                                <select class="form-select" id="provider" name="provider" required>
                                    <option value="">Select Provider</option>
                                    <?php if (isset($providers) && !empty($providers)): ?>
                                        <?php foreach ($providers as $provider): ?>
                                            <option value="<?php echo htmlspecialchars($provider['id']); ?>" <?php echo $channel['provider'] === $provider['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($provider['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a payment provider.
                                </div>
                            </div>
                            
                            <?php 
                            // Parse channel configuration
                            $config = isset($channel['config']) ? (is_array($channel['config']) ? $channel['config'] : json_decode($channel['config'], true)) : [];
                            $feesConfig = isset($channel['fees_config']) ? (is_array($channel['fees_config']) ? $channel['fees_config'] : json_decode($channel['fees_config'], true)) : [];
                            ?>
                            
                            <!-- Paystack Configuration -->
                            <div class="provider-config" id="paystack-config" style="display: <?php echo $channel['provider'] === 'paystack' ? 'block' : 'none'; ?>;">
                                <h6 class="mt-4 mb-3">Paystack Configuration</h6>
                                
                                <div class="mb-3">
                                    <label for="paystack_public_key" class="form-label">Public Key</label>
                                    <input type="text" class="form-control" id="paystack_public_key" name="public_key" value="<?php echo htmlspecialchars($config['public_key'] ?? ''); ?>">
                                    <div class="invalid-feedback">
                                        Please provide your Paystack public key.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="paystack_secret_key" class="form-label">Secret Key</label>
                                    <input type="text" class="form-control" id="paystack_secret_key" name="secret_key" value="<?php echo htmlspecialchars($config['secret_key'] ?? ''); ?>">
                                    <div class="invalid-feedback">
                                        Please provide your Paystack secret key.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="paystack_test_mode" name="test_mode" <?php echo isset($config['test_mode']) && $config['test_mode'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="paystack_test_mode">
                                            Test Mode
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Flutterwave Configuration -->
                            <div class="provider-config" id="flutterwave-config" style="display: <?php echo $channel['provider'] === 'flutterwave' ? 'block' : 'none'; ?>;">
                                <h6 class="mt-4 mb-3">Flutterwave Configuration</h6>
                                
                                <div class="mb-3">
                                    <label for="flutterwave_public_key" class="form-label">Public Key</label>
                                    <input type="text" class="form-control" id="flutterwave_public_key" name="public_key" value="<?php echo htmlspecialchars($config['public_key'] ?? ''); ?>">
                                   <div class="invalid-feedback">
                                       Please provide your Flutterwave public key.
                                   </div>
                               </div>
                               
                               <div class="mb-3">
                                   <label for="flutterwave_secret_key" class="form-label">Secret Key</label>
                                   <input type="text" class="form-control" id="flutterwave_secret_key" name="secret_key" value="<?php echo htmlspecialchars($config['secret_key'] ?? ''); ?>">
                                   <div class="invalid-feedback">
                                       Please provide your Flutterwave secret key.
                                   </div>
                               </div>
                               
                               <div class="mb-3">
                                   <label for="flutterwave_encryption_key" class="form-label">Encryption Key</label>
                                   <input type="text" class="form-control" id="flutterwave_encryption_key" name="encryption_key" value="<?php echo htmlspecialchars($config['encryption_key'] ?? ''); ?>">
                                   <div class="invalid-feedback">
                                       Please provide your Flutterwave encryption key.
                                   </div>
                               </div>
                               
                               <div class="mb-3">
                                   <div class="form-check">
                                       <input class="form-check-input" type="checkbox" value="1" id="flutterwave_test_mode" name="test_mode" <?php echo isset($config['test_mode']) && $config['test_mode'] ? 'checked' : ''; ?>>
                                       <label class="form-check-label" for="flutterwave_test_mode">
                                           Test Mode
                                       </label>
                                   </div>
                               </div>
                           </div>
                           
                           <!-- Manual Configuration -->
                           <div class="provider-config" id="manual-config" style="display: <?php echo $channel['provider'] === 'manual' ? 'block' : 'none'; ?>;">
                               <h6 class="mt-4 mb-3">Manual Payment Configuration</h6>
                               
                               <div class="mb-3">
                                   <label for="manual_payment_instructions" class="form-label">Payment Instructions</label>
                                   <textarea class="form-control" id="manual_payment_instructions" name="payment_instructions" rows="3"><?php echo htmlspecialchars($config['payment_instructions'] ?? ''); ?></textarea>
                                   <div class="invalid-feedback">
                                       Please provide payment instructions.
                                   </div>
                               </div>
                               
                               <div class="mb-3">
                                   <label for="manual_account_name" class="form-label">Account Name</label>
                                   <input type="text" class="form-control" id="manual_account_name" name="account_name" value="<?php echo htmlspecialchars($config['account_name'] ?? ''); ?>">
                               </div>
                               
                               <div class="mb-3">
                                   <label for="manual_account_number" class="form-label">Account Number</label>
                                   <input type="text" class="form-control" id="manual_account_number" name="account_number" value="<?php echo htmlspecialchars($config['account_number'] ?? ''); ?>">
                               </div>
                               
                               <div class="mb-3">
                                   <label for="manual_bank_name" class="form-label">Bank Name</label>
                                   <input type="text" class="form-control" id="manual_bank_name" name="bank_name" value="<?php echo htmlspecialchars($config['bank_name'] ?? ''); ?>">
                               </div>
                           </div>
                           
                           <!-- Fee Configuration -->
                           <h6 class="mt-4 mb-3">Fee Configuration</h6>
                           
                           <div class="row">
                               <div class="col-md-4 mb-3">
                                   <label for="fixed_fee" class="form-label">Fixed Fee</label>
                                   <div class="input-group">
                                       <span class="input-group-text">KSH</span>
                                       <input type="number" class="form-control" id="fixed_fee" name="fixed_fee" step="0.01" min="0" value="<?php echo htmlspecialchars($feesConfig['fixed_fee'] ?? '0'); ?>">
                                   </div>
                               </div>
                               
                               <div class="col-md-4 mb-3">
                                   <label for="percentage_fee" class="form-label">Percentage Fee</label>
                                   <div class="input-group">
                                       <input type="number" class="form-control" id="percentage_fee" name="percentage_fee" step="0.01" min="0" value="<?php echo htmlspecialchars($feesConfig['percentage_fee'] ?? '0'); ?>">
                                       <span class="input-group-text">%</span>
                                   </div>
                               </div>
                               
                               <div class="col-md-4 mb-3">
                                   <label for="fee_cap" class="form-label">Maximum Fee (Cap)</label>
                                   <div class="input-group">
                                       <span class="input-group-text">KSH</span>
                                       <input type="number" class="form-control" id="fee_cap" name="fee_cap" step="0.01" min="0" value="<?php echo htmlspecialchars($feesConfig['cap'] ?? '0'); ?>">
                                   </div>
                               </div>
                           </div>
                           
                           <div class="mb-3">
                               <div class="form-check">
                                   <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" <?php echo $channel['is_active'] ? 'checked' : ''; ?>>
                                   <label class="form-check-label" for="is_active">
                                       Active
                                   </label>
                               </div>
                           </div>
                           
                           <div class="mb-3">
                               <div class="form-check">
                                   <input class="form-check-input" type="checkbox" value="1" id="is_default" name="is_default" <?php echo $channel['is_default'] ? 'checked' : ''; ?>>
                                   <label class="form-check-label" for="is_default">
                                       Set as Default
                                   </label>
                               </div>
                           </div>
                           
                           <div class="text-end">
                               <a href="/admin/payment-channels" class="btn btn-secondary">Cancel</a>
                               <button type="submit" class="btn btn-primary">Update Payment Channel</button>
                           </div>
                       </form>
                   </div>
               </div>
           </main>
       </div>
   </div>
   
   <!-- Footer -->
   <?php include KIPAY_PATH . '/src/Templates/admin/partials/footer.php'; ?>
   
   <!-- Bootstrap JS -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
   
   <!-- jQuery -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
   <script>
       $(document).ready(function() {
           // Provider specific configuration toggle
           $('#provider').change(function() {
               // Hide all provider configs
               $('.provider-config').hide();
               
               // Show selected provider config
               const provider = $(this).val();
               
               if (provider === 'paystack') {
                   $('#paystack-config').show();
               } else if (provider === 'flutterwave') {
                   $('#flutterwave-config').show();
               } else if (provider === 'manual') {
                   $('#manual-config').show();
               }
           });
           
           // Form validation
           const forms = document.querySelectorAll('.needs-validation');
           Array.from(forms).forEach(function(form) {
               form.addEventListener('submit', function(event) {
                   if (!form.checkValidity()) {
                       event.preventDefault();
                       event.stopPropagation();
                   }
                   form.classList.add('was-validated');
               }, false);
           });
       });
   </script>
</body>
</html>