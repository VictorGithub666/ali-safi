#!/bin/bash
# Deployment script for Ali-Safi on Alwaysdata

echo "🚀 Starting deployment..."

# Pull latest code
echo "📦 Pulling latest code from GitHub..."
git pull origin main

# Install/update PHP dependencies
echo "📚 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Install/update Node dependencies and build
echo "🎨 Building frontend assets..."
npm ci --omit=dev
npm run build

# Clear and cache configurations
echo "🧹 Clearing and caching configurations..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
echo "🔧 Fixing permissions..."
chmod -R 775 storage bootstrap/cache

# Create storage link if missing
php artisan storage:link

echo "✅ Deployment complete!"