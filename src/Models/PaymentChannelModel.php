<?php
namespace Kipay\Models;

use Kipay\Database\Database;

/**
 * PaymentChannelModel Class for Kipay Payment Gateway
 * 
 * This class handles all database operations related to payment channels.
 */
class PaymentChannelModel
{
    /**
     * @var \Kipay\Database\Database
     */
    protected $db;
    
    /**
     * PaymentChannelModel constructor
     */
    public function __construct()
    {
        $this->db = new Database();
    }
    
    /**
     * Create a new payment channel
     * 
     * @param array $data Payment channel data
     * @return array|bool Created payment channel or false on failure
     */
    public function create(array $data)
    {
        try {
            $id = $this->db->insert('payment_channels', $data);
            
            if (!$id) {
                return false;
            }
            
            return $this->getById($id);
        } catch (\Exception $e) {
            error_log("Error creating payment channel: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a payment channel
     * 
     * @param int $id Payment channel ID
     * @param array $data Update data
     * @return bool True if successful
     */
    public function update(int $id, array $data): bool
    {
        try {
            return $this->db->update('payment_channels', $id, $data);
        } catch (\Exception $e) {
            error_log("Error updating payment channel: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a payment channel
     * 
     * @param int $id Payment channel ID
     * @return bool True if successful
     */
    public function delete(int $id): bool
    {
        try {
            return $this->db->delete('payment_channels', $id);
        } catch (\Exception $e) {
            error_log("Error deleting payment channel: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get payment channel by ID
     * 
     * @param int $id Payment channel ID
     * @return array|bool Payment channel data or false if not found
     */
    public function getById(int $id)
    {
        try {
            return $this->db->getById('payment_channels', $id);
        } catch (\Exception $e) {
            error_log("Error getting payment channel by ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get payment channels by user ID
     * 
     * @param int $userId User ID
     * @return array Payment channels
     */
    public function getByUserId(int $userId): array
    {
        try {
            $query = "SELECT * FROM payment_channels WHERE user_id = :user_id ORDER BY is_default DESC, name ASC";
            $params = ['user_id' => $userId];
            
            $result = $this->db->query($query, $params);
            
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error getting payment channels by user ID: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get active payment channels by user ID
     * 
     * @param int $userId User ID
     * @return array Active payment channels
     */
    public function getActiveByUserId(int $userId): array
    {
        try {
            $query = "SELECT * FROM payment_channels WHERE user_id = :user_id AND is_active = 1 ORDER BY is_default DESC, name ASC";
            $params = ['user_id' => $userId];
            
            $result = $this->db->query($query, $params);
            
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error getting active payment channels: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get default payment channel for a user
     * 
     * @param int $userId User ID
     * @return array|bool Default payment channel or false if none
     */
    public function getDefaultForUser(int $userId)
    {
        try {
            $query = "SELECT * FROM payment_channels WHERE user_id = :user_id AND is_default = 1 LIMIT 1";
            $params = ['user_id' => $userId];
            
            $result = $this->db->query($query, $params);
            
            return $result ? $result[0] : false;
        } catch (\Exception $e) {
            error_log("Error getting default payment channel: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear default flag for all channels of a user
     * 
     * @param int $userId User ID
     * @return bool True if successful
     */
    public function clearDefaultForUser(int $userId): bool
    {
        try {
            $query = "UPDATE payment_channels SET is_default = 0 WHERE user_id = :user_id";
            $params = ['user_id' => $userId];
            
            return $this->db->execute($query, $params);
        } catch (\Exception $e) {
            error_log("Error clearing default flag: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a payment channel has transactions
     * 
     * @param int $channelId Payment channel ID
     * @return bool True if channel has transactions
     */
    public function channelHasTransactions(int $channelId): bool
    {
        try {
            $query = "SELECT COUNT(*) as count FROM transactions WHERE payment_channel_id = :channel_id LIMIT 1";
            $params = ['channel_id' => $channelId];
            
            $result = $this->db->query($query, $params);
            
            return $result && $result[0]['count'] > 0;
        } catch (\Exception $e) {
            error_log("Error checking channel transactions: " . $e->getMessage());
            return true; // Assume it has transactions on error to prevent accidental deletion
        }
    }
    
    /**
     * Get transaction count by payment channel
     * 
     * @param int $userId User ID
     * @return array Channel transaction counts
     */
    public function getTransactionCountsByUser(int $userId): array
    {
        try {
            $query = "SELECT 
                pc.id,
                pc.name,
                COUNT(t.id) as transaction_count,
                SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN t.status = 'completed' THEN t.amount ELSE 0 END) as completed_amount
                FROM payment_channels pc
                LEFT JOIN transactions t ON pc.id = t.payment_channel_id
                WHERE pc.user_id = :user_id
                GROUP BY pc.id
                ORDER BY transaction_count DESC";
            
            $params = ['user_id' => $userId];
            
            $result = $this->db->query($query, $params);
            
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error getting transaction counts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get payment channels by provider
     * 
     * @param int $userId User ID
     * @param string $provider Provider name
     * @return array Payment channels
     */
    public function getByProvider(int $userId, string $provider): array
    {
        try {
            $query = "SELECT * FROM payment_channels WHERE user_id = :user_id AND provider = :provider";
            $params = [
                'user_id' => $userId,
                'provider' => $provider
            ];
            
            $result = $this->db->query($query, $params);
            
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error getting channels by provider: " . $e->getMessage());
            return [];
        }
    }
}