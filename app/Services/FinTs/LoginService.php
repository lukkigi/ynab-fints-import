<?php

namespace App\Services\FinTs;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Helpers\FinTsHelper;
use App\Helpers\MessageHelper;
use App\Services\SessionService;
use Fhp\CurlException;
use Fhp\FinTsNew;
use Fhp\Model\NoPsd2TanMode;
use Fhp\Model\TanMedium;
use Fhp\Model\TanMode;
use Fhp\Protocol\ServerException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;

class LoginService
{
    /**
     * @param array $accountConfiguration
     * @return RedirectResponse
     * @throws CurlException
     * @throws ServerException
     */
    public static function login(array $accountConfiguration) {
        $productName = Config::get(AppConstants::$ENV_PRODUCT_NAME);
        $productVersion = Config::get(AppConstants::$ENV_PRODUCT_VERSION);

        if ($productName == null || empty($productName) || $productVersion == null || empty($productVersion)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$CHECK_PRODUCT_KEYS);
        }

        if (SessionService::isTanMediumPresent()) {
            return self::setUpTanAndLogin();
        }

        $finTsObject = FinTsHelper::createFinTsObject($accountConfiguration[AppConstants::$CONFIG_BANK_URL],
            $accountConfiguration[AppConstants::$CONFIG_BANK_CODE], $accountConfiguration[AppConstants::$CONFIG_USERNAME],
            $accountConfiguration[AppConstants::$CONFIG_PASSWORD], $productName, $productVersion
        );

        if ($accountConfiguration[AppConstants::$CONFIG_TAN_MODE] == '-1') {
            $tanMode = new NoPsd2TanMode();
        } else {
            // TODO: check value first

            $tanMode = $finTsObject->getTanModes()[intval($accountConfiguration[AppConstants::$CONFIG_TAN_MODE])];
        }

        SessionService::putTanMode($tanMode);
        SessionService::putFinTsObject($finTsObject);

        if ($tanMode->needsTanMedium()) {
            SessionService::putAvailableTanMedia($finTsObject->getTanMedia($tanMode));

            return Redirect::route(AppConstants::$ROUTE_CHOOSE_TAN_MEDIUM);
        }

        return self::setUpTanAndLogin();
    }

    /**
     * @return RedirectResponse
     */
    public static function setUpTanAndLogin() {
        $tanMedium = null;

        /** @var FinTsNew $finTsObject */
        $finTsObject = SessionService::getFinTsObject();

        if ($finTsObject == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_FINTS_OBJECT);
        }

        /** @var TanMode $tanMode */
        $tanMode = SessionService::getTanMode();

        if ($tanMode == null) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_TAN_MODE);
        }

        if (SessionService::isTanMediumPresent()) {
            /** @var TanMedium $tanMedium */
            $tanMedium = SessionService::getTanMedium();
        }

        $finTsObject->selectTanMode($tanMode, $tanMedium);

        try {
            $loginRequest = $finTsObject->login();
        } catch (CurlException | ServerException $exception) {
            return MessageHelper::redirectToErrorMessage('login: ' . ErrorConstants::$FINTS_BANK_COMMUNICATION . ': ' . $exception->getMessage());
        }

        SessionService::putFinTsObject($finTsObject);

        if ($loginRequest->needsTan()) {
            SessionService::putTanAction($loginRequest);

            return Redirect::route(AppConstants::$ROUTE_ENTER_TAN);
        }

        return Redirect::route(AppConstants::$ROUTE_FETCH_ACCOUNTS);
    }
}
