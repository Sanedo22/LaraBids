#!/bin/sh

# Replace the port in Nginx config
sed -i "s/\${PORT}/${PORT:-10000}/g" /etc/nginx/nginx.conf

# Ensure storage and bootstrap/cache are writable
# We try chown, but if it fails (not root), we use chmod 777
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create log file if it doesn't exist and make it writable
touch /var/www/html/storage/logs/laravel.log
chmod 664 /var/www/html/storage/logs/laravel.log 2>/dev/null || true

# Clear caches and optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Run migrations
php artisan migrate --force

# Start Supervisor
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
