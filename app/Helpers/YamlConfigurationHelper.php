<?php

namespace App\Helpers;

use App\Constants\AppConstants;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlConfigurationHelper
{
    private static $FILENAME = '/configuration.yaml';

    /**
     * @return mixed|null
     */
    public static function readConfigurationFile()
    {
        // TODO: write yaml value checks

        try {
            return Yaml::parseFile(getcwd() . self::$FILENAME);
        } catch (ParseException $parseException) {
            return null;
        }
    }

    /**
     * @return array
     */
    public static function getAccountsForStartPage()
    {
        $configurationFile = self::readConfigurationFile();
        $accountNames = [];

        foreach ($configurationFile[AppConstants::$CONFIG_ACCOUNTS] as $account) {
            $accountNames[] = [
                'name' => $account[AppConstants::$CONFIG_ACCOUNT_NAME],
                'hash' => self::generateHashForAccount($account)
            ];
        }

        return $accountNames;
    }

    /**
     * @param string $accountHash
     * @return mixed|null
     */
    public static function findAccountByHash(string $accountHash)
    {
        if (empty($accountHash)) {
            return null;
        }

        $configurationFile = self::readConfigurationFile();

        foreach ($configurationFile[AppConstants::$CONFIG_ACCOUNTS] as $account) {
            if (self::generateHashForAccount($account) == $accountHash) {
                return $account;
            }
        }

        return null;
    }

    /**
     * Generates a unique hash for this specific account
     * @param array $account
     * @return string
     */
    private static function generateHashForAccount(array $account)
    {
        return md5($account[AppConstants::$CONFIG_BANK_IBAN] . $account[AppConstants::$CONFIG_USERNAME] . $account[AppConstants::$CONFIG_BUDGET_ID] . $account[AppConstants::$CONFIG_ACCOUNT_ID]);
    }
}
