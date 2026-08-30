<?php

namespace App\Domain\CreditAnalysis\Rules;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditRule;
use App\Domain\CreditDecision;

final class InterestRateRule implements CreditRule
{
    private const int HIGH_SCORE_THRESHOLD = 700;
    private const float HIGH_SCORE_MONTHLY_RATE = 2.9;
    private const float MEDIUM_SCORE_MONTHLY_RATE = 4.5;

    public function evaluate(CreditContext $context): ?CreditDecision
    {
        $context->interestRate = match (true) {
            $context->score >= self::HIGH_SCORE_THRESHOLD => self::HIGH_SCORE_MONTHLY_RATE,
            default => self::MEDIUM_SCORE_MONTHLY_RATE,
        };

        return null;
    }
}
