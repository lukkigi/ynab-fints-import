<?php

namespace App\Console\Commands;

use App\Constants\AppConstants;
use App\Constants\ErrorConstants;
use App\Facades\YnabImportFacade;
use App\Helpers\YamlConfigurationHelper;
use App\Services\FinTs\FinTsService;
use Fhp\CurlException;
use Fhp\Protocol\ServerException;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;

class RunAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:account {hash}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run an account import when no TANs are needed';

    /**
     * Execute the console command.
     *
     * @throws ServerException
     * @throws CurlException
     */
    public function handle()
    {
        $accountHash = $this->argument('hash');

        if (empty($accountHash)) {
            $this->error('accountHash is missing from arguments');
        }

        $accountToUse = YamlConfigurationHelper::findAccountByHash($accountHash);

        if ($accountToUse == null) {
            $this->error('Could not find an account with the given hash.');

            return;
        }

        $result = FinTsService::login($accountToUse);

        if (array_key_exists(ErrorConstants::$ERROR_MESSAGE, $result)) {
            $this->error($result[ErrorConstants::$ERROR_MESSAGE]);
        } else if (array_key_exists(AppConstants::$SESSION_AVAILABLE_TAN_MEDIA, $result)) {
            $this->error("Can't use this when a TAN is needed");
        } else if (array_key_exists(AppConstants::$SESSION_TAN_ACTION, $result)) {
            $this->error("Can't use this when a TAN is needed");
        } else if (array_key_exists(AppConstants::$SESSION_BANK_STATEMENTS, $result)) {
            if (array_key_exists(AppConstants::$SESSION_FINTS_OBJECT, $result)) {
                FinTsService::closeFinTsSession($result[AppConstants::$SESSION_FINTS_OBJECT]);
            }

            /** @var RedirectResponse $redirect */
            $redirect = YnabImportFacade::importBankStatements($result[AppConstants::$SESSION_BANK_STATEMENTS], $accountToUse);

            if ($redirect->getSession()->exists('successMessage')) {
                $this->info('Import was successful');
            } else {
                $this->error('There was an error while running the import');
            }
        }

        $this->error('There was an error while running the import');
    }
}
