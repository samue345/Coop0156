<?php

namespace App\Domain\CreditAnalysis;

use App\Domain\CreditDecision;

final readonly class CreditEligibilityEvaluator
{
    /**
     * @param iterable<CreditRule> $rules
     */
    public function __construct(
        private iterable $rules,
    ) {
    }

    public function evaluate(CreditContext $context): CreditDecision
    {
        foreach ($this->rules as $rule) {
            $decision = $rule->evaluate($context);

            if ($decision !== null) {
                return $decision;
            }
        }

        return CreditDecision::approved(
            (float) $context->interestRate,
            (float) $context->installment,
        );
    }
}
