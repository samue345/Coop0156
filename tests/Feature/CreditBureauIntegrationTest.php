<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreditBureauIntegrationTest extends TestCase
{
    private const string LOW_SCORE_BUREAU_CPF = '10000000361';
    private const string MEDIUM_SCORE_BUREAU_CPF = '10000000442';
    private const string HIGH_SCORE_BUREAU_CPF = '10000000523';
    private const string DEFAULT_SCORE_BUREAU_CPF = '10000000887';
    private const string FORMATTED_HIGH_SCORE_BUREAU_CPF = '100.000.005-23';
    private const string SERVER_ERROR_BUREAU_CPF = '10000000604';
    private const string MALFORMED_RESPONSE_BUREAU_CPF = '10000000796';

    private const int LOW_BUREAU_SCORE = 150;
    private const int MEDIUM_BUREAU_SCORE = 550;
    private const int HIGH_BUREAU_SCORE = 850;
    private const int DEFAULT_BUREAU_SCORE = 600;

    #[DataProvider('scoreScenarios')]
    public function test_it_returns_the_expected_score_for_mock_bureau_scenarios(string $cpf, int $expectedScore): void
    {
        $this->getJson("/api/mock/bureau/{$cpf}")
            ->assertOk()
            ->assertJsonPath('cpf', $cpf)
            ->assertJsonPath('score', $expectedScore)
            ->assertJsonPath('situacao', 'ativo');
    }

    public function test_it_sanitizes_formatted_cpf_before_returning_the_bureau_response(): void
    {
        $this->getJson('/api/mock/bureau/'.self::FORMATTED_HIGH_SCORE_BUREAU_CPF)
            ->assertOk()
            ->assertJsonPath('cpf', self::HIGH_SCORE_BUREAU_CPF)
            ->assertJsonPath('score', self::HIGH_BUREAU_SCORE);
    }

    public function test_it_returns_server_error_for_mock_bureau_failure_scenario(): void
    {
        $this->getJson('/api/mock/bureau/'.self::SERVER_ERROR_BUREAU_CPF)
            ->assertInternalServerError()
            ->assertJson([
                'error' => 'Erro interno na comunicação com o provedor de score.',
            ]);
    }

    public function test_it_returns_malformed_response_for_mock_bureau_invalid_payload_scenario(): void
    {
        $this->getJson('/api/mock/bureau/'.self::MALFORMED_RESPONSE_BUREAU_CPF)
            ->assertOk()
            ->assertJsonMissingPath('score')
            ->assertJsonPath('status_bureau', 'ok');
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function scoreScenarios(): array
    {
        return [
            'low score' => [self::LOW_SCORE_BUREAU_CPF, self::LOW_BUREAU_SCORE],
            'medium score' => [self::MEDIUM_SCORE_BUREAU_CPF, self::MEDIUM_BUREAU_SCORE],
            'high score' => [self::HIGH_SCORE_BUREAU_CPF, self::HIGH_BUREAU_SCORE],
            'default score' => [self::DEFAULT_SCORE_BUREAU_CPF, self::DEFAULT_BUREAU_SCORE],
        ];
    }
}
