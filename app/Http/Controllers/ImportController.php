<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Facades\FinTsFacade;
use App\Helpers\MessageHelper;
use App\Helpers\YamlConfigurationHelper;
use App\Services\SessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class ImportController extends Controller
{
    /**
     * @param string $accountHash
     * @return RedirectResponse
     */
    public function startImportFromPreset(string $accountHash)
    {
        FinTsFacade::clearSessionOfFinTsValues();
        Session::remove(AppConstants::$SESSION_CURRENT_ACCOUNT);

        if (empty($accountHash)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$REQUEST_MISSING_IMPORT_HASH);
        }

        $accountToUse = YamlConfigurationHelper::findAccountByHash($accountHash);

        if ($accountToUse == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$CHECK_IMPORT_HASH);
        }

        SessionService::putCurrentAccount($accountToUse);

        return FinTsFacade::login($accountToUse);
    }
}
