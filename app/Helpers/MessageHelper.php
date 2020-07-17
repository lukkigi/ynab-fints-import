<?php

namespace App\Helpers;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use Redirect;
use Session;

class MessageHelper
{
    public static function redirectToErrorMessage(string $errorMessage) {
        Session::flash(ErrorConstants::$ERROR_MESSAGE, $errorMessage);

        return Redirect::route(AppConstants::$ROUTE_START);
    }

    public static function redirectToSuccessMessage() {
        Session::flash('successMessage', 'Your transactions were succesfully imported');

        return Redirect::route(AppConstants::$ROUTE_START);
    }
}
