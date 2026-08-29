<?php

namespace App\Domain\CreditAnalysis;

use App\Domain\CreditDecision;

interface CreditRule
{
    public function evaluate(CreditContext $context): ?CreditDecision;
}
