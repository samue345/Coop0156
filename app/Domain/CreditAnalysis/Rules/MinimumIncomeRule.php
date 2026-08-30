<?php

namespace App\Domain\CreditAnalysis\Rules;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditRule;
use App\Domain\CreditDecision;

final class MinimumIncomeRule implements CreditRule
{
    private const int MINIMUM_MONTHLY_INCOME = 1500;

    public function evaluate(CreditContext $context): ?CreditDecision
    {
        return $context->income < self::MINIMUM_MONTHLY_INCOME
            ? CreditDecision::rejected('Renda mínima insuficiente')
            : null;
    }
}
