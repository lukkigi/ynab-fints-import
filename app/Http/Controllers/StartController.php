<?php

namespace App\Http\Controllers;

use App\Helpers\YamlConfigurationHelper;
use Illuminate\View\View;

class StartController extends Controller
{
    /**
     * @return View
     */
    public function start() {
        $configurationContents = YamlConfigurationHelper::readConfigurationFile();

        return view('start', [
            'accounts' => empty($configurationContents) ? [] : YamlConfigurationHelper::getAccountsForStartPage()
        ]);
    }
}
