/**
 * Kipay Admin Dashboard JavaScript
 * 
 * Main JavaScript for the admin dashboard.
 * 
 * @version 1.0.0
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const sidebarToggle = document.getElementById('sidebarCollapse');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Copy API key to clipboard
    const apiKeyCopyButtons = document.querySelectorAll('.api-key-copy');
    apiKeyCopyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const keyElement = this.closest('.api-key-container').querySelector('.api-key-value');
            const textArea = document.createElement('textarea');
            textArea.value = keyElement.textContent.trim();
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            // Show feedback
            const originalIcon = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            this.style.color = '#1cc88a';
            setTimeout(() => {
                this.innerHTML = originalIcon;
                this.style.color = '';
            }, 2000);
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Payment channel form handler
    const paymentChannelForm = document.getElementById('paymentChannelForm');
    if (paymentChannelForm) {
        const providerSelect = document.getElementById('provider');
        const configSections = document.querySelectorAll('.provider-config');
        
        // Show/hide provider specific fields
        if (providerSelect) {
            providerSelect.addEventListener('change', function() {
                const selectedProvider = this.value;
                configSections.forEach(section => {
                    if (section.id === `${selectedProvider}-config`) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            });
            
            // Trigger change event on load
            providerSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Date range picker initialization
    const dateRangePicker = document.getElementById('dateRange');
    if (dateRangePicker && typeof daterangepicker !== 'undefined') {
        new daterangepicker(dateRangePicker, {
            opens: 'left',
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            alwaysShowCalendars: true,
            autoUpdateInput: false
        });
        
        dateRangePicker.addEventListener('apply.daterangepicker', function(ev, picker) {
            this.value = picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY');
            this.dispatchEvent(new Event('change'));
        });
        
        dateRangePicker.addEventListener('cancel.daterangepicker', function(ev, picker) {
            this.value = '';
            this.dispatchEvent(new Event('change'));
        });
    }
    
    // DataTables initialization
    const dataTables = document.querySelectorAll('.dataTable');
    if (dataTables.length > 0 && typeof $.fn.DataTable !== 'undefined') {
        dataTables.forEach(table => {
            $(table).DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                }
            });
        });
    }
    
    // Toast initialization
    const toastElements = document.querySelectorAll('.toast');
    toastElements.forEach(toastEl => {
        new bootstrap.Toast(toastEl).show();
    });
    
    // File upload preview
    const fileInputs = document.querySelectorAll('.custom-file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.value.split('\\').pop();
            const label = this.nextElementSibling;
            label.textContent = fileName || 'Choose file';
            
            // Image preview
            const previewContainer = document.querySelector('.file-preview');
            if (previewContainer && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    previewContainer.innerHTML = '';
                    previewContainer.appendChild(preview);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // AJAX form submission
    const ajaxForms = document.querySelectorAll('.ajax-form');
    ajaxForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading spinner
            const spinner = document.createElement('div');
            spinner.className = 'spinner-overlay';
            spinner.innerHTML = '<div class="spinner"></div>';
            document.body.appendChild(spinner);
            
            // Collect form data
            const formData = new FormData(form);
            
            // Send AJAX request
            fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Remove spinner
                document.body.removeChild(spinner);
                
                // Show response message
                showToast(data.status === 'success' ? 'success' : 'danger', data.message);
                
                // Handle success response
                if (data.status === 'success' && data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            })
            .catch(error => {
                // Remove spinner
                document.body.removeChild(spinner);
                
                // Show error message
                showToast('danger', 'An error occurred. Please try again.');
                console.error('Error:', error);
            });
        });
    });
    
    // Toast notification function
    function showToast(type, message) {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        
        const toastElement = document.createElement('div');
        toastElement.className = `toast align-items-center text-white bg-${type} border-0`;
        toastElement.setAttribute('role', 'alert');
        toastElement.setAttribute('aria-live', 'assertive');
        toastElement.setAttribute('aria-atomic', 'true');
        
        toastElement.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        document.querySelector('.toast-container').appendChild(toastElement);
        
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 5000
        });
        
        toast.show();
        
        // Remove toast after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }
    
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const confirmMessage = this.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
            const deleteUrl = this.getAttribute('href');
            
            if (confirm(confirmMessage)) {
                // Show loading spinner
                const spinner = document.createElement('div');
                spinner.className = 'spinner-overlay';
                spinner.innerHTML = '<div class="spinner"></div>';
                document.body.appendChild(spinner);
                
                // Send delete request
                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Remove spinner
                    document.body.removeChild(spinner);
                    
                    // Show response message
                    showToast(data.status === 'success' ? 'success' : 'danger', data.message);
                    
                    // Handle success response
                    if (data.status === 'success') {
                        // Remove element from DOM if needed
                        const targetId = this.getAttribute('data-target');
                        if (targetId) {
                            const targetElement = document.getElementById(targetId);
                            if (targetElement) {
                                targetElement.remove();
                            }
                        }
                        
                        // Redirect if needed
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1000);
                        }
                    }
                })
                .catch(error => {
                    // Remove spinner
                    document.body.removeChild(spinner);
                    
                    // Show error message
                    showToast('danger', 'An error occurred. Please try again.');
                    console.error('Error:', error);
                });
            }
        });
    });
});