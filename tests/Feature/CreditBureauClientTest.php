<?php

namespace Tests\Feature;

use App\Exceptions\CreditBureauException;
use App\Integrations\CreditBureauClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreditBureauClientTest extends TestCase
{
    public function test_it_requests_the_bureau_score_for_the_given_cpf(): void
    {
        config(['services.score_bureau.url' => 'http://bureau.test/api/mock/bureau']);

        Http::fake([
            'bureau.test/api/mock/bureau/52998224725' => Http::response(['score' => 850]),
        ]);

        $score = app(CreditBureauClient::class)->scoreFor('52998224725');

        $this->assertSame(850, $score);
        Http::assertSent(fn ($request) => $request->url() === 'http://bureau.test/api/mock/bureau/52998224725');
    }

    public function test_it_throws_a_clean_exception_when_the_bureau_returns_an_error(): void
    {
        Http::fake(['*' => Http::response(['error' => 'Erro interno'], 500)]);

        $this->expectException(CreditBureauException::class);
        $this->expectExceptionMessage('O Bureau de Crédito está indisponível.');

        try {
            app(CreditBureauClient::class)->scoreFor('52998224725');
        }
        catch (CreditBureauException $exception) {
            $this->assertSame(502, $exception->statusCode);

            throw $exception;
        }
    }

    public function test_it_throws_a_timeout_exception_when_the_bureau_cannot_be_reached(): void
    {
        Http::fake(fn () => throw new ConnectionException('Operation timed out'));

        $this->expectException(CreditBureauException::class);
        $this->expectExceptionMessage('Não foi possível consultar o Bureau de Crédito.');

        try {
            app(CreditBureauClient::class)->scoreFor('52998224725');
        }
        catch (CreditBureauException $exception) {
            $this->assertSame(504, $exception->statusCode);

            throw $exception;
        }
    }

    public function test_it_preserves_the_rate_limit_response_from_the_bureau(): void
    {
        Http::fake(['*' => Http::response(['error' => 'Too many requests'], 429)]);

        $this->expectException(CreditBureauException::class);
        $this->expectExceptionMessage('O Bureau de Crédito atingiu o limite de consultas. Tente novamente mais tarde.');

        try {
            app(CreditBureauClient::class)->scoreFor('52998224725');
        }
        catch (CreditBureauException $exception) {
            $this->assertSame(429, $exception->statusCode);

            throw $exception;
        }
    }

    public function test_it_throws_a_clean_exception_when_the_bureau_response_has_no_score(): void
    {
        Http::fake(['*' => Http::response(['status_bureau' => 'ok'])]);

        $this->expectException(CreditBureauException::class);
        $this->expectExceptionMessage('O Bureau de Crédito retornou uma resposta inválida.');

        try {
            app(CreditBureauClient::class)->scoreFor('52998224725');
        }
        catch (CreditBureauException $exception) {
            $this->assertSame(502, $exception->statusCode);

            throw $exception;
        }
    }
}
