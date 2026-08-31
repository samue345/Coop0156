<?php

namespace App\Http\Requests;

use App\Rules\ValidCpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
            'cpf' => ['required', new ValidCpf(), Rule::unique('clientes', 'cpf')],
            'email' => ['required', 'email', 'max:255', Rule::unique('clientes', 'email')],
            'telefone' => ['nullable', 'string', 'max:20'],
            'renda_mensal' => ['required', 'numeric', 'gt:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function customerData(): array
    {
        return $this->safe()->only([
            'nome',
            'cpf',
            'email',
            'telefone',
            'renda_mensal',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'cpf' => 'CPF',
            'email' => 'e-mail',
            'telefone' => 'telefone',
            'renda_mensal' => 'renda mensal',
        ];
    }
}
