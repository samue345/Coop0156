<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreditAnalysisRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rate_limits_credit_analysis_requests_by_ip(): void
    {
        RateLimiter::clear(md5('credit-analysis127.0.0.1'));
        Http::fake(['*' => Http::response(['score' => 850], 200)]);

        foreach (range(1, 10) as $attempt) {
            $this->postJson('/api/analise-credito', $this->validPayload())
                ->assertCreated();
        }

        $this->postJson('/api/analise-credito', $this->validPayload())
            ->assertStatus(429)
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
            'cpf' => '52998224725',
            'nome' => 'João da Silva',
            'renda_mensal' => 10000,
            'tipo_credito' => 'pessoal',
            'valor_solicitado' => 10000,
        ];
    }
}
