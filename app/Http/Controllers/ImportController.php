<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Helpers\MessageHelper;
use App\Helpers\YamlConfigurationHelper;
use App\Helpers\YnabHelper;
use App\Services\Ynab\YnabApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class ImportController extends Controller
{
    /**
     * @return RedirectResponse
     */
    public function startImport()
    {
        if (!Session::exists(AppConstants::$SESSION_BANK_STATEMENTS)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_BANK_STATEMENTS);
        }

        if (!Session::exists(AppConstants::$SESSION_CURRENT_ACCOUNT)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        /** @var array $currentConfigurationAccount */
        $currentConfigurationAccount = Session::get(AppConstants::$SESSION_CURRENT_ACCOUNT);

        /** @var \Fhp\Model\StatementOfAccount\Statement $accountStatements */
        $accountStatements = unserialize(Session::get(AppConstants::$SESSION_BANK_STATEMENTS));

        $transactions = [];

        foreach ($accountStatements as $accountStatement) {
            $transactions[] = $accountStatement->getTransactions();
        }

        if (empty($transactions)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_TRANSACTIONS_EMPTY);
        }

        try {
            $ynabApiService = new YnabApiService($currentConfigurationAccount[AppConstants::$CONFIG_BUDGET_ID]);
        } catch (InvalidArgumentException $invalidArgumentException) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$CHECK_YNAB_CONFIG);
        }

        $ynabPayees = $ynabApiService->fetchAllPayees();
        $ynabTransactions = YnabHelper::buildTransactionsFromAccountStatements($currentConfigurationAccount[AppConstants::$CONFIG_ACCOUNT_ID], $transactions, $ynabPayees);
        $createTransactionsResponse = $ynabApiService->createTransactions($ynabTransactions);

        if ($createTransactionsResponse->getStatusCode() == 201) {
            return MessageHelper::redirectToSuccessMessage();
        }

        return MessageHelper::redirectToErrorMessage(ErrorConstants::$YNAB_UPLOAD_TRANSACTIONS);
    }

    /**
     * @param string $accountHash
     * @return RedirectResponse
     */
    public function startImportFromPreset(string $accountHash)
    {
        if (empty($accountHash)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$REQUEST_MISSING_IMPORT_HASH);
        }

        $accountToUse = YamlConfigurationHelper::findAccountByHash($accountHash);

        if ($accountToUse == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$CHECK_IMPORT_HASH);
        }

        Session::put(AppConstants::$SESSION_CURRENT_ACCOUNT, $accountToUse);

        return Redirect::route(AppConstants::$ROUTE_START_LOGIN);
    }
}
