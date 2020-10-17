<?php

namespace App\Services\FinTs;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Helpers\FinTsHelper;
use DateInterval;
use DateTime;
use Exception;
use Fhp\Action\GetSEPAAccounts;
use Fhp\Action\GetStatementOfAccount;
use Fhp\CurlException;
use Fhp\FinTsNew;
use Fhp\Model\NoPsd2TanMode;
use Fhp\Model\SEPAAccount;
use Fhp\Model\TanMedium;
use Fhp\Model\TanMode;
use Fhp\Protocol\ServerException;
use Illuminate\Support\Facades\Config;

class FinTsService
{
    /**
     * @param array $configurationAccount
     * @return array|string[]
     * @throws CurlException
     * @throws ServerException
     */
    public static function login(array $configurationAccount) {
        $productName = Config::get(AppConstants::$ENV_PRODUCT_NAME);
        $productVersion = Config::get(AppConstants::$ENV_PRODUCT_VERSION);

        if ($productName == null || empty($productName) || $productVersion == null || empty($productVersion)) {
            return [
                ErrorConstants::$ERROR_MESSAGE => ErrorConstants::$CHECK_PRODUCT_KEYS
            ];
        }

        $finTsObject = FinTsHelper::createFinTsObject($configurationAccount[AppConstants::$CONFIG_BANK_URL],
            $configurationAccount[AppConstants::$CONFIG_BANK_CODE], $configurationAccount[AppConstants::$CONFIG_USERNAME],
            $configurationAccount[AppConstants::$CONFIG_PASSWORD], $productName, $productVersion
        );

        if ($configurationAccount[AppConstants::$CONFIG_TAN_MODE] == '-1') {
            $tanMode = new NoPsd2TanMode();
        } else {
            // TODO: check value first

            $tanMode = $finTsObject->getTanModes()[intval($configurationAccount[AppConstants::$CONFIG_TAN_MODE])];
        }

        if ($tanMode->needsTanMedium()) {
            return [
                AppConstants::$SESSION_FINTS_OBJECT => $finTsObject,
                AppConstants::$SESSION_TAN_MODE => $tanMode,
                AppConstants::$SESSION_AVAILABLE_TAN_MEDIA => $finTsObject->getTanMedia($tanMode)
            ];
        }

        return self::setUpTanAndLogin($finTsObject, $tanMode, $configurationAccount, null);
    }

    /**
     * @param FinTsNew $finTsObject
     * @param TanMode $tanMode
     * @param array $configurationAccount
     * @param TanMedium|null $tanMedium
     * @return array|string[]
     * @throws Exception
     */
    public static function setUpTanAndLogin(FinTsNew $finTsObject, TanMode $tanMode, array $configurationAccount, TanMedium $tanMedium = null) {
        if ($finTsObject == null || $tanMode == null || $configurationAccount == null) {
            return [
                ErrorConstants::$ERROR_MESSAGE => 'setUpTanAndLogin ' . ErrorConstants::$GENERIC_METHOD_ERROR
            ];
        }

        $finTsObject->selectTanMode($tanMode, $tanMedium);

        try {
            $loginRequest = $finTsObject->login();
        } catch (CurlException | ServerException $exception) {
            return [
                ErrorConstants::$ERROR_MESSAGE => ErrorConstants::$FINTS_BANK_COMMUNICATION
            ];
        }

        if ($loginRequest->needsTan()) {
            return [
                AppConstants::$SESSION_FINTS_OBJECT => $finTsObject,
                AppConstants::$SESSION_TAN_ACTION => $loginRequest
            ];
        }

        return self::setupFetchAccounts($finTsObject, $configurationAccount[AppConstants::$CONFIG_BANK_IBAN]);
    }

    /**
     * @param FinTsNew $finTsObject
     * @param string $accountIban
     * @return array|string[]
     * @throws Exception
     */
    public static function setupFetchAccounts(FinTsNew $finTsObject, string $accountIban)
    {
        $getSepaAccountsAction = new GetSEPAAccounts();

        try {
            $finTsObject->execute($getSepaAccountsAction);
        } catch (CurlException | ServerException $e) {
            return [
                'errorMessage' => ErrorConstants::$FINTS_BANK_COMMUNICATION
            ];
        }

        if ($getSepaAccountsAction->needsTan()) {
            return [
                'finTsObject' => $finTsObject,
                'tanAction' => $getSepaAccountsAction,
                'errorMessage' => null
            ];
        }

        /* no TAN needed, so we can directly fetch the accounts */
        return self::fetchAccounts($finTsObject, $accountIban, $getSepaAccountsAction);
    }

