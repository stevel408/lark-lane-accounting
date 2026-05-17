# Local Development Setup

This project runs FrontAccounting locally through Docker Compose. There is no manual web installation flow for day-to-day development; the local database is initialized by scripts in `docker/`.

## Components

### `docker/install.php`
A PHP script that uses FrontAccounting's internal setup helpers to:
1.  Initialize a connection to the MariaDB container.
2.  Import the SQL schema defined in `FA_SQL_TEMPLATE`.
3.  Preserve the local `0_` table prefix used by the SQL template.
4.  Configure the default company and admin user.
5.  Generate `config.php`, `config_db.php`, and `installed_extensions.php`.

### `docker/setup.sh`
A wrapper script that:
1.  Waits for the MariaDB service to be online.
2.  Executes `docker/install.php`.

### `install_custom/`
A stable local copy of the FrontAccounting installer support files needed by the Docker setup scripts. The live `install/` directory remains absent, so local resets do not require renaming it back and forth.

## Resetting Local Data
For a deterministic local reset, remove the Docker Compose containers and named volumes first. This deletes the MariaDB data volume, so any transactions or UI-entered data not captured elsewhere will be lost.

```bash
# 1. Stop containers and delete named volumes, including the MariaDB data volume
docker compose down -v

# 2. Start fresh containers
docker compose up -d

# 3. Import the local real-estate schema and write generated config files
docker compose exec web /var/www/html/docker/setup.sh

# 4. Replay prepared transaction files, in filename order
docker compose exec web /var/www/html/docker/import_all.sh
```

After setup finishes, log in at `http://localhost:8081` with `admin / password`.

Transaction replay is intended for a freshly reset database. Re-running it against an already imported database can create duplicate journal entries.

## Verification

Confirm the expected table prefix and tables exist:

```bash
docker compose exec db mariadb -ufa_user -pfa_password frontacc \
  -e 'SHOW TABLES LIKE "0_users"; SELECT COUNT(*) AS tables_count FROM information_schema.tables WHERE table_schema="frontacc";'
```

Expected result:

* `0_users` is listed.
* `tables_count` is greater than zero, currently about `80`.
* `config_db.php` contains `'tbpref' => '0_'`.

See [TROUBLESHOOTING.md](./TROUBLESHOOTING.md) if login renders only a `Back` link and footer.
