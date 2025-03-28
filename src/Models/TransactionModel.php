<?php
namespace Kipay\Models;

use Kipay\Database\Database;

/**
 * TransactionModel Class for Kipay Payment Gateway
 * 
 * This class handles all database operations related to transactions.
 */
class TransactionModel
{
    /**
     * @var \Kipay\Database\Database
     */
    protected $db;
    
    /**
     * TransactionModel constructor
     */
    public function __construct()
    {
        $this->db = new Database();
    }
    
    /**
     * Create a new transaction
     * 
     * @param array $data Transaction data
     * @return array|bool Created transaction or false on failure
     */
    public function create(array $data)
    {
        try {
            $id = $this->db->insert('transactions', $data);
            
            if (!$id) {
                return false;
            }
            
            return $this->getById($id);
        } catch (\Exception $e) {
            error_log("Error creating transaction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a transaction log entry
     * 
     * @param array $data Log data
     * @return bool True if successful
     */
    public function createLog(array $data): bool
    {
        try {
            $id = $this->db->insert('transaction_logs', $data);
            
            return $id !== false;
        } catch (\Exception $e) {
            error_log("Error creating transaction log: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a transaction
     * 
     * @param int $id Transaction ID
     * @param array $data Update data
     * @return bool True if successful
     */
    public function update(int $id, array $data): bool
    {
        try {
            return $this->db->update('transactions', $id, $data);
        } catch (\Exception $e) {
            error_log("Error updating transaction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get transaction by ID
     * 
     * @param int $id Transaction ID
     * @return array|bool Transaction data or false if not found
     */
    public function getById(int $id)
    {
        try {
            return $this->db->getById('transactions', $id);
        } catch (\Exception $e) {
            error_log("Error getting transaction by ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get transaction by reference
     * 
     * @param string $reference Transaction reference
     * @return array|bool Transaction data or false if not found
     */
    public function getByReference(string $reference)
    {
        try {
            $query = "SELECT * FROM transactions WHERE reference = :reference LIMIT 1";
            $params = ['reference' => $reference];
            
            $result = $this->db->query($query, $params);
            
            return $result ? $result[0] : false;
        } catch (\Exception $e) {
            error_log("Error getting transaction by reference: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get transaction logs by transaction ID
     * 
     * @param int $transactionId Transaction ID
     * @return array Transaction logs
     */
    public function getLogsByTransactionId(int $transactionId): array
    {
        try {
            $query = "SELECT * FROM transaction_logs WHERE transaction_id = :transaction_id ORDER BY created_at DESC";
            $params = ['transaction_id' => $transactionId];
            
            $result = $this->db->query($query, $params);
            
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error getting transaction logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get transactions by user ID with pagination and filters
     * 
     * @param int $userId User ID
     * @param array $filters Optional filters
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array Transactions with pagination data
     */
    public function getByUserId(int $userId, array $filters = [], int $page = 1, int $limit = 20): array
    {
        try {
            $offset = ($page - 1) * $limit;
            
            // Base query
            $query = "SELECT t.*, c.email as customer_email, c.first_name as customer_first_name, 
                c.last_name as customer_last_name, pc.name as payment_channel_name 
                FROM transactions t 
                LEFT JOIN customers c ON t.customer_id = c.id 
                LEFT JOIN payment_channels pc ON t.payment_channel_id = pc.id 
                WHERE t.user_id = :user_id";
            
            $countQuery = "SELECT COUNT(*) as total FROM transactions WHERE user_id = :user_id";
            
            $params = ['user_id' => $userId];
            
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
                
                // Amount range filter
                if (isset($filters['amount_min']) && is_numeric($filters['amount_min'])) {
                    $query .= " AND t.amount >= :amount_min";
                    $countQuery .= " AND amount >= :amount_min";
                    $params['amount_min'] = $filters['amount_min'];
                }
                
                if (isset($filters['amount_max']) && is_numeric($filters['amount_max'])) {
                    $query .= " AND t.amount <= :amount_max";
                    $countQuery .= " AND amount <= :amount_max";
                    $params['amount_max'] = $filters['amount_max'];
                }
                
                // Search filter
                if (isset($filters['search']) && !empty($filters['search'])) {
                    $query .= " AND (t.reference LIKE :search OR t.description LIKE :search OR c.email LIKE :search)";
                    $countQuery .= " AND (reference LIKE :search OR description LIKE :search)";
                    $params['search'] = '%' . $filters['search'] . '%';
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
            error_log("Error getting transactions by user ID: " . $e->getMessage());
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
     * Get transaction summary by user ID
     * 
     * @param int $userId User ID
     * @param string $period Period (today, week, month, year, all)
     * @return array Transaction summary
     */
    public function getSummaryByUserId(int $userId, string $period = 'all'): array
    {
        try {
            // Base query for counts
            $query = "SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful_transactions,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_transactions,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_transactions,
                SUM(amount) as total_amount,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as successful_amount
                FROM transactions
                WHERE user_id = :user_id";
            
            $params = ['user_id' => $userId];
            
            // Add date filter based on period
            switch ($period) {
                case 'today':
                    $query .= " AND DATE(created_at) = CURDATE()";
                    break;
                case 'week':
                    $query .= " AND YEARWEEK(created_at) = YEARWEEK(CURDATE())";
                    break;
                case 'month':
                    $query .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
                    break;
                case 'year':
                    $query .= " AND YEAR(created_at) = YEAR(CURDATE())";
                    break;
            }
            
            $result = $this->db->query($query, $params);
            
            if (!$result) {
                return [
                    'total_transactions' => 0,
                    'successful_transactions' => 0,
                    'failed_transactions' => 0,
                    'pending_transactions' => 0,
                    'total_amount' => 0,
                    'successful_amount' => 0
                ];
            }
            
            // Ensure all values are numeric
            $summary = $result[0];
            foreach ($summary as $key => $value) {
                $summary[$key] = is_numeric($value) ? $value : 0;
            }
            
            return $summary;
        } catch (\Exception $e) {
            error_log("Error getting transaction summary: " . $e->getMessage());
            return [
                'total_transactions' => 0,
                'successful_transactions' => 0,
                'failed_transactions' => 0,
                'pending_transactions' => 0,
                'total_amount' => 0,
                'successful_amount' => 0
            ];
        }
    }
    
    /**
     * Get recent transactions by user ID
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of transactions
     * @return array Recent transactions
     */
    public function getRecentByUserId(int $userId, int $limit = 5): array
    {
        try {
            $query = "SELECT t.*, c.email as customer_email
                FROM transactions t
                LEFT JOIN customers c ON t.customer_id = c.id
                WHERE t.user_id = :user_id
                ORDER BY t.created_at DESC
                LIMIT :limit";
            
            $params = [
                'user_id' => $userId,
                'limit' => $limit
            ];
            
            $result = $this->db->query($query, $params);
            
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error getting recent transactions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get transaction chart data by user ID
     * 
     * @param int $userId User ID
     * @param string $period Period (week, month, year)
     * @return array Chart data
     */
    public function getChartDataByUserId(int $userId, string $period = 'week'): array
    {
        try {
            $groupBy = '';
            $dateFormat = '';
            $limit = 0;
            
            switch ($period) {
                case 'week':
                    $groupBy = "DATE(created_at)";
                    $dateFormat = "%Y-%m-%d";
                    $limit = 7;
                    break;
                case 'month':
                    $groupBy = "DATE(created_at)";
                    $dateFormat = "%Y-%m-%d";
                    $limit = 30;
                    break;
                case 'year':
                    $groupBy = "MONTH(created_at)";
                    $dateFormat = "%Y-%m";
                    $limit = 12;
                    break;
                default:
                    $groupBy = "DATE(created_at)";
                    $dateFormat = "%Y-%m-%d";
                    $limit = 7;
            }
            
            $query = "SELECT 
                DATE_FORMAT(created_at, :date_format) as label,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount,
                SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END) as failed_amount,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                COUNT(*) as total_transactions
                FROM transactions
                WHERE user_id = :user_id
                GROUP BY $groupBy
                ORDER BY created_at DESC
                LIMIT :limit";
            
            $params = [
                'user_id' => $userId,
                'date_format' => $dateFormat,
                'limit' => $limit
            ];
            
            $result = $this->db->query($query, $params);
            
            // Reverse the result to get chronological order
            return array_reverse($result ?: []);
        } catch (\Exception $e) {
            error_log("Error getting transaction chart data: " . $e->getMessage());
            return [];
        }
    }
}