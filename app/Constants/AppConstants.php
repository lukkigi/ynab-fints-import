<?php

namespace App\Constants;

/**
 * Class AppConstants - contains all the constants used in this application
 * @package App\Constants
 */
class AppConstants
{
    public static $CONFIG_ACCOUNTS = 'accounts';
    public static $CONFIG_USERNAME = 'username';
    public static $CONFIG_PASSWORD = 'password';
    public static $CONFIG_BANK_IBAN = 'bank_iban';
    public static $CONFIG_BANK_URL = 'bank_url';
    public static $CONFIG_BANK_CODE = 'bank_code';
    public static $CONFIG_TAN_MODE = 'tan_mode';
    public static $CONFIG_BUDGET_ID = 'budget_id';
    public static $CONFIG_ACCOUNT_ID = 'account_id';
    public static $CONFIG_ACCOUNT_NAME = 'account_name';

    public static $ENV_PRODUCT_NAME = 'fints.productName';
    public static $ENV_PRODUCT_VERSION = 'fints.productVersion';
    public static $ENV_YNAB_API_KEY = 'ynab.apikey';

    public static $SESSION_CURRENT_ACCOUNT = 'currentAccount';
    public static $SESSION_AVAILABLE_TAN_MEDIA = 'finTsAvailableTanMedia';
    public static $SESSION_TAN_MEDIUM = 'finTsTanMedium';
    public static $SESSION_TAN_MODE = 'finTsTanMode';
    public static $SESSION_TAN_ACTION = 'finTsTanAction';
    public static $SESSION_FINTS_OBJECT = 'finTsObject';
    public static $SESSION_SEPA_ACCOUNT = 'finTsSepaAccount';
    public static $SESSION_BANK_STATEMENTS = 'finTsBankStatements';

    public static $ROUTE_START = 'start';
    public static $ROUTE_START_LOGIN = 'startLogin';
    public static $ROUTE_START_IMPORT = 'startImport';
    public static $ROUTE_CHOOSE_TAN_MEDIUM = 'chooseTanMedium';
    public static $ROUTE_ENTER_TAN = 'enterTan';
    public static $ROUTE_FETCH_ACCOUNTS = 'fetchAccounts';
    public static $ROUTE_FETCH_TRANSACTIONS = 'fetchTransactions';
}
