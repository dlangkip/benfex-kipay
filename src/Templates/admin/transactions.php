<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Kipay Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
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
                <li class="active">
                    <a href="/admin/transactions"><i class="fas fa-exchange-alt"></i> Transactions</a>
                </li>
                <li>
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

            <!-- Transactions Content -->
            <div class="container-fluid">
                <h1 class="mt-4 mb-4">Transactions</h1>
                
                <!-- Filter Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
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
                                        <?php if (isset($paymentMethods) && is_array($paymentMethods)) : ?>
                                            <?php foreach ($paymentMethods as $method) : ?>
                                                <option value="<?php echo htmlspecialchars($method); ?>" <?php echo isset($filters['payment_method']) && $filters['payment_method'] === $method ? 'selected' : ''; ?>><?php echo htmlspecialchars(ucfirst($method)); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">All Transactions</h6>
                        <div class="dropdown no-arrow">
                            <button class="btn btn-link btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm text-gray-400"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="/admin/transactions?status=pending">View Pending</a></li>
                                <li><a class="dropdown-item" href="/admin/transactions?status=completed">View Completed</a></li>
                                <li><a class="dropdown-item" href="/admin/transactions?status=failed">View Failed</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/admin/transactions/export">Export All</a></li>
                            </ul>
                        </div>
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
                                    <?php if (isset($transactions) && !empty($transactions['data'])) : ?>
                                        <?php foreach ($transactions['data'] as $transaction) : ?>
                                            <tr>
                                                <td><?php echo $transaction['id']; ?></td>
                                                <td><?php echo htmlspecialchars($transaction['reference']); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['currency'] ?? 'NGN'); ?> <?php echo number_format($transaction['amount'], 2); ?></td>
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
                                                    <a href="/admin/transactions/view/<?php echo $transaction['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($transaction['status'] === 'pending') : ?>
                                                        <a href="/admin/transactions/verify/<?php echo $transaction['id']; ?>" class="btn btn-sm btn-warning" title="Verify Status">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No transactions found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if (isset($transactions) && $transactions['pages'] > 1) : ?>
                            <nav aria-label="Transactions pagination">
                                <ul class="pagination justify-content-center mt-4">
                                    <?php if ($transactions['page'] > 1) : ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo '/admin/transactions?' . http_build_query(array_merge($filters, ['page' => $transactions['page'] - 1])); ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php else : ?>
                                        <li class="page-item disabled">
                                            <span class="page-link"><i class="fas fa-chevron-left"></i> Previous</span>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $startPage = max(1, $transactions['page'] - 2);
                                    $endPage = min($transactions['pages'], $startPage + 4);
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++) :
                                    ?>
                                        <li class="page-item <?php echo $i === $transactions['page'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo '/admin/transactions?' . http_build_query(array_merge($filters, ['page' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($transactions['page'] < $transactions['pages']) : ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo '/admin/transactions?' . http_build_query(array_merge($filters, ['page' => $transactions['page'] + 1])); ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php else : ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">Next <i class="fas fa-chevron-right"></i></span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/admin.js"></script>
</body>
</html>