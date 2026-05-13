# Docker Environment

The environment is designed to provide a robust, consistent local setup on macOS (or any OS with Docker).

## Services

### `web` (Apache + PHP 7.4)
*   **Base Image**: `php:7.4-apache`
*   **Port**: `8080` (External) -> `80` (Internal)
*   **PHP Extensions**: `mysqli`, `gd`, `gettext`, `zip`, `intl`.
*   **Volumes**:
    *   `.` (Project root) -> `/var/www/html`
    *   `./docker/php.ini` -> `/usr/local/etc/php/conf.d/frontaccounting.ini`
*   **Environment Variables**:
    *   `TZ=UTC`
    *   `FA_SQL_TEMPLATE`: Defines the SQL file used during automated setup (e.g., `real_estate_coa.sql`).

### `db` (MariaDB 10.5)
*   **Image**: `mariadb:10.5`
*   **Port**: `3306`
*   **Credentials**:
    *   Root Password: `password`
    *   Database: `frontacc`
    *   User: `fa_user` / `fa_password`
*   **Persistence**: Data is persisted in a named volume `db_data`.

## Networking
*   The `web` service connects to the database using the hostname `db`.
*   The application `config_db.php` is configured to use `db` as the host.

## Healthchecks
The `db` service includes a healthcheck to ensure it's ready to accept connections before scripts (like the installer) attempt to run.
