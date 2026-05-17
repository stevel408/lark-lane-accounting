#!/bin/sh
set -eu

PORT="${PORT:-80}"

sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9][0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p /var/www/html/company/0/backup \
    /var/www/html/company/0/images \
    /var/www/html/company/0/js_cache \
    /var/www/html/company/0/pdf_files \
    /var/www/html/company/0/reporting \
    /var/www/html/tmp
chown -R www-data:www-data /var/www/html/company /var/www/html/tmp

php /var/www/html/docker/wait_for_db.php

if [ "${FA_AUTO_SETUP:-0}" = "1" ]; then
    if php /var/www/html/docker/db_has_schema.php; then
        echo "Skipping setup; schema is already installed."
    else
        echo "Installing FrontAccounting schema..."
        php /var/www/html/docker/install.php
    fi
fi

exec apache2-foreground
