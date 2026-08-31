<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Support\Pagination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private const string VALID_CPF = '52998224725';
    private const string SECOND_VALID_CPF = '12345678909';
    private const string UPDATED_VALID_CPF = '11144477735';
    private const string INVALID_CPF = '11111111111';
    private const string PRIMARY_EMAIL = 'joao@example.com';
    private const string SECONDARY_EMAIL = 'outro@example.com';
    private const string UPDATED_EMAIL = 'maria@example.com';
    private const string CUSTOMER_NAME = 'João da Silva';
    private const string UPDATED_CUSTOMER_NAME = 'Maria Oliveira';
    private const string CUSTOMER_PHONE = '11999998888';
    private const string UPDATED_CUSTOMER_PHONE = '11977776666';
    private const string PARTIAL_UPDATE_PHONE = '11988887777';
    private const int MISSING_CUSTOMER_ID = 999999;
    private const int CUSTOMERS_TO_SPAN_TWO_PAGES = 16;
    private const int FIRST_PAGE = 1;
    private const int SECOND_PAGE = 2;
    private const int CUSTOMERS_ON_SECOND_PAGE = 1;
    private const float DEFAULT_MONTHLY_INCOME = 3500;
    private const float PARTIAL_UPDATE_MONTHLY_INCOME = 4500;
    private const float UPDATED_MONTHLY_INCOME = 6200;
    private const float NEGATIVE_MONTHLY_INCOME = -1;
    private const string PARTIAL_UPDATE_MONTHLY_INCOME_RESPONSE = '4500.00';
    private const string UPDATED_MONTHLY_INCOME_RESPONSE = '6200.00';

    public function test_it_creates_a_customer_with_valid_data(): void
    {
        $response = $this->postJson('/api/clientes', $this->validPayload());

        $response->assertCreated()
            ->assertJsonPath('data.nome', self::CUSTOMER_NAME)
            ->assertJsonPath('data.cpf', self::VALID_CPF)
            ->assertJsonPath('data.email', self::PRIMARY_EMAIL);

        $this->assertDatabaseHas('clientes', [
            'nome' => self::CUSTOMER_NAME,
            'cpf' => self::VALID_CPF,
            'email' => self::PRIMARY_EMAIL,
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
        Customer::factory()->create(['cpf' => self::VALID_CPF]);

        $this->postJson('/api/clientes', $this->validPayload([
            'email' => self::SECONDARY_EMAIL,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_it_fails_validation_when_creating_a_customer_with_invalid_cpf(): void
    {
        $this->postJson('/api/clientes', $this->validPayload([
            'cpf' => self::INVALID_CPF,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_it_fails_validation_when_creating_a_customer_with_negative_monthly_income(): void
    {
        $this->postJson('/api/clientes', $this->validPayload([
            'renda_mensal' => self::NEGATIVE_MONTHLY_INCOME,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['renda_mensal']);
    }

    public function test_it_fails_validation_when_creating_a_customer_with_duplicated_email(): void
    {
        Customer::factory()->create(['email' => self::PRIMARY_EMAIL]);

        $this->postJson('/api/clientes', $this->validPayload([
            'cpf' => self::SECOND_VALID_CPF,
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
        Customer::factory()->count(self::CUSTOMERS_TO_SPAN_TWO_PAGES)->create();

        $this->getJson('/api/clientes')
            ->assertOk()
            ->assertJsonCount(Pagination::CUSTOMERS_PER_PAGE, 'data')
            ->assertJsonPath('meta.current_page', self::FIRST_PAGE)
            ->assertJsonPath('meta.per_page', Pagination::CUSTOMERS_PER_PAGE);

        $this->getJson('/api/clientes?page='.self::SECOND_PAGE)
            ->assertOk()
            ->assertJsonCount(self::CUSTOMERS_ON_SECOND_PAGE, 'data')
            ->assertJsonPath('meta.current_page', self::SECOND_PAGE)
            ->assertJsonPath('meta.per_page', Pagination::CUSTOMERS_PER_PAGE);
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
        $this->getJson('/api/clientes/'.self::MISSING_CUSTOMER_ID)
            ->assertNotFound();
    }

    public function test_it_partially_updates_an_existing_customer(): void
    {
        $customer = Customer::factory()->create();

        $this->patchJson("/api/clientes/{$customer->id}", [
            'telefone' => self::PARTIAL_UPDATE_PHONE,
            'renda_mensal' => self::PARTIAL_UPDATE_MONTHLY_INCOME,
        ])
            ->assertOk()
            ->assertJsonPath('data.telefone', self::PARTIAL_UPDATE_PHONE)
            ->assertJsonPath('data.renda_mensal', self::PARTIAL_UPDATE_MONTHLY_INCOME_RESPONSE);

        $this->assertDatabaseHas('clientes', [
            'id' => $customer->id,
            'telefone' => self::PARTIAL_UPDATE_PHONE,
            'renda_mensal' => self::PARTIAL_UPDATE_MONTHLY_INCOME,
        ]);
    }

    public function test_it_updates_an_existing_customer_with_put(): void
    {
        $customer = Customer::factory()->create();

        $payload = $this->validPayload([
            'nome' => self::UPDATED_CUSTOMER_NAME,
            'cpf' => self::UPDATED_VALID_CPF,
            'email' => self::UPDATED_EMAIL,
            'telefone' => self::UPDATED_CUSTOMER_PHONE,
            'renda_mensal' => self::UPDATED_MONTHLY_INCOME,
        ]);

        $this->putJson("/api/clientes/{$customer->id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.nome', self::UPDATED_CUSTOMER_NAME)
            ->assertJsonPath('data.cpf', self::UPDATED_VALID_CPF)
            ->assertJsonPath('data.email', self::UPDATED_EMAIL)
            ->assertJsonPath('data.telefone', self::UPDATED_CUSTOMER_PHONE)
            ->assertJsonPath('data.renda_mensal', self::UPDATED_MONTHLY_INCOME_RESPONSE);

        $this->assertDatabaseHas('clientes', [
            'id' => $customer->id,
            'nome' => self::UPDATED_CUSTOMER_NAME,
            'cpf' => self::UPDATED_VALID_CPF,
            'email' => self::UPDATED_EMAIL,
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
        $this->deleteJson('/api/clientes/'.self::MISSING_CUSTOMER_ID)
            ->assertNotFound();
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        $customer = Customer::factory()->make([
            'nome' => self::CUSTOMER_NAME,
            'cpf' => self::VALID_CPF,
            'email' => self::PRIMARY_EMAIL,
            'telefone' => self::CUSTOMER_PHONE,
            'renda_mensal' => self::DEFAULT_MONTHLY_INCOME,
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
