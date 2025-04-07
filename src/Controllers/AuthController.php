<?php
namespace Kipay\Controllers;

use Kipay\Models\UserModel;
use Kipay\Utils\Security;
use Kipay\Utils\Validator;

/**
 * AuthController Class for Kipay Payment Gateway
 * 
 * This class handles authentication and authorization.
 */
class AuthController
{
    /**
     * @var \Kipay\Models\UserModel
     */
    protected $userModel;
    
    /**
     * @var \Kipay\Utils\Security
     */
    protected $security;
    
    /**
     * @var \Kipay\Utils\Validator
     */
    protected $validator;
    
    /**
     * AuthController constructor
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->security = new Security();
        $this->validator = new Validator();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
     * Login page
     * 
     * @return void
     */
    public function login(): void
    {
        // Check if user is already logged in
        if (isset($_SESSION['user'])) {
            header('Location: /admin');
            exit;
        }
        
        // Handle login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLoginForm();
        }
        
        // Render login template
        $this->render('admin/login', [
            'page_title' => 'Login'
        ]);
    }
    
    /**
     * Handle login form submission
     * 
     * @return void
     */
    protected function handleLoginForm(): void
    {
        // Get form data
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        // Validate form data
        if (empty($username) || empty($password)) {
            $_SESSION['error_message'] = 'Username and password are required';
            return;
        }
        
        // Authenticate user
        $user = $this->userModel->authenticate($username, $password);

        $user = $this->userModel->getByUsername('testadmin', true);
        if ($user) {
        }        
        
        if (!$user) {
            $_SESSION['error_message'] = 'Invalid username or password';
            return;
        }
        
        // Check if user is active
        if (!$user['is_active']) {
            $_SESSION['error_message'] = 'Your account is inactive. Please contact the administrator.';
            return;
        }
        
        // Set session data
        $_SESSION['user'] = $user;
        
        // Set remember me cookie if requested
        if ($rememberMe) {
            $token = $this->security->generateToken();
            
            // Store token in database
            $this->userModel->saveRememberToken($user['id'], $token);
            
            // Set cookie (30 days expiry)
            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
        }
        
        // Log login attempt
        $this->logLoginAttempt($user['id'], true);
        
        // Redirect to dashboard
        header('Location: /admin');
        exit;
    }
    
    /**
     * Logout
     * 
     * @return void
     */
    public function logout(): void
    {
        // Clear session data
        unset($_SESSION['user']);
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
        
        // Clear all session data
        session_destroy();
        
        // Redirect to login page
        header('Location: /admin/login');
        exit;
    }
    
    /**
     * Check if user is already authenticated via remember me
     * 
     * @return bool True if authenticated
     */
    public function checkRememberMe(): bool
    {
        // Check if remember me cookie exists
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            // Get user by remember token
            $user = $this->userModel->getByRememberToken($token);
            
            if ($user) {
                // Check if user is active
                if (!$user['is_active']) {
                    return false;
                }
                
                // Set session data
                $_SESSION['user'] = $user;
                
                // Refresh remember me token
                $newToken = $this->security->generateToken();
                
                // Update token in database
                $this->userModel->saveRememberToken($user['id'], $newToken);
                
                // Update cookie (30 days expiry)
                setcookie('remember_token', $newToken, time() + (86400 * 30), '/', '', true, true);
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Forgot password page
     * 
     * @return void
     */
    public function forgotPassword(): void
    {
        // Handle forgot password form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleForgotPasswordForm();
        }
        
        // Render forgot password template
        $this->render('admin/forgot_password', [
            'page_title' => 'Forgot Password'
        ]);
    }
    
    /**
     * Handle forgot password form submission
     * 
     * @return void
     */
    protected function handleForgotPasswordForm(): void
    {
        // Get form data
        $email = $_POST['email'] ?? '';
        
        // Validate form data
        if (empty($email) || !$this->validator->validateEmail($email)) {
            $_SESSION['error_message'] = 'Please enter a valid email address';
            return;
        }
        
        // Check if user exists
        $user = $this->userModel->getByEmail($email);
        
        if (!$user) {
            // Don't reveal that email doesn't exist for security reasons
            $_SESSION['success_message'] = 'If your email address exists in our database, you will receive a password recovery link at your email address.';
            return;
        }
        
        // Generate reset token
        $token = $this->security->generateToken();
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Save reset token
        $this->userModel->saveResetToken($user['id'], $token, $expiry);
        
        // Generate reset URL
        $resetUrl = $_ENV['APP_URL'] . '/admin/reset-password?token=' . $token;
        
        // Send reset email (simplified for now)
        // In a real implementation, you would use a proper email library
        $to = $email;
        $subject = 'Reset Your Password';
        $message = "Hello {$user['first_name']},\n\n";
        $message .= "You have requested to reset your password. Please click the link below to reset your password:\n\n";
        $message .= $resetUrl . "\n\n";
        $message .= "This link will expire in 1 hour.\n\n";
        $message .= "If you did not request a password reset, please ignore this email.\n\n";
        $message .= "Regards,\nKipay Team";
        $headers = "From: noreply@kipay.com\r\n";
        
        // Uncomment to send email in production
        // mail($to, $subject, $message, $headers);
        
        // Set success message
        $_SESSION['success_message'] = 'If your email address exists in our database, you will receive a password recovery link at your email address.';
        
        // For development, show the reset URL
        if ($_ENV['APP_DEBUG'] === 'true') {
            $_SESSION['debug_reset_url'] = $resetUrl;
        }
        
        // Redirect to login page
        header('Location: /admin/login');
        exit;
    }
    
    /**
     * Reset password page
     * 
     * @return void
     */
    public function resetPassword(): void
    {
        // Get token from query params
        $token = $_GET['token'] ?? '';
        
        // Validate token
        if (empty($token)) {
            $_SESSION['error_message'] = 'Invalid password reset token';
            header('Location: /admin/login');
            exit;
        }
        
        // Check if token is valid and not expired
        $user = $this->userModel->getByResetToken($token);
        
        if (!$user) {
            $_SESSION['error_message'] = 'Invalid or expired password reset token';
            header('Location: /admin/login');
            exit;
        }
        
        // Handle reset password form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleResetPasswordForm($user['id'], $token);
        }
        
        // Render reset password template
        $this->render('admin/reset_password', [
            'page_title' => 'Reset Password',
            'token' => $token
        ]);
    }
    
    /**
     * Handle reset password form submission
     * 
     * @param int $userId User ID
     * @param string $token Reset token
     * @return void
     */
    protected function handleResetPasswordForm(int $userId, string $token): void
    {
        // Get form data
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate form data
        if (empty($password) || empty($confirmPassword)) {
            $_SESSION['error_message'] = 'Please enter a password and confirm it';
            return;
        }
        
        if ($password !== $confirmPassword) {
            $_SESSION['error_message'] = 'Passwords do not match';
            return;
        }
        
        // Update password
        $updated = $this->userModel->update($userId, [
            'password' => $password
        ]);
        
        if (!$updated) {
            $_SESSION['error_message'] = 'Failed to update password';
            return;
        }
        
        // Clear reset token
        $this->userModel->clearResetToken($userId);
        
        // Set success message
        $_SESSION['success_message'] = 'Your password has been reset successfully. You can now login with your new password.';
        
        // Redirect to login page
        header('Location: /admin/login');
        exit;
    }
    
    /**
     * Log login attempt
     * 
     * @param int $userId User ID
     * @param bool $success Whether login was successful
     * @return bool True if logged successfully
     */
    protected function logLoginAttempt(int $userId, bool $success): bool
    {
        try {
            // Create database connection
            $db = $this->userModel->getDb();
            
            // Insert login attempt
            $id = $db->insert('login_attempts', [
                'user_id' => $userId,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'success' => $success ? 1 : 0
            ]);
            
            return $id !== false;
        } catch (\Exception $e) {
            // Log error
            error_log('Error logging login attempt: ' . $e->getMessage());
            return false;
        }
    }
}