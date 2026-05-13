# Transactions Workflow

To maintain a clean history and avoid the manual web UI, this project uses a "Transactions-as-Code" workflow.

## Directory Structure
*   **`transactions/`**: Stores all transaction data files.
*   **Naming Convention**: Files should be prefixed with sequential numbers (e.g., `01_...`, `02_...`) to maintain chronological order.

## Format
Transactions are stored in JSON format.
*   **Debits**: Positive numbers.
*   **Credits**: Negative numbers.
*   **Balanced**: Each transaction must balance to zero.

Example:
```json
{
  "date": "05/11/2026",
  "reference": "PURCH-001",
  "memo": "Property Purchase",
  "lines": [
    { "account": "1810", "amount": 2500000.00, "memo": "Purchase Price" },
    { "account": "1060", "amount": -2500000.00, "memo": "Cash Payment" }
  ]
}
```

## CLI Importer (`docker/import.php`)
The importer script automates the creation of Journal Entries.

### Features
*   **Session Mocking**: Operates in a CLI environment without a browser.
*   **Automatic Balancing**: Rejects transactions that don't balance.
*   **Date Handling**: Defaults to current date if none provided; ensures MariaDB compatibility.

### Usage
```bash
docker-compose exec web php /var/www/html/docker/import.php /var/www/html/transactions/01_closing_statement.json
```
