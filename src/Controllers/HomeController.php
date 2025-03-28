<?php
namespace Kipay\Controllers;

use Kipay\Config\AppConfig;

/**
 * HomeController Class for Kipay Payment Gateway
 * 
 * This class handles the homepage and other public pages.
 */
class HomeController
{
    /**
     * @var \Kipay\Config\AppConfig
     */
    protected $config;
    
    /**
     * HomeController constructor
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
        $templateFile = KIPAY_PATH . '/src/Templates/' . $template . '.php';
        
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            echo "Template not found: $template";
        }
    }
    
    /**
     * Homepage
     * 
     * @return void
     */
    public function index(): void
    {
        // Render homepage template
        $this->render('home', [
            'page_title' => 'Home'
        ]);
    }
}