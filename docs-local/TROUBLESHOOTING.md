# Troubleshooting

## Blank Page After Login

Symptom: after logging in, FrontAccounting renders only a small `Back` link and the footer.

Most common cause: the Docker containers or MariaDB volume were recreated, but the FrontAccounting database was not re-imported. The app can still show the login screen because `config.php` and `config_db.php` are mounted from the host, but login fails when tables such as `0_users` are missing.

Reset the local database volume and install the local schema:

```bash
docker compose down -v
docker compose up -d
docker compose exec web /var/www/html/docker/setup.sh
docker compose exec web /var/www/html/docker/import_all.sh
```

Verify the database has been initialized:

```bash
docker compose exec db mariadb -ufa_user -pfa_password frontacc \
  -e 'SHOW TABLES LIKE "0_users"; SELECT COUNT(*) AS tables_count FROM information_schema.tables WHERE table_schema="frontacc";'
```

Expected result:

* `0_users` is listed.
* `tables_count` is greater than zero, currently about `80`.
* `config_db.php` contains `'tbpref' => '0_'`.

If the browser still shows the blank page after setup succeeds, clear cookies for `localhost:8081` or open a private window to discard the old broken session.
