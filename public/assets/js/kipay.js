/**
 * Kipay Payment Gateway JavaScript
 * 
 * Handles client-side payment processing for Kipay payment gateway.
 * 
 * @version 1.0.0
 */

// Create Kipay namespace
const Kipay = (function() {
    // Private variables
    let _config = {
        publicKey: '',
        currency: 'KSH',
        environment: 'test',
        callbackUrl: '',
        cancelUrl: ''
    };
    
    let _transaction = {
        reference: '',
        amount: 0,
        email: '',
        firstName: '',
        lastName: '',
        phone: '',
        description: '',
        metadata: {}
    };
    
    // Initializes Kipay with configuration
    function init(config) {
        _config = Object.assign(_config, config);
        
        // Check if PaystackJS is available
        if (typeof PaystackPop === 'undefined' && _config.environment !== 'test') {
            console.error('PaystackPop is not defined. Make sure Paystack script is included.');
        }
        
        return this;
    }
    
    // Set transaction data
    function setTransaction(transaction) {
        _transaction = Object.assign(_transaction, transaction);
        return this;
    }
    
    // Validate transaction data before payment
    function validateTransaction() {
        const requiredFields = ['reference', 'amount', 'email', 'description'];
        
        for (const field of requiredFields) {
            if (!_transaction[field]) {
                throw new Error(`${field} is required for payment`);
            }
        }
        
        // Validate email format
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(_transaction.email)) {
            throw new Error('Invalid email address');
        }
        
        // Validate amount (must be positive number)
        if (isNaN(_transaction.amount) || _transaction.amount <= 0) {
            throw new Error('Amount must be a positive number');
        }
    }
    
    // Process payment using Paystack
    function payWithPaystack() {
        try {
            validateTransaction();
            
            // Initialize Paystack payment
            const paystackHandler = PaystackPop.setup({
                key: _config.publicKey,
                email: _transaction.email,
                amount: _transaction.amount * 100, // Convert to kobo (or cents)
                currency: _config.currency,
                ref: _transaction.reference,
                firstname: _transaction.firstName,
                lastname: _transaction.lastName,
                phone: _transaction.phone,
                metadata: {
                    custom_fields: [
                        {
                            display_name: "Description",
                            variable_name: "description",
                            value: _transaction.description
                        }
                    ]
                },
                callback: function(response) {
                    // Handle success
                    if (_config.callbackUrl) {
                        window.location.href = `${_config.callbackUrl}?reference=${response.reference}`;
                    } else {
                        console.log('Payment successful:', response);
                        
                        // Dispatch success event
                        const event = new CustomEvent('kipay:success', { 
                            detail: { 
                                reference: response.reference, 
                                transaction: _transaction 
                            } 
                        });
                        document.dispatchEvent(event);
                    }
                },
                onClose: function() {
                    // Handle popup close
                    console.log('Payment canceled by user');
                    
                    // Dispatch cancel event
                    const event = new CustomEvent('kipay:cancel', { 
                        detail: { transaction: _transaction } 
                    });
                    document.dispatchEvent(event);
                    
                    if (_config.cancelUrl) {
                        window.location.href = _config.cancelUrl;
                    }
                }
            });
            
            paystackHandler.openIframe();
            return true;
        } catch (error) {
            console.error('Kipay payment error:', error);
            
            // Dispatch error event
            const event = new CustomEvent('kipay:error', { 
                detail: { 
                    message: error.message, 
                    transaction: _transaction 
                } 
            });
            document.dispatchEvent(event);
            
            return false;
        }
    }
    
    // Process payment using direct card input (for custom UI)
    function processCardPayment(cardData) {
        try {
            validateTransaction();
            
            if (!cardData || !cardData.number || !cardData.cvv || !cardData.expiry_month || !cardData.expiry_year) {
                throw new Error('Card data is incomplete');
            }
            
            // This would typically call your backend API to process the card payment
            // For security reasons, direct card processing should be done server-side
            
            console.warn('Direct card processing must be implemented server-side for security.');
            
            // Dispatch info event
            const event = new CustomEvent('kipay:info', { 
                detail: { 
                    message: 'Card processing must be implemented server-side', 
                    transaction: _transaction 
                } 
            });
            document.dispatchEvent(event);
            
            return false;
        } catch (error) {
            console.error('Kipay card payment error:', error);
            
            // Dispatch error event
            const event = new CustomEvent('kipay:error', { 
                detail: { 
                    message: error.message, 
                    transaction: _transaction 
                } 
            });
            document.dispatchEvent(event);
            
            return false;
        }
    }
    
    // Verify transaction status
    function verifyTransaction(reference) {
        return fetch(`/api/transactions/verify/${reference || _transaction.reference}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .catch(error => {
            console.error('Transaction verification error:', error);
            throw error;
        });
    }
    
    // Format currency amount
    function formatAmount(amount, currency = _config.currency) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }
    
    // Generate random transaction reference
    function generateReference(prefix = 'KIPAY') {
        const timestamp = new Date().getTime();
        const random = Math.floor(Math.random() * 10000);
        return `${prefix}${timestamp}${random}`;
    }
    
    // Public API
    return {
        init: init,
        setTransaction: setTransaction,
        payWithPaystack: payWithPaystack,
        processCardPayment: processCardPayment,
        verifyTransaction: verifyTransaction,
        formatAmount: formatAmount,
        generateReference: generateReference
    };
})();

// Initialize Kipay on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // Look for Kipay configuration in page
    const configElement = document.getElementById('kipay-config');
    if (configElement) {
        try {
            const config = JSON.parse(configElement.textContent);
            Kipay.init(config);
            
            // Dispatch ready event
            const event = new CustomEvent('kipay:ready');
            document.dispatchEvent(event);
        } catch (error) {
            console.error('Error initializing Kipay:', error);
        }
    }
    
    // Add event listeners to payment buttons
    const payButtons = document.querySelectorAll('[data-kipay-pay]');
    payButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get transaction details from data attributes
            const transaction = {
                reference: button.dataset.reference || Kipay.generateReference(),
                amount: parseFloat(button.dataset.amount || 0),
                email: button.dataset.email || '',
                firstName: button.dataset.firstName || '',
                lastName: button.dataset.lastName || '',
                phone: button.dataset.phone || '',
                description: button.dataset.description || 'Payment'
            };
            
            // Process payment
            Kipay.setTransaction(transaction).payWithPaystack();
        });
    });
});