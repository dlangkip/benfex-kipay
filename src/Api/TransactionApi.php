<?php
namespace Kipay\Api;

use Kipay\Core\Gateway;
use Kipay\Core\Transaction;
use Kipay\Core\Customer;
use Kipay\Utils\Response;
use Kipay\Utils\Validator;

/**
 * TransactionApi Class for Kipay Payment Gateway
 * 
 * This class handles all API endpoints related to transactions.
 */
class TransactionApi extends ApiController
{
    /**
     * @var \Kipay\Core\Gateway
     */
    protected $gateway;
    
    /**
     * @var \Kipay\Core\Transaction
     */
    protected $transaction;
    
    /**
     * @var \Kipay\Core\Customer
     */
    protected $customer;
    
    /**
     * @var \Kipay\Utils\Validator
     */
    protected $validator;
    
    /**
     * TransactionApi constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->gateway = new Gateway();
        $this->transaction = new Transaction();
        $this->customer = new Customer();
        $this->validator = new Validator();
    }
    
    /**
     * Initialize a transaction
     * 
     * @return void
     */
    public function initialize(): void
    {
        // Check request method
        if ($this->request->method !== 'POST') {
            $this->response->methodNotAllowed(['POST']);
            return;
        }
        
        // Validate authorization
        if (!$this->validateAuth()) {
            return;
        }
        
        // Get and validate request data
        $data = $this->request->getJson();
        
        // Validate required fields
        $requiredFields = ['amount', 'email', 'payment_channel_id'];
        $missingFields = $this->validator->validateRequired($data, $requiredFields);
        
        if (!empty($missingFields)) {
            $this->response->badRequest('Missing required fields: ' . implode(', ', $missingFields));
            return;
        }
        
        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            $this->response->badRequest('Invalid amount');
            return;
        }
        
        // Validate email
        if (!$this->validator->validateEmail($data['email'])) {
            $this->response->badRequest('Invalid email address');
            return;
        }
        
        // Add user_id to transaction data
        $data['user_id'] = $this->user['id'];
        
        // Create or update customer
        $customerData = [
            'email' => $data['email'],
            'user_id' => $this->user['id']
        ];
        
        // Add optional customer fields if provided
        $customerFields = ['first_name', 'last_name', 'phone', 'address', 'city', 'state', 'country', 'postal_code'];
        foreach ($customerFields as $field) {
            if (isset($data[$field])) {
                $customerData[$field] = $data[$field];
            }
        }
        
        $customer = $this->customer->findOrCreate($this->user['id'], $data['email'], $customerData);
        
        if (!$customer) {
            $this->response->serverError('Failed to create customer');
            return;
        }
        
        // Add customer_id to transaction data
        $data['customer_id'] = $customer['id'];
        
        // Add optional transaction fields
        if (!isset($data['description']) || empty($data['description'])) {
            $data['description'] = 'Payment of ' . $data['amount'] . ' ' . 
                ($data['currency'] ?? $this->config->get('currency', 'KSH'));
        }
        
        // Initialize the transaction
        $result = $this->gateway->initializeTransaction($data);
        
        if (!$result) {
            $this->response->serverError('Failed to initialize transaction');
            return;
        }
        
