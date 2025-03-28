# Kipay Installation Guide

This guide will walk you through the process of installing and setting up the Kipay Payment Gateway on your server.

## Prerequisites

Before installing Kipay, ensure that your server meets the following requirements:

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- mod_rewrite enabled (Apache) or equivalent URL rewriting for Nginx
- SSL certificate (required for production)
- Composer
- SSH access to your server (recommended)

## Installation Steps

### 1. Clone the Repository

Start by cloning the repository to your server:

```bash
git clone https://github.com/yourusername/kipay.git
cd kipay
```

Alternatively, you can download and extract the ZIP file to your server.

### 2. Install Dependencies

Use Composer to install the required dependencies:

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configure Environment

Copy the example environment file and update it with your details:

```bash
cp .env.example .env
```

Edit the `.env` file with your database credentials, Paystack API keys, and other settings:

```
# Database Configuration
DB_HOST=localhost
DB_NAME=kipay_db
DB_USER=your_db_user
DB_PASS=your_db_password

# Paystack Configuration
PAYSTACK_SECRET_KEY=sk_xxxx
PAYSTACK_PUBLIC_KEY=pk_xxxx
PAYSTACK_ENVIRONMENT=test  # Change to 'live' for production

# Application Configuration
APP_URL=https://your-domain.com/kipay
APP_DEBUG=false  # Set to 'true' during development
```

### 4. Create the Database

Create a new MySQL database for Kipay:

```sql
CREATE DATABASE kipay_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run Database Migrations

Import the database schema:

```bash
mysql -u your_db_user -p kipay_db < src/Database/Migrations/create_tables.sql
```

### 6. Set Directory Permissions

Set the appropriate permissions for directories that need to be writable:

```bash
chmod -R 755 .
chmod -R 777 logs
```

### 7. Configure Web Server

#### Apache Configuration

Create or modify your Apache virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/kipay/public
    
    <Directory "/path/to/kipay/public">
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/kipay_error.log
    CustomLog ${APACHE_LOG_DIR}/kipay_access.log combined
    
    # Redirect to HTTPS (recommended for production)
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>
```

Create a `.htaccess` file in the `public` directory (if it doesn't exist):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect to front controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### Nginx Configuration

Create or modify your Nginx server block:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    
    # Redirect to HTTPS (recommended for production)
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name your-domain.com;
    
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/certificate.key;
    
    root /path/to/kipay/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;  # Update with your PHP version
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    error_log /var/log/nginx/kipay_error.log;
    access_log /var/log/nginx/kipay_access.log;
}
```

### 8. Restart Web Server

Restart Apache or Nginx to apply the configuration changes:

```bash
# For Apache
sudo systemctl restart apache2

# For Nginx
sudo systemctl restart nginx
```

### 9. Create Admin User (if needed)

If you need to create an additional admin user, you can use the following SQL query:

```sql
INSERT INTO users (username, email, password, first_name, last_name, role)
VALUES ('admin', 'admin@yourdomain.com', '$2y$10$u7vhg8UKP2ZtU5LcI.3qb.cIlT5.by76AvF6uFJX8YwH4OgQYb.mW', 'Admin', 'User', 'admin');
```

This creates an admin user with the password "admin123". Remember to change this password immediately after login.

### 10. Configure Payment Providers

#### Paystack Configuration

1. Create a Paystack account at [paystack.com](https://paystack.com) if you don't have one.
2. Get your API keys from the Paystack dashboard.
3. Add your callback URL to Paystack: `https://your-domain.com/payment/verify`
4. Configure your webhook URL: `https://your-domain.com/webhook/paystack`

### 11. Test Your Installation

Access the admin dashboard at `https://your-domain.com/admin` and log in with the default credentials:

- Username: admin
- Password: admin123

## Troubleshooting

### Common Issues

#### Permission Denied
If you encounter permission errors, ensure that your web server user has the correct permissions:

```bash
chown -R www-data:www-data /path/to/kipay
```

#### 500 Internal Server Error
Check your server's error log:

```bash
# Apache
tail -n 50 /var/log/apache2/error.log

# Nginx
tail -n 50 /var/log/nginx/error.log
```

#### Database Connection Issues
Verify your database credentials in the `.env` file and ensure the MySQL server is running:

```bash
mysql -u your_db_user -p -e "SHOW DATABASES;"
```

#### Mod_Rewrite Not Enabled
For Apache, enable mod_rewrite:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## Security Recommendations

1. Change the default admin password immediately after installation.
2. Ensure your server has an SSL certificate installed and HTTPS is enforced.
3. Set proper file permissions to restrict access to sensitive files.
4. Keep your PHP and MySQL versions up to date with security patches.
5. Use a strong password for your database user.
6. Configure a firewall to restrict access to sensitive ports.

## Updating Kipay

To update Kipay to the latest version:

```bash
cd /path/to/kipay
git pull
composer install --no-dev --optimize-autoloader
```

If there are database changes, you'll need to run the migration script or apply the SQL changes manually.

## Need Help?

If you encounter any issues during installation, please:

1. Check the logs for error messages.
2. Refer to the [API Reference](api_reference.md) and [Configuration Guide](configuration.md).
3. Contact our support team at support@kipay.com.