<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    private static int $cpfSequence = 100000000;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'cpf' => $this->validCpf(),
            'email' => fake()->unique()->safeEmail(),
            'telefone' => fake()->numerify('###########'),
            'renda_mensal' => fake()->randomFloat(2, 1500, 15000),
        ];
    }

    private function validCpf(): string
    {
        $base = str_pad((string) self::$cpfSequence++, 9, '0', STR_PAD_LEFT);

        return $base
            . $this->checkDigit($base)
            . $this->checkDigit($base . $this->checkDigit($base));
    }

    private function checkDigit(string $digits): int
    {
        $sum = 0;
        $weight = strlen($digits) + 1;

        foreach (str_split($digits) as $digit) {
            $sum += (int) $digit * $weight--;
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
