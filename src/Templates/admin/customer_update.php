<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Customer - Kipay Admin</title>
    
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
                    <h1 class="h2">Update Customer</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/admin/customers/view/<?php echo $customer['id']; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Customer
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
                
                <!-- Update Customer Form -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                    </div>
                    <div class="card-body">
                        <form action="/admin/customers/update/<?php echo $customer['id']; ?>" method="post" class="needs-validation" novalidate>
                            <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" required>
                                <div class="invalid-feedback">
                                    Please provide a valid email address.
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($customer['first_name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($customer['last_name'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3">Address Information</h5>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="state" class="form-label">State/Province</label>
                                    <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($customer['postal_code'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <select class="form-select" id="country" name="country">
                                        <option value="">Select Country</option>
                                        <option value="KE" <?php echo isset($customer['country']) && $customer['country'] === 'KE' ? 'selected' : ''; ?>>Kenya</option>
                                        <option value="NG" <?php echo isset($customer['country']) && $customer['country'] === 'NG' ? 'selected' : ''; ?>>Nigeria</option>
                                        <option value="GH" <?php echo isset($customer['country']) && $customer['country'] === 'GH' ? 'selected' : ''; ?>>Ghana</option>
                                        <option value="ZA" <?php echo isset($customer['country']) && $customer['country'] === 'ZA' ? 'selected' : ''; ?>>South Africa</option>
                                        <option value="US" <?php echo isset($customer['country']) && $customer['country'] === 'US' ? 'selected' : ''; ?>>United States</option>
                                        <!-- Add more countries as needed -->
                                    </select>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3">Custom Metadata</h5>
                            
                            <div class="mb-3">
                                <label for="metadata" class="form-label">Metadata (JSON)</label>
                                <textarea class="form-control" id="metadata" name="metadata" rows="5"><?php 
                                    if (isset($customer['metadata'])) {
                                        if (is_string($customer['metadata'])) {
                                            echo htmlspecialchars($customer['metadata']);
                                        } else {
                                            echo htmlspecialchars(json_encode($customer['metadata'], JSON_PRETTY_PRINT));
                                        }
                                    }
                                ?></textarea>
                                <div class="form-text">Optional. Enter JSON data for additional customer information.</div>
                                <div class="invalid-feedback">
                                    Please provide valid JSON.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="paystack_sync" name="paystack_sync" value="1">
                                    <label class="form-check-label" for="paystack_sync">
                                        Sync customer with Paystack
                                    </label>
                                    <div class="form-text">This will update customer information in Paystack.</div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/admin/customers/view/<?php echo $customer['id']; ?>" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Customer</button>
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
    
    <!-- Custom JS -->
    <script src="/assets/js/admin.js"></script>
    
    <script>
        // Validate JSON in metadata field
        document.getElementById('metadata').addEventListener('blur', function() {
            try {
                const value = this.value.trim();
                if (value) {
                    JSON.parse(value);
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.remove('is-valid');
                }
            } catch (e) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
        
        // Form validation
        (function() {
            'use strict'
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        // Validate JSON metadata if it has content
                        const metadataField = document.getElementById('metadata');
                        if (metadataField.value.trim()) {
                            try {
                                JSON.parse(metadataField.value);
                                metadataField.classList.remove('is-invalid');
                            } catch (e) {
                                metadataField.classList.add('is-invalid');
                                event.preventDefault();
                                event.stopPropagation();
                            }
                        }
                        
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        
                        form.classList.add('was-validated');
                    }, false)
                })
        })()
    </script>
</body>
</html>