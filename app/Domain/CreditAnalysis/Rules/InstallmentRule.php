<?php

namespace App\Domain\CreditAnalysis\Rules;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditRule;
use App\Domain\CreditDecision;

final class InstallmentRule implements CreditRule
{
    private const int INSTALLMENTS = 12;

    public function evaluate(CreditContext $context): ?CreditDecision
    {
        $context->installment = round(
            ($context->requestedAmount * (1 + ($context->interestRate / 100 * self::INSTALLMENTS)))
            / self::INSTALLMENTS,
            2,
        );

        return null;
    }
}
