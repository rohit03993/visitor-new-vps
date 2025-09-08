#!/bin/bash

# VMS CRM Server Setup Script for Hostinger VPS
# This script configures your VPS server for Laravel deployment

echo "🚀 Starting VPS Server Setup for VMS CRM..."

# Update system packages
echo "📦 Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install required software
echo "🔧 Installing required software..."

# Install Nginx
echo "🌐 Installing Nginx..."
sudo apt install nginx -y

# Install MySQL
echo "🗄️ Installing MySQL..."
sudo apt install mysql-server -y

# Install PHP 8.3 and extensions
echo "🐘 Installing PHP 8.3 and extensions..."
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.3 php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd php8.3-cli php8.3-common php8.3-bcmath php8.3-intl php8.3-readline php8.3-tokenizer php8.3-fileinfo -y

# Install Composer
echo "📦 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Certbot for SSL
echo "🔒 Installing Certbot for SSL..."
sudo apt install certbot python3-certbot-nginx -y

# Start and enable services
echo "🔄 Starting and enabling services..."
sudo systemctl start nginx
sudo systemctl enable nginx
sudo systemctl start mysql
sudo systemctl enable mysql
sudo systemctl start php8.3-fpm
sudo systemctl enable php8.3-fpm

# Configure MySQL
echo "🗄️ Configuring MySQL..."
sudo mysql_secure_installation

# Create application directory
echo "📁 Creating application directory..."
sudo mkdir -p /var/www/motionagra.com
sudo chown -R $USER:$USER /var/www/motionagra.com

echo "✅ Server setup completed!"
echo ""
echo "📋 Next steps:"
echo "1. Upload your VMS CRM files to /var/www/motionagra.com"
echo "2. Configure your .env file"
echo "3. Set up database"
echo "4. Configure Nginx virtual host"
echo "5. Install SSL certificate"
echo ""
echo "🎯 Your VPS is ready for VMS CRM deployment!"