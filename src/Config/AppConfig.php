<?php
namespace Kipay\Config;

use Kipay\Models\UserModel;

/**
 * Application Configuration Class for Kipay Payment Gateway
 * 
 * This class handles application-wide configuration.
 */
class AppConfig
{
    /**
     * @var string Application URL
     */
    protected $appUrl;
    
    /**
     * @var bool Debug mode
     */
    protected $debug;
    
    /**
     * @var array Settings from database
     */
    protected $settings = [];
    
    /**
     * AppConfig constructor
     */
    public function __construct()
    {
        $this->appUrl = $_ENV['APP_URL'] ?? 'https://kipay.benfex.net';
        $this->debug = isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true';
        
        // Load settings from database if available
        $this->loadSettings();
    }
    
    /**
     * Load settings from database
     * 
     * @return void
     */
    protected function loadSettings(): void
    {
        try {
            // Check if database connection is available
            if (class_exists('\\Kipay\\Database\\Database')) {
                $userModel = new UserModel();
                $db = $userModel->getDb();
                
                // Only load settings if database connection is successful
                if ($db) {
                    $query = "SELECT setting_key, setting_value FROM settings WHERE user_id IS NULL";
                    $result = $db->query($query);
                    
                    if ($result) {
                        foreach ($result as $row) {
                            $this->settings[$row['setting_key']] = $row['setting_value'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not available yet
        }
    }
    
    /**
     * Get a configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public function get(string $key, $default = null)
    {
        // Check settings
        if (isset($this->settings[$key])) {
            return $this->settings[$key];
        }
        
        // Check class properties
        $method = 'get' . str_replace('_', '', ucwords($key, '_'));
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        
        // Check environment variables
        $envKey = 'APP_' . strtoupper($key);
        if (isset($_ENV[$envKey])) {
            return $_ENV[$envKey];
        }
        
        return $default;
    }
    
    /**
     * Set a configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->settings[$key] = $value;
    }
    
    /**
     * Get all configuration values
     * 
     * @return array All configuration values
     */
    public function getAll(): array
    {
        return [
            'app_url' => $this->appUrl,
            'debug' => $this->debug,
            'settings' => $this->settings
        ];
    }
    
    /**
     * Get application URL
     * 
     * @return string Application URL
     */
    public function getAppUrl(): string
    {
        return $this->appUrl;
    }
    
    /**
     * Get debug mode
     * 
     * @return bool Debug mode
     */
    public function getDebug(): bool
    {
        return $this->debug;
    }
    
    /**
     * Save a setting to the database
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param int|null $userId User ID (null for global setting)
     * @param bool $isPublic Whether the setting is public
     * @return bool True if successful
     */
    public function saveSetting(string $key, $value, ?int $userId = null, bool $isPublic = false): bool
    {
        try {
            // Check if database connection is available
            if (class_exists('\\Kipay\\Database\\Database')) {
                $userModel = new UserModel();
                $db = $userModel->getDb();
                
                // Only save if database connection is successful
                if ($db) {
                    // Check if setting exists
                    $query = "SELECT id FROM settings WHERE setting_key = :key";
                    $params = ['key' => $key];
                    
                    if ($userId !== null) {
                        $query .= " AND user_id = :user_id";
                        $params['user_id'] = $userId;
                    } else {
                        $query .= " AND user_id IS NULL";
                    }
                    
                    $result = $db->query($query, $params);
                    
                    if (!empty($result)) {
                        // Update existing setting
                        $id = $result[0]['id'];
                        $update = $db->update('settings', $id, [
                            'setting_value' => $value,
                            'is_public' => $isPublic
                        ]);
                        
                        // Update local settings cache
                        if ($update && $userId === null) {
                            $this->settings[$key] = $value;
                        }
                        
                        return $update;
                    } else {
                        // Create new setting
                        $data = [
                            'setting_key' => $key,
                            'setting_value' => $value,
                            'is_public' => $isPublic,
                            'user_id' => $userId
                        ];
                        
                        $id = $db->insert('settings', $data);
                        
                        // Update local settings cache
                        if ($id && $userId === null) {
                            $this->settings[$key] = $value;
                        }
                        
                        return $id !== false;
                    }
                }
            }
            
            return false;
        } catch (\Exception $e) {
            // Silently fail if database is not available yet
            return false;
        }
    }
    
    /**
     * Delete a setting from the database
     * 
     * @param string $key Setting key
     * @param int|null $userId User ID (null for global setting)
     * @return bool True if successful
     */
    public function deleteSetting(string $key, ?int $userId = null): bool
    {
        try {
            // Check if database connection is available
            if (class_exists('\\Kipay\\Database\\Database')) {
                $userModel = new UserModel();
                $db = $userModel->getDb();
                
                // Only delete if database connection is successful
                if ($db) {
                    // Check if setting exists
                    $query = "SELECT id FROM settings WHERE setting_key = :key";
                    $params = ['key' => $key];
                    
                    if ($userId !== null) {
                        $query .= " AND user_id = :user_id";
                        $params['user_id'] = $userId;
                    } else {
                        $query .= " AND user_id IS NULL";
                    }
                    
                    $result = $db->query($query, $params);
                    
                    if (!empty($result)) {
                        // Delete setting
                        $id = $result[0]['id'];
                        $delete = $db->delete('settings', $id);
                        
                        // Update local settings cache
                        if ($delete && $userId === null && isset($this->settings[$key])) {
                            unset($this->settings[$key]);
                        }
                        
                        return $delete;
                    }
                }
            }
            
            return false;
        } catch (\Exception $e) {
            // Silently fail if database is not available yet
            return false;
        }
    }
}