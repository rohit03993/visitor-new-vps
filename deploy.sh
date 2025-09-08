#!/bin/bash

# VMS CRM Deployment Script for Hostinger VPS
# This script prepares your Laravel application for deployment

echo "🚀 Starting VMS CRM Deployment Preparation..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel root directory"
    exit 1
fi

echo "✅ Laravel application found"

# Install/update dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Generate application key if not exists
if [ ! -f ".env" ]; then
    echo "⚠️  .env file not found. Creating from .env.example..."
    cp .env.example .env
fi

# Generate key
echo "🔑 Generating application key..."
php artisan key:generate

# Clear all caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions (for Linux/Mac)
if [[ "$OSTYPE" == "linux-gnu"* ]] || [[ "$OSTYPE" == "darwin"* ]]; then
    echo "🔐 Setting file permissions..."
    chmod -R 755 storage
    chmod -R 755 bootstrap/cache
fi

echo "✅ Deployment preparation completed!"
echo ""
echo "📋 Next steps:"
echo "1. Upload this entire folder to your VPS"
echo "2. Configure your .env file on the server"
echo "3. Run database migrations"
echo "4. Set up web server configuration"
echo ""
echo "🎯 Your VMS CRM is ready for deployment!"