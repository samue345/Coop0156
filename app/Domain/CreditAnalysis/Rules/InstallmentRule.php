<?php

namespace App\Domain\CreditAnalysis\Rules;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditRule;
use App\Domain\CreditDecision;

final class InstallmentRule implements CreditRule
{
    private const int INSTALLMENTS = 12;

    private const int PERCENT_DIVISOR = 100;

    public function evaluate(CreditContext $context): ?CreditDecision
    {
        $context->installment = round(
            ($context->requestedAmount * (1 + ($context->interestRate / self::PERCENT_DIVISOR * self::INSTALLMENTS)))
            / self::INSTALLMENTS,
            2,
        );

        return null;
    }
}
