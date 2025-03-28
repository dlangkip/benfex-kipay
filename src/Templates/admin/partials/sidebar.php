<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $page_title === 'Dashboard' ? 'active' : ''; ?>" href="/admin">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page_title === 'Transactions' ? 'active' : ''; ?>" href="/admin/transactions">
                    <i class="fas fa-exchange-alt"></i> Transactions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page_title === 'Payment Channels' ? 'active' : ''; ?>" href="/admin/payment-channels">
                    <i class="fas fa-credit-card"></i> Payment Channels
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page_title === 'Customers' ? 'active' : ''; ?>" href="/admin/customers">
                    <i class="fas fa-users"></i> Customers
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Administration</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo $page_title === 'Settings' ? 'active' : ''; ?>" href="/admin/settings">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page_title === 'Profile' ? 'active' : ''; ?>" href="/admin/profile">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>