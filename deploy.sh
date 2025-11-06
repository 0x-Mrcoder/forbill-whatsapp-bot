#!/bin/bash

# Railway deploy script
set -e

echo "Starting Railway deployment..."

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Install Node dependencies
echo "Installing Node dependencies..."
npm ci --only=production

# Build assets
echo "Building assets..."
npm run build

# Laravel optimizations
echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache  
php artisan view:cache

echo "Deployment preparation complete!"