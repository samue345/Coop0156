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
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CreditAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private const string VALID_CPF = '52998224725';
    private const string ALTERNATIVE_VALID_CPF = '12345678909';
    private const string SECOND_CREDIT_TYPE_CPF = '11144477735';
    private const string HIGH_SCORE_BUREAU_CPF = '10000000523';
    private const string MEDIUM_SCORE_BUREAU_CPF = '10000000442';
    private const string LOW_SCORE_BUREAU_CPF = '10000000361';
    private const string SERVER_ERROR_BUREAU_CPF = '10000000604';
    private const string TIMEOUT_BUREAU_CPF = '10000000795';
    private const string MALFORMED_RESPONSE_BUREAU_CPF = '10000000876';
    private const string CUSTOMER_NAME = 'João da Silva';
    private const string EXISTING_CUSTOMER_NAME = 'Cliente Existente';
    private const string REQUEST_CUSTOMER_NAME = 'Nome da Solicitação';
    private const string DEFAULT_CREDIT_TYPE = 'pessoal';
    private const float STANDARD_INCOME = 10000.00;
    private const float INCOME_BELOW_MINIMUM = 1499.00;
    private const float INCOME_ABOVE_COMMITMENT_LIMIT = 3000.00;
    private const float EXISTING_CUSTOMER_INCOME = 7000.00;
    private const float STANDARD_REQUESTED_AMOUNT = 10000.00;
    private const int HIGH_BUREAU_SCORE = 850;
    private const int MEDIUM_BUREAU_SCORE = 550;
    private const int LOW_BUREAU_SCORE = 150;
    private const int DEFAULT_BUREAU_SCORE = 600;
    private const float HIGH_SCORE_MONTHLY_RATE = 2.9;
    private const float MEDIUM_SCORE_MONTHLY_RATE = 4.5;
    private const float HIGH_SCORE_INSTALLMENT = 1123.33;
    private const float MEDIUM_SCORE_INSTALLMENT = 1283.33;
    private const string HIGH_SCORE_MONTHLY_RATE_RESPONSE = '2.90';
    private const string MEDIUM_SCORE_MONTHLY_RATE_RESPONSE = '4.50';
    private const string HIGH_SCORE_INSTALLMENT_RESPONSE = '1123.33';
    private const string MEDIUM_SCORE_INSTALLMENT_RESPONSE = '1283.33';
    private const string MINIMUM_INCOME_REJECTION_REASON = 'Renda mínima insuficiente';
    private const string LOW_SCORE_REJECTION_REASON = 'Score de crédito muito baixo';
    private const string INCOME_COMMITMENT_REJECTION_REASON = 'Comprometimento de renda superior a 30%';
    private const string RATE_LIMIT_CACHE_KEY = 'credit-analysis127.0.0.1';
    private const int CREDIT_ANALYSIS_RATE_LIMIT_ATTEMPTS = 10;

    public function test_it_approves_an_analysis_with_high_score_and_uses_29_percent_interest_rate(): void
    {
        $this->fakeBureauScore(self::HIGH_BUREAU_SCORE);

        $response = $this->postJson('/api/analise-credito', $this->validPayload([
            'renda_mensal' => self::STANDARD_INCOME,
            'valor_solicitado' => self::STANDARD_REQUESTED_AMOUNT,
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', 'aprovado')
            ->assertJsonPath('data.score', self::HIGH_BUREAU_SCORE)
            ->assertJsonPath('data.taxa_juros', self::HIGH_SCORE_MONTHLY_RATE_RESPONSE)
            ->assertJsonPath('data.valor_parcela', self::HIGH_SCORE_INSTALLMENT_RESPONSE)
            ->assertJsonPath('data.motivo_rejeicao', null);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => self::VALID_CPF,
            'status' => AnalysisStatus::APPROVED->value,
            'score' => self::HIGH_BUREAU_SCORE,
            'taxa_juros' => self::HIGH_SCORE_MONTHLY_RATE,
            'valor_parcela' => self::HIGH_SCORE_INSTALLMENT,
        ]);
    }

    public function test_it_approves_an_analysis_with_medium_score_and_uses_45_percent_interest_rate(): void
    {
        $this->fakeBureauScore(self::MEDIUM_BUREAU_SCORE);

        $this->postJson('/api/analise-credito', $this->validPayload([
            'renda_mensal' => self::STANDARD_INCOME,
            'valor_solicitado' => self::STANDARD_REQUESTED_AMOUNT,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'aprovado')
            ->assertJsonPath('data.score', self::MEDIUM_BUREAU_SCORE)
            ->assertJsonPath('data.taxa_juros', self::MEDIUM_SCORE_MONTHLY_RATE_RESPONSE)
            ->assertJsonPath('data.valor_parcela', self::MEDIUM_SCORE_INSTALLMENT_RESPONSE)
            ->assertJsonPath('data.motivo_rejeicao', null);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => self::VALID_CPF,
            'status' => AnalysisStatus::APPROVED->value,
            'score' => self::MEDIUM_BUREAU_SCORE,
            'taxa_juros' => self::MEDIUM_SCORE_MONTHLY_RATE,
            'valor_parcela' => self::MEDIUM_SCORE_INSTALLMENT,
        ]);
    }

    public function test_it_rejects_an_analysis_when_income_is_insufficient(): void
    {
        $this->fakeBureauScore(self::HIGH_BUREAU_SCORE);

        $this->postJson('/api/analise-credito', $this->validPayload([
            'renda_mensal' => self::INCOME_BELOW_MINIMUM,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'reprovado')
            ->assertJsonPath('data.motivo_rejeicao', self::MINIMUM_INCOME_REJECTION_REASON)
            ->assertJsonPath('data.taxa_juros', null)
            ->assertJsonPath('data.valor_parcela', null);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => self::VALID_CPF,
            'status' => AnalysisStatus::REJECTED->value,
            'motivo_rejeicao' => self::MINIMUM_INCOME_REJECTION_REASON,
        ]);
    }

    public function test_it_rejects_an_analysis_when_score_is_low(): void
    {
        $this->fakeBureauScore(self::LOW_BUREAU_SCORE);

        $this->postJson('/api/analise-credito', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'reprovado')
            ->assertJsonPath('data.score', self::LOW_BUREAU_SCORE)
            ->assertJsonPath('data.motivo_rejeicao', self::LOW_SCORE_REJECTION_REASON);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => self::VALID_CPF,
            'status' => AnalysisStatus::REJECTED->value,
            'score' => self::LOW_BUREAU_SCORE,
            'motivo_rejeicao' => self::LOW_SCORE_REJECTION_REASON,
        ]);
    }

    public function test_it_rejects_an_analysis_when_income_commitment_is_above_30_percent(): void
    {
        $this->fakeBureauScore(self::HIGH_BUREAU_SCORE);

        $this->postJson('/api/analise-credito', $this->validPayload([
            'renda_mensal' => self::INCOME_ABOVE_COMMITMENT_LIMIT,
            'valor_solicitado' => self::STANDARD_REQUESTED_AMOUNT,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'reprovado')
            ->assertJsonPath('data.motivo_rejeicao', self::INCOME_COMMITMENT_REJECTION_REASON)
            ->assertJsonPath('data.taxa_juros', null)
            ->assertJsonPath('data.valor_parcela', null);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => self::VALID_CPF,
            'status' => AnalysisStatus::REJECTED->value,
            'motivo_rejeicao' => self::INCOME_COMMITMENT_REJECTION_REASON,
        ]);
    }

    public function test_it_returns_a_clean_response_when_bureau_returns_server_error(): void
    {
        Http::fake(['*' => Http::response(['error' => 'Erro interno'], Response::HTTP_INTERNAL_SERVER_ERROR)]);

        $this->postJson('/api/analise-credito', $this->validPayload())
            ->assertStatus(Response::HTTP_BAD_GATEWAY)
            ->assertJson([
                'message' => 'O Bureau de Crédito está indisponível.',
            ]);

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => self::VALID_CPF,
            'status' => AnalysisStatus::PENDING->value,
        ]);
    }

    public function test_it_accepts_the_available_credit_types_when_requesting_analysis(): void
    {
        foreach (['pessoal', 'imobiliario', 'automotivo'] as $index => $creditType) {
            $cpf = match ($index) {
                0 => self::VALID_CPF,
                1 => self::SECOND_CREDIT_TYPE_CPF,
                default => self::ALTERNATIVE_VALID_CPF,
            };

            $this->fakeBureauScore(self::HIGH_BUREAU_SCORE);

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
                'cpf' => self::HIGH_SCORE_BUREAU_CPF,
                'score' => self::HIGH_BUREAU_SCORE,
                'status' => AnalysisStatus::APPROVED->value,
                'taxa_juros' => self::HIGH_SCORE_MONTHLY_RATE_RESPONSE,
                'valor_parcela' => self::HIGH_SCORE_INSTALLMENT_RESPONSE,
                'motivo_rejeicao' => null,
                'renda_mensal' => self::STANDARD_INCOME,
                'valor_solicitado' => self::STANDARD_REQUESTED_AMOUNT,
            ],
            [
                'cpf' => self::MEDIUM_SCORE_BUREAU_CPF,
                'score' => self::MEDIUM_BUREAU_SCORE,
                'status' => AnalysisStatus::APPROVED->value,
                'taxa_juros' => self::MEDIUM_SCORE_MONTHLY_RATE_RESPONSE,
                'valor_parcela' => self::MEDIUM_SCORE_INSTALLMENT_RESPONSE,
                'motivo_rejeicao' => null,
                'renda_mensal' => self::STANDARD_INCOME,
                'valor_solicitado' => self::STANDARD_REQUESTED_AMOUNT,
            ],
            [
                'cpf' => self::LOW_SCORE_BUREAU_CPF,
                'score' => self::LOW_BUREAU_SCORE,
                'status' => AnalysisStatus::REJECTED->value,
                'taxa_juros' => null,
                'valor_parcela' => null,
                'motivo_rejeicao' => self::LOW_SCORE_REJECTION_REASON,
                'renda_mensal' => self::STANDARD_INCOME,
                'valor_solicitado' => self::STANDARD_REQUESTED_AMOUNT,
            ],
            [
                'cpf' => self::HIGH_SCORE_BUREAU_CPF,
                'score' => self::HIGH_BUREAU_SCORE,
                'status' => AnalysisStatus::REJECTED->value,
                'taxa_juros' => null,
                'valor_parcela' => null,
                'motivo_rejeicao' => self::MINIMUM_INCOME_REJECTION_REASON,
                'renda_mensal' => self::INCOME_BELOW_MINIMUM,
                'valor_solicitado' => self::STANDARD_REQUESTED_AMOUNT,
            ],
            [
                'cpf' => self::HIGH_SCORE_BUREAU_CPF,
                'score' => self::HIGH_BUREAU_SCORE,
                'status' => AnalysisStatus::REJECTED->value,
                'taxa_juros' => null,
                'valor_parcela' => null,
                'motivo_rejeicao' => self::INCOME_COMMITMENT_REJECTION_REASON,
                'renda_mensal' => self::INCOME_ABOVE_COMMITMENT_LIMIT,
                'valor_solicitado' => self::STANDARD_REQUESTED_AMOUNT,
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
                'cpf' => self::SERVER_ERROR_BUREAU_CPF,
                'status' => Response::HTTP_BAD_GATEWAY,
                'message' => 'O Bureau de Crédito está indisponível.',
            ],
            [
                'cpf' => self::TIMEOUT_BUREAU_CPF,
                'status' => Response::HTTP_GATEWAY_TIMEOUT,
                'message' => 'Não foi possível consultar o Bureau de Crédito.',
            ],
            [
                'cpf' => self::MALFORMED_RESPONSE_BUREAU_CPF,
                'status' => Response::HTTP_BAD_GATEWAY,
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
        $this->fakeBureauScore(self::HIGH_BUREAU_SCORE);

        $this->assertDatabaseMissing('clientes', [
            'cpf' => self::VALID_CPF,
        ]);

        $this->postJson('/api/analise-credito', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'aprovado');

        $this->assertDatabaseHas('clientes', ['cpf' => self::VALID_CPF]);
        $this->assertDatabaseHas('analises_credito', [
            'cpf' => self::VALID_CPF,
            'cliente_id' => Customer::query()->where('cpf', self::VALID_CPF)->value('id'),
            'status' => AnalysisStatus::APPROVED->value,
        ]);
    }

    public function test_it_reuses_an_existing_customer_when_requesting_analysis_with_the_same_cpf(): void
    {
        $customer = Customer::factory()->create([
            'cpf' => self::VALID_CPF,
            'nome' => self::EXISTING_CUSTOMER_NAME,
            'renda_mensal' => self::EXISTING_CUSTOMER_INCOME,
        ]);

        $this->fakeBureauScore(self::HIGH_BUREAU_SCORE);

        $this->postJson('/api/analise-credito', $this->validPayload([
            'nome' => self::REQUEST_CUSTOMER_NAME,
            'renda_mensal' => self::STANDARD_INCOME,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'aprovado');

        $this->assertSame(1, Customer::query()->where('cpf', self::VALID_CPF)->count());
        $this->assertDatabaseHas('analises_credito', [
            'cpf' => self::VALID_CPF,
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
        $this->fakeBureauScore(self::DEFAULT_BUREAU_SCORE);

        $this->postJson('/api/analise-credito', $this->validPayload([
            'cpf' => self::ALTERNATIVE_VALID_CPF,
        ]))
            ->assertCreated();

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/'.self::ALTERNATIVE_VALID_CPF));
    }

    public function test_it_rate_limits_credit_analysis_requests_by_ip(): void
    {
        RateLimiter::clear(md5(self::RATE_LIMIT_CACHE_KEY));
        $this->fakeBureauScore(self::HIGH_BUREAU_SCORE);

        foreach (range(1, self::CREDIT_ANALYSIS_RATE_LIMIT_ATTEMPTS) as $attempt) {
            $this->postJson('/api/analise-credito', $this->validPayload([
                'cpf' => self::VALID_CPF,
            ]))->assertCreated();
        }

        $this->postJson('/api/analise-credito', $this->validPayload([
            'cpf' => self::VALID_CPF,
        ]))
            ->assertStatus(Response::HTTP_TOO_MANY_REQUESTS)
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
            'cpf' => self::VALID_CPF,
            'nome' => self::CUSTOMER_NAME,
            'renda_mensal' => self::STANDARD_INCOME,
            'tipo_credito' => self::DEFAULT_CREDIT_TYPE,
            'valor_solicitado' => self::STANDARD_REQUESTED_AMOUNT,
            ...$overrides,
        ];
    }

    private function fakeBureauScore(int $score): void
    {
        Http::fake(['*' => Http::response(['score' => $score], Response::HTTP_OK)]);
    }

    private function fakeBureauUsingCpfLastDigit(): void
    {
        Http::fake(function ($request) {
            $cpf = basename($request->url());
            $lastDigit = substr(preg_replace('/\D/', '', $cpf), -1);

            return match ($lastDigit) {
                '1' => Http::response(['cpf' => $cpf, 'score' => self::LOW_BUREAU_SCORE, 'situacao' => 'ativo']),
                '2' => Http::response(['cpf' => $cpf, 'score' => self::MEDIUM_BUREAU_SCORE, 'situacao' => 'ativo']),
                '3' => Http::response(['cpf' => $cpf, 'score' => self::HIGH_BUREAU_SCORE, 'situacao' => 'ativo']),
                '4' => Http::response(['error' => 'Erro interno na comunicação com o provedor de score.'], Response::HTTP_INTERNAL_SERVER_ERROR),
                '5' => throw new ConnectionException('Operation timed out after 3001 milliseconds with 0 bytes received'),
                '6' => Http::response(['cpf' => $cpf, 'status_bureau' => 'ok']),
                default => Http::response(['cpf' => $cpf, 'score' => self::DEFAULT_BUREAU_SCORE, 'situacao' => 'ativo']),
            };
        });
    }
}
