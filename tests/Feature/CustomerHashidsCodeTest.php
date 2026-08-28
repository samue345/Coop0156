<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Support\HashidsCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerHashidsCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_exposes_hashids_code(): void
    {
        $customer = Customer::create([
            'nome' => 'John Doe',
            'cpf' => '12345678901',
            'email' => 'john@example.com',
            'renda_mensal' => 3000,
        ]);

        $hashidsCode = $customer->hashids_code;

        $this->assertIsString($hashidsCode);
        $this->assertSame($customer->id, HashidsCode::decode($hashidsCode, Customer::class));
        $this->assertSame($customer->id, Customer::findByHashidsCode($hashidsCode)?->id);
        $this->assertArrayHasKey('hashids_code', $customer->toArray());
    }
}
