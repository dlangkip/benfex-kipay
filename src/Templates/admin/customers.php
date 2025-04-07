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
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <i class="fas fa-plus"></i> Add Customer
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
                
                <!-- Filter Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="/admin/customers" method="get" id="filterForm">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                        value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                                        placeholder="Name, Email, Phone">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <select class="form-select" id="country" name="country">
                                        <option value="">All Countries</option>
                                        <option value="KE" <?php echo isset($filters['country']) && $filters['country'] === 'KE' ? 'selected' : ''; ?>>Kenya</option>
                                        <option value="NG" <?php echo isset($filters['country']) && $filters['country'] === 'NG' ? 'selected' : ''; ?>>Nigeria</option>
                                        <option value="GH" <?php echo isset($filters['country']) && $filters['country'] === 'GH' ? 'selected' : ''; ?>>Ghana</option>
                                        <option value="ZA" <?php echo isset($filters['country']) && $filters['country'] === 'ZA' ? 'selected' : ''; ?>>South Africa</option>
                                        <option value="US" <?php echo isset($filters['country']) && $filters['country'] === 'US' ? 'selected' : ''; ?>>United States</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="dateRange" class="form-label">Date Range</label>
                                    <input type="text" class="form-control" id="dateRange" name="date_range" 
                                        value="<?php echo isset($filters['date_from']) && isset($filters['date_to']) ? date('m/d/Y', strtotime($filters['date_from'])) . ' - ' . date('m/d/Y', strtotime($filters['date_to'])) : ''; ?>" 
                                        placeholder="All Time">
                                </div>
                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Customers Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Customer List</h6>
                        <div class="dropdown no-arrow">
                            <button class="btn btn-link btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm text-gray-400"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="/admin/customers/export">Export All</a></li>
                                <li><a class="dropdown-item" href="#" id="refreshBtn">Refresh</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Country</th>
                                        <th>Transactions</th>
                                        <th>Total Spent</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($customers) && !empty($customers['data'])): ?>
                                        <?php foreach ($customers['data'] as $customer): ?>
                                            <tr>
                                                <td><?php echo $customer['id']; ?></td>
                                                <td>
                                                    <?php 
                                                        $name = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
                                                        echo htmlspecialchars($name ?: 'N/A'); 
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($customer['country'] ?? 'N/A'); ?></td>
                                                <td><?php echo number_format($customer['transaction_count'] ?? 0); ?></td>
                                                <td><?php echo isset($customer['total_spent']) ? number_format($customer['total_spent'], 2) : '0.00'; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="/admin/customers/view/<?php echo $customer['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCustomerModal" 
                                                           data-id="<?php echo $customer['id']; ?>"
                                                           data-email="<?php echo htmlspecialchars($customer['email']); ?>"
                                                           data-first-name="<?php echo htmlspecialchars($customer['first_name'] ?? ''); ?>"
                                                           data-last-name="<?php echo htmlspecialchars($customer['last_name'] ?? ''); ?>"
                                                           data-phone="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>"
                                                           data-country="<?php echo htmlspecialchars($customer['country'] ?? ''); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="/admin/customers/delete/<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger delete-btn" 
                                                           data-confirm="Are you sure you want to delete this customer?">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No customers found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if (isset($customers) && $customers['pages'] > 1): ?>
                            <nav aria-label="Customers pagination">
                                <ul class="pagination justify-content-center mt-4">
                                    <?php if ($customers['page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo '/admin/customers?' . http_build_query(array_merge($filters, ['page' => $customers['page'] - 1])); ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link"><i class="fas fa-chevron-left"></i> Previous</span>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $startPage = max(1, $customers['page'] - 2);
                                    $endPage = min($customers['pages'], $startPage + 4);
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i === $customers['page'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo '/admin/customers?' . http_build_query(array_merge($filters, ['page' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($customers['page'] < $customers['pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo '/admin/customers?' . http_build_query(array_merge($filters, ['page' => $customers['page'] + 1])); ?>">
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
    
    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/admin/customers/create" method="post" id="addCustomerForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <select class="form-select" id="add_country" name="country">
                                <option value="">Select Country</option>
                                <option value="KE">Kenya</option>
                                <option value="NG">Nigeria</option>
                                <option value="GH">Ghana</option>
                                <option value="ZA">South Africa</option>
                                <option value="US">United States</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/admin/customers/update" method="post" id="editCustomerForm" class="needs-validation" novalidate>
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="edit_country" class="form-label">Country</label>
                            <select class="form-select" id="edit_country" name="country">
                                <option value="">Select Country</option>
                                <option value="KE">Kenya</option>
                                <option value="NG">Nigeria</option>
                                <option value="GH">Ghana</option>
                                <option value="ZA">South Africa</option>
                                <option value="US">United States</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Customer</button>
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
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Date Range Picker -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    
    <!-- Custom JS -->
    <script src="/assets/js/admin.js"></script>
    
    <script>
        // Initialize date range picker
        $(document).ready(function() {
            // Edit customer modal
            $('#editCustomerModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const email = button.data('email');
                const firstName = button.data('first-name');
                const lastName = button.data('last-name');
                const phone = button.data('phone');
                const country = button.data('country');
                
                const modal = $(this);
                modal.find('#edit_id').val(id);
                modal.find('#edit_email').val(email);
                modal.find('#edit_first_name').val(firstName);
                modal.find('#edit_last_name').val(lastName);
                modal.find('#edit_phone').val(phone);
                modal.find('#edit_country').val(country);
            });
            
            // Date range picker
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
            
            // Refresh button
            $('#refreshBtn').click(function(e) {
                e.preventDefault();
                window.location.href = '/admin/customers';
            });
            
            // Delete confirmation
            $('.delete-btn').click(function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const confirmMsg = $(this).data('confirm');
                
                if (confirm(confirmMsg)) {
                    window.location.href = url;
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