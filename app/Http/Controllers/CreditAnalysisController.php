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
     * @param  RequestCreditAnalysisRequest  $request
     * @return JsonResponse
     */
    public function requestAnalysis(RequestCreditAnalysisRequest $request): JsonResponse
    {
        try {
            $analysis = $this->analyses->request($request->analysisData());
        }
        catch (CreditBureauException $exception)
        {
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
        if (!$creditAnalysis->status->canBeContracted()) {
            return response()->json([
                'message' => 'A análise precisa estar aprovada para ser contratada.',
                'status' => $creditAnalysis->status?->value,
            ], 422);
        }

        $creditAnalysis = $this->analyses->startContracting($creditAnalysis);

        if (!$creditAnalysis) {
            return response()->json([
                'message' => 'A análise não está mais disponível para contratação.',
            ], 409);
        }

        return response()->json([
            'message' => 'Contratação solicitada com sucesso. Acompanhe o status em Contratações.',
            'status' => $creditAnalysis->status->value,
        ], 202);
    }
}
