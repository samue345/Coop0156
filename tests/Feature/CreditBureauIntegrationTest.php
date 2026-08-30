<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreditBureauIntegrationTest extends TestCase
{
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
        $this->getJson('/api/mock/bureau/100.000.005-23')
            ->assertOk()
            ->assertJsonPath('cpf', '10000000523')
            ->assertJsonPath('score', 850);
    }

    public function test_it_returns_server_error_for_mock_bureau_failure_scenario(): void
    {
        $this->getJson('/api/mock/bureau/10000000604')
            ->assertInternalServerError()
            ->assertJson([
                'error' => 'Erro interno na comunicação com o provedor de score.',
            ]);
    }

    public function test_it_returns_malformed_response_for_mock_bureau_invalid_payload_scenario(): void
    {
        $this->getJson('/api/mock/bureau/10000000796')
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
            'low score' => ['10000000361', 150],
            'medium score' => ['10000000442', 550],
            'high score' => ['10000000523', 850],
            'default score' => ['10000000887', 600],
        ];
    }
}
