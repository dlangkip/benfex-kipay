<?php
namespace Kipay\Core;

use Kipay\Models\PaymentChannelModel;
use Kipay\Utils\Logger;
use Kipay\Utils\Validator;

/**
 * PaymentChannel Class for Kipay Payment Gateway
 * 
 * This class handles all operations related to payment channels
 * including creation, updating, and validation.
 */
class PaymentChannel
{
    /**
     * @var \Kipay\Models\PaymentChannelModel
     */
    protected $paymentChannelModel;
    
    /**
     * @var \Kipay\Utils\Logger
     */
    protected $logger;
    
    /**
     * @var \Kipay\Utils\Validator
     */
    protected $validator;
    
    /**
     * Supported payment providers
     * 
     * @var array
     */
    protected $supportedProviders = [
        'paystack' => [
            'name' => 'Paystack',
            'required_fields' => ['public_key', 'secret_key'],
            'optional_fields' => ['test_mode', 'webhook_url']
        ],
        'flutterwave' => [
            'name' => 'Flutterwave',
            'required_fields' => ['public_key', 'secret_key', 'encryption_key'],
            'optional_fields' => ['test_mode', 'webhook_url']
        ],
        'stripe' => [
            'name' => 'Stripe',
            'required_fields' => ['publishable_key', 'secret_key'],
            'optional_fields' => ['test_mode', 'webhook_secret']
        ],
        'manual' => [
            'name' => 'Manual Payment',
            'required_fields' => ['payment_instructions'],
            'optional_fields' => ['account_name', 'account_number', 'bank_name']
        ]
    ];
    
    /**
     * PaymentChannel constructor
     */
    public function __construct()
    {
        $this->paymentChannelModel = new PaymentChannelModel();
        $this->logger = new Logger('payment_channel');
        $this->validator = new Validator();
    }
    
    /**
     * Get a list of all supported payment providers
     * 
     * @return array List of supported providers
     */
    public function getSupportedProviders(): array
    {
        $providers = [];
        
        foreach ($this->supportedProviders as $key => $provider) {
            $providers[] = [
                'id' => $key,
                'name' => $provider['name']
            ];
        }
        
        return $providers;
    }
    
    /**
     * Get configuration requirements for a provider
     * 
     * @param string $provider Provider name
     * @return array|bool Provider configuration or false if not supported
     */
    public function getProviderRequirements(string $provider)
    {
        if (!isset($this->supportedProviders[$provider])) {
            return false;
        }
        
        return [
            'provider' => $provider,
            'name' => $this->supportedProviders[$provider]['name'],
            'required_fields' => $this->supportedProviders[$provider]['required_fields'],
            'optional_fields' => $this->supportedProviders[$provider]['optional_fields']
        ];
    }
    
    /**
     * Create a new payment channel
     * 
     * @param array $channelData Payment channel data
     * @return array|bool Created channel or false on failure
     */
    public function create(array $channelData)
    {
        try {
            // Validate required fields
            $requiredFields = ['user_id', 'name', 'provider'];
            foreach ($requiredFields as $field) {
                if (!isset($channelData[$field]) || empty($channelData[$field])) {
                    $this->logger->error("Missing required field for payment channel", ['field' => $field]);
                    return false;
                }
            }
            
            // Check if provider is supported
            if (!isset($this->supportedProviders[$channelData['provider']])) {
                $this->logger->error("Unsupported payment provider", ['provider' => $channelData['provider']]);
                return false;
            }
            
            // Validate provider configuration
            $config = $channelData['config'] ?? [];
            $providerRequirements = $this->supportedProviders[$channelData['provider']];
            
            foreach ($providerRequirements['required_fields'] as $field) {
                if (!isset($config[$field]) || empty($config[$field])) {
                    $this->logger->error("Missing required config field for provider", [
                        'provider' => $channelData['provider'],
                        'field' => $field
                    ]);
                    return false;
                }
            }
            
            // Encode configuration as JSON
            $channelData['config'] = json_encode($config);
            
            // Encode fees configuration as JSON if provided
            if (isset($channelData['fees_config']) && is_array($channelData['fees_config'])) {
                $channelData['fees_config'] = json_encode($channelData['fees_config']);
            }
            
            // Create the payment channel
            $channel = $this->paymentChannelModel->create($channelData);
            
            // If this is the first channel, set it as default
            if ($channel) {
                $existingChannels = $this->paymentChannelModel->getByUserId($channelData['user_id']);
                
                if (count($existingChannels) === 1) {
                    $this->paymentChannelModel->update($channel['id'], ['is_default' => true]);
                    $channel['is_default'] = true;
                }
            }
            
            return $channel;
        } catch (\Exception $e) {
            $this->logger->error("Error creating payment channel", [
                'error' => $e->getMessage(),
                'channel_data' => $channelData
            ]);
            return false;
        }
    }
    
