<?php

namespace App\Services\FinTs;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Helpers\FinTsHelper;
use App\Helpers\MessageHelper;
use Fhp\CurlException;
use Fhp\FinTsNew;
use Fhp\Model\NoPsd2TanMode;
use Fhp\Model\TanMedium;
use Fhp\Model\TanMode;
use Fhp\Protocol\ServerException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

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

        if (Session::exists(AppConstants::$SESSION_TAN_MEDIUM)) {
            return self::setUpTanAndLogin();
        }

        $finTsObject = FinTsHelper::createFinTsObject($accountConfiguration[AppConstants::$CONFIG_BANK_URL],
            $accountConfiguration[AppConstants::$CONFIG_BANK_CODE], $accountConfiguration[AppConstants::$CONFIG_USERNAME],
            $accountConfiguration[AppConstants::$CONFIG_PASSWORD], $productName, $productVersion
        );

        if ($accountConfiguration[AppConstants::$CONFIG_TAN_MODE] == '-1') {
            $tanMode = new NoPsd2TanMode();
        } else {
            $tanMode = $finTsObject->getTanModes()[intval($accountConfiguration[AppConstants::$CONFIG_TAN_MODE])];
        }

        Session::put(AppConstants::$SESSION_TAN_MODE, serialize($tanMode));

        if ($tanMode->needsTanMedium()) {
            Session::put(AppConstants::$SESSION_FINTS_OBJECT, serialize($finTsObject));
            Session::put(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA, serialize($finTsObject->getTanMedia($tanMode)));

            return Redirect::route(AppConstants::$ROUTE_CHOOSE_TAN_MEDIUM);
        }

        return self::setUpTanAndLogin();
    }

    /**
     * @return RedirectResponse
     */
    public static function setUpTanAndLogin() {
        if (!Session::exists(AppConstants::$SESSION_FINTS_OBJECT)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_FINTS_OBJECT);
        }

        if (!Session::exists(AppConstants::$SESSION_TAN_MODE)) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$SESSION_NO_TAN_MODE);
        }

        /** @var FinTsNew $finTsObject */
        $finTsObject = unserialize(Session::get(AppConstants::$SESSION_FINTS_OBJECT));

        /** @var TanMode $tanMode */
        $tanMode = unserialize(Session::get(AppConstants::$SESSION_TAN_MODE));

        $tanMedium = null;

        if (Session::exists(AppConstants::$SESSION_TAN_MEDIUM)) {
            /** @var TanMedium $tanMedium */
            $tanMedium = unserialize(Session::get(AppConstants::$SESSION_TAN_MEDIUM));
        }

        $finTsObject->selectTanMode($tanMode, $tanMedium);

        try {
            $loginRequest = $finTsObject->login();
        } catch (CurlException | ServerException $exception) {
            return MessageHelper::redirectToErrorMessage(ErrorConstants::$FINTS_BANK_COMMUNICATION);
        }

        Session::put(AppConstants::$SESSION_FINTS_OBJECT, serialize($finTsObject));

        if ($loginRequest->needsTan()) {
            Session::put(AppConstants::$SESSION_TAN_ACTION, serialize($loginRequest));

            return Redirect::route(AppConstants::$ROUTE_ENTER_TAN);
        }

        return Redirect::route(AppConstants::$ROUTE_FETCH_ACCOUNTS);
    }
}
