<?php

namespace App\Domain\CreditAnalysis;

final class CreditContext
{
    public ?float $interestRate = null;

    public ?float $installment = null;

    public function __construct(
        public readonly float $income,
        public readonly float $requestedAmount,
        public readonly int $score,
    ) {
    }
}
