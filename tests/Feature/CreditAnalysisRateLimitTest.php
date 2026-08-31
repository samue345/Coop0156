<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CreditAnalysisRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private const string RATE_LIMIT_CACHE_KEY = 'credit-analysis127.0.0.1';
    private const int HIGH_BUREAU_SCORE = 850;
    private const int CREDIT_ANALYSIS_RATE_LIMIT_ATTEMPTS = 10;
    private const string VALID_CPF = '52998224725';
    private const float STANDARD_INCOME = 10000;
    private const float STANDARD_REQUESTED_AMOUNT = 10000;
    private const string DEFAULT_CREDIT_TYPE = 'pessoal';

    public function test_it_rate_limits_credit_analysis_requests_by_ip(): void
    {
        RateLimiter::clear(md5(self::RATE_LIMIT_CACHE_KEY));
        Http::fake(['*' => Http::response(['score' => self::HIGH_BUREAU_SCORE], Response::HTTP_OK)]);

        foreach (range(1, self::CREDIT_ANALYSIS_RATE_LIMIT_ATTEMPTS) as $attempt) {
            $this->postJson('/api/analise-credito', $this->validPayload())
                ->assertCreated();
        }

        $this->postJson('/api/analise-credito', $this->validPayload())
            ->assertStatus(Response::HTTP_TOO_MANY_REQUESTS)
            ->assertJson([
                'message' => 'Too Many Attempts.',
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'cpf' => self::VALID_CPF,
            'nome' => 'João da Silva',
            'renda_mensal' => self::STANDARD_INCOME,
            'tipo_credito' => self::DEFAULT_CREDIT_TYPE,
            'valor_solicitado' => self::STANDARD_REQUESTED_AMOUNT,
        ];
    }
}
