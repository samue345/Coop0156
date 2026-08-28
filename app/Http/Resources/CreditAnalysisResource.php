<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditAnalysisResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cpf' => $this->cpf,
            'nome' => $this->nome,
            'renda_mensal' => $this->renda_mensal,
            'tipo_credito' => $this->tipo_credito?->value,
            'valor_solicitado' => $this->valor_solicitado,
            'status' => $this->status?->value,
            'score' => $this->score,
            'taxa_juros' => $this->taxa_juros,
            'valor_parcela' => $this->valor_parcela,
            'motivo_rejeicao' => $this->motivo_rejeicao,
        ];
    }
}
