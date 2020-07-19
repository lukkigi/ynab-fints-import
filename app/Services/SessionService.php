<?php

namespace App\Services;

use App\Constants\AppConstants;
use Fhp\BaseAction;
use Fhp\FinTsNew;
use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\TanMedium;
use Fhp\Model\TanMode;
use Illuminate\Support\Facades\Session;

class SessionService
{
    public static function isTanActionPresent()
    {
        return Session::exists(AppConstants::$SESSION_TAN_ACTION);
    }

    public static function getTanAction()
    {
        return self::getFromSessionAndUnserialize(AppConstants::$SESSION_TAN_ACTION);
    }

    public static function putTanAction(BaseAction $tanAction)
    {
        self::serializeAndPutIntoSession($tanAction, AppConstants::$SESSION_TAN_ACTION);
    }

    public static function removeTanAction()
    {
        self::removeFromSession(AppConstants::$SESSION_TAN_ACTION);
    }

    public static function isFinTsObjectPresent()
    {
        return Session::exists(AppConstants::$SESSION_FINTS_OBJECT);
    }

    public static function getFinTsObject()
    {
        return self::getFromSessionAndUnserialize(AppConstants::$SESSION_FINTS_OBJECT);
    }

    public static function putFinTsObject(FinTsNew $finTsObject)
    {
        self::serializeAndPutIntoSession($finTsObject, AppConstants::$SESSION_FINTS_OBJECT);
    }

    public static function isBankStatementsPresent()
    {
        return Session::exists(AppConstants::$SESSION_BANK_STATEMENTS);
    }

    public static function getBankStatements()
    {
        return self::getFromSessionAndUnserialize(AppConstants::$SESSION_BANK_STATEMENTS);
    }

    public static function putBankStatements(array $bankStatements)
    {
        self::serializeAndPutIntoSession($bankStatements, AppConstants::$SESSION_BANK_STATEMENTS);
    }

    public static function isSepaAccountPresent() {
        return Session::exists(AppConstants::$SESSION_SEPA_ACCOUNT);
    }

    public static function getSepaAccount() {
        return self::getFromSessionAndUnserialize(AppConstants::$SESSION_SEPA_ACCOUNT);
    }

    public static function putSepaAccount(SEPAAccount $sepaAccount)
    {
        self::serializeAndPutIntoSession($sepaAccount, AppConstants::$SESSION_SEPA_ACCOUNT);
    }

    public static function isTanMediumPresent()
    {
        return Session::exists(AppConstants::$SESSION_TAN_MEDIUM);
    }

    public static function getTanMedium()
    {
        return self::getFromSessionAndUnserialize(AppConstants::$SESSION_TAN_MEDIUM);
    }

    public static function putTanMedium(TanMedium $tanMedium)
    {
        self::serializeAndPutIntoSession($tanMedium, AppConstants::$SESSION_TAN_MEDIUM);
    }

    public static function getTanMode()
    {
        return self::getFromSessionAndUnserialize(AppConstants::$SESSION_TAN_MODE);
    }

    public static function putTanMode(TanMode $tanMode)
    {
        self::serializeAndPutIntoSession($tanMode, AppConstants::$SESSION_TAN_MODE);
    }

    public static function isAvailableTanMediaPresent()
    {
        return Session::exists(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA);
    }

    public static function getAvailableTanMedia()
    {
        return self::getFromSessionAndUnserialize(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA);
    }

    public static function putAvailableTanMedia(array $availableTanMedia)
    {
        self::serializeAndPutIntoSession($availableTanMedia, AppConstants::$SESSION_AVAILABLE_TAN_MEDIA);
    }

    public static function isCurrentAccountPresent()
    {
        return Session::exists(AppConstants::$SESSION_CURRENT_ACCOUNT);
    }

    public static function getCurrentAccount()
    {
        return self::getFromSession(AppConstants::$SESSION_CURRENT_ACCOUNT);
    }

    public static function putCurrentAccount(array $currentAccount)
    {
        self::putIntoSession($currentAccount, AppConstants::$SESSION_CURRENT_ACCOUNT);
    }

    private static function getFromSessionAndUnserialize(string $key)
    {
        return unserialize(Session::get($key));
    }

    private static function getFromSession(string $key)
    {
        return Session::get($key);
    }

    private static function serializeAndPutIntoSession($object, string $key)
    {
        Session::put($key, serialize($object));
    }

    private static function putIntoSession($object, string $key)
    {
        Session::put($key, $object);
    }

    private static function removeFromSession(string $key)
    {
        Session::remove($key);
    }
}
