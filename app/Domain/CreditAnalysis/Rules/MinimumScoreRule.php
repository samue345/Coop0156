<?php

namespace App\Domain\CreditAnalysis\Rules;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditRule;
use App\Domain\CreditDecision;

final class MinimumScoreRule implements CreditRule
{
    public function evaluate(CreditContext $context): ?CreditDecision
    {
        return $context->score < 400
            ? CreditDecision::rejected('Score de crédito muito baixo')
            : null;
    }
}
