# YNAB FinTs Importer

This repository adds the ability to import any FinTs connected bank account into YNAB.

## Example configuration

```
accounts:
  - bank_url: 'https://<FINTS_URL_BANK>'
    bank_code: '<BANK_BIC>'
    bank_iban: '<BANK_IBAN>'
    username: '<BANK_USERNAME>'
    password: '<BANK_PASSWORD>'
    tan_mode: 921
    budget_id: '<YNAB_BUDGET_ID>'
    account_id: '<YNAB_ACCOUNT_ID>'
    account_name: '<a name of your choice>'
```
