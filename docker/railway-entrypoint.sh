#!/bin/sh
set -eu

PORT="${PORT:-80}"

a2dismod mpm_event mpm_worker >/dev/null 2>&1 || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

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

if php /var/www/html/docker/db_has_schema.php; then
    echo "Skipping setup; schema is already installed."
elif [ "${FA_AUTO_SETUP:-0}" = "1" ]; then
    echo "Installing FrontAccounting schema because FA_AUTO_SETUP=1..."
    php /var/www/html/docker/install.php
    php /var/www/html/docker/db_has_schema.php
else
    echo "Error: FrontAccounting schema is missing and FA_AUTO_SETUP is not set to 1." >&2
    echo "Set FA_AUTO_SETUP=1 for the first deploy, then set it back to 0 after setup succeeds." >&2
    exit 1
fi

exec apache2-foreground
