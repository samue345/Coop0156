<?php

namespace App\Http\Controllers;

use App\Enums\AnalysisStatus;
use App\Exceptions\CreditBureauException;
use App\Http\Requests\RequestCreditAnalysisRequest;
use App\Http\Resources\CreditAnalysisResource;
use App\Models\CreditAnalysis;
use App\Services\CreditAnalysisService;
use Illuminate\Http\JsonResponse;

class CreditAnalysisController extends Controller
{
    public function __construct(
        private readonly CreditAnalysisService $analyses,
    ) {
    }

    /**
     * Solicita uma nova análise de crédito.
     *
     * POST /api/analise-credito
     *
     * Campos esperados no body (JSON):
     *  - nome: string, obrigatório
     *  - cpf: string, obrigatório (11 dígitos)
     *  - renda_mensal: numeric, obrigatório
     *  - tipo_credito: string, obrigatório (pessoal | imobiliario | automotivo)
     *  - valor_solicitado: numeric, obrigatório
     *
     * Fluxo esperado:
     *  1. Validar os dados de entrada.
     *  2. Persistir a análise no banco com status 'pendente'.
     *  3. Consultar a API do Bureau de Crédito (GET /api/mock/bureau/{cpf}) via Http::.
     *  4. Tratar falhas de comunicação com o Bureau (timeout, HTTP 500, resposta malformada).
     *  5. Aplicar as regras de negócio (renda mínima, faixas de score, comprometimento de renda).
     *  6. Atualizar e retornar a análise persistida com o resultado final.
     *
     * @param  RequestCreditAnalysisRequest  $request
     * @return JsonResponse
     */
    public function requestAnalysis(RequestCreditAnalysisRequest $request): JsonResponse
    {
        try {
            $analysis = $this->analyses->request($request->analysisData());
        }
        catch (CreditBureauException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->statusCode);
        }

        return (new CreditAnalysisResource($analysis))->response();
    }

    /**
     * @param  CreditAnalysis  $creditAnalysis
     * @return JsonResponse
     */
    public function contract(CreditAnalysis $creditAnalysis): JsonResponse
    {
        if ($creditAnalysis->status !== AnalysisStatus::APPROVED)
        {
            return response()->json([
                'message' => 'A análise precisa estar aprovada para ser contratada.',
                'status' => $creditAnalysis->status?->value,
            ], 422);
        }

        $creditAnalysis = $this->analyses->startContracting($creditAnalysis);

        return response()->json([
            'message' => 'Contratação enviada para processamento.',
            'status' => $creditAnalysis->status->value,
        ], 202);
    }
}
