<?php

namespace App\Domain\CreditAnalysis\Rules;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditRule;
use App\Domain\CreditDecision;

final class IncomeCommitmentRule implements CreditRule
{
    public function evaluate(CreditContext $context): ?CreditDecision
    {
        return $context->installment > $context->income * 0.3
            ? CreditDecision::rejected('Comprometimento de renda superior a 30%')
            : null;
    }
}
