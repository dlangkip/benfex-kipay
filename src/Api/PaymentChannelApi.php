<?php
namespace Kipay\Api;

use Kipay\Core\PaymentChannel;
use Kipay\Utils\Response;
use Kipay\Utils\Validator;

/**
 * PaymentChannelApi Class for Kipay Payment Gateway
 * 
 * This class handles all API endpoints related to payment channels.
 */
class PaymentChannelApi extends ApiController
{
    /**
     * @var \Kipay\Core\PaymentChannel
     */
    protected $paymentChannel;
    
    /**
     * @var \Kipay\Utils\Validator
     */
    protected $validator;
    
    /**
     * PaymentChannelApi constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->paymentChannel = new PaymentChannel();
        $this->validator = new Validator();
    }
    
    /**
     * Create a new payment channel
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
        $requiredFields = ['name', 'provider'];
        $missingFields = $this->validator->validateRequired($data, $requiredFields);
        
        if (!empty($missingFields)) {
            $this->response->badRequest('Missing required fields: ' . implode(', ', $missingFields));
            return;
        }
        
        // Validate provider
        $providers = $this->paymentChannel->getSupportedProviders();
        $supportedProviders = array_column($providers, 'id');
        
        if (!in_array($data['provider'], $supportedProviders)) {
            $this->response->badRequest('Unsupported payment provider. Supported providers are: ' . implode(', ', $supportedProviders));
            return;
        }
        
        // Get provider requirements
        $providerRequirements = $this->paymentChannel->getProviderRequirements($data['provider']);
        
        // Validate config
        if (!isset($data['config']) || !is_array($data['config'])) {
            $this->response->badRequest('Configuration is required for this provider');
            return;
        }
        
        // Check required config fields
        $missingConfigFields = [];
        foreach ($providerRequirements['required_fields'] as $field) {
            if (!isset($data['config'][$field]) || empty($data['config'][$field])) {
                $missingConfigFields[] = $field;
            }
        }
        
        if (!empty($missingConfigFields)) {
            $this->response->badRequest('Missing required configuration fields: ' . implode(', ', $missingConfigFields));
            return;
        }
        
        // Add user_id to data
        $data['user_id'] = $this->user['id'];
        
        // Create the payment channel
        $channel = $this->paymentChannel->create($data);
        
        if (!$channel) {
            $this->response->serverError('Failed to create payment channel');
            return;
        }
        
        // Return success response
        $this->response->created([
            'channel' => $channel,
            'message' => 'Payment channel created successfully'
        ]);
    }
    
    /**
     * Update a payment channel
     * 
     * @param int $id Payment channel ID
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
            $this->response->badRequest('Invalid payment channel ID');
            return;
        }
        
        // Get existing channel
        $channel = $this->paymentChannel->getById($id, true);
        
        if (!$channel) {
            $this->response->notFound('Payment channel not found');
            return;
        }
        
        // Check if channel belongs to authenticated user
        if ($channel['user_id'] != $this->user['id']) {
            $this->response->forbidden('You do not have permission to update this payment channel');
            return;
        }
        
        // Get and validate request data
        $data = $this->request->getJson();
        
        // Handle provider change
        if (isset($data['provider']) && $data['provider'] != $channel['provider']) {
            // Validate provider
            $providers = $this->paymentChannel->getSupportedProviders();
            $supportedProviders = array_column($providers, 'id');
            
            if (!in_array($data['provider'], $supportedProviders)) {
                $this->response->badRequest('Unsupported payment provider. Supported providers are: ' . implode(', ', $supportedProviders));
                return;
            }
            
            // Get provider requirements
            $providerRequirements = $this->paymentChannel->getProviderRequirements($data['provider']);
            
            // Validate config
            if (!isset($data['config']) || !is_array($data['config'])) {
                $this->response->badRequest('Configuration is required when changing provider');
                return;
            }
            
            // Check required config fields
            $missingConfigFields = [];
            foreach ($providerRequirements['required_fields'] as $field) {
                if (!isset($data['config'][$field]) || empty($data['config'][$field])) {
                    $missingConfigFields[] = $field;
                }
            }
            
            if (!empty($missingConfigFields)) {
                $this->response->badRequest('Missing required configuration fields: ' . implode(', ', $missingConfigFields));
                return;
            }
        }
        
        // Update the payment channel
        $updated = $this->paymentChannel->update($id, $data);
        
        if (!$updated) {
            $this->response->serverError('Failed to update payment channel');
            return;
        }
        
        // Get updated channel
        $updatedChannel = $this->paymentChannel->getById($id);
        
        // Return success response
        $this->response->success([
            'channel' => $updatedChannel,
            'message' => 'Payment channel updated successfully'
        ]);
    }
    
    /**
     * Delete a payment channel
     * 
     * @param int $id Payment channel ID
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
            $this->response->badRequest('Invalid payment channel ID');
            return;
        }
        
        // Get existing channel
        $channel = $this->paymentChannel->getById($id);
        
        if (!$channel) {
            $this->response->notFound('Payment channel not found');
            return;
        }
        
        // Check if channel belongs to authenticated user
        if ($channel['user_id'] != $this->user['id']) {
            $this->response->forbidden('You do not have permission to delete this payment channel');
            return;
        }
        
        // Delete the payment channel
        $deleted = $this->paymentChannel->delete($id);
        
        if (!$deleted) {
            $this->response->serverError('Failed to delete payment channel. The channel may have transactions associated with it.');
            return;
        }
        
        // Return success response
        $this->response->success([
            'message' => 'Payment channel deleted successfully'
        ]);
    }
    
    /**
     * Get a payment channel
     * 
     * @param int $id Payment channel ID
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
            $this->response->badRequest('Invalid payment channel ID');
            return;
        }
        
        // Get channel
        $channel = $this->paymentChannel->getById($id);
        
        if (!$channel) {
            $this->response->notFound('Payment channel not found');
            return;
        }
        
        // Check if channel belongs to authenticated user
        if ($channel['user_id'] != $this->user['id']) {
            $this->response->forbidden('You do not have permission to view this payment channel');
            return;
        }
        
        // Return success response
        $this->response->success([
            'channel' => $channel
        ]);
    }
    
    /**
     * List all payment channels for authenticated user
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
        
        // Get active_only parameter
        $activeOnly = $this->request->getQueryParam('active_only', 'false') === 'true';
        
        // Get channels
        $channels = $this->paymentChannel->getByUserId($this->user['id'], $activeOnly);
        
        // Return success response
        $this->response->success([
            'channels' => $channels
        ]);
    }
    
    /**
     * Get public configuration for a payment channel
     * 
     * @param int $id Payment channel ID
     * @return void
     */
    public function getPublicConfig(int $id = 0): void
    {
        // Check request method
        if ($this->request->method !== 'GET') {
            $this->response->methodNotAllowed(['GET']);
            return;
        }
        
        // Validate ID
        if ($id <= 0) {
            $this->response->badRequest('Invalid payment channel ID');
            return;
        }
        
        // Get channel configuration
        $config = $this->paymentChannel->getPaymentChannelConfig($id);
        
        if (!$config) {
            $this->response->notFound('Payment channel not found or inactive');
            return;
        }
        
        // Return success response
        $this->response->success([
            'config' => $config
        ]);
    }
    
