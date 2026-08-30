<?php

namespace App\Domain\CreditAnalysis\Rules;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditRule;
use App\Domain\CreditDecision;

final class IncomeCommitmentRule implements CreditRule
{
    private const int MAXIMUM_INCOME_COMMITMENT_PERCENTAGE = 30;
    private const int PERCENT_DIVISOR = 100;

    public function evaluate(CreditContext $context): ?CreditDecision
    {
        $maximumInstallment = $context->income
            * self::MAXIMUM_INCOME_COMMITMENT_PERCENTAGE
            / self::PERCENT_DIVISOR;

        return $context->installment > $maximumInstallment
            ? CreditDecision::rejected('Comprometimento de renda superior a '.self::MAXIMUM_INCOME_COMMITMENT_PERCENTAGE.'%')
            : null;
    }
}