    /**
     * Update a payment channel
     * 
     * @param int $channelId Channel ID
     * @param array $channelData Payment channel data
     * @return bool True if update was successful
     */
    public function update(int $channelId, array $channelData): bool
    {
        try {
            // Get existing channel
            $channel = $this->paymentChannelModel->getById($channelId);
            
            if (!$channel) {
                $this->logger->error("Payment channel not found", ['channel_id' => $channelId]);
                return false;
            }
            
            // Ensure the user_id is not changed
            if (isset($channelData['user_id']) && $channelData['user_id'] != $channel['user_id']) {
                $this->logger->error("Cannot change channel ownership", ['channel_id' => $channelId]);
                return false;
            }
            
            // Handle configuration update
            if (isset($channelData['config']) && is_array($channelData['config'])) {
                // If provider is changing, validate the new config
                if (isset($channelData['provider']) && $channelData['provider'] != $channel['provider']) {
                    if (!isset($this->supportedProviders[$channelData['provider']])) {
                        $this->logger->error("Unsupported payment provider", ['provider' => $channelData['provider']]);
                        return false;
                    }
                    
                    // Validate required fields for new provider
                    $providerRequirements = $this->supportedProviders[$channelData['provider']];
                    
                    foreach ($providerRequirements['required_fields'] as $field) {
                        if (!isset($channelData['config'][$field]) || empty($channelData['config'][$field])) {
                            $this->logger->error("Missing required config field for provider", [
                                'provider' => $channelData['provider'],
                                'field' => $field
                            ]);
                            return false;
                        }
                    }
                } else {
                    // If provider isn't changing, merge config with existing
                    $existingConfig = json_decode($channel['config'], true);
                    $channelData['config'] = array_merge($existingConfig, $channelData['config']);
                }
                
                // Encode configuration as JSON
                $channelData['config'] = json_encode($channelData['config']);
            }
            
            // Handle fees configuration update
            if (isset($channelData['fees_config']) && is_array($channelData['fees_config'])) {
                // Encode fees configuration as JSON
                $channelData['fees_config'] = json_encode($channelData['fees_config']);
            }
            
            // Handle default flag
            if (isset($channelData['is_default']) && $channelData['is_default']) {
                // Clear default flag from other channels for this user
                $this->paymentChannelModel->clearDefaultForUser($channel['user_id']);
            }
            
            // Update the payment channel
            return $this->paymentChannelModel->update($channelId, $channelData);
        } catch (\Exception $e) {
            $this->logger->error("Error updating payment channel", [
                'channel_id' => $channelId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Delete a payment channel
     * 
     * @param int $channelId Channel ID
     * @return bool True if deletion was successful
     */
    public function delete(int $channelId): bool
    {
        try {
            // Get existing channel
            $channel = $this->paymentChannelModel->getById($channelId);
            
            if (!$channel) {
                $this->logger->error("Payment channel not found", ['channel_id' => $channelId]);
                return false;
            }
            
            // Check if the channel has transactions
            $hasTransactions = $this->paymentChannelModel->channelHasTransactions($channelId);
            
            if ($hasTransactions) {
                $this->logger->error("Cannot delete payment channel with transactions", ['channel_id' => $channelId]);
                return false;
            }
            
            // Delete the payment channel
            $deleted = $this->paymentChannelModel->delete($channelId);
            
            // If this was the default channel, set another one as default
            if ($deleted && $channel['is_default']) {
                $otherChannels = $this->paymentChannelModel->getByUserId($channel['user_id']);
                
                if (!empty($otherChannels)) {
                    $this->paymentChannelModel->update($otherChannels[0]['id'], ['is_default' => true]);
                }
            }
            
            return $deleted;
        } catch (\Exception $e) {
            $this->logger->error("Error deleting payment channel", [
                'channel_id' => $channelId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get payment channel by ID
     * 
     * @param int $channelId Channel ID
     * @param bool $withConfig Include full configuration
     * @return array|bool Channel data or false on failure
     */
    public function getById(int $channelId, bool $withConfig = false)
    {
        try {
            $channel = $this->paymentChannelModel->getById($channelId);
            
            if (!$channel) {
                return false;
            }
            
            // Decode configuration if requested
            if ($withConfig) {
                $channel['config'] = json_decode($channel['config'], true);
                
                if (isset($channel['fees_config'])) {
                    $channel['fees_config'] = json_decode($channel['fees_config'], true);
                }
            } else {
                // Remove sensitive data
                unset($channel['config']);
                unset($channel['fees_config']);
            }
            
            return $channel;
        } catch (\Exception $e) {
            $this->logger->error("Error getting payment channel by ID", [
                'channel_id' => $channelId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get payment channels by user ID
     * 
     * @param int $userId User ID
     * @param bool $activeOnly Get only active channels
     * @return array List of payment channels
     */
    public function getByUserId(int $userId, bool $activeOnly = false): array
    {
        try {
            if ($activeOnly) {
                return $this->paymentChannelModel->getActiveByUserId($userId);
            } else {
                return $this->paymentChannelModel->getByUserId($userId);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error getting payment channels by user ID", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get default payment channel for a user
     * 
     * @param int $userId User ID
     * @return array|bool Default channel or false if none found
     */
    public function getDefaultForUser(int $userId)
    {
        try {
            return $this->paymentChannelModel->getDefaultForUser($userId);
        } catch (\Exception $e) {
            $this->logger->error("Error getting default payment channel", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Set a payment channel as default for a user
     * 
     * @param int $channelId Channel ID
     * @return bool True if successful
     */
    public function setAsDefault(int $channelId): bool
    {
        try {
            // Get existing channel
            $channel = $this->paymentChannelModel->getById($channelId);
            
            if (!$channel) {
                $this->logger->error("Payment channel not found", ['channel_id' => $channelId]);
                return false;
            }
            
            // Clear default flag from other channels for this user
            $this->paymentChannelModel->clearDefaultForUser($channel['user_id']);
            
            // Set this channel as default
            return $this->paymentChannelModel->update($channelId, ['is_default' => true]);
        } catch (\Exception $e) {
            $this->logger->error("Error setting payment channel as default", [
                'channel_id' => $channelId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}