    /**
     * Set a payment channel as default
     * 
     * @param int $id Payment channel ID
     * @return void
     */
    public function setDefault(int $id = 0): void
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
            $this->response->badRequest('Invalid payment channel ID');
            return;
        }
        
        // Get existing channel
        $channel = $this->paymentChannel->getById($id);
        
        if (!$channel) {
            $this->response->notFound('Payment channel not found');
            return;
        }
        
        // Check if channel belongs to authenticated user
        if ($channel['user_id'] != $this->user['id']) {
            $this->response->forbidden('You do not have permission to update this payment channel');
            return;
        }
        
        // Set as default
        $updated = $this->paymentChannel->setAsDefault($id);
        
        if (!$updated) {
            $this->response->serverError('Failed to set payment channel as default');
            return;
        }
        
        // Return success response
        $this->response->success([
            'message' => 'Payment channel set as default successfully'
        ]);
    }
    
    /**
     * Get supported payment providers
     * 
     * @return void
     */
    public function getProviders(): void
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
        
        // Get providers
        $providers = $this->paymentChannel->getSupportedProviders();
        
        // Return success response
        $this->response->success([
            'providers' => $providers
        ]);
    }
    
    /**
     * Get provider configuration requirements
     * 
     * @param string $provider Provider name
     * @return void
     */
    public function getProviderRequirements(string $provider = ''): void
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
        
        // Validate provider
        if (empty($provider)) {
            $this->response->badRequest('Provider name is required');
            return;
        }
        
        // Get provider requirements
        $requirements = $this->paymentChannel->getProviderRequirements($provider);
        
        if (!$requirements) {
            $this->response->notFound('Unsupported payment provider');
            return;
        }
        
        // Return success response
        $this->response->success([
            'requirements' => $requirements
        ]);
    }
}