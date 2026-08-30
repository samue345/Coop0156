<?php

namespace Tests\Unit\Domain\CreditAnalysis;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditEligibilityEvaluator;
use App\Domain\CreditAnalysis\Rules\IncomeCommitmentRule;
use App\Domain\CreditAnalysis\Rules\InstallmentRule;
use App\Domain\CreditAnalysis\Rules\InterestRateRule;
use App\Domain\CreditAnalysis\Rules\MinimumIncomeRule;
use App\Domain\CreditAnalysis\Rules\MinimumScoreRule;
use App\Enums\AnalysisStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CreditEligibilityEvaluatorTest extends TestCase
{
    private const float INCOME_BELOW_MINIMUM = 1499.99;
    private const float MINIMUM_ALLOWED_INCOME = 1500.00;
    private const float LOW_REQUESTED_AMOUNT = 1000.00;
    private const float STANDARD_INCOME = 10000.00;
    private const float STANDARD_REQUESTED_AMOUNT = 10000.00;
    private const float REQUESTED_AMOUNT_AT_COMMITMENT_LIMIT = 9000.00;
    private const float INCOME_WITH_INSTALLMENT_EXACTLY_AT_THIRTY_PERCENT = 3370.00;
    private const float INCOME_WITH_INSTALLMENT_ABOVE_THIRTY_PERCENT = 3369.99;

    private const int APPROVABLE_HIGH_SCORE = 850;
    private const int SCORE_BELOW_MINIMUM = 399;
    private const int MEDIUM_SCORE_MINIMUM = 400;
    private const int MEDIUM_SCORE_MAXIMUM = 699;
    private const int HIGH_SCORE_MINIMUM = 700;

    private const float MEDIUM_SCORE_MONTHLY_RATE = 4.5;
    private const float HIGH_SCORE_MONTHLY_RATE = 2.9;
    private const float STANDARD_HIGH_SCORE_INSTALLMENT = 1123.33;

    #[DataProvider('minimumIncomeCases')]
    public function test_it_rejects_only_income_below_the_minimum(float $income, AnalysisStatus $expectedStatus): void
    {
        $decision = $this->evaluator()->evaluate(new CreditContext(
            $income,
            self::LOW_REQUESTED_AMOUNT,
            self::APPROVABLE_HIGH_SCORE,
        ));

        $this->assertSame($expectedStatus, $decision->status);
        $this->assertSame(
            $expectedStatus === AnalysisStatus::REJECTED ? 'Renda mínima insuficiente' : null,
            $decision->rejectionReason,
        );
    }

    /**
     * @return array<string, array{float, AnalysisStatus}>
     */
    public static function minimumIncomeCases(): array
    {
        return [
            'below minimum' => [self::INCOME_BELOW_MINIMUM, AnalysisStatus::REJECTED],
            'at minimum' => [self::MINIMUM_ALLOWED_INCOME, AnalysisStatus::APPROVED],
        ];
    }

    #[DataProvider('scoreCases')]
    public function test_it_applies_score_thresholds(int $score, AnalysisStatus $expectedStatus, ?float $expectedRate): void
    {
        $decision = $this->evaluator()->evaluate(new CreditContext(
            self::STANDARD_INCOME,
            self::STANDARD_REQUESTED_AMOUNT,
            $score,
        ));

        $this->assertSame($expectedStatus, $decision->status);
        $this->assertSame($expectedRate, $decision->interestRate);
        $this->assertSame(
            $expectedStatus === AnalysisStatus::REJECTED ? 'Score de crédito muito baixo' : null,
            $decision->rejectionReason,
        );
    }

    /**
     * @return array<string, array{int, AnalysisStatus, float|null}>
     */
    public static function scoreCases(): array
    {
        return [
            'below minimum score' => [self::SCORE_BELOW_MINIMUM, AnalysisStatus::REJECTED, null],
            'at medium score minimum' => [
                self::MEDIUM_SCORE_MINIMUM,
                AnalysisStatus::APPROVED,
                self::MEDIUM_SCORE_MONTHLY_RATE,
            ],
            'at medium score maximum' => [
                self::MEDIUM_SCORE_MAXIMUM,
                AnalysisStatus::APPROVED,
                self::MEDIUM_SCORE_MONTHLY_RATE,
            ],
            'at high score minimum' => [
                self::HIGH_SCORE_MINIMUM,
                AnalysisStatus::APPROVED,
                self::HIGH_SCORE_MONTHLY_RATE,
            ],
        ];
    }

    public function test_it_calculates_installment_with_simple_interest_over_twelve_months(): void
    {
        $decision = $this->evaluator()->evaluate(new CreditContext(
            self::STANDARD_INCOME,
            self::STANDARD_REQUESTED_AMOUNT,
            self::HIGH_SCORE_MINIMUM,
        ));

        $this->assertSame(AnalysisStatus::APPROVED, $decision->status);
        $this->assertSame(self::HIGH_SCORE_MONTHLY_RATE, $decision->interestRate);
        $this->assertSame(self::STANDARD_HIGH_SCORE_INSTALLMENT, $decision->installment);
    }

    #[DataProvider('incomeCommitmentCases')]
    public function test_it_rejects_only_installments_above_thirty_percent_of_income(
        float $income,
        float $requestedAmount,
        AnalysisStatus $expectedStatus,
    ): void {
        $decision = $this->evaluator()->evaluate(new CreditContext(
            $income,
            $requestedAmount,
            self::HIGH_SCORE_MINIMUM,
        ));

        $this->assertSame($expectedStatus, $decision->status);
        $this->assertSame(
            $expectedStatus === AnalysisStatus::REJECTED ? 'Comprometimento de renda superior a 30%' : null,
            $decision->rejectionReason,
        );
    }

    /**
     * @return array<string, array{float, float, AnalysisStatus}>
     */
    public static function incomeCommitmentCases(): array
    {
        return [
            'exactly thirty percent' => [
                self::INCOME_WITH_INSTALLMENT_EXACTLY_AT_THIRTY_PERCENT,
                self::REQUESTED_AMOUNT_AT_COMMITMENT_LIMIT,
                AnalysisStatus::APPROVED,
            ],
            'above thirty percent' => [
                self::INCOME_WITH_INSTALLMENT_ABOVE_THIRTY_PERCENT,
                self::REQUESTED_AMOUNT_AT_COMMITMENT_LIMIT,
                AnalysisStatus::REJECTED,
            ],
        ];
    }

    private function evaluator(): CreditEligibilityEvaluator
    {
        return new CreditEligibilityEvaluator([
            new MinimumIncomeRule(),
            new MinimumScoreRule(),
            new InterestRateRule(),
            new InstallmentRule(),
            new IncomeCommitmentRule(),
        ]);
    }
}
