# Local Development Documentation

This directory contains documentation for the customized FrontAccounting environment set up for real-estate re-development projects.

## Contents

1.  [**DOCKER.md**](./DOCKER.md): Details about the Docker Compose environment, services, and networking.
2.  [**INSTALLATION.md**](./INSTALLATION.md): Guide to the automated, programmatic installation and reset process.
3.  [**CUSTOMIZATION.md**](./CUSTOMIZATION.md): Documentation on the Real Estate Chart of Accounts (COA) and Fixed Asset classes.
4.  [**TRANSACTIONS.md**](./TRANSACTIONS.md): Guide to the CLI-based "Transactions-as-Code" workflow and importer.
5.  [**LEARNINGS.md**](./LEARNINGS.md): Technical notes on system quirks, path handling, and MariaDB compatibility.

## Quick Start (Reset System)

To reset the system to a clean state with the Real Estate template:

```bash
# 1. Start containers
docker-compose up -d

# 2. Restore install folder
mv install_bak install

# 3. Run automated setup
docker-compose exec web /var/www/html/docker/setup.sh
```
