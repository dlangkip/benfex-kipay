<?php
namespace Kipay\Core;

use Kipay\Models\TransactionModel;
use Kipay\Utils\Logger;

/**
 * Transaction Class for Kipay Payment Gateway
 * 
 * This class handles all transaction-related operations including
 * creation, updating, retrieval, and reporting.
 */
class Transaction
{
    /**
     * @var \Kipay\Models\TransactionModel
     */
    protected $transactionModel;
    
    /**
     * @var \Kipay\Utils\Logger
     */
    protected $logger;
    
    /**
     * Transaction constructor
     */
    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->logger = new Logger('transaction');
    }
    
    /**
     * Create a new transaction log entry
     * 
     * @param int $transactionId Transaction ID
     * @param string $status Status message
     * @param string $message Log message
     * @param array $data Additional data
     * @return bool True if successful
     */
    public function createLog(int $transactionId, string $status, string $message, array $data = []): bool
    {
        try {
            $logData = [
                'transaction_id' => $transactionId,
                'status' => $status,
                'message' => $message,
                'data' => !empty($data) ? json_encode($data) : null
            ];
            
            return $this->transactionModel->createLog($logData);
        } catch (\Exception $e) {
            $this->logger->error("Error creating transaction log", [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get transaction details by ID
     * 
     * @param int $transactionId Transaction ID
     * @param bool $withLogs Include transaction logs
     * @return array|bool Transaction data or false on failure
     */
    public function getById(int $transactionId, bool $withLogs = false)
    {
        try {
            $transaction = $this->transactionModel->getById($transactionId);
            
            if (!$transaction) {
                return false;
            }
            
            if ($withLogs) {
                $transaction['logs'] = $this->transactionModel->getLogsByTransactionId($transaction['id']);
            }
            
            return $transaction;
        } catch (\Exception $e) {
            $this->logger->error("Error getting transaction by ID", [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get transaction details by reference
     * 
     * @param string $reference Transaction reference
     * @param bool $withLogs Include transaction logs
     * @return array|bool Transaction data or false on failure
     */
    public function getByReference(string $reference, bool $withLogs = false)
    {
        try {
            $transaction = $this->transactionModel->getByReference($reference);
            
            if (!$transaction) {
                return false;
            }
            
            if ($withLogs) {
                $transaction['logs'] = $this->transactionModel->getLogsByTransactionId($transaction['id']);
            }
            
            return $transaction;
        } catch (\Exception $e) {
            $this->logger->error("Error getting transaction by reference", [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get transactions by user ID
     * 
     * @param int $userId User ID
     * @param array $filters Optional filters
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array List of transactions
     */
    public function getByUserId(int $userId, array $filters = [], int $page = 1, int $limit = 20): array
    {
        try {
            return $this->transactionModel->getByUserId($userId, $filters, $page, $limit);
        } catch (\Exception $e) {
            $this->logger->error("Error getting transactions by user ID", [
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
     * Get transaction summary for a user
     * 
     * @param int $userId User ID
     * @param string $period Period (today, week, month, year, all)
     * @return array Transaction summary
     */
    public function getSummaryByUserId(int $userId, string $period = 'all'): array
    {
        try {
            return $this->transactionModel->getSummaryByUserId($userId, $period);
        } catch (\Exception $e) {
            $this->logger->error("Error getting transaction summary", [
                'user_id' => $userId,
                'period' => $period,
                'error' => $e->getMessage()
            ]);
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
     * Update transaction status
     * 
     * @param int $transactionId Transaction ID
     * @param string $status New status
     * @param array $additionalData Additional data to update
     * @return bool True if successful
     */
    public function updateStatus(int $transactionId, string $status, array $additionalData = []): bool
    {
        try {
            $updateData = array_merge(['status' => $status], $additionalData);
            
            $updated = $this->transactionModel->update($transactionId, $updateData);
            
            if ($updated) {
                // Create a log entry for the status update
                $this->createLog(
                    $transactionId,
                    $status,
                    "Transaction status updated to $status"
                );
            }
            
            return $updated;
        } catch (\Exception $e) {
            $this->logger->error("Error updating transaction status", [
                'transaction_id' => $transactionId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Export transactions to CSV
     * 
     * @param int $userId User ID
     * @param array $filters Optional filters
     * @return string|bool CSV content or false on failure
     */
    public function exportToCsv(int $userId, array $filters = [])
    {
        try {
            $transactions = $this->transactionModel->getByUserId($userId, $filters, 1, 1000);
            
            if (empty($transactions['data'])) {
                return "No transactions found to export.";
            }
            
            $csvData = [];
            
            // Add header row
            $csvData[] = [
                'ID', 'Reference', 'Amount', 'Currency', 'Status', 
                'Payment Method', 'Description', 'Date'
            ];
            
            // Add transaction rows
            foreach ($transactions['data'] as $transaction) {
                $csvData[] = [
                    $transaction['id'],
                    $transaction['reference'],
                    $transaction['amount'],
                    $transaction['currency'],
                    $transaction['status'],
                    $transaction['payment_method'] ?? 'N/A',
                    $transaction['description'] ?? 'N/A',
                    $transaction['created_at']
                ];
            }
            
            // Convert to CSV
            $csv = '';
            foreach ($csvData as $row) {
                $csv .= implode(',', array_map(function($cell) {
                    return '"' . str_replace('"', '""', $cell) . '"';
                }, $row)) . "\n";
            }
            
            return $csv;
        } catch (\Exception $e) {
            $this->logger->error("Error exporting transactions to CSV", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get recent transactions for a user
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of transactions
     * @return array Recent transactions
     */
    public function getRecentByUserId(int $userId, int $limit = 5): array
    {
        try {
            return $this->transactionModel->getRecentByUserId($userId, $limit);
        } catch (\Exception $e) {
            $this->logger->error("Error getting recent transactions", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get transaction chart data for a user
     * 
     * @param int $userId User ID
     * @param string $period Period (week, month, year)
     * @return array Chart data
     */
    public function getChartDataByUserId(int $userId, string $period = 'week'): array
    {
        try {
            return $this->transactionModel->getChartDataByUserId($userId, $period);
        } catch (\Exception $e) {
            $this->logger->error("Error getting transaction chart data", [
                'user_id' => $userId,
                'period' => $period,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Calculate transaction fee
     * 
     * @param float $amount Transaction amount
     * @param array $feesConfig Fee configuration
     * @return float Calculated fee
     */
    public function calculateFee(float $amount, array $feesConfig): float
    {
        $fee = 0;
        
        // Fixed fee
        if (isset($feesConfig['fixed_fee'])) {
            $fee += floatval($feesConfig['fixed_fee']);
        }
        
        // Percentage fee
        if (isset($feesConfig['percentage_fee'])) {
            $fee += ($amount * floatval($feesConfig['percentage_fee']) / 100);
        }
        
        // Cap fee if configured
        if (isset($feesConfig['cap']) && $fee > floatval($feesConfig['cap'])) {
            $fee = floatval($feesConfig['cap']);
        }
        
        return round($fee, 2);
    }
}