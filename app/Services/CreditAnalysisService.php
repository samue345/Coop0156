<?php

namespace App\Services;

use App\Enums\AnalysisStatus;
use App\Exceptions\CreditBureauException;
use App\Models\CreditAnalysis;
use App\Repositories\CreditAnalysisRepositoryInterface;
use App\Repositories\CustomerRepositoryInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CreditAnalysisService
{
    private const INSTALLMENTS = 12;

    public function __construct(
        private readonly CreditAnalysisRepositoryInterface $analyses,
        private readonly CustomerRepositoryInterface $customers,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function request(array $data): CreditAnalysis
    {
        $customer = $this->customers->firstOrCreateByCpf(
            $data['cpf'],
            [
                'nome' => $data['nome'],
                'cpf' => $data['cpf'],
                'renda_mensal' => $data['renda_mensal'],
            ],
        );

        $analysis = $this->analyses->create([
            'cliente_id' => $customer->getKey(),
            ...$data,
            'status' => AnalysisStatus::PENDING,
        ]);

        $score = $this->fetchScore($data['cpf']);
        $result = $this->evaluate(
            (float) $data['renda_mensal'],
            (float) $data['valor_solicitado'],
            $score,
        );

        return $this->analyses->update($analysis, [
            'score' => $score,
            ...$result,
        ]);
    }

    private function fetchScore(string $cpf): int
    {
        try {
            $response = Http::timeout((int) config('services.score_bureau.timeout', 3))
                ->get(rtrim((string) config('services.score_bureau.url'), '/') . '/' . $cpf);
        } catch (ConnectionException) {
            throw new CreditBureauException('Não foi possível consultar o Bureau de Crédito.', 504);
        }

        if ($response->failed()) {
            throw new CreditBureauException('O Bureau de Crédito está indisponível.');
        }

        $score = $response->json('score');

        if (! is_int($score) && ! (is_string($score) && ctype_digit($score))) {
            throw new CreditBureauException('O Bureau de Crédito retornou uma resposta inválida.');
        }

        return (int) $score;
    }

    /**
     * @return array{status: AnalysisStatus, taxa_juros: float|null, valor_parcela: float|null, motivo_rejeicao: string|null}
     */
    private function evaluate(float $income, float $requestedAmount, int $score): array
    {
        if ($income < 1500) {
            return $this->rejected('Renda mínima insuficiente');
        }

        if ($score < 400) {
            return $this->rejected('Score de crédito muito baixo');
        }

        $interestRate = $score >= 700 ? 2.9 : 4.5;
        $installment = ($requestedAmount * (1 + ($interestRate / 100 * self::INSTALLMENTS))) / self::INSTALLMENTS;

        if ($installment > $income * 0.3) {
            return $this->rejected('Comprometimento de renda superior a 30%');
        }

        return [
            'status' => AnalysisStatus::APPROVED,
            'taxa_juros' => $interestRate,
            'valor_parcela' => round($installment, 2),
            'motivo_rejeicao' => null,
        ];
    }

    /**
     * @return array{status: AnalysisStatus, taxa_juros: null, valor_parcela: null, motivo_rejeicao: string}
     */
    private function rejected(string $reason): array
    {
        return [
            'status' => AnalysisStatus::REJECTED,
            'taxa_juros' => null,
            'valor_parcela' => null,
            'motivo_rejeicao' => $reason,
        ];
    }
}
