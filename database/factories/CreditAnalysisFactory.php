<?php

namespace Database\Factories;

use App\Enums\AnalysisStatus;
use App\Enums\CreditType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CreditAnalysis>
 */
class CreditAnalysisFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Customer::factory(),
            'cpf' => '52998224725',
            'nome' => fake()->name(),
            'renda_mensal' => 10000,
            'tipo_credito' => CreditType::PERSONAL,
            'valor_solicitado' => 10000,
            'status' => AnalysisStatus::APPROVED,
            'score' => 850,
            'taxa_juros' => 2.9,
            'valor_parcela' => 1123.33,
        ];
    }
}
