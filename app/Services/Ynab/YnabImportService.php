<?php

namespace App\Services\Ynab;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Facades\FinTsFacade;
use App\Helpers\MessageHelper;
use App\Helpers\YnabHelper;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class YnabImportService
{
    /**
     * @param array $bankStatements
     * @param array $configurationAccount
     * @return ResponseInterface
     */
    public static function importBankStatements(array $bankStatements, array $configurationAccount)
    {
        $transactions = [];

        foreach ($bankStatements as $accountStatement) {
            $transactions[] = $accountStatement->getTransactions();
        }

        if (empty($transactions)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_TRANSACTIONS_EMPTY);
        }

        try {
            $ynabApiService = new YnabApiService($configurationAccount[AppConstants::$CONFIG_BUDGET_ID]);
        } catch (InvalidArgumentException $invalidArgumentException) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$CHECK_YNAB_CONFIG);
        }

        $ynabPayees = $ynabApiService->fetchAllPayees();
        $ynabTransactions = YnabHelper::buildTransactionsFromAccountStatements($configurationAccount[AppConstants::$CONFIG_ACCOUNT_ID], $transactions, $ynabPayees);

        FinTsFacade::clearSessionOfFinTsValues();

        return $ynabApiService->createTransactions($ynabTransactions);
    }
}
