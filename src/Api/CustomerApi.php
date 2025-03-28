<?php
namespace Kipay\Api;

use Kipay\Core\Customer;
use Kipay\Utils\Response;
use Kipay\Utils\Validator;

/**
 * CustomerApi Class for Kipay Payment Gateway
 * 
 * This class handles all API endpoints related to customers.
 */
class CustomerApi extends ApiController
{
    /**
     * @var \Kipay\Core\Customer
     */
    protected $customer;
    
    /**
     * @var \Kipay\Utils\Validator
     */
    protected $validator;
    
    /**
     * CustomerApi constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->customer = new Customer();
        $this->validator = new Validator();
    }
    
    /**
     * Create a new customer
     * 
     * @return void
     */
    public function create(): void
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
        $requiredFields = ['email'];
        $missingFields = $this->validator->validateRequired($data, $requiredFields);
        
        if (!empty($missingFields)) {
            $this->response->badRequest('Missing required fields: ' . implode(', ', $missingFields));
            return;
        }
        
        // Validate email
        if (!$this->validator->validateEmail($data['email'])) {
            $this->response->badRequest('Invalid email address');
            return;
        }
        
        // Add user_id to data
        $data['user_id'] = $this->user['id'];
        
        // Create the customer
        $customer = $this->customer->create($data);
        
        if (!$customer) {
            $this->response->serverError('Failed to create customer');
            return;
        }
        
        // Return success response
        $this->response->created([
            'customer' => $customer,
            'message' => 'Customer created successfully'
        ]);
    }
    
    /**
     * Update a customer
     * 
     * @param int $id Customer ID
     * @return void
     */
    public function update(int $id = 0): void
    {
        // Check request method
        if ($this->request->method !== 'PUT' && $this->request->method !== 'PATCH') {
            $this->response->methodNotAllowed(['PUT', 'PATCH']);
            return;
        }
        
        // Validate authorization
        if (!$this->validateAuth()) {
            return;
        }
        
        // Validate ID
        if ($id <= 0) {
            $this->response->badRequest('Invalid customer ID');
            return;
        }
        
        // Get existing customer
        $customer = $this->customer->getById($id);
        
        if (!$customer) {
            $this->response->notFound('Customer not found');
            return;
        }
        
        // Check if customer belongs to authenticated user
        if ($customer['user_id'] != $this->user['id']) {
            $this->response->forbidden('You do not have permission to update this customer');
            return;
        }
        
        // Get and validate request data
        $data = $this->request->getJson();
        
        // Validate email if provided
        if (isset($data['email']) && !empty($data['email']) && !$this->validator->validateEmail($data['email'])) {
            $this->response->badRequest('Invalid email address');
            return;
        }
        
        // Update the customer
        $updated = $this->customer->update($id, $data);
        
        if (!$updated) {
            $this->response->serverError('Failed to update customer');
            return;
        }
        
        // Get updated customer
        $updatedCustomer = $this->customer->getById($id);
        
        // Return success response
        $this->response->success([
            'customer' => $updatedCustomer,
            'message' => 'Customer updated successfully'
        ]);
    }
    
    /**
     * Delete a customer
     * 
     * @param int $id Customer ID
     * @return void
     */
    public function delete(int $id = 0): void
    {
        // Check request method
        if ($this->request->method !== 'DELETE') {
            $this->response->methodNotAllowed(['DELETE']);
            return;
        }
        
        // Validate authorization
        if (!$this->validateAuth()) {
            return;
        }
        
        // Validate ID
        if ($id <= 0) {
            $this->response->badRequest('Invalid customer ID');
            return;
        }
        
        // Get existing customer
        $customer = $this->customer->getById($id);
        
        if (!$customer) {
            $this->response->notFound('Customer not found');
            return;
        }
        
        // Check if customer belongs to authenticated user
        if ($customer['user_id'] != $this->user['id']) {
            $this->response->forbidden('You do not have permission to delete this customer');
            return;
        }
        
        // Delete the customer
        $deleted = $this->customer->delete($id);
        
        if (!$deleted) {
            $this->response->serverError('Failed to delete customer. The customer may have transactions associated with it.');
            return;
        }
        
        // Return success response
        $this->response->success([
            'message' => 'Customer deleted successfully'
        ]);
    }
    
    /**
     * Get a customer
     * 
     * @param int $id Customer ID
     * @return void
     */
    public function get(int $id = 0): void
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
        
        // Validate ID
        if ($id <= 0) {
            $this->response->badRequest('Invalid customer ID');
            return;
        }
        
        // Get customer
        $customer = $this->customer->getById($id);
        
        if (!$customer) {
            $this->response->notFound('Customer not found');
            return;
        }
        
        // Check if customer belongs to authenticated user
        if ($customer['user_id'] != $this->user['id']) {
            $this->response->forbidden('You do not have permission to view this customer');
            return;
        }
        
        // Return success response
        $this->response->success([
            'customer' => $customer
        ]);
    }
    
    /**
     * List all customers for authenticated user
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
        $filterFields = ['country', 'date_from', 'date_to', 'search'];
        
        foreach ($filterFields as $field) {
            $value = $this->request->getQueryParam($field);
            if ($value !== null) {
                $filters[$field] = $value;
            }
        }
        
        // Get customers
        $customers = $this->customer->getByUserId($this->user['id'], $filters, $page, $limit);
        
        // Return the customers
        $this->response->success($customers);
    }
    
    /**
     * Search customers by email, name, or phone
     * 
     * @return void
     */
    public function search(): void
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
        
        // Get search term
        $searchTerm = $this->request->getQueryParam('q', '');
        
        if (empty($searchTerm)) {
            $this->response->badRequest('Search term is required');
            return;
        }
        
        // Get limit
        $limit = (int) ($this->request->getQueryParam('limit', 10));
        
        // Search customers
        $customers = $this->customer->search($this->user['id'], $searchTerm, $limit);
        
        // Return the customers
        $this->response->success([
            'customers' => $customers
        ]);
    }
    
    /**
     * Get customer transactions
     * 
     * @param int $id Customer ID
     * @return void
     */
    public function getTransactions(int $id = 0): void
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
        
        // Validate ID
        if ($id <= 0) {
            $this->response->badRequest('Invalid customer ID');
            return;
        }
        
        // Get customer
        $customer = $this->customer->getById($id);
        
        if (!$customer) {
            $this->response->notFound('Customer not found');
            return;
        }
        
        // Check if customer belongs to authenticated user
        if ($customer['user_id'] != $this->user['id']) {
            $this->response->forbidden('You do not have permission to view this customer');
            return;
        }
        
        // Get query parameters
        $page = (int) ($this->request->getQueryParam('page', 1));
        $limit = (int) ($this->request->getQueryParam('limit', 20));
        
        // Get filters
        $filters = [];
        $filterFields = ['status', 'payment_method', 'currency', 'date_from', 'date_to'];
        
        foreach ($filterFields as $field) {
            $value = $this->request->getQueryParam($field);
            if ($value !== null) {
                $filters[$field] = $value;
            }
        }
        
        // Get transactions
        $transactions = $this->customer->getTransactions($id, $filters, $page, $limit);
        
        // Return the transactions
        $this->response->success($transactions);
    }
}