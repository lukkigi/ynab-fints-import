<?php

namespace App\Helpers;

use Fhp\FinTsNew;
use Fhp\Model\SEPAAccount;

class FinTsHelper
{
    /**
     * @param string $bankUrl
     * @param string $bankCode
     * @param string $username
     * @param string $pin
     * @param string $productName
     * @param string $productVersion
     * @param string|null $persistedInstance
     * @return FinTsNew
     */
    public static function createFinTsObject(string $bankUrl,
                                             string $bankCode,
                                             string $username,
                                             string $pin,
                                             string $productName,
                                             string $productVersion,
                                             ?string $persistedInstance = null)
    {
        return new FinTsNew($bankUrl, $bankCode, $username, $pin, $productName, $productVersion, $persistedInstance);
    }

    /**
     * @param SEPAAccount[] $sepaAccounts
     * @param string $ibanToFind
     * @return SEPAAccount
     */
    public static function findAccountWithIban(array $sepaAccounts, string $ibanToFind)
    {
        if (empty($ibanToFind)) {
            return null;
        }

        foreach ($sepaAccounts as $account) {
            if ($account->getIban() == $ibanToFind) {
                return $account;
            }
        }

        return null;
    }
}
