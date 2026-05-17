# Railway Deployment

This app should be deployed to Railway as a Docker-backed PHP web service plus a MySQL-compatible database service.

## Services

Create two Railway services in one project:

1. A MySQL database service.
2. A web service from this GitHub repository.

Railway will build the web service with `docker/Dockerfile`. The container entrypoint writes `config.php`, `config_db.php`, and `installed_extensions.php` at runtime from environment variables, waits for the database, and starts Apache.

Railway does not run `docker/setup.sh` during deployment. That script is for local Docker Compose setup. In Railway, initialization is handled by `docker/railway-entrypoint.sh`: it checks whether the FrontAccounting schema exists, runs `docker/install.php` only when `FA_AUTO_SETUP=1` and the schema is missing, and otherwise exits with a clear error instead of serving an empty app.

## Web Service Variables

Set these variables on the web service:

```text
PORT=80
FA_SQL_TEMPLATE=real_estate_coa.sql
FA_COMPANY_NAME=Lark Lane Renovation
FA_ADMIN_PASSWORD=<set a strong temporary setup password>
FA_AUTO_SETUP=1
```

For the database connection, use Railway reference variables from the MySQL service. The runtime supports either Railway's MySQL variable names or explicit `FA_DB_*` names.

Preferred explicit form:

```text
FA_DB_HOST=${{MySQL.MYSQLHOST}}
FA_DB_PORT=${{MySQL.MYSQLPORT}}
FA_DB_USER=${{MySQL.MYSQLUSER}}
FA_DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
FA_DB_NAME=${{MySQL.MYSQLDATABASE}}
```

After the first successful deploy, change `FA_AUTO_SETUP` to `0` or remove it. The entrypoint skips setup when the schema already exists, but disabling setup after initialization is clearer and safer.

## Persistent Files

FrontAccounting writes generated files under `company/`, including report PDFs, upload assets, backup files, and JavaScript cache files.

Attach a Railway volume to the web service if you need those files to survive deploys:

```text
/var/www/html/company
```

Do not mount a volume over `/var/www/html`; that would hide the application code copied into the image.

## First Deploy

1. Create the MySQL service.
2. Create the web service from the GitHub repo.
3. Add the web variables listed above.
4. Generate a Railway domain for the web service.
5. Watch the first web deploy logs. You should see the database become reachable and the FrontAccounting schema install.
6. Open the Railway domain and log in as `admin` using `FA_ADMIN_PASSWORD`.
7. Set `FA_AUTO_SETUP=0` and redeploy.

Expected first-deploy log lines:

```text
Runtime config written for database ...
Database is reachable.
FrontAccounting schema not found.
Installing FrontAccounting schema because FA_AUTO_SETUP=1...
Importing schema from /var/www/html/sql/real_estate_coa.sql...
Installation complete!
FrontAccounting schema already exists.
```

If the app deploys but the page is blank or empty, check the web service variables first. Most commonly `FA_AUTO_SETUP=1` was not set on the web service during the first deploy, so the database tables and chart of accounts were never imported.

## Importing Prepared Transactions

Run imports only after the schema exists. From a Railway shell or one-off command on the web service:

```sh
php /var/www/html/docker/import.php /var/www/html/transactions/01_closing_statement.json
php /var/www/html/docker/import.php /var/www/html/transactions/02_steven_to_bin_8000.json
```

Avoid rerunning `docker/import_all.sh` after data has already been imported, because the importer does not de-duplicate journal entries.
