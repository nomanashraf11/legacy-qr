#!/bin/bash
# Production Server Deployment Script (Backend Only)
# Run this on production server after copying frontend build files
# This script handles backend deployment only

set -e

echo "🚀 Starting backend deployment..."

# 1. Pull latest changes from git
echo "📥 Pulling latest changes from git..."
git pull origin main  # or your main branch name

# 2. Install/update backend dependencies
echo "📦 Installing backend dependencies..."
composer install --no-dev --optimize-autoloader

# 3. Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Cache configuration for production
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Run migrations (if needed)
echo "🗄️  Running database migrations..."
php artisan migrate --force

# 6. Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "⚠️  Could not set ownership (may need sudo)"

echo "✅ Backend deployment complete!"
echo ""
echo "📝 Note: Frontend files should be copied separately using copy-build-to-server.sh"
echo "   If you haven't copied frontend files yet, do that now."
