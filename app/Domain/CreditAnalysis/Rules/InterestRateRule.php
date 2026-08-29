<?php

namespace App\Domain\CreditAnalysis\Rules;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditRule;
use App\Domain\CreditDecision;

final class InterestRateRule implements CreditRule
{
    public function evaluate(CreditContext $context): ?CreditDecision
    {
        $context->interestRate = match (true) {
            $context->score >= 700 => 2.9,
            default => 4.5,
        };

        return null;
    }
}
