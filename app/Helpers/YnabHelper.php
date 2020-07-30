<?php

namespace App\Helpers;

use DateTimeInterface;

class YnabHelper
{
    /**
     * @param string $accountId
     * @param $transactions
     * @param $payees
     * @return array|null
     */
    public static function buildTransactionsFromAccountStatements(string $accountId, $transactions, $payees)
    {
        if (empty($transactions)) {
            return null;
        }

        $ynabTransactions = [];

        /* banks send these transactions differently sometimes - this is a dirty fix */
        if (count($transactions) == 1 && is_array($transactions[0])) {
            $transactions = $transactions[0];
        }

        for ($i = 0; $i < count($transactions); $i++) {
            $transaction = $transactions[$i];

            /* same as above */
            if (is_array($transaction)) {
                $transaction = $transaction[0];
            }

            $amountPrefix = $transaction->getCreditDebit() == 'credit' ? 1 : -1;

            $ynabTransactions[] = [
                'account_id' => $accountId,
                'date' => $transaction->getValutaDate()->format('Y-m-d'),
                'amount' => intval($transaction->getAmount() * $amountPrefix * 1000),
                'payee_id' => YnabHelper::findPayeeIdByName($payees, $transaction->getName()),
                'payee_name' => substr($transaction->getName(), 0, 50),
                'category_id' => null,
                'memo' => substr($transaction->getBookingText() . ' / ' . $transaction->getDescription1(), 0, 200),
                'cleared' => 'cleared',
                'import_id' => md5($transaction->getName() . $transaction->getValutaDate()->format(DateTimeInterface::ATOM) . $transaction->getDescription1())
            ];
        }

        return $ynabTransactions;
    }

    /**
     * @param array $payees
     * @param string $payeeName
     * @return mixed|null
     */
    private static function findPayeeIdByName(array $payees, string $payeeName)
    {
        if (empty($payees) || empty($payeeName)) {
            return null;
        }

        foreach ($payees as $payee) {
            if ($payee['name'] == $payeeName) {
                return $payee['id'];
            }
        }

        return null;
    }
}
