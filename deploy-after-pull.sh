#!/bin/bash
# Run this on production after git pull to apply changes

set -e
echo "=== Clearing Laravel caches ==="
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo ""
echo "=== Running migrations ==="
php artisan migrate --force

echo ""
echo "=== Restarting queue workers (if any) ==="
php artisan queue:restart 2>/dev/null || true

echo ""
echo "=== Done. If using PHP-FPM, run: sudo systemctl restart php-fpm ==="
