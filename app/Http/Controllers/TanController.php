<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Helpers\MessageHelper;
use Fhp\Action\GetSEPAAccounts;
use Fhp\Action\GetStatementOfAccount;
use Fhp\BaseAction;
use Fhp\CurlException;
use Fhp\FinTsNew;
use Fhp\Model\TanMedium;
use Fhp\Model\TanRequestChallengeImage;
use Fhp\Protocol\DialogInitialization;
use Fhp\Protocol\ServerException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class TanController
{
    private static $TAN_MEDIUM = 'tanMedium';
    private static $TAN_CODE = 'tanCode';

    /**
     * @return RedirectResponse|View
     */
    public function chooseTanMedium()
    {
        if (!Session::exists(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_AVAILABLE_TAN_MEDIA);
        }

        $currentAccountConfiguration = Session::get(AppConstants::$SESSION_CURRENT_ACCOUNT);

        /** @var TanMedium[] $tanMediaList */
        $tanMediaList = unserialize(Session::get(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA));

        return view('tanMedia', [
            'availableTanMedia' => $tanMediaList,
            'bankName' => $currentAccountConfiguration['account_name']
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function selectTanMedium(Request $request) {
        if ($request->input(self::$TAN_MEDIUM) == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$REQUEST_MISSING_TAN_MEDIUM);
        }

        if (!Session::exists(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_AVAILABLE_TAN_MEDIA);
        }

        /** @var TanMedium[] $tanMediaList */
        $tanMediaList = unserialize(Session::get(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA));

        Session::put(AppConstants::$SESSION_TAN_MEDIUM, serialize($tanMediaList[$request->input(self::$TAN_MEDIUM)]));

        return Redirect::route('startLogin');
    }

    /**
     * @return RedirectResponse|View
     */
    public function handleTanRequest()
    {
        if (!Session::exists(AppConstants::$SESSION_TAN_ACTION)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_TAN_ACTION);
        }

        $currentAccountConfiguration = Session::get(AppConstants::$SESSION_CURRENT_ACCOUNT);

        /** @var BaseAction $finTsTanAction */
        $finTsTanAction = unserialize(Session::get(AppConstants::$SESSION_TAN_ACTION));

        if (!$finTsTanAction->needsTan()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_TAN_ACTION_NEEDS_NO_TAN);
        }

        $tanRequest = $finTsTanAction->getTanRequest();
        $tanMedium = $tanRequest->getTanMediumName();
        $tanChallengeImageMimeType = null;
        $tanChallengeImageData = null;

        if ($tanRequest->getChallengeHhdUc()) {
            $tanChallengeImage = new TanRequestChallengeImage($tanRequest->getChallengeHhdUc());
            $tanChallengeImageMimeType = $tanChallengeImage->getMimeType();
            $tanChallengeImageData = base64_encode($tanChallengeImage->getData());
        }

        return view('tan', [
            'tanMedium' => $tanMedium,
            'tanChallengeImageMimeType' => $tanChallengeImageMimeType,
            'tanChallengeImageData' => $tanChallengeImageData,
            'bankName' => $currentAccountConfiguration['account_name']
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|null
     */
    public function submitTanRequest(Request $request)
    {
        if ($request->input(self::$TAN_CODE) == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$REQUEST_MISSING_TAN_CODE);
        }

        if (!Session::exists('finTsTanAction')) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_TAN_ACTION);
        }

        /** @var BaseAction $finTsTanAction */
        $finTsTanAction = unserialize(Session::get(AppConstants::$SESSION_TAN_ACTION));

        if (!$finTsTanAction->needsTan()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_TAN_ACTION_NEEDS_NO_TAN);
        }

        if (!Session::exists(AppConstants::$SESSION_FINTS_OBJECT)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_FINTS_OBJECT);
        }

        /** @var FinTsNew $finTsObject */
        $finTsObject = unserialize(Session::get(AppConstants::$SESSION_FINTS_OBJECT));

        try {
            $finTsObject->submitTan($finTsTanAction, $request->input(self::$TAN_CODE));
        } catch (CurlException | ServerException $exception) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_BANK_COMMUNICATION);
        }

        if ($finTsTanAction instanceof DialogInitialization) {
            return Redirect::route(AppConstants::$ROUTE_FETCH_ACCOUNTS);
        } else if ($finTsTanAction instanceof GetSEPAAccounts) {
            return Redirect::route(AppConstants::$ROUTE_FETCH_ACCOUNTS);
        } else if ($finTsTanAction instanceof GetStatementOfAccount) {
            return Redirect::route(AppConstants::$ROUTE_FETCH_TRANSACTIONS);
        }

        return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_BANK_COMMUNICATION);
    }
}