    /**
     * @param FinTsNew $finTsObject
     * @param string $accountIban
     * @param GetSEPAAccounts|null $getSepaAccountsAction
     * @return array|string[]
     * @throws Exception
     */
    public static function fetchAccounts(FinTsNew $finTsObject, string $accountIban, GetSEPAAccounts $getSepaAccountsAction = null)
    {
        if ($finTsObject == null || $accountIban == null || $getSepaAccountsAction == null) {
            return [
                ErrorConstants::$ERROR_MESSAGE => 'fetchAccounts ' . ErrorConstants::$GENERIC_METHOD_ERROR
            ];
        }

        if ($getSepaAccountsAction == null || !$getSepaAccountsAction->isSuccess()) {
            return [
                ErrorConstants::$ERROR_MESSAGE => ErrorConstants::$FINTS_BANK_COMMUNICATION
            ];
        }

        $sepaAccounts = $getSepaAccountsAction->getAccounts();
        $sepaAccountToUse = FinTsHelper::findAccountWithIban($sepaAccounts, $accountIban);

        if ($sepaAccountToUse == null) {
            return [
                ErrorConstants::$ERROR_MESSAGE => ErrorConstants::$FINTS_NO_ACCOUNT_WITH_DESIRED_IBAN
            ];
        }

        return self::setupFetchTransactions($finTsObject, $sepaAccountToUse);
    }

    /**
     * @param FinTsNew $finTsObject
     * @param SEPAAccount $account
     * @param DateTime|null $timeFrom
     * @param DateTime|null $timeTo
     * @return array
     * @throws Exception
     */
    public static function setupFetchTransactions(FinTsNew $finTsObject, SEPAAccount $account, DateTime $timeFrom = null, DateTime $timeTo = null)
    {
        if ($account == null) {
            return [
                ErrorConstants::$ERROR_MESSAGE => ErrorConstants::$SESSION_NO_SEPA_ACCOUNT
            ];
        }

        if ($timeFrom == null) {
            $timeFrom = (new DateTime())->sub(new DateInterval('P89D'));
        }

        if ($timeTo == null) {
            $timeTo = new DateTime();
        }

        $getStatementAction = GetStatementOfAccount::create($account, $timeFrom, $timeTo);

        try {
            $finTsObject->execute($getStatementAction);
        } catch (CurlException | ServerException $e) {
            return [
                ErrorConstants::$ERROR_MESSAGE => ErrorConstants::$FINTS_BANK_COMMUNICATION
            ];
        }

        if ($getStatementAction->needsTan()) {
            return [
                AppConstants::$SESSION_FINTS_OBJECT => $finTsObject,
                AppConstants::$SESSION_TAN_ACTION => $getStatementAction
            ];
        }

        /* no TAN needed, so we can directly fetch the accounts */
        return self::fetchTransactions($finTsObject, $getStatementAction);
    }

    /**
     * @param FinTsNew $finTsObject
     * @param GetStatementOfAccount|null $getStatementAction
     * @return array
     * @throws Exception
     */
    public static function fetchTransactions(FinTsNew $finTsObject, GetStatementOfAccount $getStatementAction = null)
    {
        if ($getStatementAction == null || !$getStatementAction->isSuccess()) {
            return [
                ErrorConstants::$ERROR_MESSAGE => ErrorConstants::$FINTS_BANK_COMMUNICATION
            ];
        }

        $statementOfAccount = $getStatementAction->getStatement();
        $statementOfAccountStatements = $statementOfAccount->getStatements();

        if (empty($statementOfAccountStatements)) {
            return [
                ErrorConstants::$ERROR_MESSAGE => ErrorConstants::$FINTS_TRANSACTIONS_EMPTY
            ];
        }

        return [
            AppConstants::$SESSION_FINTS_OBJECT => $finTsObject,
            AppConstants::$SESSION_BANK_STATEMENTS => $statementOfAccountStatements
        ];
    }

    /**
     * Logout of the FinTs session
     * @param FinTsNew $finTsObject
     */
    public static function closeFinTsSession(FinTsNew $finTsObject)
    {
        if ($finTsObject == null) {
            return;
        }

        try {
            $finTsObject->close();
        } catch (Exception $exception) {
            return;
        }
    }
}
