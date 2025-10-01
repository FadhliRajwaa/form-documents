# Use official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install zip pdo pdo_pgsql pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY . .

# Create uploads directory and set permissions
RUN mkdir -p uploads && chmod 755 uploads

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Enable Apache mod_rewrite (if needed)
RUN a2enmod rewrite

# Expose port (Render will use $PORT)
EXPOSE 80

# Create startup script to handle credentials.json from environment
RUN echo '#!/bin/bash\n\
# Check if GOOGLE_CREDENTIALS_JSON env var exists and create file\n\
if [ ! -z "$GOOGLE_CREDENTIALS_JSON" ]; then\n\
    echo "$GOOGLE_CREDENTIALS_JSON" > /var/www/html/credentials.json\n\
    echo "credentials.json created from environment variable"\n\
fi\n\
\n\
# Start Apache\n\
apache2-foreground\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Start Apache with startup script
CMD ["/usr/local/bin/start.sh"]
