<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Details - Kipay Admin</title>
    
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
                    <h1 class="h2">Transaction Details</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/admin/transactions" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Transactions
                        </a>
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
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
                
                <div class="row">
                    <!-- Transaction Details -->
                    <div class="col-md-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Transaction Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2">Basic Details</h5>
                                        <p><strong>Reference:</strong> <?php echo htmlspecialchars($transaction['reference']); ?></p>
                                        <p><strong>Provider Reference:</strong> <?php echo htmlspecialchars($transaction['provider_reference'] ?? 'N/A'); ?></p>
                                        <p><strong>Amount:</strong> <?php echo htmlspecialchars($transaction['currency'] ?? 'KSH'); ?> <?php echo number_format($transaction['amount'], 2); ?></p>
                                        <p>
                                            <strong>Status:</strong> 
                                            <span class="badge bg-<?php
                                                switch ($transaction['status']) {
                                                    case 'completed': echo 'success'; break;
                                                    case 'pending': echo 'warning'; break;
                                                    case 'failed': echo 'danger'; break;
                                                    case 'refunded': echo 'info'; break;
                                                    case 'cancelled': echo 'secondary'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?php echo ucfirst(htmlspecialchars($transaction['status'])); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2">Payment Details</h5>
                                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($transaction['payment_method'] ?? 'N/A')); ?></p>
                                        <p><strong>Payment Channel:</strong> <?php echo htmlspecialchars($payment_channel['name'] ?? 'N/A'); ?></p>
                                        <p><strong>Provider:</strong> <?php echo htmlspecialchars(ucfirst($payment_channel['provider'] ?? 'N/A')); ?></p>
                                        <p><strong>Fee:</strong> <?php echo htmlspecialchars($transaction['currency'] ?? 'KSH'); ?> <?php echo number_format($transaction['fee'] ?? 0, 2); ?></p>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2">Customer Information</h5>
                                        <?php if ($customer): ?>
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars(trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''))); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></p>
                                            <p><strong>Country:</strong> <?php echo htmlspecialchars($customer['country'] ?? 'N/A'); ?></p>
                                        <?php else: ?>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($transaction['email'] ?? 'N/A'); ?></p>
                                            <p>No detailed customer information available.</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2">Additional Information</h5>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars($transaction['description'] ?? 'N/A'); ?></p>
                                        <p><strong>IP Address:</strong> <?php echo htmlspecialchars($transaction['ip_address'] ?? 'N/A'); ?></p>
                                        <p><strong>Created At:</strong> <?php echo date('F d, Y H:i:s', strtotime($transaction['created_at'])); ?></p>
                                        <p><strong>Updated At:</strong> <?php echo date('F d, Y H:i:s', strtotime($transaction['updated_at'])); ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($transaction['status'] === 'pending'): ?>
                                    <div class="alert alert-warning">
                                        <h5><i class="fas fa-exclamation-circle"></i> Pending Transaction</h5>
                                        <p>This transaction has not been completed yet. You can verify its status by clicking the button below.</p>
                                        <a href="/admin/transactions/verify/<?php echo $transaction['id']; ?>" class="btn btn-warning">
                                            <i class="fas fa-sync-alt"></i> Verify Transaction Status
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($transaction['status'] === 'completed'): ?>
                                    <div class="alert alert-success">
                                        <h5><i class="fas fa-check-circle"></i> Successful Transaction</h5>
                                        <p>This transaction has been completed successfully.</p>
                                        <a href="/payment/receipt/<?php echo $transaction['reference']; ?>" target="_blank" class="btn btn-success">
                                            <i class="fas fa-receipt"></i> View Receipt
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($transaction['status'] === 'failed'): ?>
                                    <div class="alert alert-danger">
                                        <h5><i class="fas fa-times-circle"></i> Failed Transaction</h5>
                                        <p>This transaction has failed.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (isset($transaction['logs']) && !empty($transaction['logs'])): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Transaction Logs</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th>Message</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($transaction['logs'] as $log): ?>
                                                    <tr>
                                                        <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php
                                                                switch ($log['status']) {
                                                                    case 'completed': echo 'success'; break;
                                                                    case 'pending': echo 'warning'; break;
                                                                    case 'failed': echo 'danger'; break;
                                                                    case 'refunded': echo 'info'; break;
                                                                    case 'cancelled': echo 'secondary'; break;
                                                                    default: echo 'secondary';
                                                                }
                                                            ?>">
                                                                <?php echo ucfirst(htmlspecialchars($log['status'])); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($log['message']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Response Data -->
                    <div class="col-md-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Gateway Response</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <?php if (!empty($transaction['gateway_response'])): ?>
                                        <pre class="mb-0 bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><?php 
                                            $response = json_decode($transaction['gateway_response'], true);
                                            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                        ?></pre>
                                    <?php else: ?>
                                        <p class="mb-0 text-muted">No gateway response data available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($customer): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Customer Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 20px;">
                                                <?php 
                                                    $initials = substr($customer['first_name'] ?? '', 0, 1) . substr($customer['last_name'] ?? '', 0, 1);
                                                    echo htmlspecialchars(strtoupper($initials));
                                                ?>
                                            </div>
                                        </div>
                                        <div>
                                            <h5 class="mb-0"><?php echo htmlspecialchars(trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''))); ?></h5>
                                            <p class="mb-0 text-muted"><?php echo htmlspecialchars($customer['email']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></p>
                                    <p class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($customer['address'] ?? 'N/A'); ?></p>
                                    <p class="mb-1"><strong>City:</strong> <?php echo htmlspecialchars($customer['city'] ?? 'N/A'); ?></p>
                                    <p class="mb-1"><strong>State:</strong> <?php echo htmlspecialchars($customer['state'] ?? 'N/A'); ?></p>
                                    <p class="mb-1"><strong>Country:</strong> <?php echo htmlspecialchars($customer['country'] ?? 'N/A'); ?></p>
                                    <p class="mb-1"><strong>Postal Code:</strong> <?php echo htmlspecialchars($customer['postal_code'] ?? 'N/A'); ?></p>
                                    
                                    <div class="mt-3">
                                        <a href="/admin/customers/view/<?php echo $customer['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-user"></i> View Customer Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include KIPAY_PATH . '/src/Templates/admin/partials/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>