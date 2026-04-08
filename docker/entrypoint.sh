#!/bin/sh

# Replace the port in Nginx config
sed -i "s/\${PORT}/${PORT:-10000}/g" /etc/nginx/nginx.conf

# Wait for DB to be ready (optional but good)
# sleep 5

# Clear caches and optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Start Supervisor
/usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
