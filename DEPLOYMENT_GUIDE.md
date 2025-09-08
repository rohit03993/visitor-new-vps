# 🚀 VMS CRM Deployment Guide - Hostinger VPS

## Overview
This guide will help you deploy your VMS CRM to your Hostinger VPS and make it live at `motionagra.com`.

## Prerequisites
- ✅ Hostinger VPS access
- ✅ SSH access to your server
- ✅ Domain `motionagra.com` pointing to your VPS
- ✅ Local VMS CRM application ready

## Step-by-Step Deployment

### 1. Prepare Local Application
```bash
# Run the deployment preparation script
chmod +x deploy.sh
./deploy.sh
```

### 2. Access Your VPS
```bash
# Connect to your VPS via SSH
ssh root@your-vps-ip
# or
ssh username@your-vps-ip
```

### 3. Setup Server Environment
```bash
# Run the server setup script
chmod +x server-setup.sh
./server-setup.sh
```

### 4. Upload Application Files
```bash
# Upload your entire VMS CRM folder to /var/www/motionagra.com
# You can use SCP, SFTP, or Git
```

### 5. Configure Environment
```bash
# Navigate to your application directory
cd /var/www/motionagra.com

# Copy environment file
cp .env.example .env

# Edit the .env file with your production settings
nano .env
```

### 6. Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE vms_crm;
CREATE USER 'vms_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON vms_crm.* TO 'vms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate

# Seed the database (if needed)
php artisan db:seed
```

### 7. Configure Nginx
```bash
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/motionagra.com
```

### 8. Install SSL Certificate
```bash
# Install SSL with Certbot
sudo certbot --nginx -d motionagra.com
```

### 9. Test Your Application
- Visit `https://motionagra.com`
- Test all functionality
- Verify SSL certificate

## Important Files to Configure

### .env File
```env
APP_NAME="VMS CRM"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://motionagra.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vms_crm
DB_USERNAME=vms_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### Nginx Configuration
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name motionagra.com www.motionagra.com;
    root /var/www/motionagra.com/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Troubleshooting

### Common Issues
1. **Permission Errors**: `sudo chown -R www-data:www-data /var/www/motionagra.com`
2. **PHP Not Working**: Check PHP-FPM status `sudo systemctl status php8.3-fpm`
3. **Database Connection**: Verify credentials in .env file
4. **SSL Issues**: Check Certbot logs `sudo certbot certificates`

### Useful Commands
```bash
# Check Nginx status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.3-fpm

# Check MySQL status
sudo systemctl status mysql

# View Nginx error logs
sudo tail -f /var/log/nginx/error.log

# View Laravel logs
tail -f storage/logs/laravel.log
```

## Security Checklist
- ✅ SSL certificate installed
- ✅ Database credentials secured
- ✅ File permissions set correctly
- ✅ Firewall configured
- ✅ Regular backups scheduled

## Support
If you encounter any issues during deployment, please share:
1. The exact error message
2. Which step you're on
3. Your server environment details

---

**🎯 Your VMS CRM will be live at: https://motionagra.com**