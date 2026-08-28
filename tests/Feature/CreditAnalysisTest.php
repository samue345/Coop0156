<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Models\CreditAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreditAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_approves_an_analysis_and_creates_the_customer(): void
    {
        Http::fake(['*' => Http::response(['score' => 850], 200)]);

        $response = $this->postJson('/api/analise-credito', [
            'cpf' => '52998224725',
            'nome' => 'João da Silva',
            'renda_mensal' => 3000,
            'tipo_credito' => 'pessoal',
            'valor_solicitado' => 5000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'aprovado')
            ->assertJsonPath('data.score', 850);

        $this->assertDatabaseHas('clientes', ['cpf' => '52998224725']);
        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '52998224725',
            'status' => AnalysisStatus::APPROVED->value,
        ]);
    }

    public function test_it_contracts_an_approved_analysis(): void
    {
        $analysis = CreditAnalysis::create([
            'cpf' => '52998224725',
            'nome' => 'João da Silva',
            'renda_mensal' => 3000,
            'tipo_credito' => 'pessoal',
            'valor_solicitado' => 5000,
            'status' => AnalysisStatus::APPROVED,
            'score' => 850,
            'taxa_juros' => 2.9,
            'valor_parcela' => 470.83,
        ]);

        $this->postJson("/api/analise-credito/{$analysis->id}/contratar")
            ->assertOk()
            ->assertJson(['status' => AnalysisStatus::CONTRACTED->value]);

        $this->assertDatabaseHas('analises_credito', [
            'id' => $analysis->id,
            'status' => AnalysisStatus::CONTRACTED->value,
        ]);
    }
}
