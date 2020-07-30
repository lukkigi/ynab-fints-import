<?php


namespace App\Facades;


use App\Constants\ErrorConstants;
use App\Helpers\MessageHelper;
use App\Services\SessionService;
use App\Services\Ynab\YnabImportService;

class YnabImportFacade
{
    /**
     * @param array $bankStatements
     * @return mixed
     */
    public static function importBankStatements(array $bankStatements)
    {
        if (!SessionService::isCurrentAccountPresent()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_ACCOUNT_CONFIGURATION);
        }

        /** @var array $currentConfigurationAccount */
        $accountConfiguration = SessionService::getCurrentAccount();

        $createTransactionsResponse = YnabImportService::importBankStatements($bankStatements, $accountConfiguration);

        // 201 = transactions created, 409 = no new transactions found
        if ($createTransactionsResponse->getStatusCode() == 201 || $createTransactionsResponse->getStatusCode() == 409) {
            return MessageHelper::redirectToSuccessMessage();
        }

        return MessageHelper::redirectToErrorMessage(ErrorConstants::$YNAB_UPLOAD_TRANSACTIONS);
    }
}
