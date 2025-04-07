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
                
                <div class="row">
                    <!-- Profile Details -->
                    <div class="col-md-4 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="profile-header d-flex align-items-center mb-4">
                                    <div class="profile-img me-3">
                                        <?php 
                                            $initials = substr($user_data['first_name'] ?? '', 0, 1) . substr($user_data['last_name'] ?? '', 0, 1);
                                            $initials = strtoupper($initials);
                                            if (empty(trim($initials))) {
                                                $initials = substr($user_data['username'] ?? '', 0, 2);
                                                $initials = strtoupper($initials);
                                            }
                                        ?>
                                        <div class="d-flex align-items-center justify-content-center bg-primary text-white" 
                                             style="width: 100px; height: 100px; border-radius: 50%; font-size: 2.5rem;">
                                            <?php echo htmlspecialchars($initials); ?>
                                        </div>
                                    </div>
                                    <div class="profile-details">
                                        <h4 class="mb-1">
                                            <?php 
                                                $name = trim(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? ''));
                                                echo htmlspecialchars($name ?: $user_data['username']); 
                                            ?>
                                        </h4>
                                        <p class="text-muted mb-1"><?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>
                                        <span class="badge bg-primary"><?php echo ucfirst(htmlspecialchars($user_data['role'] ?? 'user')); ?></span>
                                    </div>
                                </div>
                                
                                <h6 class="font-weight-bold">Account Details</h6>
                                <hr>
                                
                                <p><strong>Username:</strong> <?php echo htmlspecialchars($user_data['username'] ?? ''); ?></p>
                                <p><strong>Role:</strong> <?php echo ucfirst(htmlspecialchars($user_data['role'] ?? 'user')); ?></p>
                                <p><strong>Account Status:</strong> 
                                    <?php if (isset($user_data['is_active']) && $user_data['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($user_data['created_at'] ?? 'now')); ?></p>
                                <p><strong>Last Login:</strong> <?php echo isset($user_data['last_login']) ? date('F d, Y g:i A', strtotime($user_data['last_login'])) : 'Never'; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Profile Form -->
                    <div class="col-md-8 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Edit Profile</h6>
                            </div>
                            <div class="card-body">
                                <form action="/admin/profile" method="post" class="needs-validation" novalidate>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                                value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name"
                                                value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">
                                            Please provide a valid email address.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" 
                                            value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" readonly>
                                        <div class="form-text text-muted">Username cannot be changed.</div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    <h6 class="mb-3 font-weight-bold">Change Password</h6>
                                    <p class="text-muted mb-3">Leave password fields empty if you don't want to change it.</p>
                                    
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="password" name="password">
                                            <div class="form-text">Minimum 8 characters, at least one uppercase letter, one lowercase letter, and one number.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password_confirm" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Changes
                                        </button>
                                        <a href="/admin/dashboard" class="btn btn-outline-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Two-Factor Authentication -->
                        <div class="card shadow mt-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Security Settings</h6>
                            </div>
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-3">Two-Factor Authentication</h6>
                                <p class="text-muted mb-3">Add an extra layer of security to your account by enabling two-factor authentication.</p>
                                
                                <?php if (isset($user_data['two_factor_enabled']) && $user_data['two_factor_enabled']): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> Two-factor authentication is currently <strong>enabled</strong> for your account.
                                    </div>
                                    <form action="/admin/profile/disable-2fa" method="post" class="mt-3">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-lock-open"></i> Disable Two-Factor Authentication
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Two-factor authentication is currently <strong>disabled</strong> for your account.
                                    </div>
                                    <a href="/admin/profile/enable-2fa" class="btn btn-success mt-3">
                                        <i class="fas fa-lock"></i> Enable Two-Factor Authentication
                                    </a>
                                <?php endif; ?>
                                
                                <hr class="my-4">
                                <h6 class="font-weight-bold mb-3">Session Management</h6>
                                <p class="text-muted mb-3">Manage your active sessions and sign out from other devices.</p>
                                
                                <a href="/admin/profile/session-management" class="btn btn-primary">
                                    <i class="fas fa-desktop"></i> Manage Sessions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include KIPAY_PATH . '/src/Templates/admin/partials/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/admin.js"></script>
    
    <script>
        // Form validation
        (function() {
            'use strict'
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        // Password validation
                        var password = document.getElementById('password');
                        var passwordConfirm = document.getElementById('password_confirm');
                        
                        if (password.value !== '' || passwordConfirm.value !== '') {
                            if (password.value !== passwordConfirm.value) {
                                passwordConfirm.setCustomValidity('Passwords do not match');
                                event.preventDefault();
                                event.stopPropagation();
                            } else {
                                passwordConfirm.setCustomValidity('');
                            }
                        }
                        
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>