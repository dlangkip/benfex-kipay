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
    <link href="/assets/css/styles.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                            <i class="fas fa-calendar-alt"></i> This week
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
                
                <!-- Dashboard Stats -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="dashboard-stat-card">
                            <div class="value text-primary"><?php echo number_format($summary['total_transactions']); ?></div>
                            <div class="label">Total Transactions</div>
                            <div class="icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="dashboard-stat-card">
                            <div class="value text-success"><?php echo number_format($summary['successful_transactions']); ?></div>
                            <div class="label">Successful Transactions</div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="dashboard-stat-card">
                            <div class="value text-danger"><?php echo number_format($summary['failed_transactions']); ?></div>
                            <div class="label">Failed Transactions</div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="dashboard-stat-card">
                            <div class="value text-info"><?php echo number_format($summary['successful_amount']); ?></div>
                            <div class="label">Total Revenue</div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Transaction Chart -->
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Transaction Overview</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="transactionChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Payment Channels</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($payment_channels as $channel): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?php echo htmlspecialchars($channel['name']); ?>
                                            <?php if ($channel['is_active']): ?>
                                                <span class="badge bg-success rounded-pill">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger rounded-pill">Inactive</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <div class="mt-3">
                                    <a href="/admin/payment-channels" class="btn btn-sm btn-primary">Manage Channels</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Transactions -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Recent Transactions</h5>
                                <a href="/admin/transactions" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Reference</th>
                                                <th>Amount</th>
                                                <th>Customer</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_transactions as $transaction): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($transaction['reference']); ?></td>
                                                    <td><?php echo htmlspecialchars($transaction['currency']); ?> <?php echo number_format($transaction['amount'], 2); ?></td>
                                                    <td><?php echo htmlspecialchars($transaction['customer_email'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo strtolower($transaction['status']); ?>">
                                                            <?php echo ucfirst($transaction['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="/admin/transactions/view/<?php echo $transaction['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            
                                            <?php if (empty($recent_transactions)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No transactions found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
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
    
    <!-- Chart.js Initialization -->
    <script>
        // Initialize transaction chart
        const ctx = document.getElementById('transactionChart').getContext('2d');
        
        // Chart data
        const chartData = <?php echo json_encode($chart_data); ?>;
        
        // Extract labels and data series
        const labels = chartData.map(item => item.label);
        const completedData = chartData.map(item => item.completed_amount);
        const failedData = chartData.map(item => item.failed_amount);
        const pendingData = chartData.map(item => item.pending_amount);
        
        // Create chart
        const transactionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Completed',
                        data: completedData,
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 2,
                        tension: 0.4
                    },
                    {
                        label: 'Failed',
                        data: failedData,
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 2,
                        tension: 0.4
                    },
                    {
                        label: 'Pending',
                        data: pendingData,
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        borderWidth: 2,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>