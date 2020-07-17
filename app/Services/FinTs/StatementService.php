<?php

namespace App\Services\FinTs;

use DateTime;
use Exception;
use Fhp\Action\GetStatementOfAccount;
use Fhp\CurlException;
use Fhp\FinTsNew;
use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Protocol\ServerException;

class StatementService
{
    /**
     * @param FinTsNew $finTsObject
     * @param SEPAAccount $account
     * @param $timeFrom
     * @param $timeTo
     * @return Statement[]|null
     */
    public static function getStatementsFromAccount(FinTsNew $finTsObject, SEPAAccount $account, $timeFrom, $timeTo) {
        if ($finTsObject == null || $account == null) {
            return null;
        }

        if ($timeFrom == null) {
            $timeFrom = new DateTime('1970-01-01');
        }

        if ($timeTo == null) {
            $timeTo = new DateTime();
        }

        $statementAction = GetStatementOfAccount::create($account, $timeFrom, $timeTo);

        try {
            $finTsObject->execute($statementAction);
        } catch (CurlException | ServerException $e) {
            return null;
        }

        if ($statementAction->needsTan()) {
            // TODO: is this needed?
        }

        return StatementService::handleSuccessfulStatementRequest($statementAction);
    }

    /**
     * @param GetStatementOfAccount $statementAction
     * @return Statement[]|null
     * @throws Exception
     */
    public static function handleSuccessfulStatementRequest(GetStatementOfAccount $statementAction) {
        if ($statementAction == null) {
            return null;
        }

        $statementOfAccount = $statementAction->getStatement();
        $statementOfAccountStatements = $statementOfAccount->getStatements();

        if (count($statementOfAccountStatements) == 0) {
            return null;
        }

        return $statementOfAccountStatements;
    }
}
