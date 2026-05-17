# Local Development Documentation

This directory contains documentation for the customized FrontAccounting environment set up for real-estate re-development projects.

## Contents

1.  [**DOCKER.md**](./DOCKER.md): Details about the Docker Compose environment, services, and networking.
2.  [**DEV_SETUP.md**](./DEV_SETUP.md): Guide to local Docker setup and database reset.
3.  [**CUSTOMIZATION.md**](./CUSTOMIZATION.md): Documentation on the Real Estate Chart of Accounts (COA) and Fixed Asset classes.
4.  [**TRANSACTIONS.md**](./TRANSACTIONS.md): Guide to the CLI-based "Transactions-as-Code" workflow and importer.
5.  [**LEARNINGS.md**](./LEARNINGS.md): Technical notes on system quirks, path handling, and MariaDB compatibility.
6.  [**TROUBLESHOOTING.md**](./TROUBLESHOOTING.md): Common local setup failures, including the blank page after login.

## Quick Start (Reset System)

To reset the system to a clean state with the Real Estate template:

```bash
# 1. Stop containers and delete named volumes
docker compose down -v

# 2. Start fresh containers
docker compose up -d

# 3. Run automated setup
docker compose exec web /var/www/html/docker/setup.sh

# 4. Load prepared transactions
docker compose exec web /var/www/html/docker/import_all.sh
```