        // Return the transaction data
        $this->response->success([
            'transaction' => $result['transaction'],
            'authorization_url' => $result['paystack']['authorization_url'],
            'access_code' => $result['paystack']['access_code'],
            'reference' => $result['transaction']['reference']
        ]);
    }
    
    /**
     * Verify a transaction
     * 
     * @param string $reference Transaction reference
     * @return void
     */
    public function verify(string $reference = ''): void
    {
        // Check request method
        if ($this->request->method !== 'GET') {
            $this->response->methodNotAllowed(['GET']);
            return;
        }
        
        // Validate authorization
        if (!$this->validateAuth()) {
            return;
        }
        
        // Validate reference
        if (empty($reference)) {
            $this->response->badRequest('Transaction reference is required');
            return;
        }
        
        // Verify the transaction
        $result = $this->gateway->verifyTransaction($reference);
        
        if (!$result) {
            $this->response->notFound('Transaction not found or verification failed');
            return;
        }
        
        // Check if transaction belongs to authenticated user
        if ($result['transaction']['user_id'] != $this->user['id']) {
            $this->response->forbidden('You do not have permission to view this transaction');
            return;
        }
        
        // Return the transaction data
        $this->response->success([
            'transaction' => $result['transaction'],
            'status' => $result['transaction']['status']
        ]);
    }
    
    /**
     * Get transaction details
     * 
     * @param string $reference Transaction reference
     * @return void
     */
    public function get(string $reference = ''): void
    {
        // Check request method
        if ($this->request->method !== 'GET') {
            $this->response->methodNotAllowed(['GET']);
            return;
        }
        
        // Validate authorization
        if (!$this->validateAuth()) {
            return;
        }
        
        // Validate reference
        if (empty($reference)) {
            $this->response->badRequest('Transaction reference is required');
            return;
        }
        
        // Get the transaction
        $transaction = $this->transaction->getByReference($reference, true);
        
        if (!$transaction) {
            $this->response->notFound('Transaction not found');
            return;
        }
        
        // Check if transaction belongs to authenticated user
        if ($transaction['user_id'] != $this->user['id']) {
            $this->response->forbidden('You do not have permission to view this transaction');
            return;
        }
        
        // Return the transaction data
        $this->response->success([
            'transaction' => $transaction
        ]);
    }
    
    /**
     * List all transactions for authenticated user
     * 
     * @return void
     */
    public function list(): void
    {
        // Check request method
        if ($this->request->method !== 'GET') {
            $this->response->methodNotAllowed(['GET']);
            return;
        }
        
        // Validate authorization
        if (!$this->validateAuth()) {
            return;
        }
        
        // Get query parameters
        $page = (int) ($this->request->getQueryParam('page', 1));
        $limit = (int) ($this->request->getQueryParam('limit', 20));
        
        // Get filters
        $filters = [];
        $filterFields = ['status', 'payment_method', 'currency', 'date_from', 'date_to', 'amount_min', 'amount_max', 'search'];
        
        foreach ($filterFields as $field) {
            $value = $this->request->getQueryParam($field);
            if ($value !== null) {
                $filters[$field] = $value;
            }
        }
        
        // Get transactions
        $transactions = $this->transaction->getByUserId($this->user['id'], $filters, $page, $limit);
        
        // Return the transactions
        $this->response->success($transactions);
    }
    
    /**
     * Get transaction summary for authenticated user
     * 
     * @return void
     */
    public function summary(): void
    {
        // Check request method
        if ($this->request->method !== 'GET') {
            $this->response->methodNotAllowed(['GET']);
            return;
        }
        
        // Validate authorization
        if (!$this->validateAuth()) {
            return;
        }
        
        // Get period from query parameter
        $period = $this->request->getQueryParam('period', 'all');
        
        // Validate period
        $validPeriods = ['today', 'week', 'month', 'year', 'all'];
        if (!in_array($period, $validPeriods)) {
            $this->response->badRequest('Invalid period. Valid values are: ' . implode(', ', $validPeriods));
            return;
        }
        
        // Get summary
        $summary = $this->transaction->getSummaryByUserId($this->user['id'], $period);
        
        // Return the summary
        $this->response->success($summary);
    }
    
    /**
     * Export transactions to CSV
     * 
     * @return void
     */
    public function export(): void
    {
        // Check request method
        if ($this->request->method !== 'GET') {
            $this->response->methodNotAllowed(['GET']);
            return;
        }
        
        // Validate authorization
        if (!$this->validateAuth()) {
            return;
        }
        
        // Get filters
        $filters = [];
        $filterFields = ['status', 'payment_method', 'currency', 'date_from', 'date_to', 'amount_min', 'amount_max', 'search'];
        
        foreach ($filterFields as $field) {
            $value = $this->request->getQueryParam($field);
            if ($value !== null) {
                $filters[$field] = $value;
            }
        }
        
        // Export transactions
        $csv = $this->transaction->exportToCsv($this->user['id'], $filters);
        
        if ($csv === false) {
            $this->response->serverError('Failed to export transactions');
            return;
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="transactions_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output CSV content
        echo $csv;
        exit;
    }
    
    /**
     * Get chart data for transactions
     * 
     * @return void
     */
    public function chart(): void
    {
        // Check request method
        if ($this->request->method !== 'GET') {
            $this->response->methodNotAllowed(['GET']);
            return;
        }
        
        // Validate authorization
        if (!$this->validateAuth()) {
            return;
        }
        
        // Get period from query parameter
        $period = $this->request->getQueryParam('period', 'week');
        
        // Validate period
        $validPeriods = ['week', 'month', 'year'];
        if (!in_array($period, $validPeriods)) {
            $this->response->badRequest('Invalid period. Valid values are: ' . implode(', ', $validPeriods));
            return;
        }
        
        // Get chart data
        $chartData = $this->transaction->getChartDataByUserId($this->user['id'], $period);
        
        // Return the chart data
        $this->response->success(['data' => $chartData]);
    }
    
    /**
     * Get recent transactions
     * 
     * @return void
     */
    public function recent(): void
    {
        // Check request method
        if ($this->request->method !== 'GET') {
            $this->response->methodNotAllowed(['GET']);
            return;
        }
        
        // Validate authorization
        if (!$this->validateAuth()) {
            return;
        }
        
        // Get limit from query parameter
        $limit = (int) $this->request->getQueryParam('limit', 5);
        
        // Validate limit
        if ($limit < 1 || $limit > 20) {
            $this->response->badRequest('Invalid limit. Must be between 1 and 20.');
            return;
        }
        
        // Get recent transactions
        $transactions = $this->transaction->getRecentByUserId($this->user['id'], $limit);
        
        // Return the transactions
        $this->response->success(['transactions' => $transactions]);
    }
}