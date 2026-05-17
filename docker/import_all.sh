#!/bin/sh
set -eu

transactions_dir="${1:-/var/www/html/transactions}"

if [ ! -d "$transactions_dir" ]; then
    echo "Error: transactions directory not found: $transactions_dir" >&2
    exit 1
fi

found=0
for file in "$transactions_dir"/*.json; do
    [ -e "$file" ] || continue
    found=1
    echo "Importing $file"
    php /var/www/html/docker/import.php "$file"
done

if [ "$found" -eq 0 ]; then
    echo "No transaction files found in $transactions_dir"
fi
