<?php

namespace App\Services\FinTs;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Helpers\FinTsHelper;
use App\Helpers\MessageHelper;
use DateInterval;
use DateTime;
use Exception;
use Fhp\Action\GetSEPAAccounts;
use Fhp\Action\GetStatementOfAccount;
use Fhp\BaseAction;
use Fhp\CurlException;
use Fhp\FinTsNew;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\ServerException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class FinTsService
{
    /**
     * @param string $accountIban
     * @return RedirectResponse
     * @throws Exception
     */
    public static function setupFetchAccounts(string $accountIban)
    {
        if (Session::exists(AppConstants::$SESSION_TAN_ACTION)) {
            /* we still have an open TAN request if this is true */
            return Redirect::route(AppConstants::$ROUTE_ENTER_TAN);
        }

        if (!Session::exists(AppConstants::$SESSION_FINTS_OBJECT)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_FINTS_OBJECT);
        }

        /** @var \Fhp\FinTsNew $authenticatedFinTsObject */
        $authenticatedFinTsObject = unserialize(Session::get(AppConstants::$SESSION_FINTS_OBJECT));

        $getSepaAccountsAction = new GetSEPAAccounts();

        try {
            $authenticatedFinTsObject->execute($getSepaAccountsAction);
        } catch (CurlException | ServerException $e) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_BANK_COMMUNICATION);
        }

        Session::put(AppConstants::$SESSION_FINTS_OBJECT, serialize($authenticatedFinTsObject));

        if ($getSepaAccountsAction->needsTan()) {
            Session::put(AppConstants::$SESSION_TAN_ACTION, serialize($getSepaAccountsAction));

            return Redirect::route(AppConstants::$ROUTE_ENTER_TAN);
        }

        /* no TAN needed, so we can directly fetch the accounts */
        return self::fetchAccounts($accountIban, $getSepaAccountsAction);
    }

    /**
     * @param string $accountIban
     * @param GetSEPAAccounts|null $getSepaAccountsAction
     * @return RedirectResponse
     * @throws Exception
     */
    public static function fetchAccounts(string $accountIban, GetSEPAAccounts $getSepaAccountsAction = null)
    {
        if (Session::exists(AppConstants::$SESSION_TAN_ACTION)) {
            /** @var BaseAction $tanAction */
            $tanAction = unserialize(Session::get(AppConstants::$SESSION_TAN_ACTION));

            if (!$tanAction instanceof GetSEPAAccounts) {
                return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_TAN_ACTION_WRONG_TYPE);
            }

            $getSepaAccountsAction = $tanAction;
        }

        Session::remove(AppConstants::$SESSION_TAN_ACTION);

        if ($getSepaAccountsAction == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_BANK_COMMUNICATION);
        }

        if (!$getSepaAccountsAction->isSuccess()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_BANK_COMMUNICATION);
        }

        $sepaAccounts = $getSepaAccountsAction->getAccounts();
        $sepaAccountToUse = FinTsHelper::findAccountWithIban($sepaAccounts, $accountIban);

        if ($sepaAccountToUse == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_NO_ACCOUNT_WITH_DESIRED_IBAN);
        }

        Session::put(AppConstants::$SESSION_SEPA_ACCOUNT, serialize($sepaAccountToUse));

        return Redirect::route(AppConstants::$ROUTE_FETCH_TRANSACTIONS);
    }

    /**
     * @param SEPAAccount $account
     * @param DateTime|null $timeFrom
     * @param DateTime|null $timeTo
     * @return RedirectResponse
     * @throws CurlException
     * @throws ServerException
     */
    public static function setupFetchTransactions(SEPAAccount $account, DateTime $timeFrom = null, DateTime $timeTo = null)
    {
        if ($account == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_SEPA_ACCOUNT);
        }

        if ($timeFrom == null) {
            $timeFrom = (new DateTime())->sub(new DateInterval('P89D'));
        }

        if ($timeTo == null) {
            $timeTo = new DateTime();
        }

        /** @var \Fhp\FinTsNew $authenticatedFinTsObject */
        $authenticatedFinTsObject = unserialize(Session::get(AppConstants::$SESSION_FINTS_OBJECT));

        $getStatementAction = GetStatementOfAccount::create($account, $timeFrom, $timeTo);

        try {
            $authenticatedFinTsObject->execute($getStatementAction);
        } catch (CurlException | ServerException $e) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_BANK_COMMUNICATION);
        }

        Session::put(AppConstants::$SESSION_FINTS_OBJECT, serialize($authenticatedFinTsObject));

        if ($getStatementAction->needsTan()) {
            Session::put(AppConstants::$SESSION_TAN_ACTION, serialize($getStatementAction));

            return Redirect::route(AppConstants::$ROUTE_ENTER_TAN);
        }

        /* no TAN needed, so we can directly fetch the accounts */
        return self::fetchTransactions($getStatementAction);
    }

    /**
     * @param GetStatementOfAccount|null $getStatementAction
     * @return RedirectResponse
     * @throws ServerException
     */
    public static function fetchTransactions(GetStatementOfAccount $getStatementAction = null)
    {
        if (Session::exists(AppConstants::$SESSION_TAN_ACTION)) {
            /** @var BaseAction $tanAction */
            $tanAction = unserialize(Session::get(AppConstants::$SESSION_TAN_ACTION));

            if (!$tanAction instanceof GetStatementOfAccount) {
                return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_TAN_ACTION_WRONG_TYPE);
            }

            $getStatementAction = $tanAction;
        }

        Session::remove(AppConstants::$SESSION_TAN_ACTION);

        if ($getStatementAction == null || !$getStatementAction->isSuccess()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_BANK_COMMUNICATION);
        }

        $statementOfAccount = $getStatementAction->getStatement();
        $statementOfAccountStatements = $statementOfAccount->getStatements();

        if (count($statementOfAccountStatements) == 0) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_TRANSACTIONS_EMPTY);
        }

        Session::put(AppConstants::$SESSION_BANK_STATEMENTS, serialize($statementOfAccountStatements));

        /* we have everything we need from the bank at this point */
        self::closeFinTsSession();

        return Redirect::route(AppConstants::$ROUTE_START_IMPORT);
    }

    /**
     * Logout of the FinTs session
     * @throws ServerException
     */
    public static function closeFinTsSession()
    {
        if (!Session::exists(AppConstants::$SESSION_FINTS_OBJECT)) {
            return;
        }

        /** @var FinTsNew $finTsObject */
        $finTsObject = Session::get(AppConstants::$SESSION_FINTS_OBJECT);
        $finTsObject->close();

        self::clearSessionOfFinTsValues();
    }

    /**
     * Remove every session value relating to FinTs stuff
     */
    public static function clearSessionOfFinTsValues() {
        Session::remove(AppConstants::$SESSION_FINTS_OBJECT);
        Session::remove(AppConstants::$SESSION_TAN_ACTION);
        Session::remove(AppConstants::$SESSION_TAN_MEDIUM);
        Session::remove(AppConstants::$SESSION_TAN_MODE);
        Session::remove(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA);
        Session::remove(AppConstants::$SESSION_SEPA_ACCOUNT);
    }
}
