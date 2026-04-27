# Use PHP 8.2 FPM Alpine as base
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    nodejs \
    npm \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    mysql-client \
    curl

# Download TiDB Cloud CA Certificate
RUN curl https://cacerts.digicert.com/isrgrootx1.pem -o /etc/ssl/certs/isrgrootx1.pem

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
RUN npm install && npm run build

# Remove node_modules after build to save space
RUN rm -rf node_modules

# Copy Nginx and Supervisor configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Set permissions
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod 644 /etc/ssl/certs/isrgrootx1.pem

# Fix PHP-FPM logging permissions
RUN touch /var/log/php-fpm.log && \
    chown www-data:www-data /var/log/php-fpm.log && \
    sed -i 's/error_log = \/proc\/self\/fd\/2/error_log = \/var\/log\/php-fpm.log/g' /usr/local/etc/php-fpm.d/docker.conf || true

# Expose the port Render expects
EXPOSE 10000

# Start via entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
