<?php
namespace Kipay\Models;

use Kipay\Database\Database;

/**
 * CustomerModel Class for Kipay Payment Gateway
 * 
 * This class handles all database operations related to customers.
 */
class CustomerModel
{
    /**
     * @var \Kipay\Database\Database
     */
    protected $db;
    
    /**
     * CustomerModel constructor
     */
    public function __construct()
    {
        $this->db = new Database();
    }
    
    /**
     * Create a new customer
     * 
     * @param array $data Customer data
     * @return array|bool Created customer or false on failure
     */
    public function create(array $data)
    {
        try {
            // Handle metadata
            if (isset($data['metadata']) && is_array($data['metadata'])) {
                $data['metadata'] = json_encode($data['metadata']);
            }
            
            $id = $this->db->insert('customers', $data);
            
            if (!$id) {
                return false;
            }
            
            return $this->getById($id);
        } catch (\Exception $e) {
            error_log("Error creating customer: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a customer
     * 
     * @param int $id Customer ID
     * @param array $data Update data
     * @return bool True if successful
     */
    public function update(int $id, array $data): bool
    {
        try {
            // Handle metadata
            if (isset($data['metadata']) && is_array($data['metadata'])) {
                $data['metadata'] = json_encode($data['metadata']);
            }
            
            return $this->db->update('customers', $id, $data);
        } catch (\Exception $e) {
            error_log("Error updating customer: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a customer
     * 
     * @param int $id Customer ID
     * @return bool True if successful
     */
    public function delete(int $id): bool
    {
        try {
            return $this->db->delete('customers', $id);
        } catch (\Exception $e) {
            error_log("Error deleting customer: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get customer by ID
     * 
     * @param int $id Customer ID
     * @return array|bool Customer data or false if not found
     */
    public function getById(int $id)
    {
        try {
            return $this->db->getById('customers', $id);
        } catch (\Exception $e) {
            error_log("Error getting customer by ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get customer by user ID and email
     * 
     * @param int $userId User ID
     * @param string $email Customer email
     * @return array|bool Customer data or false if not found
     */
    public function getByUserAndEmail(int $userId, string $email)
    {
        try {
            $query = "SELECT * FROM customers WHERE user_id = :user_id AND email = :email LIMIT 1";
            $params = [
                'user_id' => $userId,
                'email' => $email
            ];
            
            $result = $this->db->query($query, $params);
            
            return $result ? $result[0] : false;
        } catch (\Exception $e) {
            error_log("Error getting customer by email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get customers by user ID with pagination and filters
     * 
     * @param int $userId User ID
     * @param array $filters Optional filters
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array Customers with pagination data
     */
    public function getByUserId(int $userId, array $filters = [], int $page = 1, int $limit = 20): array
    {
        try {
            $offset = ($page - 1) * $limit;
            
            // Base query
            $query = "SELECT c.*, 
                COUNT(t.id) as transaction_count,
                SUM(CASE WHEN t.status = 'completed' THEN t.amount ELSE 0 END) as total_spent
                FROM customers c
                LEFT JOIN transactions t ON c.id = t.customer_id
                WHERE c.user_id = :user_id";
            
            $countQuery = "SELECT COUNT(*) as total FROM customers WHERE user_id = :user_id";
            
            $params = ['user_id' => $userId];
            
            // Add filters
            if (!empty($filters)) {
                // Search filter
                if (isset($filters['search']) && !empty($filters['search'])) {
                    $search = "%" . $filters['search'] . "%";
                    $query .= " AND (c.email LIKE :search OR c.first_name LIKE :search OR c.last_name LIKE :search OR c.phone LIKE :search)";
                    $countQuery .= " AND (email LIKE :search OR first_name LIKE :search OR last_name LIKE :search OR phone LIKE :search)";
                    $params['search'] = $search;
                }
                
                // Country filter
                if (isset($filters['country']) && !empty($filters['country'])) {
                    $query .= " AND c.country = :country";
                    $countQuery .= " AND country = :country";
                    $params['country'] = $filters['country'];
                }
                
                // Date range filter
                if (isset($filters['date_from']) && !empty($filters['date_from'])) {
                    $query .= " AND c.created_at >= :date_from";
                    $countQuery .= " AND created_at >= :date_from";
                    $params['date_from'] = $filters['date_from'] . ' 00:00:00';
                }
                
                if (isset($filters['date_to']) && !empty($filters['date_to'])) {
                    $query .= " AND c.created_at <= :date_to";
                    $countQuery .= " AND created_at <= :date_to";
                    $params['date_to'] = $filters['date_to'] . ' 23:59:59';
                }
            }
            
            // Group by customer ID
            $query .= " GROUP BY c.id";
            
            // Complete query with order and limit
            $query .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";
            
            // Add pagination parameters
            $params['limit'] = $limit;
            $params['offset'] = $offset;
            
            // Execute queries
            $customers = $this->db->query($query, $params);
            $totalResult = $this->db->query($countQuery, array_diff_key($params, ['limit' => true, 'offset' => true]));
            
            $total = $totalResult ? $totalResult[0]['total'] : 0;
            $pages = ceil($total / $limit);
            
            return [
                'data' => $customers ?: [],
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => $pages
            ];
        } catch (\Exception $e) {
            error_log("Error getting customers by user ID: " . $e->getMessage());
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
     * Search customers by email, name, or phone
     * 
     * @param int $userId User ID
     * @param string $searchTerm Search term
     * @param int $limit Maximum results
     * @return array Matching customers
     */
    public function search(int $userId, string $searchTerm, int $limit = 10): array
    {
        try {
            $query = "SELECT * FROM customers 
                WHERE user_id = :user_id 
                AND (email LIKE :search OR first_name LIKE :search OR last_name LIKE :search OR phone LIKE :search)
                ORDER BY created_at DESC
                LIMIT :limit";
            
            $params = [
                'user_id' => $userId,
                'search' => '%' . $searchTerm . '%',
                'limit' => $limit
            ];
            
            $result = $this->db->query($query, $params);
            
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error searching customers: " . $e->getMessage());
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
     * @return array Transactions with pagination data
     */
    public function getTransactions(int $customerId, array $filters = [], int $page = 1, int $limit = 20): array
    {
        try {
            $offset = ($page - 1) * $limit;
            
            // Base query
            $query = "SELECT t.*, pc.name as payment_channel_name 
                FROM transactions t
                LEFT JOIN payment_channels pc ON t.payment_channel_id = pc.id
                WHERE t.customer_id = :customer_id";
            
            $countQuery = "SELECT COUNT(*) as total FROM transactions WHERE customer_id = :customer_id";
            
            $params = ['customer_id' => $customerId];
            
            // Add filters
            if (!empty($filters)) {
                foreach ($filters as $key => $value) {
                    if (in_array($key, ['status', 'payment_method', 'currency']) && !empty($value)) {
                        $query .= " AND t.$key = :$key";
                        $countQuery .= " AND $key = :$key";
                        $params[$key] = $value;
                    }
                }
                
                // Date range filter
                if (isset($filters['date_from']) && !empty($filters['date_from'])) {
                    $query .= " AND t.created_at >= :date_from";
                    $countQuery .= " AND created_at >= :date_from";
                    $params['date_from'] = $filters['date_from'] . ' 00:00:00';
                }
                
                if (isset($filters['date_to']) && !empty($filters['date_to'])) {
                    $query .= " AND t.created_at <= :date_to";
                    $countQuery .= " AND created_at <= :date_to";
                    $params['date_to'] = $filters['date_to'] . ' 23:59:59';
                }
            }
            
            // Complete query with order and limit
            $query .= " ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset";
            
            // Add pagination parameters
            $params['limit'] = $limit;
            $params['offset'] = $offset;
            
            // Execute queries
            $transactions = $this->db->query($query, $params);
            $totalResult = $this->db->query($countQuery, array_diff_key($params, ['limit' => true, 'offset' => true]));
            
            $total = $totalResult ? $totalResult[0]['total'] : 0;
            $pages = ceil($total / $limit);
            
            return [
                'data' => $transactions ?: [],
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => $pages
            ];
        } catch (\Exception $e) {
            error_log("Error getting customer transactions: " . $e->getMessage());
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
     * Check if a customer has transactions
     * 
     * @param int $customerId Customer ID
     * @return bool True if customer has transactions
     */
    public function customerHasTransactions(int $customerId): bool
    {
        try {
            $query = "SELECT COUNT(*) as count FROM transactions WHERE customer_id = :customer_id LIMIT 1";
            $params = ['customer_id' => $customerId];
            
            $result = $this->db->query($query, $params);
            
            return $result && $result[0]['count'] > 0;
        } catch (\Exception $e) {
            error_log("Error checking customer transactions: " . $e->getMessage());
            return true; // Assume it has transactions on error to prevent accidental deletion
        }
    }
}