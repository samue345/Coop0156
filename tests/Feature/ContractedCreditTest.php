<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Models\CreditAnalysis;
use App\Models\Customer;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractedCreditTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_contracts_that_are_processing_or_contracted(): void
    {
        $processingCustomer = Customer::factory()->create([
            'nome' => 'Cliente em Processamento',
        ]);
        $contractedCustomer = Customer::factory()->create([
            'nome' => 'Cliente Contratado',
        ]);

        $processing = CreditAnalysis::factory()->create([
            'cliente_id' => $processingCustomer->id,
            'status' => AnalysisStatus::PROCESSING_CONTRACT,
        ]);
        $contracted = CreditAnalysis::factory()->create([
            'cliente_id' => $contractedCustomer->id,
            'status' => AnalysisStatus::CONTRACTED,
            'updated_at' => CarbonImmutable::create(2026, 8, 31, 15, 30, 0, 'UTC'),
        ]);

        $this->get('/contratacoes')
            ->assertOk()
            ->assertSee('Contratações realizadas')
            ->assertSee($processing->hashids_code)
            ->assertSee('Cliente em Processamento')
            ->assertSee('Processando')
            ->assertSee($contracted->hashids_code)
            ->assertSee('Cliente Contratado')
            ->assertSee('Contratado')
            ->assertSee('31/08/2026 12:30');
    }

    public function test_it_does_not_list_analyses_that_are_not_contracts(): void
    {
        foreach ([AnalysisStatus::PENDING, AnalysisStatus::APPROVED, AnalysisStatus::REJECTED] as $status) {
            $customer = Customer::factory()->create([
                'nome' => "Cliente {$status->value}",
            ]);

            CreditAnalysis::factory()->create([
                'cliente_id' => $customer->id,
                'status' => $status,
            ]);
        }

        $this->get('/contratacoes')
            ->assertOk()
            ->assertSee('Nenhuma contratação encontrada.')
            ->assertDontSee('Cliente pendente')
            ->assertDontSee('Cliente aprovado')
            ->assertDontSee('Cliente reprovado');
    }

    public function test_it_shows_the_empty_contracts_state(): void
    {
        $this->get('/contratacoes')
            ->assertOk()
            ->assertSee('Nenhuma contratação encontrada.');
    }
}
