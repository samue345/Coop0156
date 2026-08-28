<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidCpf;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidCpfTest extends TestCase
{
    public function test_it_accepts_a_valid_cpf(): void
    {
        $validator = Validator::make(['cpf' => '52998224725'], [
            'cpf' => [new ValidCpf()],
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_it_rejects_an_invalid_cpf(): void
    {
        $validator = Validator::make(['cpf' => '52998224726'], [
            'cpf' => [new ValidCpf()],
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_it_rejects_a_cpf_with_repeated_digits(): void
    {
        $validator = Validator::make(['cpf' => '11111111111'], [
            'cpf' => [new ValidCpf()],
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_it_rejects_a_cpf_with_a_different_length(): void
    {
        $validator = Validator::make(['cpf' => '5299822472'], [
            'cpf' => [new ValidCpf()],
        ]);

        $this->assertTrue($validator->fails());
    }
}
