<?php

namespace App\Services;

use App\Domain\CreditAnalysis\CreditContext;
use App\Domain\CreditAnalysis\CreditEligibilityEvaluator;
use App\Enums\AnalysisStatus;
use App\Integrations\CreditBureauClient;
use App\Models\CreditAnalysis;
use App\Repositories\CreditAnalysisRepositoryInterface;
use App\Repositories\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreditAnalysisService
{
    public function __construct(
        private readonly CreditAnalysisRepositoryInterface $analyses,
        private readonly CustomerRepositoryInterface $customers,
        private readonly CreditBureauClient $bureau,
        private readonly CreditEligibilityEvaluator $evaluator,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function request(array $data): CreditAnalysis
    {
        $analysis = $this->createPendingAnalysis($data);

        $score = $this->bureau->scoreFor($data['cpf']);

        $decision = $this->evaluator->evaluate(new CreditContext(
            (float) $data['renda_mensal'],
            (float) $data['valor_solicitado'],
            $score,
        ));

        return $this->completeAnalysis($analysis, $score, $decision->toArray());
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

}
