<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Rules\ValidCpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
            'nome' => ['sometimes', 'required', 'string', 'max:255'],
            'cpf' => [
                'sometimes',
                'required',
                new ValidCpf(),
                Rule::unique('clientes', 'cpf')->ignore($this->customer()?->getKey()),
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('clientes', 'email')->ignore($this->customer()?->getKey()),
            ],
            'telefone' => ['nullable', 'string', 'max:20'],
            'renda_mensal' => ['sometimes', 'required', 'numeric', 'gt:0'],
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

    private function customer(): ?Customer
    {
        $customer = $this->route('customer');

        return $customer instanceof Customer ? $customer : null;
    }
}
