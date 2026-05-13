# Automated Installation

FrontAccounting normally requires a manual 6-step web-based wizard. This project has been automated for one-command setup.

## Components

### `docker/install.php`
A PHP script that leverages FrontAccounting's internal classes (`sys_prefs`, `items_cart`) to:
1.  Initialize a connection to the MariaDB container.
2.  Import the SQL schema defined in `FA_SQL_TEMPLATE`.
3.  Configure the default company and admin user.
4.  Generate `config.php` and `config_db.php` automatically.

### `docker/setup.sh`
A wrapper script that:
1.  Waits for the MariaDB service to be online.
2.  Executes `docker/install.php`.
3.  Renames the `install/` directory to `install_bak` for security.

## Resetting the System
To wipe the current database and start fresh with the customized real-estate template:

```bash
# 1. Restore the installer
mv install_bak install

# 2. Run the setup
docker-compose exec web /var/www/html/docker/setup.sh
```
