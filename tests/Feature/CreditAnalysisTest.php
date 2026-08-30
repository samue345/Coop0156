<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Jobs\ProcessContractingJob;
use App\Models\CreditAnalysis;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreditAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_approves_an_analysis_with_high_score_and_uses_29_percent_interest_rate(): void
    {
        $this->fakeBureauScore(850);

        $response = $this->postJson('/api/analise-credito', $this->validPayload([
            'renda_mensal' => 10000,
            'valor_solicitado' => 10000,
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', 'aprovado')
            ->assertJsonPath('data.score', 850)
            ->assertJsonPath('data.taxa_juros', '2.90')
            ->assertJsonPath('data.valor_parcela', '1123.33')
            ->assertJsonPath('data.motivo_rejeicao', null);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '52998224725',
            'status' => AnalysisStatus::APPROVED->value,
            'score' => 850,
            'taxa_juros' => 2.9,
            'valor_parcela' => 1123.33,
        ]);
    }

    public function test_it_approves_an_analysis_with_medium_score_and_uses_45_percent_interest_rate(): void
    {
        $this->fakeBureauScore(550);

        $this->postJson('/api/analise-credito', $this->validPayload([
            'renda_mensal' => 10000,
            'valor_solicitado' => 10000,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'aprovado')
            ->assertJsonPath('data.score', 550)
            ->assertJsonPath('data.taxa_juros', '4.50')
            ->assertJsonPath('data.valor_parcela', '1283.33')
            ->assertJsonPath('data.motivo_rejeicao', null);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '52998224725',
            'status' => AnalysisStatus::APPROVED->value,
            'score' => 550,
            'taxa_juros' => 4.5,
            'valor_parcela' => 1283.33,
        ]);
    }

    public function test_it_rejects_an_analysis_when_income_is_insufficient(): void
    {
        $this->fakeBureauScore(850);

        $this->postJson('/api/analise-credito', $this->validPayload([
            'renda_mensal' => 1499,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'reprovado')
            ->assertJsonPath('data.motivo_rejeicao', 'Renda mínima insuficiente')
            ->assertJsonPath('data.taxa_juros', null)
            ->assertJsonPath('data.valor_parcela', null);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '52998224725',
            'status' => AnalysisStatus::REJECTED->value,
            'motivo_rejeicao' => 'Renda mínima insuficiente',
        ]);
    }

    public function test_it_rejects_an_analysis_when_score_is_low(): void
    {
        $this->fakeBureauScore(150);

        $this->postJson('/api/analise-credito', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'reprovado')
            ->assertJsonPath('data.score', 150)
            ->assertJsonPath('data.motivo_rejeicao', 'Score de crédito muito baixo');

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '52998224725',
            'status' => AnalysisStatus::REJECTED->value,
            'score' => 150,
            'motivo_rejeicao' => 'Score de crédito muito baixo',
        ]);
    }

    public function test_it_rejects_an_analysis_when_income_commitment_is_above_30_percent(): void
    {
        $this->fakeBureauScore(850);

        $this->postJson('/api/analise-credito', $this->validPayload([
            'renda_mensal' => 3000,
            'valor_solicitado' => 10000,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'reprovado')
            ->assertJsonPath('data.motivo_rejeicao', 'Comprometimento de renda superior a 30%')
            ->assertJsonPath('data.taxa_juros', null)
            ->assertJsonPath('data.valor_parcela', null);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '52998224725',
            'status' => AnalysisStatus::REJECTED->value,
            'motivo_rejeicao' => 'Comprometimento de renda superior a 30%',
        ]);
    }

    public function test_it_returns_a_clean_response_when_bureau_returns_server_error(): void
    {
        Http::fake(['*' => Http::response(['error' => 'Erro interno'], 500)]);

        $this->postJson('/api/analise-credito', $this->validPayload())
            ->assertStatus(502)
            ->assertJson([
                'message' => 'O Bureau de Crédito está indisponível.',
            ]);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '52998224725',
            'status' => AnalysisStatus::PENDING->value,
        ]);
    }

    public function test_it_accepts_the_available_credit_types_when_requesting_analysis(): void
    {
        foreach (['pessoal', 'imobiliario', 'automotivo'] as $index => $creditType) {
            $cpf = match ($index) {
                0 => '52998224725',
                1 => '11144477735',
                default => '12345678909',
            };

            $this->fakeBureauScore(850);

            $this->postJson('/api/analise-credito', $this->validPayload([
                'cpf' => $cpf,
                'tipo_credito' => $creditType,
            ]))
                ->assertCreated()
                ->assertJsonPath('data.tipo_credito', $creditType)
                ->assertJsonPath('data.status', 'aprovado');

            $this->assertDatabaseHas('analises_credito', [
                'cpf' => $cpf,
                'tipo_credito' => $creditType,
                'status' => AnalysisStatus::APPROVED->value,
            ]);
        }
    }

    public function test_it_rejects_an_invalid_credit_type_when_requesting_analysis(): void
    {
        Http::fake();

        $this->postJson('/api/analise-credito', $this->validPayload([
            'tipo_credito' => 'consignado',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tipo_credito']);

        Http::assertNothingSent();
    }

    public function test_it_applies_credit_rules_using_the_mock_bureau_cpf_score_scenarios(): void
    {
        $this->fakeBureauUsingCpfLastDigit();

        $cases = [
            [
                'cpf' => '10000000523',
                'score' => 850,
                'status' => AnalysisStatus::APPROVED->value,
                'taxa_juros' => '2.90',
                'valor_parcela' => '1123.33',
                'motivo_rejeicao' => null,
                'renda_mensal' => 10000,
                'valor_solicitado' => 10000,
            ],
            [
                'cpf' => '10000000442',
                'score' => 550,
                'status' => AnalysisStatus::APPROVED->value,
                'taxa_juros' => '4.50',
                'valor_parcela' => '1283.33',
                'motivo_rejeicao' => null,
                'renda_mensal' => 10000,
                'valor_solicitado' => 10000,
            ],
            [
                'cpf' => '10000000361',
                'score' => 150,
                'status' => AnalysisStatus::REJECTED->value,
                'taxa_juros' => null,
                'valor_parcela' => null,
                'motivo_rejeicao' => 'Score de crédito muito baixo',
                'renda_mensal' => 10000,
                'valor_solicitado' => 10000,
            ],
            [
                'cpf' => '10000000523',
                'score' => 850,
                'status' => AnalysisStatus::REJECTED->value,
                'taxa_juros' => null,
                'valor_parcela' => null,
                'motivo_rejeicao' => 'Renda mínima insuficiente',
                'renda_mensal' => 1499,
                'valor_solicitado' => 10000,
            ],
            [
                'cpf' => '10000000523',
                'score' => 850,
                'status' => AnalysisStatus::REJECTED->value,
                'taxa_juros' => null,
                'valor_parcela' => null,
                'motivo_rejeicao' => 'Comprometimento de renda superior a 30%',
                'renda_mensal' => 3000,
                'valor_solicitado' => 10000,
            ],
        ];

        foreach ($cases as $case) {
            $this->postJson('/api/analise-credito', $this->validPayload([
                'cpf' => $case['cpf'],
                'renda_mensal' => $case['renda_mensal'],
                'valor_solicitado' => $case['valor_solicitado'],
            ]))
                ->assertCreated()
                ->assertJsonPath('data.score', $case['score'])
                ->assertJsonPath('data.status', $case['status'])
                ->assertJsonPath('data.taxa_juros', $case['taxa_juros'])
                ->assertJsonPath('data.valor_parcela', $case['valor_parcela'])
                ->assertJsonPath('data.motivo_rejeicao', $case['motivo_rejeicao']);
        }
    }

    public function test_it_returns_clean_errors_for_mock_bureau_failure_scenarios_by_cpf(): void
    {
        $this->fakeBureauUsingCpfLastDigit();

        $cases = [
            [
                'cpf' => '10000000604',
                'status' => 502,
                'message' => 'O Bureau de Crédito está indisponível.',
            ],
            [
                'cpf' => '10000000795',
                'status' => 504,
                'message' => 'Não foi possível consultar o Bureau de Crédito.',
            ],
            [
                'cpf' => '10000000876',
                'status' => 502,
                'message' => 'O Bureau de Crédito retornou uma resposta inválida.',
            ],
        ];

        foreach ($cases as $case) {
            $this->postJson('/api/analise-credito', $this->validPayload(['cpf' => $case['cpf']]))
                ->assertStatus($case['status'])
                ->assertJson(['message' => $case['message']]);

            $this->assertDatabaseHas('analises_credito', [
                'cpf' => $case['cpf'],
                'status' => AnalysisStatus::PENDING->value,
            ]);
        }
    }

    public function test_it_creates_the_customer_automatically_when_requesting_analysis_with_a_new_cpf(): void
    {
        $this->fakeBureauScore(850);

        $this->assertDatabaseMissing('clientes', [
            'cpf' => '52998224725',
        ]);

        $this->postJson('/api/analise-credito', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'aprovado');

        $this->assertDatabaseHas('clientes', ['cpf' => '52998224725']);
        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '52998224725',
            'cliente_id' => Customer::query()->where('cpf', '52998224725')->value('id'),
            'status' => AnalysisStatus::APPROVED->value,
        ]);
    }

    public function test_it_reuses_an_existing_customer_when_requesting_analysis_with_the_same_cpf(): void
    {
        $customer = Customer::factory()->create([
            'cpf' => '52998224725',
            'nome' => 'Cliente Existente',
            'renda_mensal' => 7000,
        ]);

        $this->fakeBureauScore(850);

        $this->postJson('/api/analise-credito', $this->validPayload([
            'nome' => 'Nome da Solicitação',
            'renda_mensal' => 10000,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'aprovado');

        $this->assertSame(1, Customer::query()->where('cpf', '52998224725')->count());
        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '52998224725',
            'cliente_id' => $customer->id,
            'status' => AnalysisStatus::APPROVED->value,
        ]);
    }

    public function test_it_contracts_an_approved_analysis(): void
    {
        Queue::fake();

        $analysis = CreditAnalysis::factory()->create();

        $this->postJson("/api/analise-credito/{$analysis->id}/contratar")
            ->assertAccepted()
            ->assertJson([
                'message' => 'Contratação solicitada com sucesso. Acompanhe o status em Contratações.',
                'status' => AnalysisStatus::PROCESSING_CONTRACT->value,
            ]);

        $this->assertDatabaseHas('analises_credito', [
            'id' => $analysis->id,
            'status' => AnalysisStatus::PROCESSING_CONTRACT->value,
        ]);

        Queue::assertPushed(ProcessContractingJob::class);
    }

    public function test_it_does_not_contract_analyses_that_are_not_approved(): void
    {
        Queue::fake();

        foreach ([AnalysisStatus::PENDING, AnalysisStatus::REJECTED, AnalysisStatus::PROCESSING_CONTRACT, AnalysisStatus::CONTRACTED] as $status) {
            $analysis = CreditAnalysis::factory()->create([
                'status' => $status,
            ]);

            $this->postJson("/api/analise-credito/{$analysis->id}/contratar")
                ->assertUnprocessable()
                ->assertJson([
                    'message' => 'A análise precisa estar aprovada para ser contratada.',
                    'status' => $status->value,
                ]);

            $this->assertDatabaseHas('analises_credito', [
                'id' => $analysis->id,
                'status' => $status->value,
            ]);
        }

        Queue::assertNothingPushed();
    }

    public function test_it_uses_the_informed_cpf_to_request_the_bureau_score(): void
    {
        $this->fakeBureauScore(600);

        $this->postJson('/api/analise-credito', $this->validPayload([
            'cpf' => '12345678909',
        ]))
            ->assertCreated();

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/12345678909'));
    }

    public function test_it_rate_limits_credit_analysis_requests_by_ip(): void
    {
        RateLimiter::clear(md5('credit-analysis127.0.0.1'));
        $this->fakeBureauScore(850);

        foreach (range(1, 10) as $attempt) {
            $this->postJson('/api/analise-credito', $this->validPayload([
                'cpf' => '52998224725',
            ]))->assertCreated();
        }

        $this->postJson('/api/analise-credito', $this->validPayload([
            'cpf' => '52998224725',
        ]))
            ->assertStatus(429)
            ->assertJson([
                'message' => 'Too Many Attempts.',
            ]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'cpf' => '52998224725',
            'nome' => 'João da Silva',
            'renda_mensal' => 10000,
            'tipo_credito' => 'pessoal',
            'valor_solicitado' => 10000,
            ...$overrides,
        ];
    }

    private function fakeBureauScore(int $score): void
    {
        Http::fake(['*' => Http::response(['score' => $score], 200)]);
    }

    private function fakeBureauUsingCpfLastDigit(): void
    {
        Http::fake(function ($request) {
            $cpf = basename($request->url());
            $lastDigit = substr(preg_replace('/\D/', '', $cpf), -1);

            return match ($lastDigit) {
                '1' => Http::response(['cpf' => $cpf, 'score' => 150, 'situacao' => 'ativo']),
                '2' => Http::response(['cpf' => $cpf, 'score' => 550, 'situacao' => 'ativo']),
                '3' => Http::response(['cpf' => $cpf, 'score' => 850, 'situacao' => 'ativo']),
                '4' => Http::response(['error' => 'Erro interno na comunicação com o provedor de score.'], 500),
                '5' => throw new ConnectionException('Operation timed out after 3001 milliseconds with 0 bytes received'),
                '6' => Http::response(['cpf' => $cpf, 'status_bureau' => 'ok']),
                default => Http::response(['cpf' => $cpf, 'score' => 600, 'situacao' => 'ativo']),
            };
        });
    }
}
