<?php

namespace App\Services;

use App\Contracts\CreditScoreProvider;
use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditEligibilityEvaluator;
use App\Domain\CreditDecision;
use App\Enums\AnalysisStatus;
use App\Jobs\ProcessContractingJob;
use App\Models\CreditAnalysis;
use App\Repositories\CreditAnalysisRepositoryInterface;
use App\Repositories\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;

readonly class CreditAnalysisService
{
    public function __construct(
        private CreditAnalysisRepositoryInterface $analyses,
        private CustomerRepositoryInterface       $customers,
        private CreditScoreProvider               $scoreProvider,
        private CreditEligibilityEvaluator        $evaluator,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function request(array $data): CreditAnalysis
    {
        $analysis = $this->createPendingAnalysis($data);

        $score = $this->scoreProvider->scoreFor($data['cpf']);

        $decision = $this->evaluator->evaluate(new CreditContext(
            $data['renda_mensal'],
            $data['valor_solicitado'],
            $score,
        ));

        return $this->completeAnalysis($analysis, $score, $this->decisionData($decision));
    }

    private function createPendingAnalysis(array $data): CreditAnalysis
    {
        return DB::transaction(function () use ($data) {
            $customer = $this->customers->firstOrCreateByCpf(
                $data['cpf'],
                [
                    'nome' => $data['nome'],
                    'cpf' => $data['cpf'],
                    'renda_mensal' => $data['renda_mensal'],
                ],
            );

            return $this->analyses->create([
                'cliente_id' => $customer->getKey(),
                ...$data,
                'status' => AnalysisStatus::PENDING,
            ]);
        });
    }

    /**
     * @param array<string, mixed> $result
     */
    private function completeAnalysis(CreditAnalysis $analysis, int $score, array $result): CreditAnalysis
    {
        return $this->analyses->update($analysis, ['score' => $score, ...$result]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decisionData(CreditDecision $decision): array
    {
        return [
            'status' => $decision->status,
            'taxa_juros' => $decision->interestRate,
            'valor_parcela' => $decision->installment,
            'motivo_rejeicao' => $decision->rejectionReason,
        ];
    }

    public function startContracting(CreditAnalysis $analysis): ?CreditAnalysis
    {
        if (!$this->analyses->transitionToProcessingContract($analysis)) {
            return null;
        }

        $analysis->status = AnalysisStatus::PROCESSING_CONTRACT;

        ProcessContractingJob::dispatch($analysis);

        return $analysis;
    }
}
