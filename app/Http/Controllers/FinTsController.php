<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Helpers\MessageHelper;
use App\Services\FinTs\FinTsService;
use App\Services\FinTs\LoginService;
use Exception;
use Fhp\Action\GetSEPAAccounts;
use Fhp\Action\GetStatementOfAccount;
use Fhp\CurlException;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\ServerException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class FinTsController extends Controller
{
    /**
     * @return RedirectResponse
     */
    public function startLogin()
    {
        if (!Session::exists(AppConstants::$SESSION_CURRENT_ACCOUNT)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        $currentAccountConfiguration = Session::get(AppConstants::$SESSION_CURRENT_ACCOUNT);

        if ($currentAccountConfiguration == null || empty($currentAccountConfiguration)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        return LoginService::login($currentAccountConfiguration);
    }

    /**
     * @return RedirectResponse
     * @throws Exception
     */
    public function fetchAccounts()
    {
        if (!Session::exists(AppConstants::$SESSION_CURRENT_ACCOUNT)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        $currentAccountConfiguration = Session::get(AppConstants::$SESSION_CURRENT_ACCOUNT);

        if ($currentAccountConfiguration == null || empty($currentAccountConfiguration)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        if (Session::exists(AppConstants::$SESSION_TAN_ACTION)) {
            $finTsLastAction = unserialize(Session::get(AppConstants::$SESSION_TAN_ACTION));

            if ($finTsLastAction instanceof GetSEPAAccounts) {
                return FinTsService::fetchAccounts($currentAccountConfiguration[AppConstants::$CONFIG_BANK_IBAN]);
            }
        }

        $fetchAccountsResult = FinTsService::setupFetchAccounts($currentAccountConfiguration[AppConstants::$CONFIG_BANK_IBAN]);

        if ($fetchAccountsResult instanceof RedirectResponse) {
            return $fetchAccountsResult;
        }

        return FinTsService::fetchAccounts($currentAccountConfiguration[AppConstants::$CONFIG_BANK_IBAN]);
    }

    /**
     * @return RedirectResponse
     * @throws CurlException
     * @throws ServerException
     */
    public function fetchTransactions()
    {
        if (!Session::exists(AppConstants::$SESSION_CURRENT_ACCOUNT)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        $currentAccountConfiguration = Session::get(AppConstants::$SESSION_CURRENT_ACCOUNT);

        if ($currentAccountConfiguration == null || empty($currentAccountConfiguration)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        if (!Session::exists(AppConstants::$SESSION_SEPA_ACCOUNT)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_SEPA_ACCOUNT);
        }

        /** @var SEPAAccount $sepaAccount */
        $sepaAccount = unserialize(Session::get(AppConstants::$SESSION_SEPA_ACCOUNT));

        if (Session::exists(AppConstants::$SESSION_TAN_ACTION)) {
            $finTsLastAction = Session::get(AppConstants::$SESSION_TAN_ACTION);

            if ($finTsLastAction instanceof GetStatementOfAccount) {
                return FinTsService::fetchTransactions();
            }
        }

        $fetchTransactionsResult = FinTsService::setupFetchTransactions($sepaAccount);

        if ($fetchTransactionsResult instanceof RedirectResponse) {
            return $fetchTransactionsResult;
        }

        return FinTsService::fetchTransactions();
    }
}
