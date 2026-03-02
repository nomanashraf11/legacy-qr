#!/bin/bash
# Production Deployment Script
# Run this script on your production server after pulling from git

set -e

echo "🚀 Starting production deployment..."

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

# 6. Build frontend with production environment
echo "🔨 Building frontend for production..."
cd frontend
npm install
export VITE_API_BASE_URL="https://www.livinglegacyqr.xyz/api"
export VITE_GOOGLE_API_KEY="AIzaSyBSF_cWChkYEVRE337dWmKl1usv9asM1As"
export VITE_SPOTIFY_CLIENT_ID="a079012386e644ba81a345fed291157b"
export VITE_LIVE_URL="https://qr.livinglegacyqr.com/"
export VITE_BASE_URL="https://legacy.livinglegacyqr.com/"
npm run build
cd ..

# 7. Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # Adjust user/group as needed

echo "✅ Deployment complete!"
echo ""
echo "📝 Next steps:"
echo "   1. Verify .env file has correct production values"
echo "   2. Test the application"
echo "   3. Restart your web server if needed"
