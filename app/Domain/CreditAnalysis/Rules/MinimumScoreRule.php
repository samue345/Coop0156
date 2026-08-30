<?php

namespace App\Domain\CreditAnalysis\Rules;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditRule;
use App\Domain\CreditDecision;

final class MinimumScoreRule implements CreditRule
{
    private const int MINIMUM_APPROVAL_SCORE = 400;

    public function evaluate(CreditContext $context): ?CreditDecision
    {
        return $context->score < self::MINIMUM_APPROVAL_SCORE
            ? CreditDecision::rejected('Score de crédito muito baixo')
            : null;
    }
}
