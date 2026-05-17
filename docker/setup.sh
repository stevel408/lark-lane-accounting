#!/bin/bash
set -e

echo "Waiting for MariaDB to be ready..."
# Use PHP to wait for the database connection
php -r '
$host = "db";
$user = "fa_user";
$pass = "fa_password";
$max_attempts = 30;
$attempt = 0;
while ($attempt < $max_attempts) {
    $mysqli = @new mysqli($host, $user, $pass);
    if (!$mysqli->connect_error) {
        exit(0);
    }
    $attempt++;
    sleep(1);
}
exit(1);
'

echo "Database is ready. Running FrontAccounting installation script..."
php /var/www/html/docker/install.php

echo "Done! You can now access FrontAccounting at http://localhost:8081"
echo "Login: admin / password"
