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

            /* some banks group transactions together by date */
            if (is_array($transaction)) {
                for ($j = 0; $j < count($transaction); $j++) {
                    $ynabTransactions[] = self::createYnabTransactionFromFinTsTransaction($transaction[$j], $accountId, $payees);
                }
            } else {
                $ynabTransactions[] = self::createYnabTransactionFromFinTsTransaction($transaction, $accountId, $payees);
            }
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

    private static function createYnabTransactionFromFinTsTransaction($transaction, $accountId, $payees) {
        $amountPrefix = $transaction->getCreditDebit() == 'credit' ? 1 : -1;

        return [
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
}
