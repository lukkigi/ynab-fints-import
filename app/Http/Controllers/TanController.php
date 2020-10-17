<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Helpers\MessageHelper;
use App\Services\SessionService;
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
        if (!SessionService::isAvailableTanMediaPresent()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_AVAILABLE_TAN_MEDIA);
        }

        $currentAccountConfiguration = SessionService::getCurrentAccount();

        /** @var TanMedium[] $tanMediaList */
        $tanMediaList = SessionService::getAvailableTanMedia();

        return view('tanMedia', [
            'availableTanMedia' => $tanMediaList,
            'bankName' => $currentAccountConfiguration[AppConstants::$CONFIG_ACCOUNT_NAME]
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

        if (!SessionService::isAvailableTanMediaPresent()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_AVAILABLE_TAN_MEDIA);
        }

        /** @var TanMedium[] $tanMediaList */
        $tanMediaList = SessionService::getAvailableTanMedia();

        SessionService::putTanMedium($tanMediaList[$request->input(self::$TAN_MEDIUM)]);

        return Redirect::route('startLogin');
    }

    /**
     * @return RedirectResponse|View
     */
    public function handleTanRequest()
    {
        if (!SessionService::isTanActionPresent()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_TAN_ACTION);
        }

        $currentAccountConfiguration = SessionService::getCurrentAccount();

        /** @var BaseAction $finTsTanAction */
        $finTsTanAction = SessionService::getTanAction();

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

        if (!SessionService::isTanActionPresent()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_TAN_ACTION);
        }

        /** @var BaseAction $finTsTanAction */
        $finTsTanAction = SessionService::getTanAction();

        if (!$finTsTanAction->needsTan()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_TAN_ACTION_NEEDS_NO_TAN);
        }

        if (!SessionService::isFinTsObjectPresent()) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_FINTS_OBJECT);
        }

        /** @var FinTsNew $finTsObject */
        $finTsObject = SessionService::getFinTsObject();

        try {
            $finTsObject->submitTan($finTsTanAction, $request->input(self::$TAN_CODE));
        } catch (CurlException | ServerException $exception) {
            return MessageHelper::redirectToErrorMessage('submitTan: ' . ErrorConstants::$FINTS_BANK_COMMUNICATION);
        }

        if ($finTsTanAction instanceof DialogInitialization) {
            return Redirect::route(AppConstants::$ROUTE_FETCH_ACCOUNTS);
        } else if ($finTsTanAction instanceof GetSEPAAccounts) {
            return Redirect::route(AppConstants::$ROUTE_FETCH_ACCOUNTS);
        } else if ($finTsTanAction instanceof GetStatementOfAccount) {
            return Redirect::route(AppConstants::$ROUTE_FETCH_TRANSACTIONS);
        }

        return MessageHelper::redirectToErrorMessage('submitTan (no type): ' . ErrorConstants::$FINTS_BANK_COMMUNICATION);
    }
}
