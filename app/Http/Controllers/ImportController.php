<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Facades\FinTsFacade;
use App\Helpers\MessageHelper;
use App\Helpers\YamlConfigurationHelper;
use App\Services\SessionService;
use Fhp\CurlException;
use Fhp\Protocol\ServerException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ImportController extends Controller
{
    public function getDetails() {
        return view('getDetails');
    }

    public function saveDetails(Request $request) {
        $request->validate([
            'bank_url' => 'required',
            'bank_code' => 'required|numeric',
            'bank_iban' => 'required|alpha_num',
            'username' => 'required',
            'password' => 'required',
            'tan_mode' => 'required|numeric',
            'budget_id' => 'required',
            'account_id' => 'required'
        ]);

        SessionService::putCurrentAccount($request->all());

        return FinTsFacade::login($request->all());
    }

    /**
     * @param string $accountHash
     * @return RedirectResponse|mixed
     * @throws CurlException
     * @throws ServerException
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
