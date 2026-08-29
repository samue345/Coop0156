<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_customer_with_valid_data(): void
    {
        $response = $this->postJson('/api/clientes', $this->validPayload());

        $response->assertCreated()
            ->assertJsonPath('data.nome', 'João da Silva')
            ->assertJsonPath('data.cpf', '52998224725')
            ->assertJsonPath('data.email', 'joao@example.com');

        $this->assertDatabaseHas('clientes', [
            'nome' => 'João da Silva',
            'cpf' => '52998224725',
            'email' => 'joao@example.com',
        ]);
    }

    public function test_it_fails_validation_when_creating_a_customer_without_required_fields(): void
    {
        $this->postJson('/api/clientes', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nome',
                'cpf',
                'email',
                'renda_mensal',
            ]);
    }

    public function test_it_fails_validation_when_creating_a_customer_with_duplicated_cpf(): void
    {
        Customer::factory()->create(['cpf' => '52998224725']);

        $this->postJson('/api/clientes', $this->validPayload([
            'email' => 'outro@example.com',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_it_fails_validation_when_creating_a_customer_with_invalid_cpf(): void
    {
        $this->postJson('/api/clientes', $this->validPayload([
            'cpf' => '11111111111',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_it_fails_validation_when_creating_a_customer_with_negative_monthly_income(): void
    {
        $this->postJson('/api/clientes', $this->validPayload([
            'renda_mensal' => -1,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['renda_mensal']);
    }

    public function test_it_fails_validation_when_creating_a_customer_with_duplicated_email(): void
    {
        Customer::factory()->create(['email' => 'joao@example.com']);

        $this->postJson('/api/clientes', $this->validPayload([
            'cpf' => '12345678909',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_fails_validation_when_creating_an_existing_customer(): void
    {
        Customer::factory()->create($this->validPayload());

        $this->postJson('/api/clientes', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'cpf',
                'email',
            ]);
    }

    public function test_it_lists_paginated_customers(): void
    {
        Customer::factory()->count(16)->create();

        $this->getJson('/api/clientes')
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 15);

        $this->getJson('/api/clientes?page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 15);
    }

    public function test_it_shows_an_existing_customer_by_id(): void
    {
        $customer = Customer::factory()->create();

        $this->getJson("/api/clientes/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $customer->hashids_code)
            ->assertJsonPath('data.nome', $customer->nome)
            ->assertJsonPath('data.cpf', $customer->cpf);
    }

    public function test_it_returns_not_found_when_showing_a_missing_customer(): void
    {
        $this->getJson('/api/clientes/999999')
            ->assertNotFound();
    }

    public function test_it_partially_updates_an_existing_customer(): void
    {
        $customer = Customer::factory()->create();

        $this->patchJson("/api/clientes/{$customer->id}", [
            'telefone' => '11988887777',
            'renda_mensal' => 4500,
        ])
            ->assertOk()
            ->assertJsonPath('data.telefone', '11988887777')
            ->assertJsonPath('data.renda_mensal', '4500.00');

        $this->assertDatabaseHas('clientes', [
            'id' => $customer->id,
            'telefone' => '11988887777',
            'renda_mensal' => 4500,
        ]);
    }

    public function test_it_updates_an_existing_customer_with_put(): void
    {
        $customer = Customer::factory()->create();

        $payload = $this->validPayload([
            'nome' => 'Maria Oliveira',
            'cpf' => '11144477735',
            'email' => 'maria@example.com',
            'telefone' => '11977776666',
            'renda_mensal' => 6200,
        ]);

        $this->putJson("/api/clientes/{$customer->id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.nome', 'Maria Oliveira')
            ->assertJsonPath('data.cpf', '11144477735')
            ->assertJsonPath('data.email', 'maria@example.com')
            ->assertJsonPath('data.telefone', '11977776666')
            ->assertJsonPath('data.renda_mensal', '6200.00');

        $this->assertDatabaseHas('clientes', [
            'id' => $customer->id,
            'nome' => 'Maria Oliveira',
            'cpf' => '11144477735',
            'email' => 'maria@example.com',
        ]);
    }

    public function test_it_deletes_an_existing_customer_without_body(): void
    {
        $customer = Customer::factory()->create();

        $this->deleteJson("/api/clientes/{$customer->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('clientes', [
            'id' => $customer->id,
        ]);
    }

    public function test_it_returns_not_found_when_deleting_a_missing_customer(): void
    {
        $this->deleteJson('/api/clientes/999999')
            ->assertNotFound();
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        $customer = Customer::factory()->make([
            'nome' => 'João da Silva',
            'cpf' => '52998224725',
            'email' => 'joao@example.com',
            'telefone' => '11999998888',
            'renda_mensal' => 3500,
            ...$overrides,
        ]);

        return $customer->only([
            'nome',
            'cpf',
            'email',
            'telefone',
            'renda_mensal',
        ]);
    }
}
