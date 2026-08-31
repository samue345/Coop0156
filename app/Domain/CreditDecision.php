<?php

namespace App\Domain;

use App\Enums\AnalysisStatus;

final readonly class CreditDecision
{
    private function __construct(
        public AnalysisStatus $status,
        public ?float $interestRate,
        public ?float $installment,
        public ?string $rejectionReason,
    )
    { }

    public static function approved(float $interestRate, float $installment,): self
    {
        return new self(
            AnalysisStatus::APPROVED,
            $interestRate,
            $installment,
            null,
        );
    }

    public static function rejected(string $reason): self
    {
        return new self(
            AnalysisStatus::REJECTED,
            null,
            null,
            $reason,
        );
    }

}
