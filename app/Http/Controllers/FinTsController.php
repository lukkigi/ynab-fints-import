<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Helpers\MessageHelper;
use App\Services\FinTs\FinTsService;
use App\Services\FinTs\LoginService;
use App\Services\SessionService;
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
        if (!SessionService::isCurrentAccountPresent()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        $currentAccountConfiguration = SessionService::getCurrentAccount();

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
        if (!SessionService::isCurrentAccountPresent()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        $currentAccountConfiguration = SessionService::getCurrentAccount();

        if ($currentAccountConfiguration == null || empty($currentAccountConfiguration)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        if (SessionService::isTanActionPresent()) {
            $finTsLastAction = SessionService::getTanAction();

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
     * @throws ServerException
     */
    public function fetchTransactions()
    {
        if (!SessionService::isCurrentAccountPresent()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        $currentAccountConfiguration = SessionService::getCurrentAccount();

        if ($currentAccountConfiguration == null || empty($currentAccountConfiguration)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        if (!SessionService::isSepaAccountPresent()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_SEPA_ACCOUNT);
        }

        /** @var SEPAAccount $sepaAccount */
        $sepaAccount = SessionService::getSepaAccount();

        if (SessionService::isTanActionPresent()) {
            $finTsLastAction = SessionService::getTanAction();

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
