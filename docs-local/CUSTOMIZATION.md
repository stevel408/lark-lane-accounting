# Customization (Real Estate)

The system has been specifically tailored for tracking real-estate re-development projects.

## Chart of Accounts (COA)
The customized COA is located in `sql/real_estate_coa.sql`. It includes:

### Project-Specific Asset Accounts
*   `1810`: Land (Project)
*   `1811`: Building (Project)
*   `1815`: Construction WIP

### Specialized Expense Accounts
*   **Cost of Goods Sold (Direct Costs)**:
    *   `5070`: Contractor Materials
    *   `5080`: Contractor Labor/Fees
    *   `5090`: Permits and Licenses
*   **Carrying/Closing Costs**:
    *   `6015`: Agent Credits/Rebates
    *   `6045`: HOA Fees (Project)
    *   `6050`: Title & Escrow Fees
    *   `6055`: Recording & Gov Fees

### Liability Accounts (Partner Funding)
*   `2680`: Loans from Bin Zhou/Beini Ouyang
*   `2681`: Loans from Steven Li Living Trust
*   `2682`: Loans from OHANA INVESTMENTS LLC

## Fixed Assets
A new Fixed Asset Class **"Property Re-development"** (`RE_DEV`) has been added.
*   **Depreciation**: Set to 0% by default, as property re-development (flips) typically involves appreciation or immediate sale rather than regular depreciation.

## Dimensions
The system has `use_dimension = 1` enabled in `sys_prefs`. 
*   **Usage**: Create a new Dimension for each property (e.g., "123 Main St"). Tag all income and expenses to this Dimension to generate a per-project Profit & Loss.
