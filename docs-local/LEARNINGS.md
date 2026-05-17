# Technical Learnings

This document records the technical hurdles and discoveries made during the setup and customization of FrontAccounting.

## 1. Path Handling in CLI
**Discovery**: PHP's `include` and `require` behaviors differ between the web server and the CLI. 
**Solution**: Always use `realpath(__DIR__ . "/..")` to establish a reliable `$path_to_root`. This ensures scripts run correctly regardless of whether the Current Working Directory (CWD) is the project root or the `docker/` folder.

## 2. MariaDB Date Constraints
**Discovery**: Recent versions of MariaDB are strict about empty strings in `DATE` columns (Error 1292).
**Solution**: FrontAccounting core often uses `''` as a placeholder for "no date." Our importer script ensures `event_date` and `doc_date` are explicitly set to a valid SQL date format (`YYYY-MM-DD`) or `Today()`.

## 3. Session & Security Mocking
**Discovery**: Much of FrontAccounting's logic is tightly coupled to the `$_SESSION` global and the `items_cart` class.
**Solution**: To run CLI scripts, we include `install_custom/isession.inc` to set up a minimal session environment without restoring the live `install/` directory. We also need to manually define missing globals like `$installed_extensions` and mock the `wa_current_user` object with appropriate access levels.

## 4. HTTPS/SSL Requirements
**Discovery**: FrontAccounting has a `SECURE_ONLY` flag in `includes/session.inc` that defaults to `true`.
**Solution**: For local development on port 8080 (non-HTTPS), this must be set to `false` to avoid redirects or blank pages.

## 5. Fiscal Year Constraints
**Discovery**: Transactions will be rejected by the audit trail if a valid Fiscal Year does not exist for the transaction date.
**Solution**: Updated the SQL template to include a 2026 fiscal year to accommodate current project entries.
