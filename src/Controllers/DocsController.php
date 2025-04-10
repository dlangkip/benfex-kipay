<?php
namespace Kipay\Controllers;

use Kipay\Config\AppConfig;

/**
 * DocsController Class for Kipay Payment Gateway
 * 
 * This class handles the documentation pages.
 */
class DocsController
{
    /**
     * @var \Kipay\Config\AppConfig
     */
    protected $config;
    
    /**
     * DocsController constructor
     */
    public function __construct()
    {
        $this->config = new AppConfig();
    }
    
    /**
     * Render a template with data
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return void
     */
    protected function render(string $template, array $data = []): void
    {
        // Add site settings
        $data['site_name'] = $this->config->get('site_name', 'Kipay Payment Gateway');
        $data['site_url'] = $this->config->get('site_url', '/');
        $data['logo_url'] = $this->config->get('logo_url', '/assets/images/logo.png');
        
        // Extract data to variables
        extract($data);
        
        // Include template file
        $templateFile = KIPAY_PATH . '/src/Templates/docs/' . $template . '.php';
        
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            echo "Template not found: $template";
        }
    }
    
    /**
     * Documentation homepage
     * 
     * @return void
     */
    public function index(): void
    {
        $this->render('index', [
            'page_title' => 'Documentation',
            'active_page' => 'home'
        ]);
    }
    
    /**
     * API documentation
     * 
     * @return void
     */
    public function api(): void
    {
        $this->render('api', [
            'page_title' => 'API Documentation',
            'active_page' => 'api'
        ]);
    }
    
    /**
     * Getting started documentation
     * 
     * @return void
     */
    public function gettingStarted(): void
    {
        $this->render('getting-started', [
            'page_title' => 'Getting Started',
            'active_page' => 'getting-started'
        ]);
    }
    
    /**
     * Webhooks documentation
     * 
     * @return void
     */
    public function webhooks(): void
    {
        $this->render('webhooks', [
            'page_title' => 'Webhooks',
            'active_page' => 'webhooks'
        ]);
    }
    
    /**
     * Payment channels documentation
     * 
     * @return void
     */
    public function paymentChannels(): void
    {
        $this->render('payment-channels', [
            'page_title' => 'Payment Channels',
            'active_page' => 'payment-channels'
        ]);
    }
}