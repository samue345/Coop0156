<?php

namespace App\Http\Requests;

use App\Enums\CreditType;
use App\Rules\ValidCpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestCreditAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', new ValidCpf()],
            'renda_mensal' => ['required', 'numeric', 'gt:0'],
            'tipo_credito' => ['required', Rule::enum(CreditType::class)],
            'valor_solicitado' => ['required', 'numeric', 'gt:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'cpf' => 'CPF',
            'renda_mensal' => 'renda mensal',
            'tipo_credito' => 'tipo de crédito',
            'valor_solicitado' => 'valor solicitado',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function analysisData(): array
    {
        return $this->safe()->only([
            'nome',
            'cpf',
            'renda_mensal',
            'tipo_credito',
            'valor_solicitado',
        ]);
    }
}
