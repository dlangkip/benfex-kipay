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
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/admin/customers" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Customers
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
                
                <!-- Customer Details Card -->
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="customer-header d-flex align-items-center mb-4">
                                    <div class="customer-img me-3">
                                        <?php 
                                            $initials = substr($customer['first_name'] ?? '', 0, 1) . substr($customer['last_name'] ?? '', 0, 1);
                                            $initials = strtoupper($initials);
                                            if (empty(trim($initials))) {
                                                $initials = substr($customer['email'] ?? '', 0, 2);
                                                $initials = strtoupper($initials);
                                            }
                                        ?>
                                        <div class="d-flex align-items-center justify-content-center bg-primary text-white" 
                                             style="width: 70px; height: 70px; border-radius: 50%; font-size: 2rem;">
                                            <?php echo htmlspecialchars($initials); ?>
                                        </div>
                                    </div>
                                    <div class="customer-details">
                                        <h4 class="mb-1">
                                            <?php 
                                                $name = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
                                                echo htmlspecialchars($name ?: 'N/A'); 
                                            ?>
                                        </h4>
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($customer['email'] ?? ''); ?></p>
                                    </div>
                                </div>
                                
                                <h6 class="font-weight-bold mb-3">Contact Information</h6>
                                <ul class="list-group list-group-flush mb-4">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Email:</span>
                                        <span class="text-primary"><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Phone:</span>
                                        <span><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Country:</span>
                                        <span><?php echo htmlspecialchars($customer['country'] ?? 'N/A'); ?></span>
                                    </li>
                                </ul>
                                
                                <?php if (!empty($customer['address']) || !empty($customer['city']) || !empty($customer['state']) || !empty($customer['postal_code'])): ?>
                                    <h6 class="font-weight-bold mb-3">Address</h6>
                                    <p class="mb-1"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></p>
                                    <p class="mb-1">
                                        <?php 
                                            $cityState = trim(($customer['city'] ?? '') . ($customer['city'] && $customer['state'] ? ', ' : '') . ($customer['state'] ?? ''));
                                            echo htmlspecialchars($cityState); 
                                        ?>
                                        <?php echo !empty($customer['postal_code']) ? htmlspecialchars($customer['postal_code']) : ''; ?>
                                    </p>
                                    <p class="mb-3"><?php echo htmlspecialchars($customer['country'] ?? ''); ?></p>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <a href="/admin/customers/update/<?php echo $customer['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Edit Customer
                                    </a>
                                    <?php if (empty($transactions['data'])): ?>
                                        <a href="/admin/customers/delete/<?php echo $customer['id']; ?>" class="btn btn-danger btn-sm delete-btn" 
                                           data-confirm="Are you sure you want to delete this customer?">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Customer Stats Card -->
                        <div class="card shadow mt-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Customer Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-6 mb-3">
                                        <h5 class="text-primary mb-0"><?php echo isset($transactions['total']) ? number_format($transactions['total']) : '0'; ?></h5>
                                        <small class="text-muted">Total Transactions</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <?php
                                            $totalSpent = 0;
                                            if (!empty($transactions['data'])) {
                                                foreach ($transactions['data'] as $transaction) {
                                                    if ($transaction['status'] === 'completed') {
                                                        $totalSpent += $transaction['amount'];
                                                    }
                                                }
                                            }
                                            $currency = !empty($transactions['data']) ? ($transactions['data'][0]['currency'] ?? 'KSH') : 'KSH';
                                        ?>
                                        <h5 class="text-success mb-0"><?php echo htmlspecialchars($currency); ?> <?php echo number_format($totalSpent, 2); ?></h5>
                                        <small class="text-muted">Total Spent</small>
                                    </div>
                                </div>
                                
                                <?php
                                    $firstTransaction = null;
                                    $lastTransaction = null;
                                    
                                    if (!empty($transactions['data'])) {
                                        $firstTransaction = end($transactions['data']);
                                        $lastTransaction = reset($transactions['data']);
                                    }
                                ?>
                                
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>First Transaction:</span>
                                        <span><?php echo $firstTransaction ? date('M d, Y', strtotime($firstTransaction['created_at'])) : 'N/A'; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Last Transaction:</span>
                                        <span><?php echo $lastTransaction ? date('M d, Y', strtotime($lastTransaction['created_at'])) : 'N/A'; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Customer Since:</span>
                                        <span><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Transactions -->
                    <div class="col-md-8 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Transaction History</h6>
                                
                                <?php if (!empty($transactions['data'])): ?>
                                    <a href="/admin/customers/transactions/export/<?php echo $customer['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-file-export"></i> Export
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if (empty($transactions['data'])): ?>
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle"></i> This customer has no transactions yet.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Reference</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($transactions['data'] as $transaction): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($transaction['reference']); ?></td>
                                                        <td><?php echo htmlspecialchars($transaction['currency'] ?? 'KSH'); ?> <?php echo number_format($transaction['amount'], 2); ?></td>
                                                        <td>
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
                                                        </td>
                                                        <td><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></td>
                                                        <td>
                                                            <a href="/admin/transactions/view/<?php echo $transaction['id']; ?>" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if ($transaction['status'] === 'pending'): ?>
                                                                <a href="/admin/transactions/verify/<?php echo $transaction['id']; ?>" class="btn btn-sm btn-warning" title="Verify Status">
                                                                    <i class="fas fa-sync-alt"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Pagination -->
                                    <?php if ($transactions['pages'] > 1): ?>
                                        <nav aria-label="Transaction pagination">
                                            <ul class="pagination justify-content-center mt-4">
                                                <?php if ($transactions['page'] > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?php echo $transactions['page'] - 1; ?>">
                                                            <i class="fas fa-chevron-left"></i> Previous
                                                        </a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link"><i class="fas fa-chevron-left"></i> Previous</span>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php
                                                $startPage = max(1, $transactions['page'] - 2);
                                                $endPage = min($transactions['pages'], $startPage + 4);
                                                
                                                for ($i = $startPage; $i <= $endPage; $i++):
                                                ?>
                                                    <li class="page-item <?php echo $i === $transactions['page'] ? 'active' : ''; ?>">
                                                        <a class="page-link" href="?page=<?php echo $i; ?>">
                                                            <?php echo $i; ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>
                                                
                                                <?php if ($transactions['page'] < $transactions['pages']): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?php echo $transactions['page'] + 1; ?>">
                                                            Next <i class="fas fa-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">Next <i class="fas fa-chevron-right"></i></span>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Customer Metadata -->
                        <?php if (!empty($customer['metadata'])): ?>
                            <div class="card shadow mt-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Additional Information</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (is_string($customer['metadata'])): ?>
                                        <?php $metadata = json_decode($customer['metadata'], true); ?>
                                    <?php else: ?>
                                        <?php $metadata = $customer['metadata']; ?>
                                    <?php endif; ?>
                                    
                                    <?php if ($metadata): ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Key</th>
                                                        <th>Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($metadata as $key => $value): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($key); ?></td>
                                                            <td>
                                                                <?php 
                                                                    if (is_array($value)) {
                                                                        echo '<pre>' . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) . '</pre>';
                                                                    } else {
                                                                        echo htmlspecialchars($value);
                                                                    }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle"></i> No additional information available.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Customer Notes -->
                        <div class="card shadow mt-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Customer Notes</h6>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                    <i class="fas fa-plus"></i> Add Note
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i> No notes have been added for this customer yet.
                                </div>
                                
                                <!-- Placeholder for notes -->
                                <!-- This would be populated from a notes table in your database -->
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Add Note Modal -->
    <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addNoteModalLabel">Add Customer Note</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/admin/customers/add-note/<?php echo $customer['id']; ?>" method="post">
                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>
                            <textarea class="form-control" id="note" name="note" rows="4" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include KIPAY_PATH . '/src/Templates/admin/partials/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/admin.js"></script>
    
    <script>
        // Delete confirmation
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const confirmMessage = this.getAttribute('data-confirm');
                if (confirm(confirmMessage)) {
                    window.location.href = this.getAttribute('href');
                }
            });
        });
    </script>
</body>
</html>