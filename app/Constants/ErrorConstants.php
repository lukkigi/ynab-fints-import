<?php

namespace App\Constants;

class ErrorConstants
{
    public static $ERROR_MESSAGE = 'errorMessage';

    public static $GENERIC_METHOD_ERROR = 'An argument for the method was unexpectedly null';
    public static $GENERAL_ERROR = 'Something went wrong while running the application';

    public static $SESSION_ACCOUNT_CONFIGURATION = 'Could not get the current account configuration from the session';
    public static $SESSION_NO_AVAILABLE_TAN_MEDIA = 'Could not read the available TAN media from the session';
    public static $SESSION_NO_TAN_ACTION = 'Could not get the action which needs a TAN';
    public static $SESSION_NO_FINTS_OBJECT = 'Could not get the FinTs object from the session';
    public static $SESSION_NO_SEPA_ACCOUNT = 'Could not get the SEPA account object from the session';
    public static $SESSION_NO_BANK_STATEMENTS = 'Could not get bank statements from the session';
    public static $SESSION_NO_TAN_MODE = 'Could not get the selected TAN mode from the session';

    public static $FINTS_TAN_ACTION_NEEDS_NO_TAN = 'The current TAN action did in fact not need a TAN';
    public static $FINTS_BANK_COMMUNICATION = 'There was an error communicating with the bank';
    public static $FINTS_TRANSACTIONS_EMPTY = 'Your retrieved transactions for the last 90 days were empty';
    public static $FINTS_TAN_ACTION_WRONG_TYPE = 'The current TAN action was of the wrong type';
    public static $FINTS_NO_ACCOUNT_WITH_DESIRED_IBAN = 'The specified bank account does not contain the specified IBAN';

    public static $YNAB_UPLOAD_TRANSACTIONS = 'There was an error importing the transactions. Please try again later';

    public static $REQUEST_MISSING_TAN_MEDIUM = 'The request was missing the selected TAN medium';
    public static $REQUEST_MISSING_TAN_CODE = 'The request was missing the entered TAN code';
    public static $REQUEST_MISSING_IMPORT_HASH = 'The request was missing an import hash';

    public static $CHECK_YNAB_CONFIG = 'Please check your YNAB config if all values are correct';
    public static $CHECK_IMPORT_HASH = 'Could not find an account for the specified import hash';
    public static $CHECK_PRODUCT_KEYS = 'The keys from Deutsche Kreditwirtschaft (productName, productVersion) could not be found';
}
