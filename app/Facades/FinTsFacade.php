<?php

namespace App\Facades;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Helpers\MessageHelper;
use App\Services\FinTs\FinTsService;
use App\Services\SessionService;
use Exception;
use Fhp\Action\GetSEPAAccounts;
use Fhp\Action\GetStatementOfAccount;
use Fhp\BaseAction;
use Fhp\CurlException;
use Fhp\FinTsNew;
use Fhp\Model\TanMedium;
use Fhp\Model\TanMode;
use Fhp\Protocol\ServerException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class FinTsFacade
{
    /**
     * @param array|null $configurationAccount
     * @return RedirectResponse|mixed
     * @throws CurlException
     * @throws ServerException
     */
    public static function login(array $configurationAccount = null) {
        if ($configurationAccount == null) {
            $configurationAccount = SessionService::getCurrentAccount();
        }

        if (SessionService::isTanMediumPresent()) {
            /** @var FinTsNew $finTsObject */
            $finTsObject = SessionService::getFinTsObject();

            /** @var TanMedium $tanMedium */
            $tanMedium = SessionService::getTanMedium();

            /** @var TanMode $tanMode */
            $tanMode = SessionService::getTanMode();

            return self::evaluateResult(FinTsService::setUpTanAndLogin($finTsObject, $tanMode, $configurationAccount, $tanMedium));
        }

        return self::evaluateResult(FinTsService::login($configurationAccount));
    }

    /**
     * @return RedirectResponse|mixed
     * @throws Exception
     */
    public static function fetchAccounts()
    {
        /** @var FinTsNew $finTsObject */
        $finTsObject = SessionService::getFinTsObject();

        /** @var array $configurationAccount */
        $configurationAccount = SessionService::getCurrentAccount();

        if ($finTsObject == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_FINTS_OBJECT);
        }

        if (SessionService::isTanActionPresent()) {
            /** @var BaseAction $tanAction */
            $tanAction = SessionService::getTanAction();

            if (!$tanAction instanceof GetSEPAAccounts) {
                return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_TAN_ACTION_WRONG_TYPE);
            }

            SessionService::removeTanAction();

            return self::evaluateResult(FinTsService::fetchAccounts($finTsObject, $configurationAccount[AppConstants::$CONFIG_BANK_IBAN], $tanAction));
        }

        try {
            return self::evaluateResult(FinTsService::setupFetchAccounts($finTsObject, $configurationAccount[AppConstants::$CONFIG_BANK_IBAN]));
        } catch (Exception $exception) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_BANK_COMMUNICATION);
        }
    }

    /**
     * @return RedirectResponse|mixed
     * @throws Exception
     */
    public static function fetchTransactions()
    {
        /** @var FinTsNew $finTsObject */
        $finTsObject = SessionService::getFinTsObject();

        if ($finTsObject == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_FINTS_OBJECT);
        }

        if (SessionService::isTanActionPresent()) {
            /** @var BaseAction $tanAction */
            $tanAction = SessionService::getTanAction();

            if (!$tanAction instanceof GetStatementOfAccount) {
                return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_TAN_ACTION_WRONG_TYPE);
            }

            SessionService::removeTanAction();

            return self::evaluateResult(FinTsService::fetchTransactions($finTsObject, $tanAction));
        }

        return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_BANK_COMMUNICATION);
    }

    /**
     * @param $result
     * @return RedirectResponse|mixed
     * @throws ServerException
     */
    private static function evaluateResult($result)
    {
        if (array_key_exists(ErrorConstants::$ERROR_MESSAGE, $result)) {
            return MessageHelper::redirectToErrorMessage($result[ErrorConstants::$ERROR_MESSAGE]);
        } else if (array_key_exists(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA, $result)) {
            SessionService::putFinTsObject($result[AppConstants::$SESSION_FINTS_OBJECT]);
            SessionService::putTanMode($result[AppConstants::$SESSION_TAN_MODE]);
            SessionService::putAvailableTanMedia($result[AppConstants::$SESSION_AVAILABLE_TAN_MEDIA]);

            return Redirect::route(AppConstants::$ROUTE_CHOOSE_TAN_MEDIUM);
        } else if (array_key_exists(AppConstants::$SESSION_TAN_ACTION, $result)) {
            SessionService::putFinTsObject($result[AppConstants::$SESSION_FINTS_OBJECT]);
            SessionService::putTanAction($result[AppConstants::$SESSION_TAN_ACTION]);

            return Redirect::route(AppConstants::$ROUTE_ENTER_TAN);
        } else if (array_key_exists(AppConstants::$SESSION_BANK_STATEMENTS, $result)) {
            if (array_key_exists(AppConstants::$SESSION_FINTS_OBJECT, $result)) {
                FinTsService::closeFinTsSession($result[AppConstants::$SESSION_FINTS_OBJECT]);
            }

            self::clearSessionOfFinTsValues();

            return YnabImportFacade::importBankStatements($result[AppConstants::$SESSION_BANK_STATEMENTS]);
        }

        return MessageHelper::redirectToErrorMessage(ErrorConstants::$GENERAL_ERROR);
    }

    /**
     * Remove every session value relating to FinTs stuff
     */
    public static function clearSessionOfFinTsValues()
    {
        Session::remove(AppConstants::$SESSION_FINTS_OBJECT);
        Session::remove(AppConstants::$SESSION_TAN_ACTION);
        Session::remove(AppConstants::$SESSION_TAN_MEDIUM);
        Session::remove(AppConstants::$SESSION_TAN_MODE);
        Session::remove(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA);
        Session::remove(AppConstants::$SESSION_SEPA_ACCOUNT);
    }
}
