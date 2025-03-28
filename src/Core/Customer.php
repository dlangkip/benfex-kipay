<?php
namespace Kipay\Core;

use Kipay\Models\CustomerModel;
use Kipay\Utils\Logger;
use Kipay\Utils\Validator;

/**
 * Customer Class for Kipay Payment Gateway
 * 
 * This class handles all operations related to customers including
 * creation, updating, and management.
 */
class Customer
{
    /**
     * @var \Kipay\Models\CustomerModel
     */
    protected $customerModel;
    
    /**
     * @var \Kipay\Utils\Logger
     */
    protected $logger;
    
    /**
     * @var \Kipay\Utils\Validator
     */
    protected $validator;
    
    /**
     * Customer constructor
     */
    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->logger = new Logger('customer');
        $this->validator = new Validator();
    }
    
    /**
     * Create a new customer
     * 
     * @param array $customerData Customer data
     * @return array|bool Created customer or false on failure
     */
    public function create(array $customerData)
    {
        try {
            // Validate required fields
            $requiredFields = ['user_id', 'email'];
            foreach ($requiredFields as $field) {
                if (!isset($customerData[$field]) || empty($customerData[$field])) {
                    $this->logger->error("Missing required field for customer", ['field' => $field]);
                    return false;
                }
            }
            
            // Validate email format
            if (!$this->validator->validateEmail($customerData['email'])) {
                $this->logger->error("Invalid email format", ['email' => $customerData['email']]);
                return false;
            }
            
            // Check if customer already exists for this user
            $existingCustomer = $this->customerModel->getByUserAndEmail(
                $customerData['user_id'],
                $customerData['email']
            );
            
            if ($existingCustomer) {
                // Return existing customer
                return $existingCustomer;
            }
            
            // Create the customer
            return $this->customerModel->create($customerData);
        } catch (\Exception $e) {
            $this->logger->error("Error creating customer", [
                'error' => $e->getMessage(),
                'customer_data' => $customerData
            ]);
            return false;
        }
    }
    
    /**
     * Update a customer
     * 
     * @param int $customerId Customer ID
     * @param array $customerData Customer data
     * @return bool True if update was successful
     */
    public function update(int $customerId, array $customerData): bool
    {
        try {
            // Get existing customer
            $customer = $this->customerModel->getById($customerId);
            
            if (!$customer) {
                $this->logger->error("Customer not found", ['customer_id' => $customerId]);
                return false;
            }
            
            // Ensure the user_id is not changed
            if (isset($customerData['user_id']) && $customerData['user_id'] != $customer['user_id']) {
                $this->logger->error("Cannot change customer ownership", ['customer_id' => $customerId]);
                return false;
            }
            
            // Validate email format if provided
            if (isset($customerData['email']) && !empty($customerData['email']) && 
                !$this->validator->validateEmail($customerData['email'])) {
                $this->logger->error("Invalid email format", ['email' => $customerData['email']]);
                return false;
            }
            
            // Handle metadata update
            if (isset($customerData['metadata']) && is_array($customerData['metadata'])) {
                // If metadata exists, merge with existing
                if (!empty($customer['metadata'])) {
                    $existingMetadata = json_decode($customer['metadata'], true);
                    $customerData['metadata'] = array_merge($existingMetadata, $customerData['metadata']);
                }
                
                // Encode metadata as JSON
                $customerData['metadata'] = json_encode($customerData['metadata']);
            }
            
            // Update the customer
            return $this->customerModel->update($customerId, $customerData);
        } catch (\Exception $e) {
            $this->logger->error("Error updating customer", [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get customer by ID
     * 
     * @param int $customerId Customer ID
     * @return array|bool Customer data or false on failure
     */
    public function getById(int $customerId)
    {
        try {
            $customer = $this->customerModel->getById($customerId);
            
            if (!$customer) {
                return false;
            }
            
            // Decode metadata if present
            if (!empty($customer['metadata'])) {
                $customer['metadata'] = json_decode($customer['metadata'], true);
            }
            
            return $customer;
        } catch (\Exception $e) {
            $this->logger->error("Error getting customer by ID", [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get customers by user ID
     * 
     * @param int $userId User ID
     * @param array $filters Optional filters
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array List of customers
     */
    public function getByUserId(int $userId, array $filters = [], int $page = 1, int $limit = 20): array
    {
        try {
            $customers = $this->customerModel->getByUserId($userId, $filters, $page, $limit);
            
            // Decode metadata for each customer
            foreach ($customers['data'] as &$customer) {
                if (!empty($customer['metadata'])) {
                    $customer['metadata'] = json_decode($customer['metadata'], true);
                }
            }
            
            return $customers;
        } catch (\Exception $e) {
            $this->logger->error("Error getting customers by user ID", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'data' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'pages' => 0
            ];
        }
    }
    
    /**
     * Find or create a customer
     * 
     * @param int $userId User ID
     * @param string $email Customer email
     * @param array $additionalData Additional customer data
     * @return array|bool Customer data or false on failure
     */
    public function findOrCreate(int $userId, string $email, array $additionalData = [])
    {
        try {
            // Validate email format
            if (!$this->validator->validateEmail($email)) {
                $this->logger->error("Invalid email format", ['email' => $email]);
                return false;
            }
            
            // Check if customer exists
            $customer = $this->customerModel->getByUserAndEmail($userId, $email);
            
            if ($customer) {
                // Update customer if additional data provided
                if (!empty($additionalData)) {
                    $this->update($customer['id'], $additionalData);
                    $customer = $this->getById($customer['id']);
                }
                
                return $customer;
            }
            
            // Create new customer
            $customerData = array_merge(['user_id' => $userId, 'email' => $email], $additionalData);
            
            return $this->create($customerData);
        } catch (\Exception $e) {
            $this->logger->error("Error finding or creating customer", [
                'user_id' => $userId,
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Delete a customer
     * 
     * @param int $customerId Customer ID
     * @return bool True if deletion was successful
     */
    public function delete(int $customerId): bool
    {
        try {
            // Get existing customer
            $customer = $this->customerModel->getById($customerId);
            
            if (!$customer) {
                $this->logger->error("Customer not found", ['customer_id' => $customerId]);
                return false;
            }
            
            // Check if the customer has transactions
            $hasTransactions = $this->customerModel->customerHasTransactions($customerId);
            
            if ($hasTransactions) {
                $this->logger->error("Cannot delete customer with transactions", ['customer_id' => $customerId]);
                return false;
            }
            
            // Delete the customer
            return $this->customerModel->delete($customerId);
        } catch (\Exception $e) {
            $this->logger->error("Error deleting customer", [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Search customers by email, name, or phone
     * 
     * @param int $userId User ID
     * @param string $searchTerm Search term
     * @param int $limit Maximum results
     * @return array List of matching customers
     */
    public function search(int $userId, string $searchTerm, int $limit = 10): array
    {
        try {
            return $this->customerModel->search($userId, $searchTerm, $limit);
        } catch (\Exception $e) {
            $this->logger->error("Error searching customers", [
                'user_id' => $userId,
                'search_term' => $searchTerm,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get customer transactions
     * 
     * @param int $customerId Customer ID
     * @param array $filters Optional filters
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array List of transactions
     */
    public function getTransactions(int $customerId, array $filters = [], int $page = 1, int $limit = 20): array
    {
        try {
            return $this->customerModel->getTransactions($customerId, $filters, $page, $limit);
        } catch (\Exception $e) {
            $this->logger->error("Error getting customer transactions", [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
            return [
                'data' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'pages' => 0
            ];
        }
    }
    
    /**
     * Create or update customer with Paystack
     * 
     * @param array $customerData Customer data
     * @param \Yabacon\Paystack $paystackClient Paystack client
     * @return array|bool Updated customer data or false on failure
     */
    public function syncWithPaystack(array $customerData, \Yabacon\Paystack $paystackClient)
    {
        try {
            // Check if we already have a Paystack customer code
            if (!empty($customerData['paystack_customer_code'])) {
                // Update existing Paystack customer
                $response = $paystackClient->customer->update([
                    'id' => $customerData['paystack_customer_code'],
                    'first_name' => $customerData['first_name'] ?? '',
                    'last_name' => $customerData['last_name'] ?? '',
                    'phone' => $customerData['phone'] ?? '',
                    'metadata' => $customerData['metadata'] ?? []
                ]);
                
                if ($response->status) {
                    // Update local customer with Paystack data
                    $updateData = [
                        'paystack_customer_code' => $response->data->customer_code
                    ];
                    
                    $this->update($customerData['id'], $updateData);
                    
                    return $this->getById($customerData['id']);
                }
            } else {
                // Create new Paystack customer
                $response = $paystackClient->customer->create([
                    'email' => $customerData['email'],
                    'first_name' => $customerData['first_name'] ?? '',
                    'last_name' => $customerData['last_name'] ?? '',
                    'phone' => $customerData['phone'] ?? '',
                    'metadata' => $customerData['metadata'] ?? []
                ]);
                
                if ($response->status) {
                    // Update local customer with Paystack data
                    $updateData = [
                        'paystack_customer_code' => $response->data->customer_code
                    ];
                    
                    $this->update($customerData['id'], $updateData);
                    
                    return $this->getById($customerData['id']);
                }
            }
            
            $this->logger->error("Error syncing customer with Paystack", [
                'customer_id' => $customerData['id'],
                'error' => $response->message ?? 'Unknown error'
            ]);
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error("Exception syncing customer with Paystack", [
                'customer_id' => $customerData['id'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}