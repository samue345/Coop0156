<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidCpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('O :attribute deve ser um CPF válido.');

            return;
        }

        if (! preg_match('/^\d{11}$/', $value) || preg_match('/^(\d)\1{10}$/', $value)) {
            $fail('O :attribute deve ser um CPF válido.');

            return;
        }

        $cpf = $value;

        $firstDigit = $this->calculateCheckDigit(substr($cpf, 0, 9));
        $secondDigit = $this->calculateCheckDigit(substr($cpf, 0, 9) . $firstDigit);

        if ($cpf !== substr($cpf, 0, 9) . $firstDigit . $secondDigit) {
            $fail('O :attribute deve ser um CPF válido.');
        }
    }

    private function calculateCheckDigit(string $digits): int
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
