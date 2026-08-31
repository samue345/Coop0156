<?php

namespace Tests\Feature;

use App\Exceptions\CreditBureauException;
use App\Integrations\CreditBureauClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CreditBureauClientTest extends TestCase
{
    private const string SCORE_BUREAU_URL = 'http://bureau.test/api/mock/bureau';
    private const string VALID_CPF = '52998224725';
    private const int HIGH_BUREAU_SCORE = 850;

    public function test_it_requests_the_bureau_score_for_the_given_cpf(): void
    {
        config(['services.score_bureau.url' => self::SCORE_BUREAU_URL]);

        Http::fake([
            'bureau.test/api/mock/bureau/'.self::VALID_CPF => Http::response(['score' => self::HIGH_BUREAU_SCORE]),
        ]);

        $score = app(CreditBureauClient::class)->scoreFor(self::VALID_CPF);

        $this->assertSame(self::HIGH_BUREAU_SCORE, $score);
        Http::assertSent(fn ($request) => $request->url() === self::SCORE_BUREAU_URL.'/'.self::VALID_CPF);
    }

    public function test_it_throws_a_clean_exception_when_the_bureau_returns_an_error(): void
    {
        Http::fake(['*' => Http::response(['error' => 'Erro interno'], Response::HTTP_INTERNAL_SERVER_ERROR)]);

        $this->expectException(CreditBureauException::class);
        $this->expectExceptionMessage('O Bureau de Crédito está indisponível.');

        try {
            app(CreditBureauClient::class)->scoreFor(self::VALID_CPF);
        }
        catch (CreditBureauException $exception) {
            $this->assertSame(Response::HTTP_BAD_GATEWAY, $exception->statusCode);

            throw $exception;
        }
    }

    public function test_it_throws_a_timeout_exception_when_the_bureau_cannot_be_reached(): void
    {
        Http::fake(fn () => throw new ConnectionException('Operation timed out'));

        $this->expectException(CreditBureauException::class);
        $this->expectExceptionMessage('Não foi possível consultar o Bureau de Crédito.');

        try {
            app(CreditBureauClient::class)->scoreFor(self::VALID_CPF);
        }
        catch (CreditBureauException $exception) {
            $this->assertSame(Response::HTTP_GATEWAY_TIMEOUT, $exception->statusCode);

            throw $exception;
        }
    }

    public function test_it_preserves_the_rate_limit_response_from_the_bureau(): void
    {
        Http::fake(['*' => Http::response(['error' => 'Too many requests'], Response::HTTP_TOO_MANY_REQUESTS)]);

        $this->expectException(CreditBureauException::class);
        $this->expectExceptionMessage('O Bureau de Crédito atingiu o limite de consultas. Tente novamente mais tarde.');

        try {
            app(CreditBureauClient::class)->scoreFor(self::VALID_CPF);
        }
        catch (CreditBureauException $exception) {
            $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $exception->statusCode);

            throw $exception;
        }
    }

    public function test_it_throws_a_clean_exception_when_the_bureau_response_has_no_score(): void
    {
        Http::fake(['*' => Http::response(['status_bureau' => 'ok'])]);

        $this->expectException(CreditBureauException::class);
        $this->expectExceptionMessage('O Bureau de Crédito retornou uma resposta inválida.');

        try {
            app(CreditBureauClient::class)->scoreFor(self::VALID_CPF);
        }
        catch (CreditBureauException $exception) {
            $this->assertSame(Response::HTTP_BAD_GATEWAY, $exception->statusCode);

            throw $exception;
        }
    }
}
