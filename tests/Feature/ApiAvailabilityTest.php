<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_listing_api_is_available(): void
    {
        Customer::factory()->create();

        $this->getJson('/api/clientes')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'code',
                        'nome',
                        'cpf',
                        'email',
                        'telefone',
                        'renda_mensal',
                    ],
                ],
            ]);
    }

    public function test_credit_analysis_api_is_available_and_returns_validation_errors(): void
    {
        $this->postJson('/api/analise-credito', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nome',
                'cpf',
                'renda_mensal',
                'tipo_credito',
                'valor_solicitado',
            ]);
    }

    public function test_mock_bureau_api_is_available(): void
    {
        $this->getJson('/api/mock/bureau/10000000523')
            ->assertOk()
            ->assertJsonPath('cpf', '10000000523')
            ->assertJsonPath('score', 850);
    }
}
