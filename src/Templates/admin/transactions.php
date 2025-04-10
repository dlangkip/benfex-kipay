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
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
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
                        <a href="/admin/transactions/export<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-file-export"></i> Export
                        </a>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/admin/transactions?status=pending">Pending Transactions</a></li>
                                <li><a class="dropdown-item" href="/admin/transactions?status=completed">Completed Transactions</a></li>
                                <li><a class="dropdown-item" href="/admin/transactions?status=failed">Failed Transactions</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/admin/transactions">All Transactions</a></li>
                            </ul>
                        </div>
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
                
                <!-- Filter Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="/admin/transactions" method="get" id="filterForm">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="dateRange" class="form-label">Date Range</label>
                                    <input type="text" class="form-control" id="dateRange" name="date_range" value="<?php echo isset($filters['date_from']) && isset($filters['date_to']) ? date('m/d/Y', strtotime($filters['date_from'])) . ' - ' . date('m/d/Y', strtotime($filters['date_to'])) : ''; ?>" placeholder="All Time">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?php echo isset($filters['status']) && $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo isset($filters['status']) && $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="failed" <?php echo isset($filters['status']) && $filters['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                        <option value="refunded" <?php echo isset($filters['status']) && $filters['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                        <option value="cancelled" <?php echo isset($filters['status']) && $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select" id="payment_method" name="payment_method">
                                        <option value="">All Methods</option>
                                        <option value="card" <?php echo isset($filters['payment_method']) && $filters['payment_method'] === 'card' ? 'selected' : ''; ?>>Card</option>
                                        <option value="bank" <?php echo isset($filters['payment_method']) && $filters['payment_method'] === 'bank' ? 'selected' : ''; ?>>Bank Transfer</option>
                                        <option value="ussd" <?php echo isset($filters['payment_method']) && $filters['payment_method'] === 'ussd' ? 'selected' : ''; ?>>USSD</option>
                                        <option value="mobile_money" <?php echo isset($filters['payment_method']) && $filters['payment_method'] === 'mobile_money' ? 'selected' : ''; ?>>Mobile Money</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" placeholder="Reference, Email, Description">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Apply Filters
                                    </button>
                                    <a href="/admin/transactions" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                    <a href="/admin/transactions/export<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-success">
                                        <i class="fas fa-file-export"></i> Export CSV
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Transactions Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Transaction List</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                        <th>Customer</th>
                                        <th>Payment Channel</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($transactions) && !empty($transactions['data'])): ?>
                                        <?php foreach ($transactions['data'] as $transaction): ?>
                                            <tr>
                                                <td><?php echo $transaction['id']; ?></td>
                                                <td><?php echo htmlspecialchars($transaction['reference']); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['currency'] ?? 'KSH'); ?> <?php echo number_format($transaction['amount'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['customer_email'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['payment_channel_name'] ?? 'N/A'); ?></td>
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
                                                    <div class="btn-group">
                                                        <a href="/admin/transactions/view/<?php echo $transaction['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($transaction['status'] === 'pending'): ?>
                                                            <a href="/admin/transactions/verify/<?php echo $transaction['id']; ?>" class="btn btn-sm btn-warning" title="Verify Status">
                                                                <i class="fas fa-sync-alt"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No transactions found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if (isset($transactions) && $transactions['pages'] > 1): ?>
                            <nav aria-label="Transactions pagination">
                                <ul class="pagination justify-content-center mt-4">
                                    <?php if ($transactions['page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo '/admin/transactions?' . http_build_query(array_merge($filters, ['page' => $transactions['page'] - 1])); ?>">
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
                                            <a class="page-link" href="<?php echo '/admin/transactions?' . http_build_query(array_merge($filters, ['page' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($transactions['page'] < $transactions['pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo '/admin/transactions?' . http_build_query(array_merge($filters, ['page' => $transactions['page'] + 1])); ?>">
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
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Date Range Picker -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    
    <script>
        $(document).ready(function() {
            // Initialize date range picker
            $('#dateRange').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                },
                ranges: {
                   'Today': [moment(), moment()],
                   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                   'This Month': [moment().startOf('month'), moment().endOf('month')],
                   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });
            
            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });
            
            $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });
    </script>
</body>
</html>