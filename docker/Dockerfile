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

# Enable Apache modules
RUN a2enmod rewrite

# The application files will be mounted via volume for development,
# but we set the working directory.
WORKDIR /var/www/html
