FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Create SQLite database directory
RUN mkdir -p /var/www/var && chmod 777 /var/www/var

# Generate JWT keys
RUN mkdir -p /var/www/config/jwt && \
    openssl genpkey -out /var/www/config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:jwt_passphrase && \
    openssl pkey -in /var/www/config/jwt/private.pem -out /var/www/config/jwt/public.pem -pubout -passin pass:jwt_passphrase

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/var

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]