#!/bin/sh

# Replace the port in Nginx config
sed -i "s/\${PORT}/${PORT:-10000}/g" /etc/nginx/nginx.conf

# Ensure permissions are correct at runtime
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear caches and optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Start Supervisor
/usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
