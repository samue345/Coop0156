<?php

namespace App\Domain\CreditAnalysis\Rules;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditRule;
use App\Domain\CreditDecision;

final class MinimumIncomeRule implements CreditRule
{
    public function evaluate(CreditContext $context): ?CreditDecision
    {
        return $context->income < 1500
            ? CreditDecision::rejected('Renda mínima insuficiente')
            : null;
    }
}
