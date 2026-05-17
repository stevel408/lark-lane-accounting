FROM php:7.4-apache

# Install dependencies for PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zlib1g-dev \
    libicu-dev \
    g++ \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    mysqli \
    gd \
    gettext \
    zip \
    intl

# FrontAccounting runs through mod_php, which requires prefork. Make the MPM
# choice explicit so platform builders do not leave event/worker enabled too.
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork rewrite

WORKDIR /var/www/html

COPY . /var/www/html

RUN chmod +x /var/www/html/docker/railway-entrypoint.sh \
    && mkdir -p /var/www/html/company/0/backup \
        /var/www/html/company/0/images \
        /var/www/html/company/0/js_cache \
        /var/www/html/company/0/pdf_files \
        /var/www/html/company/0/reporting \
        /var/www/html/tmp \
    && chown -R www-data:www-data /var/www/html/company /var/www/html/tmp

ENTRYPOINT ["sh", "/var/www/html/docker/railway-entrypoint.sh"]
CMD ["apache2-foreground"]
